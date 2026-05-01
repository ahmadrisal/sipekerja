<?php

namespace App\Livewire\Dashboards;

use App\Models\PimpinanPegawaiScore;
use App\Models\Rating;
use App\Models\ScoringConfig;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class KepalaDashboard extends Component
{
    public $activeTab = 'overview';
    public $month;
    public $year;
    public $search = '';
    public $teamFilter = 'All';
    public $statusFilter = 'All';
    public $sortKey = 'averageScore';
    public $sortDir = 'desc';

    public $detailUserId = null;
    public $reportUserId = null;
    public $showIncompleteTeamsDialog = false;
    public $showIncompleteEmployeesDialog = false;
    public $reportSearch = '';

    // Override form state (keyed by rating ID)
    public $overrideFormState = [];
    public $showOverrideValidationDialog = false;

    // Input Nilai Ketua Tim
    public $ktFormState = [];
    public $showKtResetDialog = false;
    public $ktResetKey = null;
    public $showKtValidationDialog = false;
    public $ktSearch  = '';
    public $ktSortKey = 'ketua_name';
    public $ktSortDir = 'asc';

    // Nilai akhir langsung kepala ke pegawai
    public $pegawaiKepalaFormState = [];

    // Nilai KT sebagai pegawai (pre-fill dari avg tim, overridable)
    public $ktPegawaiFormState = [];

    private $shouldRefreshCharts = false;
    public $periodConfirmed = false;

    public function mount()
    {
        $this->month = null;
        $this->year  = (int) date('Y');
    }

    public function confirmPeriod()
    {
        if (!$this->month || !$this->year) return;
        $this->periodConfirmed = true;
        $this->shouldRefreshCharts = true;
        $this->loadKetuaTimFormState();
        $this->loadKtPegawaiFormState();
        $this->loadPegawaiKepalaFormState();
    }

    public function handleSort($key)
    {
        if ($this->sortKey === $key) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortKey = $key;
            $this->sortDir = 'desc';
        }
    }

    public function setDetailUser($id)
    {
        $this->detailUserId = $id;
        $this->loadOverrideFormState();
    }

    private function loadOverrideFormState(): void
    {
        if (!$this->detailUserId) {
            $this->overrideFormState = [];
            return;
        }

        $ratings = Rating::where('target_user_id', $this->detailUserId)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->with(['team', 'evaluator'])
            ->get();

        $this->overrideFormState = [];
        foreach ($ratings as $r) {
            $hasWork = $r->score > 0;
            $this->overrideFormState[$r->id] = [
                'team_name'    => $r->team->team_name ?? '-',
                'leader_name'  => $r->evaluator->name ?? '-',
                'has_work'     => $hasWork,
                'score'        => $hasWork ? $r->score : '',
                'volume_work'  => $r->volume_work ?? '',
                'quality_work' => $r->quality_work ?? '',
                'notes'        => ($r->notes === 'TIDAK_ADA_PEKERJAAN') ? '' : ($r->notes ?? ''),
                'final_score'  => $r->final_score,
                'overridden'   => (bool) $r->overridden_by,
                'is_dirty'     => false,
            ];
        }
    }

    public function saveOverride(string $ratingId, $score, ?string $volumeWork, ?string $qualityWork): void
    {
        $entry = $this->overrideFormState[$ratingId] ?? null;
        if (!$entry) return;

        if ($entry['has_work']) {
            if (!$score || (float) $score < 1 || (float) $score > 100) {
                $this->showOverrideValidationDialog = true;
                return;
            }
            $scoreVal   = (float) $score;
            $finalScore = $this->calcFinalScore($scoreVal, $volumeWork, $qualityWork);
            $volWork    = $volumeWork ?: null;
            $qualWork   = $qualityWork ?: null;
            $notes      = $entry['notes'] ?: null;
        } else {
            $scoreVal   = 0;
            $finalScore = null;
            $volWork    = null;
            $qualWork   = null;
            $notes      = 'TIDAK_ADA_PEKERJAAN';
        }

        Rating::where('id', $ratingId)->update([
            'score'         => $scoreVal,
            'volume_work'   => $volWork,
            'quality_work'  => $qualWork,
            'notes'         => $notes,
            'final_score'   => $finalScore,
            'overridden_by' => Auth::id(),
        ]);

        $this->overrideFormState[$ratingId]['overridden'] = true;
        $this->shouldRefreshCharts = true;
    }

    public function resetOverride($ratingId): void
    {
        Rating::where('id', $ratingId)->update(['overridden_by' => null]);
        $this->overrideFormState[$ratingId]['overridden'] = false;
    }

    private function calcFinalScore(float $score, ?string $volumeWork, ?string $qualityWork): float
    {
        $c = ScoringConfig::getAll(activeSatkerId());

        $volScore = match($volumeWork) {
            'Ringan' => $c['volume_ringan'] ?? 60,
            'Berat'  => $c['volume_berat']  ?? 100,
            default  => $c['volume_sedang'] ?? 80,
        };
        $qualScore = match($qualityWork) {
            'Kurang'      => $c['quality_kurang']      ?? 50,
            'Cukup'       => $c['quality_cukup']       ?? 75,
            'Baik'        => $c['quality_baik']        ?? 90,
            'Sangat Baik' => $c['quality_sangat_baik'] ?? 100,
            default       => $c['quality_cukup']       ?? 75,
        };

        $wScore   = ($c['weight_score']   ?? 80) / 100;
        $wVolume  = ($c['weight_volume']  ?? 10) / 100;
        $wQuality = ($c['weight_quality'] ?? 10) / 100;

        return round(($score * $wScore) + ($volScore * $wVolume) + ($qualScore * $wQuality), 2);
    }

    public function updatedMonth()
    {
        $this->periodConfirmed = false;
        $this->shouldRefreshCharts = true;
        $this->ktFormState = [];
        $this->ktPegawaiFormState = [];
        $this->overrideFormState = [];
        $this->pegawaiKepalaFormState = [];
    }

    public function updatedYear()
    {
        $this->periodConfirmed = false;
        $this->shouldRefreshCharts = true;
        $this->ktFormState = [];
        $this->ktPegawaiFormState = [];
        $this->overrideFormState = [];
        $this->pegawaiKepalaFormState = [];
    }

    public function updatedTeamFilter()
    {
        $this->shouldRefreshCharts = true;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab === 'overview') {
            $this->shouldRefreshCharts = true;
        }
    }

    public function sortKt($col): void
    {
        if ($this->ktSortKey === $col) {
            $this->ktSortDir = $this->ktSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ktSortKey = $col;
            $this->ktSortDir = 'asc';
        }
    }

    public function setReportUserId($id)
    {
        $this->reportUserId = $id;
        $this->detailUserId = null;
        $this->setActiveTab('report');
        $this->reportSearch = '';
    }

    // ── Nilai Ketua Tim ──────────────────────────────────────────────

    private function loadKetuaTimFormState(): void
    {
        $ketuaTimUsers = User::role('Ketua Tim')
            ->where('satker_id', activeSatkerId())
            ->with('ledTeams')
            ->get();

        $existingRatings = Rating::where('evaluator_id', Auth::id())
            ->where('satker_id', activeSatkerId())
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get();

        $this->ktFormState = [];
        foreach ($ketuaTimUsers as $kt) {
            foreach ($kt->ledTeams as $team) {
                $key = "{$kt->id}_{$team->id}";
                $rating = $existingRatings
                    ->where('target_user_id', $kt->id)
                    ->where('team_id', $team->id)
                    ->first();

                $this->ktFormState[$key] = [
                    'ketua_id' => $kt->id,
                    'team_id'  => $team->id,
                    'score'    => $rating ? $rating->score : '',
                    'notes'    => $rating ? $rating->notes : '',
                    'is_dirty' => false,
                    'is_rated' => $rating ? true : false,
                ];
            }
        }
    }

    public function saveKetuaTimRating($key): void
    {
        $entry = $this->ktFormState[$key];

        if (!$entry['score'] || $entry['score'] < 1 || $entry['score'] > 100) {
            $this->showKtValidationDialog = true;
            return;
        }

        Rating::updateOrCreate(
            [
                'evaluator_id'   => Auth::id(),
                'target_user_id' => $entry['ketua_id'],
                'team_id'        => $entry['team_id'],
                'period_month'   => $this->month,
                'period_year'    => $this->year,
            ],
            [
                'satker_id'    => activeSatkerId(),
                'score'        => $entry['score'],
                'final_score'  => $entry['score'],
                'volume_work'  => null,
                'quality_work' => null,
                'notes'        => $entry['notes'] ?? null,
            ]
        );

        $this->ktFormState[$key]['is_dirty'] = false;
        $this->ktFormState[$key]['is_rated'] = true;
        session()->flash('kt_success', 'Penilaian berhasil disimpan.');
    }

    public function confirmResetKt($key): void
    {
        $this->ktResetKey = $key;
        $this->showKtResetDialog = true;
    }

    public function executeResetKt(): void
    {
        if (!$this->ktResetKey) return;

        $key   = $this->ktResetKey;
        $entry = $this->ktFormState[$key];

        Rating::where('evaluator_id', Auth::id())
            ->where('target_user_id', $entry['ketua_id'])
            ->where('team_id', $entry['team_id'])
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->delete();

        $this->ktFormState[$key] = [
            'ketua_id' => $entry['ketua_id'],
            'team_id'  => $entry['team_id'],
            'score'    => '',
            'notes'    => '',
            'is_dirty' => false,
            'is_rated' => false,
        ];

        $this->showKtResetDialog = false;
        $this->ktResetKey = null;
        session()->flash('kt_success', 'Penilaian berhasil di-reset.');
    }

    public function cancelResetKt(): void
    {
        $this->showKtResetDialog = false;
        $this->ktResetKey = null;
    }

    private function loadKtPegawaiFormState(): void
    {
        $ketuaIds = User::role('Ketua Tim')
            ->where('satker_id', activeSatkerId())
            ->pluck('id');

        if ($ketuaIds->isEmpty()) {
            $this->ktPegawaiFormState = [];
            return;
        }

        // KTs who are non-leader members of at least one team in this satker
        $memberKtIds = DB::table('team_members')
            ->join('teams', 'team_members.team_id', '=', 'teams.id')
            ->whereIn('team_members.user_id', $ketuaIds->toArray())
            ->whereColumn('teams.leader_id', '!=', 'team_members.user_id')
            ->where('teams.satker_id', activeSatkerId())
            ->pluck('team_members.user_id')
            ->flip();

        $receivedRatings = Rating::whereIn('target_user_id', $ketuaIds)
            ->whereIn('evaluator_id', $ketuaIds)
            ->where('satker_id', activeSatkerId())
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->where('score', '>', 0)
            ->get(['target_user_id', 'final_score']);

        $overrides = PimpinanPegawaiScore::where('pimpinan_id', Auth::id())
            ->whereIn('pegawai_id', $ketuaIds)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get()->keyBy('pegawai_id');

        $this->ktPegawaiFormState = [];
        foreach ($ketuaIds as $ktId) {
            $ratings = $receivedRatings->where('target_user_id', $ktId);
            $autoAvg = $ratings->count() > 0
                ? (int) round($ratings->avg('final_score'))
                : null;
            $override = $overrides->get($ktId);
            $this->ktPegawaiFormState[$ktId] = [
                'auto_avg'      => $autoAvg,
                'score'         => $override ? (string) $override->score : ($autoAvg !== null ? (string) $autoAvg : ''),
                'is_overridden' => (bool) $override,
                'is_member'     => isset($memberKtIds[$ktId]),
            ];
        }
    }

    public function saveKtPegawaiScore(string $ktId): void
    {
        $score = $this->ktPegawaiFormState[$ktId]['score'] ?? '';
        if ($score === '' || (float) $score < 1 || (float) $score > 100) {
            $this->showKtValidationDialog = true;
            return;
        }

        PimpinanPegawaiScore::updateOrCreate(
            ['pimpinan_id' => Auth::id(), 'pegawai_id' => $ktId,
             'period_month' => $this->month, 'period_year' => $this->year],
            ['score' => (float) $score]
        );

        $this->ktPegawaiFormState[$ktId]['is_overridden'] = true;
        session()->flash('kt_success', 'Nilai sebagai pegawai berhasil disimpan.');
    }

    public function resetKtPegawaiScore(string $ktId): void
    {
        PimpinanPegawaiScore::where('pimpinan_id', Auth::id())
            ->where('pegawai_id', $ktId)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->delete();

        if (isset($this->ktPegawaiFormState[$ktId])) {
            $auto = $this->ktPegawaiFormState[$ktId]['auto_avg'];
            $this->ktPegawaiFormState[$ktId]['score']         = $auto !== null ? (string) $auto : '';
            $this->ktPegawaiFormState[$ktId]['is_overridden'] = false;
        }

        session()->flash('kt_success', 'Nilai sebagai pegawai berhasil direset.');
    }

    public function loadKtCharts(): void
    {
        $ktData = $this->buildKetuaTimData();

        $scatter = [];
        $barLabels  = [];
        $barNips    = [];
        $barSeries  = [];

        foreach ($ktData as $kt) {
            $teamCount  = count($kt['teams']);
            $nilaiAkhir = $kt['nilai_akhir'];

            $scatter[] = [
                'name' => $kt['ketua_name'],
                'nip'  => $kt['ketua_nip'] ?? '',
                'x'    => $teamCount,
                'y'    => $nilaiAkhir,
            ];

            $barLabels[] = $kt['ketua_name'];
            $barNips[]   = $kt['ketua_nip'] ?? '';
            $barSeries[] = $nilaiAkhir;
        }

        $this->dispatch('refreshKtCharts', ktChartData: [
            'scatter'   => $scatter,
            'barLabels' => $barLabels,
            'barNips'   => $barNips,
            'barSeries' => $barSeries,
        ]);
    }

    // ── Nilai Akhir Kepala ke Pegawai ────────────────────────────────

    private function loadPegawaiKepalaFormState(): void
    {
        $pegawaiIds = User::role('Pegawai')
            ->where('satker_id', activeSatkerId())
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['Ketua Tim', 'Kepala Kabkot', 'Pimpinan', 'Admin']);
            })
            ->pluck('id');

        $existing = PimpinanPegawaiScore::where('pimpinan_id', Auth::id())
            ->whereIn('pegawai_id', $pegawaiIds)
            ->where('period_month', $this->month)
            ->where('period_year',  $this->year)
            ->get()
            ->keyBy('pegawai_id');

        $this->pegawaiKepalaFormState = [];
        foreach ($pegawaiIds as $id) {
            $rec = $existing->get($id);
            $this->pegawaiKepalaFormState[$id] = [
                'score'    => $rec ? (string) $rec->score : '',
                'is_rated' => (bool) $rec,
            ];
        }
    }

    public function savePegawaiKepalaScore(string $pegawaiId): void
    {
        $score = $this->pegawaiKepalaFormState[$pegawaiId]['score'] ?? '';

        if ($score === '' || (float) $score < 1 || (float) $score > 100) {
            $this->showOverrideValidationDialog = true;
            return;
        }

        PimpinanPegawaiScore::updateOrCreate(
            [
                'pimpinan_id'  => Auth::id(),
                'pegawai_id'   => $pegawaiId,
                'period_month' => $this->month,
                'period_year'  => $this->year,
            ],
            ['score' => (float) $score]
        );

        $this->pegawaiKepalaFormState[$pegawaiId]['is_rated'] = true;
        session()->flash('pegawai_kepala_success', 'Nilai akhir berhasil disimpan.');
    }

    public function resetPegawaiKepalaScore(string $pegawaiId): void
    {
        PimpinanPegawaiScore::where('pimpinan_id', Auth::id())
            ->where('pegawai_id', $pegawaiId)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->delete();

        if (isset($this->pegawaiKepalaFormState[$pegawaiId])) {
            $this->pegawaiKepalaFormState[$pegawaiId]['score']    = '';
            $this->pegawaiKepalaFormState[$pegawaiId]['is_rated'] = false;
        }

        session()->flash('pegawai_kepala_success', 'Nilai akhir berhasil direset.');
    }

    // ── Build data helpers ───────────────────────────────────────────

    private function buildKetuaTimData(): array
    {
        $ketuaTimUsers = User::role('Ketua Tim')
            ->where('satker_id', activeSatkerId())
            ->with('ledTeams')
            ->orderBy('name')
            ->get();

        $ketuaIds = $ketuaTimUsers->pluck('id')->toArray();

        $memberRatings = Rating::whereIn('evaluator_id', $ketuaIds)
            ->where('satker_id', activeSatkerId())
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->where('score', '>', 0)
            ->get(['evaluator_id', 'team_id', 'final_score']);

        $kepalaRatings = Rating::where('evaluator_id', Auth::id())
            ->where('satker_id', activeSatkerId())
            ->whereIn('target_user_id', $ketuaIds)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get(['target_user_id', 'team_id', 'score']);

        $ktData = [];
        foreach ($ketuaTimUsers as $kt) {
            if ($kt->ledTeams->isEmpty()) continue;

            $teams       = [];
            $savedScores = [];

            foreach ($kt->ledTeams as $team) {
                $key = "{$kt->id}_{$team->id}";

                $scores = $memberRatings
                    ->where('evaluator_id', $kt->id)
                    ->where('team_id', $team->id)
                    ->pluck('final_score')
                    ->filter(fn($s) => $s !== null)
                    ->values()
                    ->toArray();

                $saved = $kepalaRatings
                    ->where('target_user_id', $kt->id)
                    ->where('team_id', $team->id)
                    ->first();

                if ($saved) $savedScores[] = $saved->score;

                $teams[] = [
                    'key'         => $key,
                    'team_name'   => $team->team_name,
                    'avg'         => count($scores) ? (int) round(array_sum($scores) / count($scores)) : null,
                    'max'         => count($scores) ? (int) round(max($scores)) : null,
                    'q3'          => count($scores) ? $this->calcQ3($scores) : null,
                    'rated_count' => count($scores),
                ];
            }

            $pegEntry  = $this->ktPegawaiFormState[$kt->id] ?? null;
            $pegScore  = null;
            if ($pegEntry) {
                if ($pegEntry['is_overridden'] && $pegEntry['score'] !== '') {
                    $pegScore = (float) $pegEntry['score'];
                } elseif ($pegEntry['auto_avg'] !== null) {
                    $pegScore = (float) $pegEntry['auto_avg'];
                }
            }
            $allScores = $savedScores;
            if ($pegScore !== null) $allScores[] = $pegScore;

            $ktData[$kt->id] = [
                'ketua_name'   => $kt->name,
                'ketua_nip'    => $kt->nip,
                'teams'        => $teams,
                'pegawai_auto' => $pegEntry['auto_avg'] ?? null,
                'nilai_akhir'  => count($allScores) ? (int) round(array_sum($allScores) / count($allScores)) : null,
            ];
        }

        return $ktData;
    }

    private function calcQ3(array $scores): float
    {
        sort($scores);
        $n   = count($scores);
        if ($n === 1) return round($scores[0], 2);
        $pos   = 0.75 * ($n - 1);
        $floor = (int) $pos;
        $frac  = $pos - $floor;
        $val   = isset($scores[$floor + 1])
            ? $scores[$floor] + $frac * ($scores[$floor + 1] - $scores[$floor])
            : $scores[$floor];
        return round($val, 2);
    }

    private function prepareChartsData($data)
    {
        $teamSumMap   = [];
        $teamCountMap = [];
        $teamLeaderMap = [];
        $teamScoresMap = [];
        foreach ($data as $u) {
            foreach ($u->details as $d) {
                if ($d['score'] !== null && $d['score'] > 0) {
                    $t = $d['teamName'];
                    $teamSumMap[$t]   = ($teamSumMap[$t] ?? 0) + $d['score'];
                    $teamCountMap[$t] = ($teamCountMap[$t] ?? 0) + 1;
                    $teamScoresMap[$t][] = $d['score'];
                    if (!isset($teamLeaderMap[$t])) {
                        $teamLeaderMap[$t] = $d['leaderName'];
                    }
                }
            }
        }

        $teamPerfMap = [];
        foreach ($teamSumMap as $t => $sum) {
            $teamPerfMap[$t] = round($sum / $teamCountMap[$t], 2);
        }

        arsort($teamPerfMap);
        $topTeams      = array_slice($teamPerfMap, 0, 15);
        $topTeamLabels = array_keys($topTeams);
        $topTeamLeaders = array_map(fn($t) => $teamLeaderMap[$t] ?? '-', $topTeamLabels);
        $topTeamMax    = array_map(fn($t) => max($teamScoresMap[$t] ?? [0]), $topTeamLabels);
        $topTeamQ3     = array_map(fn($t) => $this->calcQ3($teamScoresMap[$t] ?? [0]), $topTeamLabels);

        $dist = ['Sangat Baik' => 0, 'Baik' => 0, 'Cukup' => 0, 'Kurang' => 0, 'Belum Dinilai' => 0];
        foreach ($data as $u) {
            if ($u->averageScore >= 90)       $dist['Sangat Baik']++;
            elseif ($u->averageScore >= 80)   $dist['Baik']++;
            elseif ($u->averageScore >= 60)   $dist['Cukup']++;
            elseif ($u->averageScore > 0)     $dist['Kurang']++;
            else                              $dist['Belum Dinilai']++;
        }

        $scatter = [];
        $totalX = 0; $totalY = 0; $countValid = 0;
        foreach ($data as $u) {
            if ($u->averageScore > 0) {
                $scatter[] = ['x' => $u->activeWorkTeams, 'y' => $u->averageScore, 'name' => $u->name];
                $totalX += $u->activeWorkTeams;
                $totalY += $u->averageScore;
                $countValid++;
            }
        }
        $avgX = $countValid > 0 ? $totalX / $countValid : 0;
        $avgY = $countValid > 0 ? $totalY / $countValid : 0;

        return [
            'teamSize' => ['labels' => $topTeamLabels, 'series' => array_values($topTeams), 'leaders' => array_values($topTeamLeaders), 'maxScores' => $topTeamMax, 'q3Scores' => $topTeamQ3],
            'perfDist' => ['labels' => array_keys($dist), 'series' => array_values($dist)],
            'scatter'  => $scatter,
            'avgX'     => round($avgX, 2),
            'avgY'     => round($avgY, 2),
        ];
    }

    public function render(DashboardService $service)
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',    4 => 'April',
            5 => 'Mei',     6 => 'Juni',     7 => 'Juli',      8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        if (!$this->periodConfirmed) {
            $sc = ScoringConfig::getAll(activeSatkerId());
            return view('livewire.dashboards.kepala-dashboard', [
                'rekap' => collect(), 'allUsers' => collect(), 'stats' => [],
                'allTeams' => collect(), 'charts' => [], 'ktData' => [],
                'reportUser' => null, 'monthNames' => $monthNames,
                'scoringConfig' => [
                    'weight_score'        => $sc['weight_score']        ?? 80,
                    'weight_volume'       => $sc['weight_volume']       ?? 10,
                    'weight_quality'      => $sc['weight_quality']      ?? 10,
                    'volume_ringan'       => $sc['volume_ringan']       ?? 60,
                    'volume_sedang'       => $sc['volume_sedang']       ?? 80,
                    'volume_berat'        => $sc['volume_berat']        ?? 100,
                    'quality_kurang'      => $sc['quality_kurang']      ?? 50,
                    'quality_cukup'       => $sc['quality_cukup']       ?? 75,
                    'quality_baik'        => $sc['quality_baik']        ?? 90,
                    'quality_sangat_baik' => $sc['quality_sangat_baik'] ?? 100,
                ],
            ]);
        }

        $rekap = $service->getPimpinanRekap($this->month, $this->year);
        $data  = collect($rekap['data']);

        $pegawaiOnlyIds = User::role('Pegawai')
            ->where('satker_id', activeSatkerId())
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['Ketua Tim', 'Kepala Kabkot', 'Pimpinan', 'Admin']);
            })
            ->pluck('id')
            ->all();
        $data = $data->filter(fn($u) => in_array($u->id, $pegawaiOnlyIds));

        $kepalaScores = PimpinanPegawaiScore::where('pimpinan_id', Auth::id())
            ->whereIn('pegawai_id', $pegawaiOnlyIds)
            ->where('period_month', $this->month)
            ->where('period_year',  $this->year)
            ->get()
            ->keyBy('pegawai_id');

        $data = $data->map(function ($u) use ($kepalaScores) {
            $teamScores = collect($u->details)->pluck('score')->filter(fn($s) => $s > 0)->values();
            $u->min_score = $teamScores->count() ? round($teamScores->min(), 2) : null;
            $u->max_score = $teamScores->count() ? round($teamScores->max(), 2) : null;
            $rec = $kepalaScores->get($u->id);
            $u->kepala_score = $rec ? (float) $rec->score : null;
            $raw = $u->kepala_score ?? ($u->averageScore > 0 ? $u->averageScore : null);
            $u->nilai_akhir = $raw !== null ? (int) round($raw, 0) : null;
            return $u;
        });

        if ($this->search) {
            $q = strtolower($this->search);
            $data = $data->filter(fn($u) =>
                str_contains(strtolower($u->name), $q) || str_contains($u->nip, $q)
            );
        }

        if ($this->teamFilter !== 'All') {
            $data = $data->filter(fn($u) =>
                collect($u->details)->contains('teamName', $this->teamFilter)
            );
        }

        if ($this->statusFilter === 'HasTeam') {
            $data = $data->filter(fn($u) => $u->totalTeams > 0);
        } elseif ($this->statusFilter === 'NoTeam') {
            $data = $data->filter(fn($u) => $u->totalTeams === 0);
        }

        $isDesc = $this->sortDir === 'desc';
        $data   = $data->sortBy($this->sortKey, SORT_REGULAR, $isDesc);

        $allTeams = collect($rekap['data'])->flatMap(fn($u) => collect($u->details)->pluck('teamName'))->unique()->sort();

        $charts = $this->prepareChartsData($rekap['data']);

        if ($this->shouldRefreshCharts && $this->activeTab === 'overview') {
            $this->dispatch('refreshCharts', charts: $charts);
        }

        $ktData = $this->buildKetuaTimData();
        if ($this->ktSearch) {
            $q = strtolower($this->ktSearch);
            $ktData = array_filter($ktData, fn($d) =>
                str_contains(strtolower($d['ketua_name']), $q) ||
                str_contains($d['ketua_nip'] ?? '', $q)
            );
        }
        $ktSortKey = $this->ktSortKey;
        $ktSortDir = $this->ktSortDir;
        uasort($ktData, function ($a, $b) use ($ktSortKey, $ktSortDir) {
            $av = $a[$ktSortKey] ?? '';
            $bv = $b[$ktSortKey] ?? '';
            if ($av === null && $bv === null) return 0;
            if ($av === null) return 1;
            if ($bv === null) return -1;
            return $ktSortDir === 'asc' ? ($av <=> $bv) : ($bv <=> $av);
        });

        $sc = ScoringConfig::getAll(activeSatkerId());

        return view('livewire.dashboards.kepala-dashboard', [
            'rekap'         => $data,
            'allUsers'      => collect($rekap['data']),
            'stats'         => $rekap['stats'],
            'allTeams'      => $allTeams,
            'charts'        => $charts,
            'ktData'        => $ktData,
            'scoringConfig' => [
                'weight_score'        => $sc['weight_score']        ?? 80,
                'weight_volume'       => $sc['weight_volume']       ?? 10,
                'weight_quality'      => $sc['weight_quality']      ?? 10,
                'volume_ringan'       => $sc['volume_ringan']       ?? 60,
                'volume_sedang'       => $sc['volume_sedang']       ?? 80,
                'volume_berat'        => $sc['volume_berat']        ?? 100,
                'quality_kurang'      => $sc['quality_kurang']      ?? 50,
                'quality_cukup'       => $sc['quality_cukup']       ?? 75,
                'quality_baik'        => $sc['quality_baik']        ?? 90,
                'quality_sangat_baik' => $sc['quality_sangat_baik'] ?? 100,
            ],
            'monthNames' => $monthNames,
            'reportUser' => $this->reportUserId ? \App\Models\User::find($this->reportUserId) : null,
        ]);
    }
}
