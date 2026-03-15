<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_ai_recipe_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('recipe_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('source_recipe_id')->nullable()->constrained('recipes')->onDelete('set null');
            $table->string('action', 64);
            $table->json('request_meta')->nullable();
            $table->boolean('success');
            $table->text('error_message')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->string('idempotency_key', 64)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ai_recipe_logs');
    }
};
