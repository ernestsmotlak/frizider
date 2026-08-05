<?php

namespace App\Ai;

/**
 * One piece of user-provided content in an AI request: text, inline binary
 * data (base64), or a reference to a file uploaded via a provider's Files API.
 *
 * Instructions never travel through parts — they belong in the system
 * instruction. Keeping the two separate is the prompt-injection mitigation.
 */
final readonly class AiPart
{
    public const TYPE_TEXT = 'text';
    public const TYPE_INLINE = 'inline';
    public const TYPE_FILE = 'file';

    private function __construct(
        public string  $type,
        public ?string $text = null,
        public ?string $mimeType = null,
        public ?string $data = null,
        public ?string $uri = null,
    )
    {
    }

    public static function text(string $text): self
    {
        return new self(self::TYPE_TEXT, text: $text);
    }

    public static function inline(string $mimeType, string $base64Data): self
    {
        return new self(self::TYPE_INLINE, mimeType: $mimeType, data: $base64Data);
    }

    public static function file(string $mimeType, string $uri): self
    {
        return new self(self::TYPE_FILE, mimeType: $mimeType, uri: $uri);
    }
}
