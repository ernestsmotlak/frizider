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
        Schema::table('cooking_sessions', function (Blueprint $table) {
            $table->decimal('timer_fab_x_percent', 5, 4)->nullable()->after('recipe_id');
            $table->decimal('timer_fab_y_percent', 5, 4)->nullable()->after('timer_fab_x_percent');
        });

        Schema::create('cooking_session_timers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooking_session_id')->constrained()->onDelete('cascade');

            $table->timestamp('started_at');
            $table->unsignedInteger('duration_seconds');
            $table->string('note', 255)->nullable();

            $table->string('status', 20)->default('running');

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->unsignedInteger('remaining_seconds_at_pause')->nullable();

            $table->timestamps();

            $table->index(['cooking_session_id', 'status']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cooking_session_timers');

        Schema::table('cooking_sessions', function (Blueprint $table) {
            $table->dropColumn(['timer_fab_x_percent', 'timer_fab_y_percent']);
        });
    }
};
