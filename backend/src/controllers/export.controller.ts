import { Request, Response } from 'express';
import ExcelJS from 'exceljs';
import PDFDocument from 'pdfkit';
import prisma from '../config/prisma';

export const exportExcel = async (req: Request, res: Response): Promise<void> => {
    try {
        const month = parseInt(req.query.month as string);
        const year = parseInt(req.query.year as string);

        if (!month || !year) {
            res.status(400).json({ error: 'Month and Year parameters are required for export' });
            return;
        }

        const ratings = await prisma.rating.findMany({
            where: { periodMonth: month, periodYear: year },
            include: {
                targetUser: { select: { nip: true, name: true } },
                evaluator: { select: { nip: true, name: true } },
            },
        });

        const workbook = new ExcelJS.Workbook();
        const sheet = workbook.addWorksheet('Rekapitulasi Kinerja');

        sheet.columns = [
            { header: 'NIP Pegawai', key: 'targetNip', width: 20 },
            { header: 'Nama Pegawai', key: 'targetName', width: 30 },
            { header: 'Nilai Kinerja', key: 'score', width: 15 },
            { header: 'Catatan', key: 'notes', width: 40 },
            { header: 'Evaluator', key: 'evaluatorName', width: 30 },
        ];

        ratings.forEach((r: any) => {
            sheet.addRow({
                targetNip: r.targetUser.nip,
                targetName: r.targetUser.name,
                score: r.score,
                notes: r.notes || '-',
                evaluatorName: r.evaluator.name,
            });
        });

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', 'attachment; filename="rekapitulasi-kinerja.xlsx"');

        await workbook.xlsx.write(res);
        res.end();
    } catch (error) {
        res.status(500).json({ error: 'Failed to generate Excel report' });
    }
};

export const exportPdf = async (req: Request, res: Response): Promise<void> => {
    try {
        const month = parseInt(req.query.month as string);
        const year = parseInt(req.query.year as string);

        if (!month || !year) {
            res.status(400).json({ error: 'Month and Year parameters are required for export' });
            return;
        }

        const ratings = await prisma.rating.findMany({
            where: { periodMonth: month, periodYear: year },
            include: { targetUser: { select: { nip: true, name: true } } },
        });

        const doc = new PDFDocument();

        res.setHeader('Content-Type', 'application/pdf');
        res.setHeader('Content-Disposition', 'attachment; filename="rekapitulasi-kinerja.pdf"');

        doc.pipe(res);

        doc.fontSize(20).text('Laporan Rekapitulasi Kinerja Pegawai', { align: 'center' });
        doc.moveDown();

        ratings.forEach((r: any) => {
            doc.fontSize(12).text(`NIP: ${r.targetUser.nip} | Nama: ${r.targetUser.name} | Nilai: ${r.score}`);
        });

        doc.end();
    } catch (error) {
        res.status(500).json({ error: 'Failed to generate PDF report' });
    }
};
