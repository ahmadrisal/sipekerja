import { Request, Response } from 'express';
import bcrypt from 'bcryptjs';
import prisma from '../config/prisma';

export const getUsers = async (req: Request, res: Response): Promise<void> => {
    try {
        const users = await prisma.user.findMany({
            select: {
                id: true,
                nip: true,
                username: true,
                name: true,
                email: true,
                createdAt: true,
                userRoles: {
                    include: { role: true },
                },
                ledTeams: true,
                teamMembers: {
                    include: { team: true },
                },
            },
        });
        res.json(users);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const getAdminStats = async (req: Request, res: Response): Promise<void> => {
    try {
        const users = await prisma.user.findMany({
            select: { id: true, nip: true, name: true, teamMembers: { include: { team: true } } }
        });
        const teams = await prisma.team.findMany({
            include: { members: { include: { user: true } } }
        });

        const unassignedUsers = users.filter(u => u.teamMembers.length === 0);

        let largestTeam = { teamName: '-', count: 0 };
        let totalTeamMembers = 0;
        const teamDetails = teams.map(t => {
            const count = t.members.length;
            totalTeamMembers += count;
            if (count > largestTeam.count) largestTeam = { teamName: t.teamName, count };
            return {
                id: t.id,
                teamName: t.teamName,
                memberCount: count,
                members: t.members.map(m => m.user.name)
            };
        });

        const avgMembersPerTeam = teams.length > 0 ? (totalTeamMembers / teams.length).toFixed(1) : 0;

        let mostTeamsEmployee = { name: '-', count: 0 };
        let minTeams = users.length > 0 ? users[0].teamMembers.length : 0;
        let totalTeamsAssigned = 0;

        const userDetails = users.map(u => {
            const count = u.teamMembers.length;
            totalTeamsAssigned += count;
            if (count > mostTeamsEmployee.count) mostTeamsEmployee = { name: u.name, count };
            if (count < minTeams) minTeams = count;
            return {
                id: u.id,
                nip: u.nip,
                name: u.name,
                teamNames: u.teamMembers.map(tm => tm.team.teamName)
            };
        });

        const avgTeamsPerEmployee = users.length > 0 ? (totalTeamsAssigned / users.length).toFixed(1) : 0;

        res.json({
            stats: {
                totalUsers: users.length,
                totalTeams: teams.length,
                unassignedUsersCount: unassignedUsers.length,
                largestTeam,
                avgMembersPerTeam,
                mostTeamsEmployee,
                minTeamsPerEmployee: minTeams,
                avgTeamsPerEmployee
            },
            userDetails,
            teamDetails,
            unassignedUsers: unassignedUsers.map(u => ({ id: u.id, nip: u.nip, name: u.name }))
        });
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const createUser = async (req: Request, res: Response): Promise<void> => {
    try {
        const { nip, username, name, email, password, roleIds } = req.body;
        if (!nip || !name || !email || !password) {
            res.status(400).json({ error: 'NIP, name, email, and password are required' });
            return;
        }

        const hashedPassword = await bcrypt.hash(password, 10);

        const user = await prisma.user.create({
            data: {
                nip,
                username: username || null,
                name,
                email,
                password: hashedPassword,
                userRoles: {
                    create: (roleIds || []).map((id: string) => ({ role: { connect: { id } } })),
                },
            },
            select: { id: true, nip: true, username: true, name: true, email: true },
        });

        res.status(201).json(user);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const updateUser = async (req: Request, res: Response): Promise<void> => {
    try {
        const { id } = req.params;
        const { name, email, username, roleIds } = req.body;

        const data: any = {};
        if (name) data.name = name;
        if (email) data.email = email;
        if (username !== undefined) data.username = username || null;

        if (roleIds) {
            // Clear existing roles and assign new ones
            await prisma.userRole.deleteMany({ where: { userId: id } });
            data.userRoles = {
                create: roleIds.map((rId: string) => ({ role: { connect: { id: rId } } })),
            };
        }

        const user = await prisma.user.update({
            where: { id },
            data,
            select: { id: true, nip: true, username: true, name: true, email: true },
        });
        res.json(user);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const deleteUser = async (req: Request, res: Response): Promise<void> => {
    try {
        const { id } = req.params;
        await prisma.user.delete({ where: { id } });
        res.status(204).send();
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const resetPassword = async (req: Request, res: Response): Promise<void> => {
    try {
        const { id } = req.params;
        const { newPassword } = req.body;

        if (!newPassword || newPassword.trim().length === 0) {
            res.status(400).json({ error: 'Password baru tidak boleh kosong atau hanya berisi spasi.' });
            return;
        }

        const user = await prisma.user.findUnique({ where: { id } });
        if (!user) {
            res.status(404).json({ error: 'User tidak ditemukan.' });
            return;
        }

        const hashedPassword = await bcrypt.hash(newPassword, 10);
        await prisma.user.update({
            where: { id },
            data: { password: hashedPassword },
        });

        res.json({ message: `Password untuk ${user.name} berhasil direset.` });
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};
