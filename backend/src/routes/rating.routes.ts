import { Router } from 'express';
import { getRatings, createRating, getMyMembers, getMyRatings, updateRating, deleteRating, getKetuaTimStats, getPimpinanRekap, getPegawaiDashboard } from '../controllers/rating.controller';
import { authenticateToken } from '../middleware/auth.middleware';
import { requireRole } from '../middleware/rbac.middleware';

const router = Router();

router.use(authenticateToken);

// View all ratings (Admin/Pimpinan)
router.get('/', getRatings);

// Pimpinan: rekap total rata rata
router.get('/pimpinan-rekap', requireRole(['Pimpinan']), getPimpinanRekap);

// Ketua Tim: dashboard stats
router.get('/my-stats', requireRole(['Ketua Tim']), getKetuaTimStats);

// Ketua Tim: get deduplicated members to evaluate
router.get('/my-members', requireRole(['Ketua Tim']), getMyMembers);

// Ketua Tim: get own ratings for a period
router.get('/my-ratings', requireRole(['Ketua Tim']), getMyRatings);

// Pegawai/Pimpinan/Admin: read-only dashboard of performance
router.get('/pegawai-dashboard', requireRole(['Pegawai', 'Pimpinan', 'Admin']), getPegawaiDashboard);

// Only Ketua Tim can create evaluation ratings
router.post('/', requireRole(['Ketua Tim']), createRating);

// Ketua Tim can update their own ratings
router.put('/:id', requireRole(['Ketua Tim']), updateRating);

// Ketua Tim can reset (delete) their own ratings
router.delete('/:id', requireRole(['Ketua Tim']), deleteRating);

export default router;
