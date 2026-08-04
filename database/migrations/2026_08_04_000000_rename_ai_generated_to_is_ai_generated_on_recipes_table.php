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
        if (Schema::hasColumn('recipes', 'ai_generated') && ! Schema::hasColumn('recipes', 'is_ai_generated')) {
            Schema::table('recipes', function (Blueprint $table) {
                $table->renameColumn('ai_generated', 'is_ai_generated');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('recipes', 'is_ai_generated') && ! Schema::hasColumn('recipes', 'ai_generated')) {
            Schema::table('recipes', function (Blueprint $table) {
                $table->renameColumn('is_ai_generated', 'ai_generated');
            });
        }
    }
};
