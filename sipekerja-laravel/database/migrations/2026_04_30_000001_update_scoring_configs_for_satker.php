<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scoring_configs', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->uuid('satker_id')->nullable()->after('id');
            $table->unique(['key', 'satker_id']);
        });
    }

    public function down(): void
    {
        Schema::table('scoring_configs', function (Blueprint $table) {
            $table->dropUnique(['key', 'satker_id']);
            $table->dropColumn('satker_id');
            $table->unique('key');
        });
    }
};
