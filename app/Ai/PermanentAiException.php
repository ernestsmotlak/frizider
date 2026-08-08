<?php

namespace App\Ai;

use RuntimeException;

/**
 * The request itself was wrong — a bad model name, a rejected schema, a dead
 * key. Repeating it byte for byte cannot change the answer, so the job stops
 * at the first attempt instead of spending its retries and the user's patience.
 *
 * Extends RuntimeException so nothing that already catches the generic client
 * failure has to know about this distinction.
 */
class PermanentAiException extends RuntimeException
{
}
