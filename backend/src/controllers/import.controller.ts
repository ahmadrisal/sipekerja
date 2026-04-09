import { Request, Response } from 'express';
import * as XLSX from 'xlsx';
import bcrypt from 'bcryptjs';
import prisma from '../config/prisma';

/**
 * Download Excel template for employee import
 */
export const downloadTemplate = async (_req: Request, res: Response): Promise<void> => {
    try {
        const wb = XLSX.utils.book_new();
        const header = ['NIP', 'Username', 'Nama Lengkap', 'Email'];
        const exampleRows = [
            ['123456', 'johndoe', 'John Doe', 'john@example.com'],
            ['789012', 'janesmith', 'Jane Smith', 'jane@example.com'],
        ];
        const ws = XLSX.utils.aoa_to_sheet([header, ...exampleRows]);

        // Set column widths
        ws['!cols'] = [{ wch: 15 }, { wch: 20 }, { wch: 30 }, { wch: 30 }];

        XLSX.utils.book_append_sheet(wb, ws, 'Template Pegawai');
        const buf = XLSX.write(wb, { type: 'buffer', bookType: 'xlsx' });

        res.setHeader('Content-Disposition', 'attachment; filename=template_import_pegawai.xlsx');
        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.send(buf);
    } catch (error) {
        res.status(500).json({ error: 'Gagal membuat template.' });
    }
};

/**
 * Parse uploaded Excel and return preview data (without saving)
 */
export const previewImport = async (req: Request, res: Response): Promise<void> => {
    try {
        if (!req.file) {
            res.status(400).json({ error: 'File tidak ditemukan.' });
            return;
        }

        const wb = XLSX.read(req.file.buffer, { type: 'buffer' });
        const ws = wb.Sheets[wb.SheetNames[0]];
        const rows: any[] = XLSX.utils.sheet_to_json(ws, { header: ['nip', 'username', 'nama', 'email'], range: 1 });

        if (rows.length === 0) {
            res.status(400).json({ error: 'File kosong atau format tidak sesuai.' });
            return;
        }

        // Validate and check for existing NIPs
        const existingUsers = await prisma.user.findMany({ select: { nip: true } });
        const existingNips = new Set(existingUsers.map(u => u.nip));

        const validRows: any[] = [];
        const errors: string[] = [];

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const rowNum = i + 2; // Excel row (header is row 1)
            if (!row.nip || !row.nama) {
                errors.push(`Baris ${rowNum}: NIP dan Nama Lengkap wajib diisi.`);
                continue;
            }
            const nipStr = String(row.nip).trim();
            if (existingNips.has(nipStr)) {
                errors.push(`Baris ${rowNum}: NIP ${nipStr} sudah terdaftar di sistem.`);
                continue;
            }
            validRows.push({
                nip: nipStr,
                username: row.username ? String(row.username).trim() : null,
                name: String(row.nama).trim(),
                email: row.email ? String(row.email).trim() : null,
            });
        }

        res.json({ total: rows.length, valid: validRows.length, errors, preview: validRows });
    } catch (error) {
        res.status(500).json({ error: 'Gagal membaca file Excel.' });
    }
};

/**
 * Actually import employees from the validated data
 */
export const executeImport = async (req: Request, res: Response): Promise<void> => {
    try {
        const { employees } = req.body;

        if (!employees || !Array.isArray(employees) || employees.length === 0) {
            res.status(400).json({ error: 'Data pegawai kosong.' });
            return;
        }

        // Find "Pegawai" role
        const pegawaiRole = await prisma.role.findFirst({ where: { roleName: 'Pegawai' } });
        if (!pegawaiRole) {
            res.status(500).json({ error: 'Role Pegawai belum tersedia di sistem.' });
            return;
        }

        const defaultPassword = await bcrypt.hash('pegawai123', 10);
        let created = 0;

        for (const emp of employees) {
            try {
                const user = await prisma.user.create({
                    data: {
                        nip: String(emp.nip),
                        username: emp.username || null,
                        name: emp.name,
                        email: emp.email || null,
                        password: defaultPassword,
                    },
                });

                // Assign "Pegawai" role
                await prisma.userRole.create({
                    data: { userId: user.id, roleId: pegawaiRole.id },
                });

                created++;
            } catch (e: any) {
                // Skip duplicates silently
                console.error(`Skipped ${emp.nip}:`, e.message);
            }
        }

        res.status(201).json({ message: `${created} pegawai berhasil diimport.`, created });
    } catch (error) {
        res.status(500).json({ error: 'Gagal mengimport pegawai.' });
    }
};
