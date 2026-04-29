<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satker extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'type', 'kode', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function isProvinsi(): bool
    {
        return $this->type === 'provinsi';
    }

    public function isKabkot(): bool
    {
        return $this->type === 'kabkot';
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'provinsi' ? 'Provinsi' : 'Kabupaten/Kota';
    }
}
