import { Request, Response } from 'express';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import prisma from '../config/prisma';

const JWT_SECRET = process.env.JWT_SECRET || 'fallback_secret';

export const login = async (req: Request, res: Response): Promise<void> => {
    try {
        const { username, password } = req.body;

        if (!username || !password) {
            res.status(400).json({ error: 'Username dan password wajib diisi.' });
            return;
        }

        const user = await prisma.user.findUnique({
            where: { username },
            include: {
                userRoles: {
                    include: { role: true },
                },
            },
        });

        if (!user) {
            res.status(401).json({ error: 'Username atau password salah.' });
            return;
        }

        const validPassword = await bcrypt.compare(password, user.password);
        if (!validPassword) {
            res.status(401).json({ error: 'Username atau password salah.' });
            return;
        }

        const roles = user.userRoles.map((ur: any) => ur.role.roleName);

        const payload = {
            id: user.id,
            nip: user.nip,
            username: user.username,
            roles,
        };

        const token = jwt.sign(payload, JWT_SECRET, { expiresIn: '1d' });

        res.json({
            message: 'Login successful',
            token,
            user: {
                id: user.id,
                nip: user.nip,
                username: user.username,
                name: user.name,
                email: user.email,
                roles,
            },
        });
    } catch (error) {
        console.error('Login error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const getProfile = async (req: Request, res: Response): Promise<void> => {
    try {
        const userId = (req as any).user?.id;
        if (!userId) {
            res.status(401).json({ error: 'Unauthorized' });
            return;
        }

        const user = await prisma.user.findUnique({
            where: { id: userId },
            select: {
                id: true,
                nip: true,
                username: true,
                name: true,
                email: true,
                userRoles: { include: { role: true } }
            }
        });

        if (!user) {
            res.status(404).json({ error: 'User not found' });
            return;
        }

        res.json({
            ...user,
            roles: user.userRoles.map((ur: any) => ur.role.roleName)
        });
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const changePassword = async (req: Request, res: Response): Promise<void> => {
    try {
        const userId = (req as any).user?.id;
        if (!userId) {
            res.status(401).json({ error: 'Unauthorized' });
            return;
        }

        const { currentPassword, newPassword } = req.body;

        if (!currentPassword || !newPassword) {
            res.status(400).json({ error: 'Password lama dan password baru wajib diisi.' });
            return;
        }

        // Validate: no spaces only
        if (newPassword.trim().length === 0) {
            res.status(400).json({ error: 'Password baru tidak boleh hanya berisi spasi.' });
            return;
        }

        const user = await prisma.user.findUnique({ where: { id: userId } });
        if (!user) {
            res.status(404).json({ error: 'User tidak ditemukan.' });
            return;
        }

        const validOld = await bcrypt.compare(currentPassword, user.password);
        if (!validOld) {
            res.status(400).json({ error: 'Password lama tidak sesuai.' });
            return;
        }

        const hashedNew = await bcrypt.hash(newPassword, 10);
        await prisma.user.update({
            where: { id: userId },
            data: { password: hashedNew },
        });

        res.json({ message: 'Password berhasil diubah.' });
    } catch (error) {
        console.error('Change password error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
};
