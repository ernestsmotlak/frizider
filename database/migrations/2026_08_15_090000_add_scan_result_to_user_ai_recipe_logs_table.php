<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_ai_recipe_logs', function (Blueprint $table) {
            // A recipe run produces one row and points at it with recipe_id. A
            // pantry scan produces a list that does not exist anywhere yet —
            // it is a suggestion, and nothing is written until the user has
            // agreed to it. This is where that list waits.
            $table->json('result_json')->nullable()->after('recipe_id');

            // When the user turned that suggestion into real pantry items.
            // Null means the scan is still waiting to be reviewed, which is
            // also what stops a second tap from adding everything twice.
            $table->timestamp('confirmed_at')->nullable()->after('acknowledged_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_ai_recipe_logs', function (Blueprint $table) {
            $table->dropColumn(['result_json', 'confirmed_at']);
        });
    }
};
