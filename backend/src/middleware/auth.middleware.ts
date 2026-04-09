import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';

const JWT_SECRET = process.env.JWT_SECRET || 'fallback_secret';

export interface AuthRequest extends Request {
    user?: {
        id: string;
        nip: string;
        roles: string[];
    };
}

export const authenticateToken = (req: AuthRequest, res: Response, next: NextFunction): void => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (!token) {
        res.status(401).json({ error: 'Access token missing' });
        return;
    }

    jwt.verify(token, JWT_SECRET, (err, user) => {
        if (err) {
            res.status(403).json({ error: 'Invalid token' });
            return;
        }
        req.user = user as any;
        next();
    });
};
