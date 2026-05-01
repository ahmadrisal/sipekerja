<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill satker_id on ratings that have it null, derived from the team's satker_id
        DB::statement('
            UPDATE ratings
            SET satker_id = (
                SELECT teams.satker_id
                FROM teams
                WHERE teams.id = ratings.team_id
                  AND teams.satker_id IS NOT NULL
            )
            WHERE satker_id IS NULL
        ');
    }

    public function down(): void
    {
        //
    }
};
