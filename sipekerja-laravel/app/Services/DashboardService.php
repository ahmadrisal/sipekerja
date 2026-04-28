<?php

namespace App\Services;

use App\Models\Rating;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Calculate Final Score based on 80% base, 10% volume, 10% quality
     */
    public function calculateFinalScore($score, $volumeWork = null, $qualityWork = null)
    {
        $volScore = 80;
        if ($volumeWork === 'Ringan') $volScore = 60;
        else if ($volumeWork === 'Berat') $volScore = 100;

        $qualScore = 75;
        if ($qualityWork === 'Kurang') $qualScore = 50;
        else if ($qualityWork === 'Baik') $qualScore = 90;
        else if ($qualityWork === 'Sangat Baik') $qualScore = 100;

        $final = ($score * 0.8) + ($volScore * 0.1) + ($qualScore * 0.1);
        return round($final, 2);
    }

    /**
     * Get Rekap for Pimpinan (Summary of all users)
     */
    public function getPimpinanRekap($month, $year)
    {
        // Fetch users with their teams and ratings for the period
        $users = User::with([
            'teams.leader',
            'ratingsReceived' => function($query) use ($month, $year) {
                $query->where('period_month', $month)->where('period_year', $year);
            }
        ])->get();

        $rekap = $users->map(function($user) {
            $userTeams = $user->teams;
            $totalScore = 0;
            $activeWorkTeams = 0;
            $ratedTeamsCount = 0;

            $details = $userTeams->map(function($team) use ($user, &$totalScore, &$activeWorkTeams, &$ratedTeamsCount) {
                $teamRating = $user->ratingsReceived->where('team_id', $team->id)->first();
                $score = null;

                if ($teamRating) {
                    $ratedTeamsCount++;
                    // Only count towards average if score > 0 (No Work = 0)
                    if ($teamRating->score > 0) {
                        $score = $teamRating->final_score ?? $teamRating->score;
                        $totalScore += $score;
                        $activeWorkTeams++;
                    } else {
                        $score = 0;
                    }
                }

                return [
                    'teamId' => $team->id,
                    'teamName' => $team->team_name,
                    'leaderName' => $team->leader?->name ?? '-',
                    'score' => $score
                ];
            });

            $averageScore = $activeWorkTeams > 0 ? round($totalScore / $activeWorkTeams, 2) : 0;

            return (object) [
                'id' => $user->id,
                'nip' => $user->nip,
                'username' => $user->username,
                'name' => $user->name,
                'totalTeams' => $userTeams->count(), // Total teams assigned
                'activeWorkTeams' => $activeWorkTeams, // Teams with actual work
                'ratedTeams' => $ratedTeamsCount, // Teams actually assessed
                'averageScore' => $averageScore,
                'details' => $details
            ];
        });

        $totalMemberships = $rekap->sum('totalTeams');
        $totalRatedMemberships = $rekap->sum('ratedTeams');

        return [
            'month' => $month,
            'year' => $year,
            'data' => $rekap,
            'stats' => [
                'totalPegawai' => $users->count(),
                'totalTeams' => Team::count(),
                'ratedCount' => $totalRatedMemberships,
                'totalCount' => $totalMemberships,
                'progress' => $totalMemberships > 0 ? round(($totalRatedMemberships / $totalMemberships) * 100, 1) : 0,
                'compliance' => $this->getComplianceStats($month, $year)
            ]
        ];
    }

    /**
     * Get Compliance Stats (Missing Ratings)
     */
    public function getComplianceStats($month, $year)
    {
        // 1. Incomplete Teams
        $teams = Team::with(['leader', 'members', 'ratings' => function($q) use ($month, $year) {
            $q->where('period_month', $month)->where('period_year', $year);
        }])->get();

        $incompleteTeams = $teams->map(function($team) {
            $ratedUserIds = $team->ratings->pluck('target_user_id')->toArray();
            $unratedMembers = $team->members->filter(fn($m) => !in_array($m->id, $ratedUserIds));
            
            if ($unratedMembers->count() > 0) {
                return [
                    'team_id' => $team->id,
                    'team_name' => $team->team_name,
                    'leader_name' => $team->leader?->name ?? '-',
                    'pending_count' => $unratedMembers->count(),
                    'pending_members' => $unratedMembers->pluck('name')->toArray()
                ];
            }
            return null;
        })->filter()->values();

        // 2. Incomplete Employees
        $users = User::with(['teams.leader', 'ratingsReceived' => function($q) use ($month, $year) {
            $q->where('period_month', $month)->where('period_year', $year);
        }])->get();

        $incompleteEmployees = $users->map(function($user) use ($month, $year) {
            if ($user->teams->count() === 0) return null;

            $missingTeams = [];
            foreach ($user->teams as $team) {
                $hasRating = $user->ratingsReceived->where('team_id', $team->id)->first();
                if (!$hasRating) {
                    $missingTeams[] = [
                        'team_name' => $team->team_name,
                        'leader_name' => $team->leader?->name ?? '-'
                    ];
                }
            }

            if (count($missingTeams) > 0) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'nip' => $user->nip,
                    'missing_count' => count($missingTeams),
                    'missing_details' => $missingTeams
                ];
            }
            return null;
        })->filter()->values();

        return [
            'teams' => $incompleteTeams,
            'employees' => $incompleteEmployees,
            'teamsCount' => $incompleteTeams->count(),
            'employeesCount' => $incompleteEmployees->count()
        ];
    }

    /**
     * Get Statistics for Ketua Tim
     */
    public function getKetuaTimStats($userId, $month, $year)
    {
        $ledTeams = Team::where('leader_id', $userId)->with('members')->get();
        
        $teamDetails = $ledTeams->map(function($t) use ($userId) {
            return [
                'id' => $t->id,
                'teamName' => $t->team_name,
                'members' => $t->members->where('id', '!=', $userId)->map(function($m) {
                    return ['id' => $m->id, 'nip' => $m->nip, 'name' => $m->name];
                })->values()
            ];
        });

        $uniqueMembers = $ledTeams->flatMap(function($t) use ($userId) {
            return $t->members->where('id', '!=', $userId);
        })->unique('id');

        $ratingsCreated = Rating::where('evaluator_id', $userId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->get();

        $ratedIds = $ratingsCreated->pluck('target_user_id')->unique();
        $unratedMembers = $uniqueMembers->whereNotIn('id', $ratedIds);

        // Chart data per team
        $teamChartData = $ledTeams->map(function($t) use ($ratingsCreated) {
            $teamRatings = $ratingsCreated->where('team_id', $t->id)->where('score', '>', 0);
            if ($teamRatings->isEmpty()) return null;

            $avg = round($teamRatings->avg('final_score') ?? $teamRatings->avg('score'), 1);
            return [
                'teamName' => $t->team_name,
                'avgScore' => $avg,
                'totalRated' => $teamRatings->count()
            ];
        })->filter()->values();

        return [
            'teamCount' => $ledTeams->count(),
            'uniqueMemberCount' => $uniqueMembers->count(),
            'ratedCount' => $ratedIds->count(),
            'unratedCount' => $unratedMembers->count(),
            'teamDetails' => $teamDetails,
            'unratedMembers' => $unratedMembers->values(),
            'teamChartData' => $teamChartData
        ];
    }

    /**
     * Get Statistics for Admin
     */
    public function getAdminStats()
    {
        $users = User::with('teams')->get();
        $teams = Team::with('members')->get();
        
        $unassignedUsers = $users->filter(fn($u) => $u->teams->count() === 0);
        
        $largestTeam = $teams->sortByDesc(fn($t) => $t->members->count())->first();
        
        $totalMemberships = $teams->sum(fn($t) => $t->members->count());
        $avgMembersPerTeam = $teams->count() > 0 ? round($totalMemberships / $teams->count(), 1) : 0;
        
        $mostTeamsEmployee = $users->sortByDesc(fn($u) => $u->teams->count())->first();
        $avgTeamsPerEmployee = $users->count() > 0 ? round($totalMemberships / $users->count(), 1) : 0;
        $minTeamsPerEmployee = $users->min(fn($u) => $u->teams->count()) ?? 0;

        return [
            'stats' => [
                'totalUsers' => $users->count(),
                'totalTeams' => $teams->count(),
                'unassignedUsersCount' => $unassignedUsers->count(),
                'largestTeam' => [
                    'teamName' => $largestTeam?->team_name ?? '-',
                    'count' => $largestTeam?->members->count() ?? 0
                ],
                'avgMembersPerTeam' => $avgMembersPerTeam,
                'mostTeamsEmployee' => [
                    'name' => $mostTeamsEmployee?->name ?? '-',
                    'count' => $mostTeamsEmployee?->teams->count() ?? 0
                ],
                'minTeamsPerEmployee' => $minTeamsPerEmployee,
                'avgTeamsPerEmployee' => $avgTeamsPerEmployee,
            ],
            'userDetails' => $users->map(fn($u) => [
                'id' => $u->id,
                'nip' => $u->nip,
                'name' => $u->name,
                'teamNames' => $u->teams->pluck('team_name')->toArray()
            ]),
            'teamDetails' => $teams->map(fn($t) => [
                'id' => $t->id,
                'teamName' => $t->team_name,
                'memberCount' => $t->members->count(),
                'members' => $t->members->pluck('user.name')->toArray()
            ]),
            'unassignedUsers' => $unassignedUsers->values()
        ];
    }

    /**
     * Chart data for Admin Dashboard
     */
    public function getAdminChartData(): array
    {
        $users = User::with('teams')->get();
        $teams = Team::with(['members', 'leader'])->get();

        // 1. Histogram: distribusi jumlah tim per pegawai
        $teamCountPerEmployee = $users->map(fn($u) => $u->teams->count());
        $maxCount = max((int) $teamCountPerEmployee->max(), 4);
        $histLabels = range(0, $maxCount);
        $histData   = array_map(
            fn($n) => $teamCountPerEmployee->filter(fn($c) => $c === $n)->count(),
            $histLabels
        );
        $histColors = array_map(
            fn($n) => $n === 0 ? '#f43f5e' : '#6366f1',
            $histLabels
        );

        // 2. Distribusi ukuran tim
        $avgMembers  = $teams->count() > 0 ? round($teams->avg(fn($t) => $t->members->count()), 1) : 0;
        $teamSizeRows = $teams->sortByDesc(fn($t) => $t->members->count())->values()->map(fn($t) => [
            'name'    => $t->team_name,
            'count'   => $t->members->count(),
            'leader'  => $t->leader?->name ?? '-',
            'members' => $t->members->pluck('name')->toArray(),
        ])->toArray();

        // 3. Status plot pegawai (donut)
        $plotted   = $users->filter(fn($u) => $u->teams->count() > 0)->count();
        $unplotted = $users->count() - $plotted;

        // 4. Top 10 pegawai beban tim terbanyak
        $topEmployees = $users->sortByDesc(fn($u) => $u->teams->count())
            ->take(10)->values()->map(fn($u) => [
                'name'  => $u->name,
                'nip'   => $u->nip,
                'count' => $u->teams->count(),
                'teams' => $u->teams->pluck('team_name')->toArray(),
            ])->toArray();

        return [
            'histogram' => [
                'labels' => array_map(fn($n) => $n === 0 ? 'Belum Terplot' : "{$n} Tim", $histLabels),
                'data'   => array_values($histData),
                'colors' => $histColors,
            ],
            'teamSize' => [
                'rows' => $teamSizeRows,
                'avg'  => $avgMembers,
            ],
            'plotStatus' => [
                'plotted'   => $plotted,
                'unplotted' => $unplotted,
            ],
            'topEmployees' => $topEmployees,
        ];
    }

    /**
     * Get Statistics for Pegawai
     */
    public function getPegawaiDashboard($userId, $month, $year)
    {
        $user = User::with(['teams.members', 'ratingsReceived' => function($q) use ($month, $year) {
            $q->where('period_month', $month)->where('period_year', $year)->with('evaluator', 'team');
        }])->findOrFail($userId);

        $teams = $user->teams;
        $ratingsThisMonth = $user->ratingsReceived;
        
        // Summary
        $ratedCount = $ratingsThisMonth->count();
        $totalTeams = $teams->count();
        $overallAvg = $ratedCount > 0 ? round($ratingsThisMonth->avg('final_score'), 2) : null;

        $grade = 'Belum Dinilai';
        $gradeColor = 'slate';
        if ($overallAvg !== null) {
            if ($overallAvg >= 90) { $grade = 'Sangat Baik'; $gradeColor = 'green'; }
            elseif ($overallAvg >= 80) { $grade = 'Baik'; $gradeColor = 'blue'; }
            elseif ($overallAvg >= 60) { $grade = 'Cukup'; $gradeColor = 'amber'; }
            else { $grade = 'Kurang'; $gradeColor = 'red'; }
        }

        // History (Last 6 Months)
        $history = [];
        for ($i = 5; $i >= 0; $i--) {
            $hMonth = $month - $i;
            $hYear = $year;
            if ($hMonth <= 0) { $hMonth += 12; $hYear -= 1; }
            
            $hRatings = Rating::where('target_user_id', $userId)
                ->where('period_month', $hMonth)
                ->where('period_year', $hYear)
                ->get();
            
            $history[] = [
                'label' => date('M y', mktime(0,0,0,$hMonth, 1, $hYear)),
                'avgScore' => $hRatings->count() > 0 ? round($hRatings->avg('final_score'), 2) : null
            ];
        }

        // Team Comparison
        $comparison = $teams->map(function($t) use ($ratingsThisMonth, $month, $year) {
            $myRating = $ratingsThisMonth->where('team_id', $t->id)->first();
            $teamAvg = Rating::where('team_id', $t->id)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->avg('final_score');
            
            return [
                'teamName' => $t->team_name,
                'myScore' => $myRating->final_score ?? null,
                'teamAvg' => $teamAvg ? round($teamAvg, 2) : null
            ];
        });

        return [
            'month' => $month,
            'year' => $year,
            'user' => $user,
            'summary' => [
                'totalTeams' => $totalTeams,
                'ratedTeamsThisMonth' => $ratedCount,
                'overallAverage' => $overallAvg,
                'grade' => $grade,
                'gradeColor' => $gradeColor,
            ],
            'teams' => $teams,
            'ratingsDetail' => $ratingsThisMonth,
            'scoreHistory' => $history,
            'teamComparison' => $comparison
        ];
    }
}
