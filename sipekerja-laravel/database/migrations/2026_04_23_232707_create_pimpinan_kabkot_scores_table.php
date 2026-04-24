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
        Schema::create('pimpinan_kabkot_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pimpinan_id');
            $table->uuid('kabkot_id');
            $table->decimal('score', 5, 2)->nullable();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->timestamps();

            $table->foreign('pimpinan_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('kabkot_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['pimpinan_id', 'kabkot_id', 'period_month', 'period_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pimpinan_kabkot_scores');
    }
};
