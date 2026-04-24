<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ScoringConfig extends Model
{
    protected $fillable = ['key', 'label', 'group', 'value'];

    protected $casts = ['value' => 'float'];

    public static function getAll(): array
    {
        return Cache::remember('scoring_configs', 3600, function () {
            return static::all()->pluck('value', 'key')->toArray();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('scoring_configs');
    }

    public static function defaults(): array
    {
        return [
            // Bobot (harus total 100)
            ['key' => 'weight_score',   'label' => 'Bobot Nilai Dasar',          'group' => 'bobot',   'value' => 80],
            ['key' => 'weight_volume',  'label' => 'Bobot Volume/Kesulitan',      'group' => 'bobot',   'value' => 10],
            ['key' => 'weight_quality', 'label' => 'Bobot Kualitas Kerja',        'group' => 'bobot',   'value' => 10],
            // Skor Volume/Kesulitan
            ['key' => 'volume_ringan',  'label' => 'Skor Volume — Ringan',        'group' => 'volume',  'value' => 60],
            ['key' => 'volume_sedang',  'label' => 'Skor Volume — Sedang',        'group' => 'volume',  'value' => 80],
            ['key' => 'volume_berat',   'label' => 'Skor Volume — Berat',         'group' => 'volume',  'value' => 100],
            // Skor Kualitas Kerja
            ['key' => 'quality_kurang',      'label' => 'Skor Kualitas — Kurang',      'group' => 'kualitas', 'value' => 50],
            ['key' => 'quality_cukup',       'label' => 'Skor Kualitas — Cukup',       'group' => 'kualitas', 'value' => 75],
            ['key' => 'quality_baik',        'label' => 'Skor Kualitas — Baik',         'group' => 'kualitas', 'value' => 90],
            ['key' => 'quality_sangat_baik', 'label' => 'Skor Kualitas — Sangat Baik', 'group' => 'kualitas', 'value' => 100],
        ];
    }
}
