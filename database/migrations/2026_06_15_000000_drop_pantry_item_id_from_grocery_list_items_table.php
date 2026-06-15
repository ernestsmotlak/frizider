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
        Schema::table('grocery_list_items', function (Blueprint $table) {
            $table->dropForeign(['pantry_item_id']);
            $table->dropColumn('pantry_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grocery_list_items', function (Blueprint $table) {
            $table->foreignId('pantry_item_id')->nullable()->after('grocery_list_id')->constrained()->nullOnDelete();
        });
    }
};
