import { Request, Response } from 'express';
import prisma from '../config/prisma';

/**
 * Auto-assign the "Ketua Tim" role to the given user if they don't already have it.
 */
async function ensureKetuaTimRole(userId: string): Promise<void> {
    const ketuaTimRole = await prisma.role.findUnique({ where: { roleName: 'Ketua Tim' } });
    if (!ketuaTimRole) return;

    const existing = await prisma.userRole.findUnique({
        where: { userId_roleId: { userId, roleId: ketuaTimRole.id } },
    });

    if (!existing) {
        await prisma.userRole.create({
            data: { userId, roleId: ketuaTimRole.id },
        });
    }
}

export const getTeams = async (req: Request, res: Response): Promise<void> => {
    try {
        const teams = await prisma.team.findMany({
            include: {
                leader: { select: { id: true, name: true, nip: true } },
                members: {
                    include: { user: { select: { id: true, name: true, nip: true } } },
                },
            },
        });
        res.json(teams);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const createTeam = async (req: Request, res: Response): Promise<void> => {
    try {
        const { teamName, leaderId, memberIds } = req.body;
        if (!teamName) {
            res.status(400).json({ error: 'Team name is required' });
            return;
        }

        const team = await prisma.team.create({
            data: {
                teamName,
                leaderId: leaderId || null,
                members: {
                    create: (memberIds || []).map((userId: string) => ({ user: { connect: { id: userId } } })),
                },
            },
            include: { leader: true, members: true },
        });

        // Auto-assign "Ketua Tim" role to the leader
        if (leaderId) {
            await ensureKetuaTimRole(leaderId);
        }

        res.status(201).json(team);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const updateTeam = async (req: Request, res: Response): Promise<void> => {
    try {
        const { id } = req.params;
        const { teamName, leaderId, memberIds } = req.body;

        const data: any = {};
        if (teamName) data.teamName = teamName;
        if (leaderId !== undefined) data.leaderId = leaderId;

        if (memberIds) {
            await prisma.teamMember.deleteMany({ where: { teamId: id } });
            data.members = {
                create: memberIds.map((userId: string) => ({ user: { connect: { id: userId } } })),
            };
        }

        const team = await prisma.team.update({
            where: { id },
            data,
        });

        // Auto-assign "Ketua Tim" role to the new leader
        if (leaderId) {
            await ensureKetuaTimRole(leaderId);
        }

        res.json(team);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const deleteTeam = async (req: Request, res: Response): Promise<void> => {
    try {
        const { id } = req.params;
        await prisma.team.delete({ where: { id } });
        res.status(204).send();
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};
