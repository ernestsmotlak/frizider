<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Ai\PromptRepository::class);

        $this->app->bind(\App\Contracts\AiClient::class, function () {
            return match (config('services.ai.driver')) {
                'gemini' => new \App\Ai\GeminiAiClient(),
                default => new \App\Ai\FakeAiClient(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
