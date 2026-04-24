<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kabkot_ratings', function (Blueprint $table) {
            $table->foreignUuid('overridden_by')->nullable()->constrained('users')->nullOnDelete()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('kabkot_ratings', function (Blueprint $table) {
            $table->dropForeign(['overridden_by']);
            $table->dropColumn('overridden_by');
        });
    }
};
