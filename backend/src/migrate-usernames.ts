import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
    console.log('Setting usernames for existing users...');

    // Known users with specific username/password mappings
    const knownUsers: { nip: string; username: string; password: string }[] = [
        { nip: '111111', username: 'admin', password: 'admin' },
        { nip: '123456', username: 'bos', password: 'bos' },
        { nip: '222222', username: 'ketua1', password: 'ketua1' },
    ];

    for (const ku of knownUsers) {
        const user = await prisma.user.findUnique({ where: { nip: ku.nip } });
        if (user) {
            // Check if username is already taken by this user or available
            const existingWithUsername = await prisma.user.findUnique({ where: { username: ku.username } });
            if (existingWithUsername && existingWithUsername.id !== user.id) {
                console.log(`Username "${ku.username}" already taken by another user, skipping NIP ${ku.nip}`);
                continue;
            }
            const hashedPw = await bcrypt.hash(ku.password, 10);
            await prisma.user.update({
                where: { id: user.id },
                data: {
                    username: ku.username,
                    password: hashedPw,
                },
            });
            console.log(`Updated user ${user.name}: username=${ku.username}`);
        } else {
            console.log(`User with NIP ${ku.nip} not found, skipping`);
        }
    }

    // Also try to find users by their current username (if they were created with username already)
    const usernameMap: { [username: string]: string } = {
        'admin': 'admin',
        'bos': 'bos',
        'ketua1': 'ketua1',
    };
    for (const [uname, pw] of Object.entries(usernameMap)) {
        const user = await prisma.user.findUnique({ where: { username: uname } });
        if (user) {
            const hashedPw = await bcrypt.hash(pw, 10);
            await prisma.user.update({
                where: { id: user.id },
                data: { password: hashedPw },
            });
            console.log(`Reset password for ${user.name} (username: ${uname})`);
        }
    }

    // For any remaining users without a username, auto-generate one from their name
    const usersWithoutUsername = await prisma.user.findMany({
        where: { username: null },
    });

    for (const u of usersWithoutUsername) {
        let baseUsername = u.name.toLowerCase().replace(/\s+/g, '.').replace(/[^a-z0-9.]/g, '');
        if (!baseUsername) baseUsername = `user_${u.nip}`;

        let candidate = baseUsername;
        let counter = 1;
        while (true) {
            const existing = await prisma.user.findUnique({ where: { username: candidate } });
            if (!existing) break;
            candidate = `${baseUsername}${counter}`;
            counter++;
        }

        await prisma.user.update({
            where: { id: u.id },
            data: { username: candidate },
        });
        console.log(`Set username for ${u.name}: ${candidate}`);
    }

    // Show all users summary
    const allUsers = await prisma.user.findMany({ select: { nip: true, username: true, name: true } });
    console.log('\n=== All Users Summary ===');
    allUsers.forEach(u => console.log(`  NIP: ${u.nip} | Username: ${u.username || 'NULL'} | Name: ${u.name}`));
    console.log('\nDone!');
}

main()
    .catch(console.error)
    .finally(() => prisma.$disconnect());
