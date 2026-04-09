import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
    console.log('Start seeding...');

    try {
        // 1. Roles
        const rolesData = ['Admin', 'Pimpinan', 'Ketua Tim', 'Pegawai'];
        for (const r of rolesData) {
            await prisma.role.upsert({
                where: { roleName: r },
                update: {},
                create: { roleName: r },
            });
        }
        const adminRole = await prisma.role.findUnique({ where: { roleName: 'Admin' } });
        const pimpinanRole = await prisma.role.findUnique({ where: { roleName: 'Pimpinan' } });
        const ketuaTimRole = await prisma.role.findUnique({ where: { roleName: 'Ketua Tim' } });
        const pegawaiRole = await prisma.role.findUnique({ where: { roleName: 'Pegawai' } });

        if (!adminRole || !ketuaTimRole || !pegawaiRole || !pimpinanRole) throw new Error('Roles are missing');

        // 2. Admin User
        const hashedAdminPassword = await bcrypt.hash('admin123', 10);
        const admin = await prisma.user.upsert({
            where: { nip: '111111' },
            update: {},
            create: {
                nip: '111111',
                name: 'Administrator',
                email: 'admin@sipekerja.local',
                password: hashedAdminPassword,
                userRoles: {
                    create: [{ roleId: adminRole.id }],
                },
            },
        });

        // 3. Pimpinan User
        const hashedPimpinanPassword = await bcrypt.hash('pimpinan123', 10);
        const pimpinan = await prisma.user.upsert({
            where: { nip: '123456' },
            update: {},
            create: {
                nip: '123456',
                name: 'Kepala BPS',
                email: 'kepala@sipekerja.local',
                password: hashedPimpinanPassword,
                userRoles: {
                    create: [{ roleId: pimpinanRole.id }],
                },
            },
        });

        // 4. Ketua Tim User (also has Pegawai role)
        const hashedKetuaPassword = await bcrypt.hash('ketua123', 10);
        const ketua = await prisma.user.upsert({
            where: { nip: '222222' },
            update: {},
            create: {
                nip: '222222',
                name: 'Budi Ketua',
                email: 'budi@sipekerja.local',
                password: hashedKetuaPassword,
                userRoles: {
                    create: [{ roleId: ketuaTimRole.id }, { roleId: pegawaiRole.id }],
                },
            },
        });

        // 5. Pegawai
        const hashedPegawaiPassword = await bcrypt.hash('pegawai123', 10);
        const pegawai = await prisma.user.upsert({
            where: { nip: '333333' },
            update: {},
            create: {
                nip: '333333',
                name: 'Ani Pegawai',
                email: 'ani@sipekerja.local',
                password: hashedPegawaiPassword,
                userRoles: {
                    create: [{ roleId: pegawaiRole.id }],
                },
            },
        });

        console.log('Seeding done. Use NIP 111111 (Admin, admin123), 123456 (Pimpinan), or 222222 (Ketua Tim)');
    } catch (err) {
        console.error(err);
    } finally {
        await prisma.$disconnect();
    }
}

main();
