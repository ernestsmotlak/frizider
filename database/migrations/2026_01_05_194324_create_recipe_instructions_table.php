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
        Schema::create('recipe_instructions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('recipe_id')
                ->constrained()
                ->onDelete('cascade');
            
            $table->text('instruction');
            $table->unsignedInteger('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_instructions');
    }
};
