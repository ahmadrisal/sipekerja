import { Response, NextFunction } from 'express';
import { AuthRequest } from './auth.middleware';

export const requireRole = (allowedRoles: string[]) => {
    return (req: AuthRequest, res: Response, next: NextFunction): void => {
        const userRoles = req.user?.roles || [];

        // Check if user has at least one of the allowed roles
        const hasRole = userRoles.some(role => allowedRoles.includes(role));

        if (!hasRole) {
            res.status(403).json({ error: 'Forbidden: Insufficient permissions' });
            return;
        }

        next();
    };
};
