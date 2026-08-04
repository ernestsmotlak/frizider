<?php

namespace App\Enums;

enum AiGenerationStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failure = 'failure';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Failure]);
    }

}
