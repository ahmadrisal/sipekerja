<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KabkotRating extends Model
{
    use HasUuids;

    protected $fillable = [
        'evaluator_id',
        'kabkot_id',
        'team_id',
        'score',
        'notes',
        'overridden_by',
        'override_flag_hidden',
        'period_month',
        'period_year',
    ];

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function kabkot(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kabkot_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
