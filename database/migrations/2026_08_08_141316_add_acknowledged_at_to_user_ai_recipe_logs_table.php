<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_ai_recipe_logs', function (Blueprint $table) {
            // When the user was told how this run ended. Null means the result
            // is still news. Replaces a per-browser localStorage marker, so
            // dismissing on one device now settles it everywhere.
            $table->timestamp('acknowledged_at')->nullable()->after('completed_at');
        });

        // Everything already finished has had its chance to be announced.
        // Without this, deploying would greet each user with a burst of
        // results they saw days ago.
        DB::table('user_ai_recipe_logs')
            ->whereNull('acknowledged_at')
            ->whereIn('status', ['completed', 'failed'])
            ->update(['acknowledged_at' => DB::raw('COALESCE(completed_at, updated_at, created_at)')]);
    }

    public function down(): void
    {
        Schema::table('user_ai_recipe_logs', function (Blueprint $table) {
            $table->dropColumn('acknowledged_at');
        });
    }
};
