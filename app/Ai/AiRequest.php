<?php

namespace App\Ai;

final readonly class AiRequest
{
    /**
     * @param AiPart[] $parts user data only — never instructions
     * @param array    $schema response schema enforced by the provider
     */
    public function __construct(
        public string $systemInstruction,
        public array  $parts,
        public array  $schema,
        public string $model,
        public int    $thinkingBudget = 0,
    )
    {
    }
}
