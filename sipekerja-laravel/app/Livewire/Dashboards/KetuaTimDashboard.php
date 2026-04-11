<?php

namespace App\Livewire\Dashboards;

use App\Models\Rating;
use App\Models\Team;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class KetuaTimDashboard extends Component
{
    public $activeTab = 'dashboard';
    public $month;
    public $year;
    
    // Dashboard States
    public $showTeamsDialog = false;
    public $showMembersDialog = false;
    public $showUnratedDialog = false;

    // Penilaian States
    public $formState = [];
    public $validationMessages = [];
    public $showValidationDialog = false;
    
    // Reset Dialog States
    public $showResetDialog = false;
    public $resetKey = null;

    public $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    public function mount()
    {
        $this->month = date('n');
        $this->year = date('Y');
        $this->loadData();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updatedMonth()
    {
        $this->loadData();
        if ($this->activeTab === 'dashboard') {
            $this->dispatch('refreshKetuaCharts');
        }
    }

    public function updatedYear()
    {
        $this->loadData();
        if ($this->activeTab === 'dashboard') {
            $this->dispatch('refreshKetuaCharts');
        }
    }

    public function loadData()
    {
        $user = Auth::user();
        
        $teams = Team::where('leader_id', $user->id)->with('members')->get();
        $existingRatings = Rating::where('evaluator_id', $user->id)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get();

        $this->formState = [];
        foreach ($teams as $team) {
            foreach ($team->members as $member) {
                if ($member->id === $user->id) continue;

                $key = "{$member->id}_{$team->id}";
                $rating = $existingRatings->where('target_user_id', $member->id)->where('team_id', $team->id)->first();
                
                $hasWork = $rating ? ($rating->score > 0) : true;
                
                $this->formState[$key] = [
                    'member_id' => $member->id,
                    'team_id' => $team->id,
                    'has_work' => $hasWork,
                    'score' => $hasWork ? ($rating->score ?? '') : '',
                    'volume_work' => $hasWork ? ($rating->volume_work ?? '') : '',
                    'quality_work' => $hasWork ? ($rating->quality_work ?? '') : '',
                    'notes' => $rating->notes ?? '',
                    'is_dirty' => false,
                    'is_rated' => $rating ? true : false,
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
            if (!$entry['score']) $errors[] = 'Nilai Dasar';
            if (!$entry['volume_work']) $errors[] = 'Volume / Tingkat Kesulitan';
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

            $score = $entry['score'];
            $finalScore = $this->calculateFinalScore($entry['score'], $entry['volume_work'], $entry['quality_work']);
            $volumeWork = $entry['volume_work'];
            $qualityWork = $entry['quality_work'];
        } else {
            // Pegawai tidak ada pekerjaan di bulan tersebut.
            $score = 0;
            $finalScore = null;
            $volumeWork = null;
            $qualityWork = null;
        }

        Rating::updateOrCreate(
            [
                'evaluator_id' => Auth::id(),
                'target_user_id' => $entry['member_id'],
                'team_id' => $entry['team_id'],
                'period_month' => $this->month,
                'period_year' => $this->year,
            ],
            [
                'score' => $score,
                'volume_work' => $volumeWork,
                'quality_work' => $qualityWork,
                'notes' => 'TIDAK_ADA_PEKERJAAN',
                'final_score' => $finalScore,
            ]
        );

        $this->formState[$key]['is_dirty'] = false;
        $this->formState[$key]['is_rated'] = true;
        
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

        $key = $this->resetKey;
        $entry = $this->formState[$key];
        
        Rating::where('evaluator_id', Auth::id())
            ->where('target_user_id', $entry['member_id'])
            ->where('team_id', $entry['team_id'])
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->delete();

        $this->formState[$key] = [
            'member_id' => $entry['member_id'],
            'team_id' => $entry['team_id'],
            'has_work' => true,
            'score' => '',
            'volume_work' => '',
            'quality_work' => '',
            'notes' => '',
            'is_dirty' => false,
            'is_rated' => false,
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

    private function calculateFinalScore($score, $volumeWork, $qualityWork)
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

    public function render(DashboardService $service)
    {
        $user = Auth::user();
        $stats = $service->getKetuaTimStats($user->id, $this->month, $this->year);

        // Required for Penilaian Tab
        $teams = Team::where('leader_id', $user->id)->with('members')->get();
        $membersData = [];
        foreach ($teams as $team) {
            foreach ($team->members as $member) {
                if ($member->id === $user->id) continue;
                
                if (!isset($membersData[$member->id])) {
                    $membersData[$member->id] = [
                        'id' => $member->id,
                        'name' => $member->name,
                        'nip' => $member->nip,
                        'teams' => []
                    ];
                }
                $membersData[$member->id]['teams'][] = $team;
            }
        }

        return view('livewire.dashboards.ketua-tim-dashboard', [
            'stats' => $stats,
            'membersData' => $membersData,
        ]);
    }
}
