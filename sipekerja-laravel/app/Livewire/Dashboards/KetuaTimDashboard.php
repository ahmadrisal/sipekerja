<?php

namespace App\Livewire\Dashboards;

use App\Models\KabkotRating;
use App\Models\Rating;
use App\Models\ScoringConfig;
use App\Models\Team;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class KetuaTimDashboard extends Component
{
    public $activeTab = 'dashboard';
    public $month;
    public $year;

    // Dashboard States
    public $showTeamsDialog   = false;
    public $showMembersDialog = false;
    public $showUnratedDialog = false;

    // Penilaian Pegawai States
    public $formState           = [];
    public $validationMessages  = [];
    public $showValidationDialog = false;
    public $showResetDialog     = false;
    public $resetKey            = null;

    // Penilaian Kabkot States
    public $kabkotFormState          = [];
    public $showKabkotResetDialog    = false;
    public $kabkotResetKey           = null;
    public $showKabkotValidationDialog = false;

    public $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    public function mount()
    {
        $this->month = date('n');
        $this->year  = date('Y');
        $this->loadData();
        $this->loadKabkotData();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updatedMonth()
    {
        $this->loadData();
        $this->loadKabkotData();
        if ($this->activeTab === 'dashboard') {
            $this->dispatch('refreshKetuaCharts');
        }
    }

    public function updatedYear()
    {
        $this->loadData();
        $this->loadKabkotData();
        if ($this->activeTab === 'dashboard') {
            $this->dispatch('refreshKetuaCharts');
        }
    }

    // ── Penilaian Pegawai ────────────────────────────────────────────

    public function loadData()
    {
        $user  = Auth::user();
        $teams = Team::where('leader_id', $user->id)->with('members')->get();

        $existingRatings = Rating::where('evaluator_id', $user->id)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get();

        $this->formState = [];
        foreach ($teams as $team) {
            foreach ($team->members as $member) {
                if ($member->id === $user->id) continue;

                $key    = "{$member->id}_{$team->id}";
                $rating = $existingRatings->where('target_user_id', $member->id)->where('team_id', $team->id)->first();
                $hasWork = $rating ? ($rating->score > 0) : true;

                $this->formState[$key] = [
                    'member_id'    => $member->id,
                    'team_id'      => $team->id,
                    'has_work'     => $hasWork,
                    'score'        => $hasWork ? ($rating->score ?? '') : '',
                    'volume_work'  => $hasWork ? ($rating->volume_work ?? '') : '',
                    'quality_work' => $hasWork ? ($rating->quality_work ?? '') : '',
                    'notes'        => ($rating && $rating->notes !== 'TIDAK_ADA_PEKERJAAN') ? ($rating->notes ?? '') : '',
                    'is_dirty'     => false,
                    'is_rated'     => $rating ? true : false,
                    'overridden'   => $rating ? (bool) $rating->overridden_by : false,
                ];
            }
        }
    }

    public function updateField($key, $field, $value)
    {
        $this->formState[$key][$field] = $value;
        $this->formState[$key]['is_dirty'] = true;
    }

    public function saveRating($key)
    {
        $entry = $this->formState[$key];

        if ($entry['has_work']) {
            $errors = [];
            if (!$entry['score'])        $errors[] = 'Nilai Dasar';
            if (!$entry['volume_work'])  $errors[] = 'Volume / Tingkat Kesulitan';
            if (!$entry['quality_work']) $errors[] = 'Kualitas Pekerjaan';

            if (!empty($errors)) {
                $this->validationMessages = $errors;
                $this->showValidationDialog = true;
                return;
            }

            if ($entry['score'] < 1 || $entry['score'] > 100) {
                $this->validationMessages = ['Nilai Dasar harus berada pada rentang 1 hingga 100'];
                $this->showValidationDialog = true;
                return;
            }

            $score       = $entry['score'];
            $finalScore  = $this->calculateFinalScore($entry['score'], $entry['volume_work'], $entry['quality_work']);
            $volumeWork  = $entry['volume_work'];
            $qualityWork = $entry['quality_work'];
            $notes       = $entry['notes'] ?: null;
        } else {
            $score = 0; $finalScore = null; $volumeWork = null; $qualityWork = null;
            $notes = 'TIDAK_ADA_PEKERJAAN';
        }

        Rating::updateOrCreate(
            ['evaluator_id' => Auth::id(), 'target_user_id' => $entry['member_id'],
             'team_id' => $entry['team_id'], 'period_month' => $this->month, 'period_year' => $this->year],
            ['score' => $score, 'volume_work' => $volumeWork, 'quality_work' => $qualityWork,
             'notes' => $notes, 'final_score' => $finalScore, 'overridden_by' => null]
        );

        $this->formState[$key]['is_dirty']   = false;
        $this->formState[$key]['is_rated']   = true;
        $this->formState[$key]['overridden'] = false;

        $stats = (new DashboardService())->getKetuaTimStats(Auth::id(), $this->month, $this->year);
        $this->dispatch('refreshKetuaCharts', chartData: $stats['teamChartData']);
        session()->flash('success', 'Penilaian berhasil disimpan.');
    }

    public function confirmReset($key)
    {
        $this->resetKey = $key;
        $this->showResetDialog = true;
    }

    public function executeReset()
    {
        if (!$this->resetKey) return;
        $key   = $this->resetKey;
        $entry = $this->formState[$key];

        Rating::where('evaluator_id', Auth::id())
            ->where('target_user_id', $entry['member_id'])
            ->where('team_id', $entry['team_id'])
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->delete();

        $this->formState[$key] = [
            'member_id' => $entry['member_id'], 'team_id' => $entry['team_id'],
            'has_work' => true, 'score' => '', 'volume_work' => '', 'quality_work' => '',
            'notes' => '', 'is_dirty' => false, 'is_rated' => false,
        ];

        $this->showResetDialog = false;
        $this->resetKey = null;
        $stats = (new DashboardService())->getKetuaTimStats(Auth::id(), $this->month, $this->year);
        $this->dispatch('refreshKetuaCharts', chartData: $stats['teamChartData']);
        session()->flash('success', 'Penilaian berhasil di-reset.');
    }

    public function cancelReset()
    {
        $this->showResetDialog = false;
        $this->resetKey = null;
    }

    // ── Penilaian Kabkot ─────────────────────────────────────────────

    public function loadKabkotData(): void
    {
        $user      = Auth::user();
        $teams     = Team::where('leader_id', $user->id)->get();
        $kabkots   = User::role('Kepala Kabkot')->orderBy('name')->get();

        $existing = KabkotRating::where('evaluator_id', $user->id)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get();

        $this->kabkotFormState = [];
        foreach ($teams as $team) {
            foreach ($kabkots as $kabkot) {
                $key    = "{$kabkot->id}_{$team->id}";
                $rating = $existing->where('kabkot_id', $kabkot->id)->where('team_id', $team->id)->first();

                $hasWork = $rating ? ($rating->score > 0) : true;

                $this->kabkotFormState[$key] = [
                    'kabkot_id'   => $kabkot->id,
                    'kabkot_name' => $kabkot->name,
                    'kabkot_nip'  => $kabkot->nip,
                    'team_id'     => $team->id,
                    'team_name'   => $team->team_name,
                    'has_work'    => $hasWork,
                    'score'       => ($rating && $rating->score > 0) ? $rating->score : '',
                    'notes'       => ($rating && $rating->notes !== 'TIDAK_ADA_PEKERJAAN') ? ($rating->notes ?? '') : '',
                    'is_dirty'    => false,
                    'is_rated'    => $rating ? true : false,
                    'overridden'  => $rating && $rating->overridden_by && !$rating->override_flag_hidden,
                ];
            }
        }
    }

    public function saveKabkotRating($key): void
    {
        $entry = $this->kabkotFormState[$key];

        if ($entry['has_work']) {
            if (!$entry['score'] || $entry['score'] < 1 || $entry['score'] > 100) {
                $this->showKabkotValidationDialog = true;
                return;
            }
            $score = $entry['score'];
            $notes = $entry['notes'] ?: null;
        } else {
            $score = 0;
            $notes = 'TIDAK_ADA_PEKERJAAN';
        }

        KabkotRating::updateOrCreate(
            ['evaluator_id' => Auth::id(), 'kabkot_id' => $entry['kabkot_id'],
             'team_id' => $entry['team_id'], 'period_month' => $this->month, 'period_year' => $this->year],
            ['score' => $score, 'notes' => $notes]
        );

        $this->kabkotFormState[$key]['is_dirty'] = false;
        $this->kabkotFormState[$key]['is_rated'] = true;
        session()->flash('kabkot_success', 'Penilaian Kabkot berhasil disimpan.');
    }

    public function confirmResetKabkot($key): void
    {
        $this->kabkotResetKey = $key;
        $this->showKabkotResetDialog = true;
    }

    public function executeResetKabkot(): void
    {
        if (!$this->kabkotResetKey) return;
        $key   = $this->kabkotResetKey;
        $entry = $this->kabkotFormState[$key];

        KabkotRating::where('evaluator_id', Auth::id())
            ->where('kabkot_id', $entry['kabkot_id'])
            ->where('team_id', $entry['team_id'])
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->delete();

        $this->kabkotFormState[$key]['score']    = '';
        $this->kabkotFormState[$key]['notes']    = '';
        $this->kabkotFormState[$key]['has_work'] = true;
        $this->kabkotFormState[$key]['is_dirty'] = false;
        $this->kabkotFormState[$key]['is_rated'] = false;

        $this->showKabkotResetDialog = false;
        $this->kabkotResetKey = null;
        session()->flash('kabkot_success', 'Penilaian Kabkot berhasil di-reset.');
    }

    public function cancelResetKabkot(): void
    {
        $this->showKabkotResetDialog = false;
        $this->kabkotResetKey = null;
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function calculateFinalScore($score, $volumeWork, $qualityWork): float
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

    public function render(DashboardService $service)
    {
        $user  = Auth::user();
        $stats = $service->getKetuaTimStats($user->id, $this->month, $this->year);

        $teams      = Team::where('leader_id', $user->id)->with('members')->get();
        $membersData = [];
        foreach ($teams as $team) {
            foreach ($team->members as $member) {
                if ($member->id === $user->id) continue;
                if (!isset($membersData[$member->id])) {
                    $membersData[$member->id] = ['id' => $member->id, 'name' => $member->name, 'nip' => $member->nip, 'teams' => []];
                }
                $membersData[$member->id]['teams'][] = $team;
            }
        }

        // Build kabkot grouped data for view (group by kabkot_id)
        $kabkotData = [];
        foreach ($this->kabkotFormState as $key => $entry) {
            $kid = $entry['kabkot_id'];
            if (!isset($kabkotData[$kid])) {
                $kabkotData[$kid] = [
                    'kabkot_name' => $entry['kabkot_name'],
                    'kabkot_nip'  => $entry['kabkot_nip'],
                    'teams'       => [],
                ];
            }
            $kabkotData[$kid]['teams'][] = ['key' => $key, 'team_name' => $entry['team_name']];
        }

        $c = ScoringConfig::getAll();

        return view('livewire.dashboards.ketua-tim-dashboard', [
            'stats'         => $stats,
            'membersData'   => $membersData,
            'kabkotData'    => $kabkotData,
            'scoringConfig' => [
                'weight_score'        => $c['weight_score']        ?? 80,
                'weight_volume'       => $c['weight_volume']       ?? 10,
                'weight_quality'      => $c['weight_quality']      ?? 10,
                'volume_ringan'       => $c['volume_ringan']       ?? 60,
                'volume_sedang'       => $c['volume_sedang']       ?? 80,
                'volume_berat'        => $c['volume_berat']        ?? 100,
                'quality_kurang'      => $c['quality_kurang']      ?? 50,
                'quality_cukup'       => $c['quality_cukup']       ?? 75,
                'quality_baik'        => $c['quality_baik']        ?? 90,
                'quality_sangat_baik' => $c['quality_sangat_baik'] ?? 100,
            ],
        ]);
    }
}
