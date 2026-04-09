import { Request, Response } from 'express';
import prisma from '../config/prisma';

export const getRoles = async (req: Request, res: Response): Promise<void> => {
    try {
        const roles = await prisma.role.findMany();
        res.json(roles);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const createRole = async (req: Request, res: Response): Promise<void> => {
    try {
        const { roleName } = req.body;
        if (!roleName) {
            res.status(400).json({ error: 'Role name is required' });
            return;
        }
        const role = await prisma.role.create({
            data: { roleName },
        });
        res.status(201).json(role);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const updateRole = async (req: Request, res: Response): Promise<void> => {
    try {
        const { id } = req.params;
        const { roleName } = req.body;
        const role = await prisma.role.update({
            where: { id },
            data: { roleName },
        });
        res.json(role);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const deleteRole = async (req: Request, res: Response): Promise<void> => {
    try {
        const { id } = req.params;
        await prisma.role.delete({ where: { id } });
        res.status(204).send();
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};
