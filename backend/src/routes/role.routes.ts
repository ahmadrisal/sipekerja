import { Router } from 'express';
import { getRoles, createRole, updateRole, deleteRole } from '../controllers/role.controller';
import { authenticateToken } from '../middleware/auth.middleware';
import { requireRole } from '../middleware/rbac.middleware';

const router = Router();

router.use(authenticateToken);
// Only Admin can manage roles
router.use(requireRole(['Admin']));

router.get('/', getRoles);
router.post('/', createRole);
router.put('/:id', updateRole);
router.delete('/:id', deleteRole);

export default router;
