import { Router } from 'express';
import multer from 'multer';
import { downloadTemplate, previewImport, executeImport } from '../controllers/import.controller';
import { authenticateToken } from '../middleware/auth.middleware';
import { requireRole } from '../middleware/rbac.middleware';

const router = Router();
const upload = multer({ storage: multer.memoryStorage() });

router.use(authenticateToken);
router.use(requireRole(['Admin']));

// Download Excel template
router.get('/template', downloadTemplate);

// Preview uploaded Excel (parse without saving)
router.post('/preview', upload.single('file'), previewImport);

// Execute import from parsed data
router.post('/execute', executeImport);

export default router;
