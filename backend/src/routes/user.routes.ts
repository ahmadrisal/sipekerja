import { Router } from 'express';
import { getUsers, createUser, updateUser, deleteUser, getAdminStats, resetPassword } from '../controllers/user.controller';
import { authenticateToken } from '../middleware/auth.middleware';
import { requireRole } from '../middleware/rbac.middleware';

const router = Router();

router.use(authenticateToken);
// Admin manages users
router.use(requireRole(['Admin']));

router.get('/admin-stats', getAdminStats);
router.get('/', getUsers);
router.post('/', createUser);
router.put('/:id', updateUser);
router.put('/:id/reset-password', resetPassword);
router.delete('/:id', deleteUser);

export default router;
