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

        // One provider, bound outright. There used to be a driver switch here
        // with a stand-in client behind the default arm, which meant any value
        // that was not exactly "gemini" — a typo, a missing key, a stale config
        // cache — quietly served fabricated data to real users and charged a
        // credit for it. Tests fake the transport instead; see TestCase.
        $this->app->bind(\App\Contracts\AiClient::class, \App\Ai\GeminiAiClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
