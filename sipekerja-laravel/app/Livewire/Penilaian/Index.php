<?php

namespace App\Livewire\Penilaian;



use App\Models\Rating;
use App\Models\ScoringConfig;
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
        $teams = Team::where('leader_id', $user->id)->where('satker_id', activeSatkerId())->with('members')->get();

        // Fetch existing ratings for this period
        $existingRatings = Rating::where('evaluator_id', $user->id)
            ->where('satker_id', activeSatkerId())
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
                'satker_id'    => activeSatkerId(),
                'score'        => $entry['score'],
                'volume_work'  => $entry['volume_work'],
                'quality_work' => $entry['quality_work'],
                'notes'        => $entry['notes'],
                'final_score'  => $finalScore,
            ]
        );

        $this->formState[$key]['is_dirty'] = false;
        $this->formState[$key]['is_rated'] = true;
        
        session()->flash('success', 'Penilaian berhasil disimpan.');
    }

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

    public function render()
    {
        $user = Auth::user();
        $teams = Team::where('leader_id', $user->id)->where('satker_id', activeSatkerId())->with('members')->get();

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

        $c = ScoringConfig::getAll();

        return view('livewire.penilaian.index', [
            'members'    => $membersData,
            'monthNames' => ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
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
