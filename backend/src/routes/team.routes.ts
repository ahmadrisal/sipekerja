import { Router } from 'express';
import { getTeams, createTeam, updateTeam, deleteTeam } from '../controllers/team.controller';
import { authenticateToken } from '../middleware/auth.middleware';
import { requireRole } from '../middleware/rbac.middleware';

const router = Router();

router.use(authenticateToken);
// Admin and Pimpinan can view teams
router.get('/', requireRole(['Admin', 'Pimpinan']), getTeams);

// Only Admin can manage (CRUD) teams
router.use(requireRole(['Admin']));

router.post('/', createTeam);
router.put('/:id', updateTeam);
router.delete('/:id', deleteTeam);

export default router;
