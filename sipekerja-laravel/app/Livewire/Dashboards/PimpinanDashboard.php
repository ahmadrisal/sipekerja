<?php

namespace App\Livewire\Dashboards;

use App\Models\KabkotRating;
use App\Models\PimpinanKabkotScore;
use App\Models\PimpinanPegawaiScore;
use App\Models\Rating;
use App\Models\ScoringConfig;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PimpinanDashboard extends Component
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
    public $reportData = null;

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

    // Rekap & Override Kabkot
    public $detailKabkotId = null;
    public $kabkotOverrideFormState = [];
    public $showKabkotOverrideValidationDialog = false;
    public $kabkotSearch  = '';
    public $kabkotSortKey = 'name';
    public $kabkotSortDir = 'asc';

    // Nilai langsung pimpinan ke kabkot
    public $kabkotPimpinanFormState = [];

    // Nilai akhir langsung pimpinan ke pegawai
    public $pegawaiPimpinanFormState = [];

    // Incomplete stats dialogs (Kabkot)
    public $showKabkotIncompleteTeamsDialog = false;
    public $kabkotIncompleteTeamsData = [];
    public $showKabkotIncompleteKabkotDialog = false;
    public $kabkotIncompleteKabkotData = [];

    private $shouldRefreshCharts = false;

    public function mount()
    {
        $this->month = date('n');
        $this->year = date('Y');
        $this->loadKetuaTimFormState();
        $this->loadPimpinanKabkotFormState();
        $this->loadPimpinanPegawaiFormState();
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
        $c = ScoringConfig::getAll();

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
        $this->shouldRefreshCharts = true;
        $this->loadKetuaTimFormState();
        $this->loadOverrideFormState();
        $this->loadPimpinanKabkotFormState();
        $this->loadPimpinanPegawaiFormState();
        $this->detailKabkotId = null;
        $this->kabkotOverrideFormState = [];
    }

    public function updatedYear()
    {
        $this->shouldRefreshCharts = true;
        $this->loadKetuaTimFormState();
        $this->loadOverrideFormState();
        $this->loadPimpinanKabkotFormState();
        $this->loadPimpinanPegawaiFormState();
        $this->detailKabkotId = null;
        $this->kabkotOverrideFormState = [];
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

    public function sortKabkot($col): void
    {
        if ($this->kabkotSortKey === $col) {
            $this->kabkotSortDir = $this->kabkotSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->kabkotSortKey = $col;
            $this->kabkotSortDir = 'asc';
        }
    }

    public function setReportUserId($id)
    {
        $this->reportUserId = $id;
        $this->detailUserId = null;
        $this->setActiveTab('report');
        $this->reportSearch = '';
    }

    private function loadKetuaTimFormState(): void
    {
        $ketuaTimUsers = User::role('Ketua Tim')->with('ledTeams')->get();
        $existingRatings = Rating::where('evaluator_id', Auth::id())
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
            'scatter'    => $scatter,
            'barLabels'  => $barLabels,
            'barNips'    => $barNips,
            'barSeries'  => $barSeries,
        ]);
    }

    // ── Rekap & Override Kabkot ───────────────────────────────────────

    public function setDetailKabkot($id): void
    {
        if ($this->detailKabkotId === $id) {
            $this->detailKabkotId = null;
            $this->kabkotOverrideFormState = [];
            return;
        }
        $this->detailKabkotId = $id;
        $this->loadKabkotOverrideFormState();
    }

    private function loadKabkotOverrideFormState(): void
    {
        if (!$this->detailKabkotId) {
            $this->kabkotOverrideFormState = [];
            return;
        }

        $ratings = KabkotRating::where('kabkot_id', $this->detailKabkotId)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->with(['team', 'evaluator'])
            ->orderBy('team_id')
            ->get();

        $this->kabkotOverrideFormState = [];
        foreach ($ratings as $r) {
            $this->kabkotOverrideFormState[$r->id] = [
                'team_name'      => $r->team->team_name ?? '-',
                'evaluator_name' => $r->evaluator->name ?? '-',
                'score'          => $r->score > 0 ? $r->score : '',
                'has_work'       => $r->score > 0,
                'overridden'     => (bool) $r->overridden_by,
                'flag_hidden'    => (bool) $r->override_flag_hidden,
                'is_dirty'       => false,
            ];
        }
    }

    public function saveKabkotOverride($ratingId): void
    {
        $entry = $this->kabkotOverrideFormState[$ratingId];

        if (!$entry['score'] || $entry['score'] < 1 || $entry['score'] > 100) {
            $this->showKabkotOverrideValidationDialog = true;
            return;
        }

        KabkotRating::where('id', $ratingId)->update([
            'score'        => (float) $entry['score'],
            'overridden_by' => Auth::id(),
        ]);

        $this->kabkotOverrideFormState[$ratingId]['is_dirty']  = false;
        $this->kabkotOverrideFormState[$ratingId]['overridden'] = true;
    }

    public function resetKabkotOverride($ratingId): void
    {
        KabkotRating::where('id', $ratingId)->update(['overridden_by' => null, 'override_flag_hidden' => false]);
        $this->kabkotOverrideFormState[$ratingId]['overridden']   = false;
        $this->kabkotOverrideFormState[$ratingId]['flag_hidden']  = false;
    }

    public function hideKabkotOverrideFlag($ratingId): void
    {
        KabkotRating::where('id', $ratingId)->update(['override_flag_hidden' => true]);
        $this->kabkotOverrideFormState[$ratingId]['flag_hidden'] = true;
    }

    // ── Nilai Langsung Pimpinan ke Kabkot ────────────────────────────

    private function loadPimpinanKabkotFormState(): void
    {
        $kabkots   = User::role('Kepala Kabkot')->orderBy('name')->get();
        $existing  = PimpinanKabkotScore::where('pimpinan_id', Auth::id())
            ->where('period_month', $this->month)
            ->where('period_year',  $this->year)
            ->get()
            ->keyBy('kabkot_id');

        $this->kabkotPimpinanFormState = [];
        foreach ($kabkots as $kab) {
            $rec = $existing->get($kab->id);
            $this->kabkotPimpinanFormState[$kab->id] = [
                'score'    => $rec ? $rec->score : '',
                'is_rated' => (bool) $rec,
            ];
        }
    }

    public function resetPimpinanKabkotScore(string $kabkotId): void
    {
        PimpinanKabkotScore::where('pimpinan_id', Auth::id())
            ->where('kabkot_id', $kabkotId)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->delete();

        if (isset($this->kabkotPimpinanFormState[$kabkotId])) {
            $this->kabkotPimpinanFormState[$kabkotId]['score']    = '';
            $this->kabkotPimpinanFormState[$kabkotId]['is_rated'] = false;
        }

        session()->flash('kabkot_pimpinan_success', 'Nilai berhasil direset.');
    }

    public function savePimpinanKabkotScore(string $kabkotId): void
    {
        $score = $this->kabkotPimpinanFormState[$kabkotId]['score'] ?? '';

        if ($score === '' || (float) $score < 1 || (float) $score > 100) {
            $this->showKabkotOverrideValidationDialog = true;
            return;
        }

        PimpinanKabkotScore::updateOrCreate(
            [
                'pimpinan_id'  => Auth::id(),
                'kabkot_id'    => $kabkotId,
                'period_month' => $this->month,
                'period_year'  => $this->year,
            ],
            ['score' => (float) $score]
        );

        $this->kabkotPimpinanFormState[$kabkotId]['is_rated'] = true;
        session()->flash('kabkot_pimpinan_success', 'Nilai berhasil disimpan.');
    }

    // ── Nilai Akhir Langsung Pimpinan ke Pegawai ─────────────────────

    private function loadPimpinanPegawaiFormState(): void
    {
        $pegawaiIds = User::role('Pegawai')
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

        $this->pegawaiPimpinanFormState = [];
        foreach ($pegawaiIds as $id) {
            $rec = $existing->get($id);
            $this->pegawaiPimpinanFormState[$id] = [
                'score'    => $rec ? (string) $rec->score : '',
                'is_rated' => (bool) $rec,
            ];
        }
    }

    public function savePimpinanPegawaiScore(string $pegawaiId): void
    {
        $score = $this->pegawaiPimpinanFormState[$pegawaiId]['score'] ?? '';

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

        $this->pegawaiPimpinanFormState[$pegawaiId]['is_rated'] = true;
        session()->flash('pegawai_pimpinan_success', 'Nilai akhir berhasil disimpan.');
    }

    public function resetPimpinanPegawaiScore(string $pegawaiId): void
    {
        PimpinanPegawaiScore::where('pimpinan_id', Auth::id())
            ->where('pegawai_id', $pegawaiId)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->delete();

        if (isset($this->pegawaiPimpinanFormState[$pegawaiId])) {
            $this->pegawaiPimpinanFormState[$pegawaiId]['score']    = '';
            $this->pegawaiPimpinanFormState[$pegawaiId]['is_rated'] = false;
        }

        session()->flash('pegawai_pimpinan_success', 'Nilai akhir berhasil direset.');
    }

    public function openIncompleteTeamsDialog(): void
    {
        $kabkots  = User::role('Kepala Kabkot')->get();
        $allTeams = \App\Models\Team::with('leader')->get();

        $ratings = KabkotRating::where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get(['team_id', 'kabkot_id']);

        $result = [];
        foreach ($allTeams as $team) {
            $ratedIds  = $ratings->where('team_id', $team->id)->pluck('kabkot_id');
            $unrated   = $kabkots->whereNotIn('id', $ratedIds->toArray())->values();
            if ($unrated->isNotEmpty()) {
                $result[] = [
                    'team_name'      => $team->team_name,
                    'leader_name'    => $team->leader->name ?? '-',
                    'unrated_kabkots'=> $unrated->pluck('name')->toArray(),
                ];
            }
        }

        $this->kabkotIncompleteTeamsData = $result;
        $this->showKabkotIncompleteTeamsDialog = true;
    }

    public function openIncompleteKabkotDialog(): void
    {
        $kabkots  = User::role('Kepala Kabkot')->orderBy('name')->get();
        $allTeams = \App\Models\Team::all();

        $ratings = KabkotRating::where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get(['team_id', 'kabkot_id']);

        $result = [];
        foreach ($kabkots as $kabkot) {
            $ratedTeamIds  = $ratings->where('kabkot_id', $kabkot->id)->pluck('team_id');
            $unratedTeams  = $allTeams->whereNotIn('id', $ratedTeamIds->toArray())->values();
            if ($unratedTeams->isNotEmpty()) {
                $result[] = [
                    'kabkot_name'  => $kabkot->name,
                    'kabkot_nip'   => $kabkot->nip,
                    'unrated_teams'=> $unratedTeams->pluck('team_name')->toArray(),
                ];
            }
        }

        $this->kabkotIncompleteKabkotData = $result;
        $this->showKabkotIncompleteKabkotDialog = true;
    }

    private function buildKabkotRekapData(): array
    {
        $kabkots = User::role('Kepala Kabkot')->orderBy('name')->get();
        if ($kabkots->isEmpty()) return [];

        $kabkotIds = $kabkots->pluck('id');

        $allRatings = KabkotRating::whereIn('kabkot_id', $kabkotIds)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get(['kabkot_id', 'score', 'overridden_by']);

        $pimpinanScores = PimpinanKabkotScore::where('pimpinan_id', Auth::id())
            ->whereIn('kabkot_id', $kabkotIds)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get()
            ->keyBy('kabkot_id');

        $rekap = [];
        foreach ($kabkots as $kabkot) {
            $ratings    = $allRatings->where('kabkot_id', $kabkot->id);
            $scores     = $ratings->pluck('score')->filter()->values()->toArray();
            $overridden = $ratings->filter(fn($r) => $r->overridden_by)->count();
            $pimpinanRec = $pimpinanScores->get($kabkot->id);

            $rekap[] = (object) [
                'id'             => $kabkot->id,
                'name'           => $kabkot->name,
                'nip'            => $kabkot->nip,
                'avg_score'      => count($scores) ? round(array_sum($scores) / count($scores), 2) : null,
                'q3_score'       => count($scores) ? $this->calcQ3($scores) : null,
                'max_score'      => count($scores) ? round(max($scores), 2) : null,
                'rated_count'    => count($scores),
                'has_override'   => $overridden > 0,
                'pimpinan_score' => $pimpinanRec ? (float) $pimpinanRec->score : null,
            ];
        }

        return $rekap;
    }

    private function buildKabkotStats(): array
    {
        $kabkots  = User::role('Kepala Kabkot')->get();
        $allTeams = \App\Models\Team::all();

        if ($kabkots->isEmpty() || $allTeams->isEmpty()) {
            return ['incomplete_teams' => 0, 'incomplete_kabkots' => 0,
                    'total_teams' => $allTeams->count(), 'total_kabkots' => $kabkots->count()];
        }

        $ratings = KabkotRating::where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get(['team_id', 'kabkot_id']);

        $incompleteTeams = 0;
        foreach ($allTeams as $team) {
            $ratedIds = $ratings->where('team_id', $team->id)->pluck('kabkot_id');
            if ($kabkots->whereNotIn('id', $ratedIds->toArray())->isNotEmpty()) {
                $incompleteTeams++;
            }
        }

        $incompleteKabkots = 0;
        foreach ($kabkots as $kabkot) {
            $ratedTeamIds = $ratings->where('kabkot_id', $kabkot->id)->pluck('team_id');
            if ($allTeams->whereNotIn('id', $ratedTeamIds->toArray())->isNotEmpty()) {
                $incompleteKabkots++;
            }
        }

        return [
            'incomplete_teams'   => $incompleteTeams,
            'incomplete_kabkots' => $incompleteKabkots,
            'total_teams'        => $allTeams->count(),
            'total_kabkots'      => $kabkots->count(),
        ];
    }

    public function render(DashboardService $service)
    {
        $rekap = $service->getPimpinanRekap($this->month, $this->year);
        $data = collect($rekap['data']);

        // Only show pure Pegawai (not Ketua Tim / Kepala Kabkot)
        $pegawaiOnlyIds = User::role('Pegawai')
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['Ketua Tim', 'Kepala Kabkot', 'Pimpinan', 'Admin']);
            })
            ->pluck('id')
            ->all();
        $data = $data->filter(fn($u) => in_array($u->id, $pegawaiOnlyIds));

        // Augment each pegawai with min/max from team scores + pimpinan direct score
        $pimpinanPegawaiScores = PimpinanPegawaiScore::where('pimpinan_id', Auth::id())
            ->whereIn('pegawai_id', $pegawaiOnlyIds)
            ->where('period_month', $this->month)
            ->where('period_year',  $this->year)
            ->get()
            ->keyBy('pegawai_id');

        $data = $data->map(function ($u) use ($pimpinanPegawaiScores) {
            $teamScores = collect($u->details)->pluck('score')->filter(fn($s) => $s > 0)->values();
            $u->min_score = $teamScores->count() ? round($teamScores->min(), 2) : null;
            $u->max_score = $teamScores->count() ? round($teamScores->max(), 2) : null;
            $rec = $pimpinanPegawaiScores->get($u->id);
            $u->pimpinan_score = $rec ? (float) $rec->score : null;
            $raw = $u->pimpinan_score ?? ($u->averageScore > 0 ? $u->averageScore : null);
            $u->nilai_akhir = $raw !== null ? (int) round($raw, 0) : null;
            return $u;
        });

        // Filtering
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

        // Sorting
        $isDesc = $this->sortDir === 'desc';
        $data = $data->sortBy($this->sortKey, SORT_REGULAR, $isDesc);

        // Extract unique teams for filter
        $allTeams = collect($rekap['data'])->flatMap(fn($u) => collect($u->details)->pluck('teamName'))->unique()->sort();

        // Prepare charts data (single computation per render)
        $charts = $this->prepareChartsData($rekap['data']);

        if ($this->shouldRefreshCharts && $this->activeTab === 'overview') {
            $this->dispatch('refreshCharts', charts: $charts);
        }

        // Ketua Tim input tab data
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

        // Kabkot rekap tab data
        $kabkotRekap  = $this->buildKabkotRekapData();
        if ($this->kabkotSearch) {
            $q = strtolower($this->kabkotSearch);
            $kabkotRekap = array_values(array_filter($kabkotRekap, fn($d) =>
                str_contains(strtolower($d->name), $q) ||
                str_contains($d->nip ?? '', $q)
            ));
        }
        $kabkotSortKey = $this->kabkotSortKey;
        $kabkotSortDir = $this->kabkotSortDir;
        usort($kabkotRekap, function ($a, $b) use ($kabkotSortKey, $kabkotSortDir) {
            $av = $a->$kabkotSortKey ?? null;
            $bv = $b->$kabkotSortKey ?? null;
            if ($av === null && $bv === null) return 0;
            if ($av === null) return 1;
            if ($bv === null) return -1;
            return $kabkotSortDir === 'asc' ? ($av <=> $bv) : ($bv <=> $av);
        });
        $kabkotStats  = $this->buildKabkotStats();

        $sc = ScoringConfig::getAll();

        return view('livewire.dashboards.pimpinan-dashboard', [
            'rekap'         => $data,
            'allUsers'      => collect($rekap['data']),
            'stats'         => $rekap['stats'],
            'allTeams'      => $allTeams,
            'charts'        => $charts,
            'ktData'        => $ktData,
            'kabkotRekap'   => $kabkotRekap,
            'kabkotStats'   => $kabkotStats,
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
            'monthNames' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ],
            'reportUser' => $this->reportUserId ? \App\Models\User::find($this->reportUserId) : null,
        ]);
    }

    private function buildKetuaTimData(): array
    {
        $ketuaTimUsers = User::role('Ketua Tim')->with('ledTeams')->orderBy('name')->get();

        $ketuaIds = $ketuaTimUsers->pluck('id')->toArray();

        // Ratings given by each KT to their members (for rekomendasi)
        $memberRatings = Rating::whereIn('evaluator_id', $ketuaIds)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->where('score', '>', 0)
            ->get(['evaluator_id', 'team_id', 'final_score']);

        // Ratings given by Pimpinan to KT (for nilai_akhir)
        $pimpinanRatings = Rating::where('evaluator_id', Auth::id())
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

                $saved = $pimpinanRatings
                    ->where('target_user_id', $kt->id)
                    ->where('team_id', $team->id)
                    ->first();

                if ($saved) $savedScores[] = $saved->score;

                $teams[] = [
                    'key'         => $key,
                    'team_name'   => $team->team_name,
                    'avg'         => count($scores) ? round(array_sum($scores) / count($scores), 2) : null,
                    'max'         => count($scores) ? round(max($scores), 2) : null,
                    'q3'         => count($scores) ? $this->calcQ3($scores) : null,
                    'rated_count' => count($scores),
                ];
            }

            $ktData[$kt->id] = [
                'ketua_name'  => $kt->name,
                'ketua_nip'   => $kt->nip,
                'teams'       => $teams,
                'nilai_akhir' => count($savedScores) ? round(array_sum($savedScores) / count($savedScores), 2) : null,
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
        // 1. Team Performance Distribution (Average Score)
        $teamSumMap = [];
        $teamCountMap = [];
        $teamLeaderMap = [];
        $teamScoresMap = [];
        foreach ($data as $u) {
            foreach ($u->details as $d) {
                if ($d['score'] !== null && $d['score'] > 0) {
                    $t = $d['teamName'];
                    $teamSumMap[$t] = ($teamSumMap[$t] ?? 0) + $d['score'];
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
        $topTeams = array_slice($teamPerfMap, 0, 15);
        $topTeamLabels = array_keys($topTeams);
        $topTeamLeaders = array_map(fn($t) => $teamLeaderMap[$t] ?? '-', $topTeamLabels);
        $topTeamMax = array_map(fn($t) => max($teamScoresMap[$t] ?? [0]), $topTeamLabels);
        $topTeamQ3  = array_map(fn($t) => $this->calcQ3($teamScoresMap[$t] ?? [0]), $topTeamLabels);

        // 2. Perf Distribution
        $dist = ['Sangat Baik' => 0, 'Baik' => 0, 'Cukup' => 0, 'Kurang' => 0, 'Belum Dinilai' => 0];
        foreach ($data as $u) {
            if ($u->averageScore >= 90) $dist['Sangat Baik']++;
            elseif ($u->averageScore >= 80) $dist['Baik']++;
            elseif ($u->averageScore >= 60) $dist['Cukup']++;
            elseif ($u->averageScore > 0) $dist['Kurang']++;
            else $dist['Belum Dinilai']++;
        }

        // 3. Scatter
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
}
