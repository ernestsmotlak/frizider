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
        Schema::create('shopping_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grocery_list_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('shopping_session_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('notes')->nullable();
            $table->string('unit', 10)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_purchased')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_items');
    }
};
