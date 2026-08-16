<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // There is one AI client now and it is the real one, so nothing but
        // this stops a test reaching Google with the key from .env. Any request
        // that is not explicitly faked fails the test instead of going out.
        Http::preventStrayRequests();
    }

    /**
     * Answer the next Gemini call with $payload.
     *
     * Faking the transport rather than substituting the client is the point:
     * the request encoding, the response unwrapping and the status handling in
     * GeminiAiClient are all worth exercising, and a stand-in client skips
     * every one of them — which is how a client that fabricated its own
     * answers could pass a suite that never once built a real request.
     */
    protected function fakeGemini(array $payload, int $tokens = 1234): void
    {
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode($payload)]]]]],
            'usageMetadata' => ['totalTokenCount' => $tokens],
            'modelVersion' => 'gemini-test',
        ])]);
    }
}
