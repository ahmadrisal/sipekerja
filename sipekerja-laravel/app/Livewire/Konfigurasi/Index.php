<?php

namespace App\Livewire\Konfigurasi;

use App\Models\Rating;
use App\Models\ScoringConfig;
use Livewire\Component;

class Index extends Component
{
    public array $bobot = [];
    public array $volume = [];
    public array $kualitas = [];

    public bool $showSuccess = false;
    public string $errorMessage = '';
    public bool $hasLocalOverride = false;

    public function mount(): void
    {
        $this->loadConfig();
    }

    private function loadConfig(): void
    {
        $satkerId = activeSatkerId();
        $c = ScoringConfig::getAll($satkerId);

        $this->bobot = [
            'weight_score'   => $c['weight_score']   ?? 80,
            'weight_volume'  => $c['weight_volume']  ?? 10,
            'weight_quality' => $c['weight_quality'] ?? 10,
        ];

        $this->volume = [
            'volume_ringan' => $c['volume_ringan'] ?? 60,
            'volume_sedang' => $c['volume_sedang'] ?? 80,
            'volume_berat'  => $c['volume_berat']  ?? 100,
        ];

        $this->kualitas = [
            'quality_kurang'      => $c['quality_kurang']      ?? 50,
            'quality_cukup'       => $c['quality_cukup']       ?? 75,
            'quality_baik'        => $c['quality_baik']        ?? 90,
            'quality_sangat_baik' => $c['quality_sangat_baik'] ?? 100,
        ];

        $this->hasLocalOverride = $satkerId
            && ScoringConfig::where('satker_id', $satkerId)->exists();
    }

    public function save(): void
    {
        $this->errorMessage = '';
        $this->showSuccess  = false;

        $totalBobot = array_sum($this->bobot);
        if (abs($totalBobot - 100) > 0.01) {
            $this->errorMessage = "Total bobot harus 100%. Saat ini: {$totalBobot}%";
            return;
        }

        foreach (array_merge($this->bobot, $this->volume, $this->kualitas) as $value) {
            if (!is_numeric($value) || $value < 0) {
                $this->errorMessage = "Semua nilai harus berupa angka positif.";
                return;
            }
        }

        $satkerId = activeSatkerId();
        $all = array_merge($this->bobot, $this->volume, $this->kualitas);

        foreach ($all as $key => $value) {
            if ($satkerId) {
                ScoringConfig::setForSatker($satkerId, $key, (float) $value);
            } else {
                ScoringConfig::setGlobal($key, (float) $value);
            }
        }

        $this->recalculateRatingsForSatker($satkerId);
        $this->hasLocalOverride = true;
        $this->showSuccess = true;
    }

    private function recalculateRatingsForSatker(?string $satkerId): void
    {
        $c = ScoringConfig::getAll($satkerId);

        $wScore   = $c['weight_score']   / 100;
        $wVolume  = $c['weight_volume']  / 100;
        $wQuality = $c['weight_quality'] / 100;

        $volMap = [
            'Ringan' => $c['volume_ringan'],
            'Sedang' => $c['volume_sedang'],
            'Berat'  => $c['volume_berat'],
        ];
        $qualMap = [
            'Kurang'      => $c['quality_kurang'],
            'Cukup'       => $c['quality_cukup'],
            'Baik'        => $c['quality_baik'],
            'Sangat Baik' => $c['quality_sangat_baik'],
        ];

        $query = $satkerId
            ? Rating::where('satker_id', $satkerId)
            : Rating::query();

        $query->each(function (Rating $rating) use ($wScore, $wVolume, $wQuality, $volMap, $qualMap) {
            $volScore  = $volMap[$rating->volume_work]   ?? $volMap['Sedang'];
            $qualScore = $qualMap[$rating->quality_work] ?? $qualMap['Cukup'];
            $rating->update(['final_score' => round(
                ($rating->score * $wScore) + ($volScore * $wVolume) + ($qualScore * $wQuality), 2
            )]);
        });
    }

    public function resetToGlobal(): void
    {
        $satkerId = activeSatkerId();
        if ($satkerId) {
            ScoringConfig::resetSatkerToGlobal($satkerId);
        }
        $this->recalculateRatingsForSatker($satkerId);
        $this->loadConfig();
        $this->showSuccess  = true;
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.konfigurasi.index');
    }
}
