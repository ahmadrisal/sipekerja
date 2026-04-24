<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PimpinanPegawaiScore extends Model
{
    use HasUuids;

    protected $fillable = [
        'pimpinan_id',
        'pegawai_id',
        'score',
        'period_month',
        'period_year',
    ];

    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pimpinan_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }
}
