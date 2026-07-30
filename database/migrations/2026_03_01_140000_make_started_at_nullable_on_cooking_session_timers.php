<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MODIFY is MySQL-only; sqlite (fresh test databases) uses the schema builder.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE cooking_session_timers MODIFY started_at TIMESTAMP NULL");
        } else {
            Schema::table('cooking_session_timers', function (Blueprint $table) {
                $table->timestamp('started_at')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE cooking_session_timers MODIFY started_at TIMESTAMP NOT NULL");
        } else {
            Schema::table('cooking_session_timers', function (Blueprint $table) {
                $table->timestamp('started_at')->nullable(false)->change();
            });
        }
    }
};
