<?php

namespace App\Livewire\Penilaian;



use App\Models\Rating;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    public $month;
    public $year;
    public $formState = [];
    public $validationMessages = [];
    public $showValidationDialog = false;

    protected $listeners = ['refresh' => '$refresh'];

    public function mount()
    {
        $this->month = request('month', date('n'));
        $this->year = request('year', date('Y'));
        $this->loadData();
    }

    public function updatedMonth() { $this->loadData(); }
    public function updatedYear() { $this->loadData(); }

    public function loadData()
    {
        $user = Auth::user();
        
        // Fetch teams led by this user with their members
        $teams = Team::where('leader_id', $user->id)->with('members')->get();
        
        // Fetch existing ratings for this period
        $existingRatings = Rating::where('evaluator_id', $user->id)
            ->where('period_month', $this->month)
            ->where('period_year', $this->year)
            ->get();

        $this->formState = [];
        foreach ($teams as $team) {
            foreach ($team->members as $member) {
                // Skip if member is the leader themselves (usually not the case but safe)
                if ($member->id === $user->id) continue;

                $key = "{$member->id}_{$team->id}";
                $rating = $existingRatings->where('target_user_id', $member->id)->where('team_id', $team->id)->first();
                
                $this->formState[$key] = [
                    'member_id' => $member->id,
                    'team_id' => $team->id,
                    'score' => $rating->score ?? '',
                    'volume_work' => $rating->volume_work ?? '',
                    'quality_work' => $rating->quality_work ?? '',
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
        
        // Validation
        $errors = [];
        if (!$entry['score']) $errors[] = 'Nilai Dasar';
        if (!$entry['volume_work']) $errors[] = 'Volume/Tingkat Kesulitan';
        if (!$entry['quality_work']) $errors[] = 'Kualitas Pekerjaan';

        if (!empty($errors)) {
            $this->validationMessages = $errors;
            $this->showValidationDialog = true;
            return;
        }

        if ($entry['score'] < 1 || $entry['score'] > 100) {
            $this->validationMessages = ['Nilai Dasar harus antara 1-100'];
            $this->showValidationDialog = true;
            return;
        }

        // Calculate Final Score using 80/10/10 rule
        $finalScore = $this->calculateFinalScore($entry['score'], $entry['volume_work'], $entry['quality_work']);

        Rating::updateOrCreate(
            [
                'evaluator_id' => Auth::id(),
                'target_user_id' => $entry['member_id'],
                'team_id' => $entry['team_id'],
                'period_month' => $this->month,
                'period_year' => $this->year,
            ],
            [
                'score' => $entry['score'],
                'volume_work' => $entry['volume_work'],
                'quality_work' => $entry['quality_work'],
                'notes' => $entry['notes'],
                'final_score' => $finalScore,
            ]
        );

        $this->formState[$key]['is_dirty'] = false;
        $this->formState[$key]['is_rated'] = true;
        
        session()->flash('success', 'Penilaian berhasil disimpan.');
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

    public function render()
    {
        $user = Auth::user();
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

        return view('livewire.penilaian.index', [
            'members' => $membersData,
            'monthNames' => ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
        ]);
    }
}
