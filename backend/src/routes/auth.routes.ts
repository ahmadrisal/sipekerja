import { Router } from 'express';
import { login, getProfile, changePassword } from '../controllers/auth.controller';
import { authenticateToken } from '../middleware/auth.middleware';

const router = Router();

router.post('/login', login);
router.get('/profile', authenticateToken, getProfile);
router.put('/change-password', authenticateToken, changePassword);

export default router;
