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
        Schema::table('user_ai_recipe_logs', function (Blueprint $table) {
            $table->string('status', 16)->default('pending')->after('action');
            $table->timestamp('completed_at')->nullable()->after('tokens_used');
            $table->dropColumn('success');

            $table->dropIndex('user_ai_recipe_logs_idempotency_key_index');
            $table->unique(['user_id', 'idempotency_key']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_ai_recipe_logs', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'idempotency_key']);
            $table->dropIndex(['user_id', 'status']);
            $table->index('idempotency_key');

            $table->boolean('success')->default(false)->after('action');
            $table->dropColumn(['status', 'completed_at']);
        });
    }
};
