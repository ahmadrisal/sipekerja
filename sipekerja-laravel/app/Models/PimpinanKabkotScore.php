<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PimpinanKabkotScore extends Model
{
    use HasUuids;

    protected $fillable = [
        'pimpinan_id',
        'kabkot_id',
        'score',
        'period_month',
        'period_year',
    ];

    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pimpinan_id');
    }

    public function kabkot(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kabkot_id');
    }
}
