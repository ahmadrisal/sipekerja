<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('target_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('team_id')->constrained('teams')->onDelete('cascade');
            $table->float('score');
            $table->text('notes')->nullable();
            $table->string('volume_work')->nullable();
            $table->string('quality_work')->nullable();
            $table->float('final_score')->nullable();
            $table->integer('period_month');
            $table->integer('period_year');
            $table->timestamps();

            $table->index(['period_month', 'period_year']);
            $table->index('target_user_id');
            $table->index('evaluator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
