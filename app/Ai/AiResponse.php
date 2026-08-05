<?php

namespace App\Ai;

final readonly class AiResponse
{
    /**
     * @param array $data decoded response matching the request's schema
     */
    public function __construct(
        public array  $data,
        public ?int   $tokensUsed,
        public string $model,
    )
    {
    }
}
