import { Request, Response } from 'express';
import prisma from '../config/prisma';

export const getRatings = async (req: Request, res: Response): Promise<void> => {
    try {
        const ratings = await prisma.rating.findMany({
            include: {
                evaluator: { select: { name: true, nip: true } },
                targetUser: { select: { name: true, nip: true } },
                team: { select: { teamName: true } },
            },
        });
        res.json(ratings);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

const calculateFinalScore = (score: number, volumeWork?: string, qualityWork?: string): number => {
    let volScore = 80;
    if (volumeWork === 'Ringan') volScore = 60;
    else if (volumeWork === 'Berat') volScore = 100;

    let qualScore = 75;
    if (qualityWork === 'Kurang') qualScore = 50;
    else if (qualityWork === 'Baik') qualScore = 90;
    else if (qualityWork === 'Sangat Baik') qualScore = 100;

    let final = (score * 0.6) + (volScore * 0.2) + (qualScore * 0.2);
    // Round to 2 decimal places
    return Math.round(final * 100) / 100;
};

export const createRating = async (req: Request, res: Response): Promise<void> => {
    try {
        const evaluatorId = (req as any).user?.id;
        const { targetUserId, teamId, score, notes, periodMonth, periodYear, volumeWork, qualityWork } = req.body;

        if (!targetUserId || !teamId || score === undefined || !periodMonth || !periodYear) {
            res.status(400).json({ error: 'Missing required evaluation fields' });
            return;
        }

        if (score < 1 || score > 100) {
            res.status(400).json({ error: 'Score must be between 1 and 100' });
            return;
        }

        // Prevent duplicate: one evaluator → one target per team per month
        const existing = await prisma.rating.findFirst({
            where: { evaluatorId, targetUserId, teamId, periodMonth, periodYear },
        });
        if (existing) {
            res.status(409).json({ error: 'Penilaian untuk pegawai ini di tim tersebut pada periode yang sama sudah ada.' });
            return;
        }

        const finalScore = calculateFinalScore(score, volumeWork, qualityWork);

        const rating = await prisma.rating.create({
            data: {
                evaluatorId,
                targetUserId,
                teamId,
                score,
                notes,
                volumeWork,
                qualityWork,
                finalScore,
                periodMonth,
                periodYear,
            },
        });

        res.status(201).json(rating);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

/**
 * Get unique members that the current Ketua Tim should evaluate.
 * De-duplicates members across multiple teams led by the same user.
 */
export const getMyMembers = async (req: Request, res: Response): Promise<void> => {
    try {
        const userId = (req as any).user?.id;

        // Find all teams led by this user
        const ledTeams = await prisma.team.findMany({
            where: { leaderId: userId },
            include: {
                members: {
                    include: { user: { select: { id: true, nip: true, name: true } } },
                },
            },
        });

        // Deduplicate members across teams
        const memberMap = new Map<string, { id: string; nip: string; name: string; teams: { id: string; teamName: string }[] }>();

        for (const team of ledTeams) {
            for (const m of team.members) {
                if (m.user.id === userId) continue; // skip self
                if (!memberMap.has(m.user.id)) {
                    memberMap.set(m.user.id, { ...m.user, teams: [] });
                }
                memberMap.get(m.user.id)!.teams.push({ id: team.id, teamName: team.teamName });
            }
        }

        res.json(Array.from(memberMap.values()));
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

/**
 * Get Ketua Tim dashboard stats with detailed breakdowns
 */
export const getKetuaTimStats = async (req: Request, res: Response): Promise<void> => {
    try {
        const userId = (req as any).user?.id;
        const queryMonth = parseInt(req.query.month as string);
        const queryYear = parseInt(req.query.year as string);

        const now = new Date();
        const month = !isNaN(queryMonth) ? queryMonth : now.getMonth() + 1;
        const year = !isNaN(queryYear) ? queryYear : now.getFullYear();

        // Find all teams led by this user with member details
        const ledTeams = await prisma.team.findMany({
            where: { leaderId: userId },
            include: {
                members: {
                    include: { user: { select: { id: true, nip: true, name: true } } },
                },
            },
        });

        // Build team details and deduplicate members
        const teamDetails = ledTeams.map(t => ({
            id: t.id,
            teamName: t.teamName,
            members: t.members
                .filter(m => m.user.id !== userId)
                .map(m => ({ id: m.user.id, nip: m.user.nip, name: m.user.name })),
        }));

        const memberMap = new Map<string, { id: string; nip: string; name: string; teams: string[] }>();
        for (const team of ledTeams) {
            for (const m of team.members) {
                if (m.user.id === userId) continue;
                if (!memberMap.has(m.user.id)) {
                    memberMap.set(m.user.id, { id: m.user.id, nip: m.user.nip, name: m.user.name, teams: [] });
                }
                memberMap.get(m.user.id)!.teams.push(team.teamName);
            }
        }

        // Count rated members this month
        const ratingsThisMonth = await prisma.rating.findMany({
            where: {
                evaluatorId: userId,
                periodMonth: month,
                periodYear: year,
            },
            select: { targetUserId: true, teamId: true, score: true, finalScore: true },
        });
        const ratedIds = new Set(ratingsThisMonth.map(r => r.targetUserId));

        const allMembers = Array.from(memberMap.values());
        const unratedMembers = allMembers.filter(m => !ratedIds.has(m.id));

        // Compute per-team average score for chart based on MEMBERSHIP
        // If Ani (score 76) is in TPI and MR, her score counts for both teams' average
        // Using finalScore instead of base score
        const ratingMap = new Map<string, number>(); // map of "m.id-t.id" to finalScore
        for (const r of ratingsThisMonth) {
            ratingMap.set(`${r.targetUserId}-${r.teamId}`, r.finalScore || r.score);
        }

        const teamChartData = teamDetails.map(t => {
            const memberScores = t.members
                .map(m => ratingMap.get(`${m.id}-${t.id}`))
                .filter((s): s is number => s !== undefined);
            const avg = memberScores.length > 0
                ? Math.round((memberScores.reduce((a, b) => a + b, 0) / memberScores.length) * 10) / 10
                : 0;
            return { teamName: t.teamName, avgScore: avg, totalRated: memberScores.length };
        });

        res.json({
            teamCount: ledTeams.length,
            uniqueMemberCount: allMembers.length,
            ratedCount: ratedIds.size,
            unratedCount: unratedMembers.length,
            currentMonth: month,
            currentYear: year,
            teamDetails,
            unratedMembers,
            teamChartData,
        });
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

/**
 * Get ratings created by the current evaluator for a specific period
 */
export const getMyRatings = async (req: Request, res: Response): Promise<void> => {
    try {
        const evaluatorId = (req as any).user?.id;
        const { month, year } = req.query;

        const ratings = await prisma.rating.findMany({
            where: {
                evaluatorId,
                periodMonth: Number(month),
                periodYear: Number(year),
            },
            include: {
                targetUser: { select: { id: true, name: true, nip: true } },
                team: { select: { id: true, teamName: true } },
            },
        });
        res.json(ratings);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

/**
 * Update an existing rating (Ketua Tim can edit their own ratings)
 */
export const updateRating = async (req: Request, res: Response): Promise<void> => {
    try {
        const evaluatorId = (req as any).user?.id;
        const id = req.params.id as string;
        const { score, notes, volumeWork, qualityWork } = req.body;

        // Verify this rating belongs to the evaluator
        const existing = await prisma.rating.findFirst({
            where: { id, evaluatorId },
        });

        if (!existing) {
            res.status(404).json({ error: 'Penilaian tidak ditemukan atau bukan milik Anda.' });
            return;
        }

        if (score !== undefined && (score < 1 || score > 100)) {
            res.status(400).json({ error: 'Nilai harus antara 1 - 100.' });
            return;
        }

        const newScore = score !== undefined ? score : existing.score;
        const newVol = volumeWork !== undefined ? volumeWork : existing.volumeWork;
        const newQual = qualityWork !== undefined ? qualityWork : existing.qualityWork;
        const finalScore = calculateFinalScore(newScore, newVol || undefined, newQual || undefined);

        const updated = await prisma.rating.update({
            where: { id },
            data: {
                ...(score !== undefined && { score }),
                ...(notes !== undefined && { notes }),
                ...(volumeWork !== undefined && { volumeWork }),
                ...(qualityWork !== undefined && { qualityWork }),
                finalScore,
            },
        });

        res.json(updated);
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

/**
 * Delete a rating (reset) — Ketua Tim can only delete their own ratings
 */
export const deleteRating = async (req: Request, res: Response): Promise<void> => {
    try {
        const evaluatorId = (req as any).user?.id;
        const { id } = req.params;

        const existing = await prisma.rating.findFirst({
            where: { id, evaluatorId },
        });

        if (!existing) {
            res.status(404).json({ error: 'Penilaian tidak ditemukan atau bukan milik Anda.' });
            return;
        }

        await prisma.rating.delete({ where: { id } });
        res.status(204).send();
    } catch (error) {
        res.status(500).json({ error: 'Internal server error' });
    }
};

export const getPimpinanRekap = async (req: Request, res: Response): Promise<void> => {
    try {
        const month = parseInt(req.query.month as string) || new Date().getMonth() + 1;
        const year = parseInt(req.query.year as string) || new Date().getFullYear();

        // Optimized query: Fetch users with their teams and SPECIFIC ratings for the period in one go
        const users = await prisma.user.findMany({
            select: {
                id: true,
                nip: true,
                name: true,
                teamMembers: {
                    select: {
                        team: {
                            select: {
                                id: true,
                                teamName: true,
                                leader: { select: { name: true } }
                            }
                        }
                    }
                },
                ratingsReceived: {
                    where: { periodMonth: month, periodYear: year },
                    select: {
                        teamId: true,
                        score: true,
                        finalScore: true,
                    }
                }
            }
        });

        const rekap = users.map(user => {
            const userTeams = user.teamMembers.map(tm => tm.team);
            let totalScore = 0;
            let ratedTeamsCount = 0;

            const details = userTeams.map(team => {
                const teamRating = user.ratingsReceived.find(r => r.teamId === team.id);
                if (teamRating) {
                    const skorPenilaian = teamRating.finalScore ?? teamRating.score;
                    totalScore += skorPenilaian;
                    ratedTeamsCount++;
                }
                return {
                    teamId: team.id,
                    teamName: team.teamName,
                    leaderName: (team as any).leader?.name || '-',
                    score: teamRating ? (teamRating.finalScore ?? teamRating.score) : null
                };
            });

            const overallAverage = ratedTeamsCount > 0 ? (totalScore / ratedTeamsCount).toFixed(2) : '0.00';

            return {
                id: user.id,
                nip: user.nip,
                name: user.name,
                totalTeams: userTeams.length,
                ratedTeams: ratedTeamsCount,
                averageScore: parseFloat(overallAverage),
                details
            };
        });

        res.json({
            month,
            year,
            data: rekap
        });
    } catch (error) {
        console.error('Failed to get Pimpinan rekap', error);
        res.status(500).json({ error: 'Internal server error' });
    }
};

/**
 * Pegawai Dashboard — read-only view of own performance data
 */
export const getPegawaiDashboard = async (req: Request, res: Response): Promise<void> => {
    try {
        const requesterId = (req as any).user?.id;
        const requesterRoles = (req as any).user?.roles || [];
        const targetUserId = req.query.userId as string;

        if (!requesterId) {
            res.status(401).json({ error: 'Unauthorized' });
            return;
        }

        // Determine which user's data to fetch
        let userId = requesterId;
        if (targetUserId && targetUserId !== requesterId) {
            if (!requesterRoles.includes('Admin') && !requesterRoles.includes('Pimpinan')) {
                res.status(403).json({ error: 'Akses ditolak. Hanya Admin atau Pimpinan yang dapat melihat report pegawai lain.' });
                return;
            }
            userId = targetUserId;
        }

        const queryMonth = parseInt(req.query.month as string);
        const queryYear = parseInt(req.query.year as string);
        const now = new Date();
        const month = !isNaN(queryMonth) ? queryMonth : now.getMonth() + 1;
        const year = !isNaN(queryYear) ? queryYear : now.getFullYear();

        // 1. Get user teams
        const userWithTeams = await prisma.user.findUnique({
            where: { id: userId },
            select: {
                id: true,
                nip: true,
                name: true,
                username: true,
                teamMembers: {
                    include: {
                        team: {
                            select: {
                                id: true,
                                teamName: true,
                                leader: { select: { id: true, name: true } },
                                members: {
                                    select: { userId: true },
                                },
                            },
                        },
                    },
                },
            },
        });

        if (!userWithTeams) {
            res.status(404).json({ error: 'User not found' });
            return;
        }

        const teams = userWithTeams.teamMembers.map(tm => ({
            id: tm.team.id,
            teamName: tm.team.teamName,
            leaderName: tm.team.leader?.name || '-',
            memberCount: tm.team.members.length,
        }));

        // 2. Get ratings received for the selected month
        const ratingsThisMonth = await prisma.rating.findMany({
            where: {
                targetUserId: userId,
                periodMonth: month,
                periodYear: year,
            },
            include: {
                evaluator: { select: { name: true } },
                team: { select: { teamName: true } },
            },
        });

        const ratingsDetail = ratingsThisMonth.map(r => ({
            teamName: r.team.teamName,
            evaluatorName: r.evaluator.name,
            score: r.score,
            volumeWork: r.volumeWork,
            qualityWork: r.qualityWork,
            finalScore: r.finalScore,
            notes: r.notes,
        }));

        // Compute overall average for this month
        const ratedScores = ratingsThisMonth.filter(r => r.finalScore !== null).map(r => r.finalScore!);
        const overallAvg = ratedScores.length > 0
            ? Math.round((ratedScores.reduce((a, b) => a + b, 0) / ratedScores.length) * 100) / 100
            : null;

        // 3. Monthly score history (last 6 months for trend chart)
        const historyMonths: { month: number; year: number }[] = [];
        let hm = month, hy = year;
        for (let i = 0; i < 6; i++) {
            historyMonths.unshift({ month: hm, year: hy });
            hm--;
            if (hm === 0) { hm = 12; hy--; }
        }

        const allHistoryRatings = await prisma.rating.findMany({
            where: {
                targetUserId: userId,
                OR: historyMonths.map(h => ({ periodMonth: h.month, periodYear: h.year })),
            },
            select: { periodMonth: true, periodYear: true, finalScore: true, score: true },
        });

        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        const scoreHistory = historyMonths.map(h => {
            const monthRatings = allHistoryRatings.filter(r => r.periodMonth === h.month && r.periodYear === h.year);
            const scores = monthRatings.map(r => r.finalScore || r.score);
            const avg = scores.length > 0 ? Math.round((scores.reduce((a, b) => a + b, 0) / scores.length) * 100) / 100 : null;
            return {
                label: `${monthNames[h.month - 1]} ${h.year}`,
                month: h.month,
                year: h.year,
                avgScore: avg,
                ratingCount: scores.length,
            };
        });

        // 4. Optimized Comparison: Use groupBy to get team averages in one query
        const teamIds = teams.map(t => t.id);
        const teamAggregates = await prisma.rating.groupBy({
            by: ['teamId'],
            where: {
                teamId: { in: teamIds },
                periodMonth: month,
                periodYear: year,
            },
            _avg: {
                finalScore: true,
                score: true,
            },
            _count: {
                id: true,
            },
        });

        const teamComparison = teams.map(team => {
            const aggregate = teamAggregates.find(a => a.teamId === team.id);
            const teamAvg = aggregate
                ? Math.round(((aggregate._avg.finalScore ?? aggregate._avg.score) || 0) * 100) / 100
                : null;

            const myRating = ratingsThisMonth.find(r => r.teamId === team.id);
            const myScore = myRating ? (myRating.finalScore ?? myRating.score) : null;

            return {
                teamName: team.teamName,
                myScore,
                teamAvg,
                totalRated: aggregate?._count.id || 0,
            };
        });

        // 5. Performance grade
        let grade = 'Belum Dinilai';
        let gradeColor = 'slate';
        if (overallAvg !== null) {
            if (overallAvg >= 90) { grade = 'Sangat Baik'; gradeColor = 'green'; }
            else if (overallAvg >= 80) { grade = 'Baik'; gradeColor = 'blue'; }
            else if (overallAvg >= 60) { grade = 'Cukup'; gradeColor = 'amber'; }
            else { grade = 'Kurang'; gradeColor = 'red'; }
        }

        res.json({
            month,
            year,
            user: {
                id: userWithTeams.id,
                nip: userWithTeams.nip,
                name: userWithTeams.name,
                username: userWithTeams.username,
            },
            summary: {
                totalTeams: teams.length,
                ratedTeamsThisMonth: ratingsThisMonth.length,
                overallAverage: overallAvg,
                grade,
                gradeColor,
            },
            teams,
            ratingsDetail,
            scoreHistory,
            teamComparison,
        });
    } catch (error) {
        console.error('Failed to get Pegawai dashboard', error);
        res.status(500).json({ error: 'Internal server error' });
    }
};
