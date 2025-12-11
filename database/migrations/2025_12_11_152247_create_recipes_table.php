<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();

            $table->unsignedInteger('servings')->nullable();
            $table->unsignedInteger('prep_time')->nullable();
            $table->unsignedInteger('cook_time')->nullable();

            $table->string('image_url', 500)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
