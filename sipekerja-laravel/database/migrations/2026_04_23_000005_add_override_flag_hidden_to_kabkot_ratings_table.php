<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kabkot_ratings', function (Blueprint $table) {
            $table->boolean('override_flag_hidden')->default(false)->after('overridden_by');
        });
    }

    public function down(): void
    {
        Schema::table('kabkot_ratings', function (Blueprint $table) {
            $table->dropColumn('override_flag_hidden');
        });
    }
};
