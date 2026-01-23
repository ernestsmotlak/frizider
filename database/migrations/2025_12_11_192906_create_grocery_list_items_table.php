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
        Schema::create('grocery_list_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('grocery_list_id')->constrained()->onDelete('cascade');

            $table->foreignId('pantry_item_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name', 255);
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit', 10)->nullable();
            $table->string('notes', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            // Shopping-specific
            $table->boolean('is_purchased')->default(false);
            $table->timestamps();

            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grocery_list_items');
    }
};
