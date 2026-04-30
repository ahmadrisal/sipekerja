<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ScoringConfig extends Model
{
    protected $fillable = ['key', 'label', 'group', 'value', 'satker_id'];

    protected $casts = ['value' => 'float'];

    // Global defaults (satker_id = null), optionally merged with satker overrides
    public static function getAll(?string $satkerId = null): array
    {
        $cacheKey = 'scoring_configs_' . ($satkerId ?? 'global');
        return Cache::remember($cacheKey, 3600, function () use ($satkerId) {
            $global = static::whereNull('satker_id')->where('key', '!=', 'maintenance_mode')
                ->pluck('value', 'key')->toArray();
            if (!$satkerId) return $global;
            $overrides = static::where('satker_id', $satkerId)->pluck('value', 'key')->toArray();
            return array_merge($global, $overrides);
        });
    }

    public static function setGlobal(string $key, float $value): void
    {
        static::updateOrCreate(
            ['key' => $key, 'satker_id' => null],
            ['value' => $value]
        );
        static::clearCache();
    }

    public static function setForSatker(string $satkerId, string $key, float $value): void
    {
        static::updateOrCreate(
            ['key' => $key, 'satker_id' => $satkerId],
            ['value' => $value]
        );
        static::clearCache($satkerId);
    }

    public static function resetSatkerToGlobal(string $satkerId): void
    {
        static::where('satker_id', $satkerId)->delete();
        static::clearCache($satkerId);
    }

    public static function clearCache(?string $satkerId = null): void
    {
        Cache::forget('scoring_configs_global');
        if ($satkerId) {
            Cache::forget('scoring_configs_' . $satkerId);
        } else {
            Satker::pluck('id')->each(fn($id) => Cache::forget('scoring_configs_' . $id));
        }
    }

    public static function getMaintenanceMode(): bool
    {
        return Cache::remember('maintenance_mode', 60, function () {
            $row = static::whereNull('satker_id')->where('key', 'maintenance_mode')->first();
            return $row && $row->value >= 1.0;
        });
    }

    public static function setMaintenanceMode(bool $on): void
    {
        static::updateOrCreate(
            ['key' => 'maintenance_mode', 'satker_id' => null],
            ['label' => 'Maintenance Mode', 'group' => 'system', 'value' => $on ? 1.0 : 0.0]
        );
        Cache::forget('maintenance_mode');
    }

    public static function defaults(): array
    {
        return [
            ['key' => 'weight_score',        'label' => 'Bobot Nilai Kinerja',         'group' => 'bobot',    'value' => 80],
            ['key' => 'weight_volume',        'label' => 'Bobot Volume/Kesulitan',      'group' => 'bobot',    'value' => 10],
            ['key' => 'weight_quality',       'label' => 'Bobot Kualitas Kerja',        'group' => 'bobot',    'value' => 10],
            ['key' => 'volume_ringan',        'label' => 'Skor Volume — Ringan',        'group' => 'volume',   'value' => 60],
            ['key' => 'volume_sedang',        'label' => 'Skor Volume — Sedang',        'group' => 'volume',   'value' => 80],
            ['key' => 'volume_berat',         'label' => 'Skor Volume — Berat',         'group' => 'volume',   'value' => 100],
            ['key' => 'quality_kurang',       'label' => 'Skor Kualitas — Kurang',      'group' => 'kualitas', 'value' => 50],
            ['key' => 'quality_cukup',        'label' => 'Skor Kualitas — Cukup',       'group' => 'kualitas', 'value' => 75],
            ['key' => 'quality_baik',         'label' => 'Skor Kualitas — Baik',        'group' => 'kualitas', 'value' => 90],
            ['key' => 'quality_sangat_baik',  'label' => 'Skor Kualitas — Sangat Baik', 'group' => 'kualitas', 'value' => 100],
        ];
    }
}
