<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cooking_session_timers', function (Blueprint $table) {
            $table->integer('original_duration_seconds')
                ->nullable()
                ->after('duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cooking_session_timers', function (Blueprint $table) {
            $table->dropColumn('original_duration_seconds');
        });
    }
};
