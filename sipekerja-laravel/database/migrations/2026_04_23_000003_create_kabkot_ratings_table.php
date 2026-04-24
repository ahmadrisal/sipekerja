<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kabkot_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('kabkot_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->float('score');
            $table->text('notes')->nullable();
            $table->integer('period_month');
            $table->integer('period_year');
            $table->timestamps();

            $table->unique(['evaluator_id', 'kabkot_id', 'team_id', 'period_month', 'period_year'], 'kabkot_ratings_unique');
            $table->index(['period_month', 'period_year']);
            $table->index('kabkot_id');
            $table->index('evaluator_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kabkot_ratings');
    }
};
