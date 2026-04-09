import { Router } from 'express';
import { exportExcel, exportPdf } from '../controllers/export.controller';
import { authenticateToken } from '../middleware/auth.middleware';
import { requireRole } from '../middleware/rbac.middleware';

const router = Router();

// Only Pimpinan or Admin can export data
router.use(authenticateToken);
router.use(requireRole(['Admin', 'Pimpinan']));

router.get('/excel', exportExcel);
router.get('/pdf', exportPdf);

export default router;
