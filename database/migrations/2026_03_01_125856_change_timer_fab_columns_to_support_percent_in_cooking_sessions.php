<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cooking_sessions', function (Blueprint $table) {
            $table->decimal('timer_fab_x_percent', 5, 2)->nullable()->change();
            $table->decimal('timer_fab_y_percent', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cooking_sessions', function (Blueprint $table) {
            $table->decimal('timer_fab_x_percent', 5, 4)->nullable()->change();
            $table->decimal('timer_fab_y_percent', 5, 4)->nullable()->change();
        });
    }
};
