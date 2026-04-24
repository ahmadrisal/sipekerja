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

    public function mount(): void
    {
        $this->loadConfig();
    }

    private function loadConfig(): void
    {
        $configs = ScoringConfig::all()->keyBy('key');

        $this->bobot = [
            'weight_score'   => $configs['weight_score']->value   ?? 80,
            'weight_volume'  => $configs['weight_volume']->value  ?? 10,
            'weight_quality' => $configs['weight_quality']->value ?? 10,
        ];

        $this->volume = [
            'volume_ringan' => $configs['volume_ringan']->value ?? 60,
            'volume_sedang' => $configs['volume_sedang']->value ?? 80,
            'volume_berat'  => $configs['volume_berat']->value  ?? 100,
        ];

        $this->kualitas = [
            'quality_kurang'      => $configs['quality_kurang']->value      ?? 50,
            'quality_cukup'       => $configs['quality_cukup']->value       ?? 75,
            'quality_baik'        => $configs['quality_baik']->value        ?? 90,
            'quality_sangat_baik' => $configs['quality_sangat_baik']->value ?? 100,
        ];
    }

    public function save(): void
    {
        $this->errorMessage = '';
        $this->showSuccess = false;

        $totalBobot = array_sum($this->bobot);
        if (abs($totalBobot - 100) > 0.01) {
            $this->errorMessage = "Total bobot harus 100%. Saat ini: {$totalBobot}%";
            return;
        }

        foreach ([$this->bobot, $this->volume, $this->kualitas] as $group) {
            foreach ($group as $key => $value) {
                if (!is_numeric($value) || $value < 0) {
                    $this->errorMessage = "Semua nilai harus berupa angka positif.";
                    return;
                }
            }
        }

        $all = array_merge($this->bobot, $this->volume, $this->kualitas);
        foreach ($all as $key => $value) {
            ScoringConfig::where('key', $key)->update(['value' => (float) $value]);
        }

        ScoringConfig::clearCache();
        $this->recalculateAllRatings();
        $this->showSuccess = true;
    }

    private function recalculateAllRatings(): void
    {
        $c = ScoringConfig::getAll();

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

        Rating::all()->each(function (Rating $rating) use ($wScore, $wVolume, $wQuality, $volMap, $qualMap) {
            $volScore  = $volMap[$rating->volume_work]   ?? $volMap['Sedang'];
            $qualScore = $qualMap[$rating->quality_work] ?? $qualMap['Cukup'];

            $rating->final_score = round(
                ($rating->score * $wScore) + ($volScore * $wVolume) + ($qualScore * $wQuality),
                2
            );
            $rating->save();
        });
    }

    public function resetToDefault(): void
    {
        foreach (ScoringConfig::defaults() as $d) {
            ScoringConfig::where('key', $d['key'])->update(['value' => $d['value']]);
        }
        ScoringConfig::clearCache();
        $this->recalculateAllRatings();
        $this->loadConfig();
        $this->showSuccess = true;
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.konfigurasi.index');
    }
}
