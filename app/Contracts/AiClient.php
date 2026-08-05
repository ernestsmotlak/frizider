<?php

namespace App\Contracts;

use App\Ai\AiRequest;
use App\Ai\AiResponse;

interface AiClient
{
    /**
     * @throws \RuntimeException when the provider fails or returns an unusable shape
     */
    public function send(AiRequest $request): AiResponse;
}
