<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use RuntimeException;

class InsufficientCreditsException extends RuntimeException
{
    public function __construct(
        public readonly int $required,
        public readonly int $available,
    )
    {
        parent::__construct("Insufficient AI credits: {$required} required, {$available} available.");
    }

    public function render(Request $request)
    {
        return response()->json([
            'message' => 'Not enough AI credits.',
            'required' => $this->required,
            'available' => $this->available,
        ], 402);
    }
}
