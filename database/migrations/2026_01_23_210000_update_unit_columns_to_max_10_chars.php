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
        // Truncate existing unit values longer than 10 characters
        DB::statement('UPDATE pantry_items SET unit = SUBSTRING(unit, 1, 10) WHERE CHAR_LENGTH(unit) > 10');
        DB::statement('UPDATE recipe_ingredients SET unit = SUBSTRING(unit, 1, 10) WHERE CHAR_LENGTH(unit) > 10');
        DB::statement('UPDATE grocery_list_items SET unit = SUBSTRING(unit, 1, 10) WHERE CHAR_LENGTH(unit) > 10');
        DB::statement('UPDATE shopping_items SET unit = SUBSTRING(unit, 1, 10) WHERE CHAR_LENGTH(unit) > 10');

        Schema::table('pantry_items', function (Blueprint $table) {
            $table->string('unit', 10)->nullable()->change();
        });

        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->string('unit', 10)->nullable()->change();
        });

        Schema::table('grocery_list_items', function (Blueprint $table) {
            $table->string('unit', 10)->nullable()->change();
        });

        Schema::table('shopping_items', function (Blueprint $table) {
            $table->string('unit', 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pantry_items', function (Blueprint $table) {
            $table->string('unit', 50)->nullable()->change();
        });

        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->string('unit', 50)->nullable()->change();
        });

        Schema::table('grocery_list_items', function (Blueprint $table) {
            $table->string('unit', 50)->nullable()->change();
        });

        Schema::table('shopping_items', function (Blueprint $table) {
            $table->string('unit')->nullable()->change();
        });
    }
};
