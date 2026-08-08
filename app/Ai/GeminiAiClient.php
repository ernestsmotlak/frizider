<?php

namespace App\Ai;

use App\Contracts\AiClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiAiClient implements AiClient
{
    public function send(AiRequest $request): AiResponse
    {
        $baseUrl = rtrim(config('services.ai.gemini.base_url'), '/');

        $response = Http::withHeaders(['x-goog-api-key' => config('services.ai.gemini.key')])
            ->timeout((int)config('services.ai.gemini.timeout', 45))
            ->post("{$baseUrl}/models/{$request->model}:generateContent", [
                'system_instruction' => [
                    'parts' => [['text' => $request->systemInstruction]],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => array_map($this->encodePart(...), $request->parts),
                    ],
                ],
                // The schema lives here, in the API config — not in the prompt.
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $request->schema,
                    'thinkingConfig' => ['thinkingBudget' => $request->thinkingBudget],
                ],
            ]);

        if ($response->failed()) {
            $message = "Gemini request failed ({$response->status()}): ".substr($response->body(), 0, 500);

            // 4xx means we sent something wrong, and the same bytes will be
            // just as wrong in ten seconds. 429 is the exception — that one
            // is about timing, which is exactly what a retry fixes.
            throw $response->clientError() && $response->status() !== 429
                ? new PermanentAiException($message)
                : new RuntimeException($message);
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (!is_string($text) || $text === '') {
            throw new RuntimeException(
                'Gemini returned no content: '.substr($response->body(), 0, 500)
            );
        }

        $data = json_decode($text, true);

        if (!is_array($data)) {
            throw new RuntimeException(
                'Gemini returned undecodable content: '.substr($text, 0, 500)
            );
        }

        $tokens = $response->json('usageMetadata.totalTokenCount');

        return new AiResponse(
            data: $data,
            tokensUsed: is_numeric($tokens) ? (int)$tokens : null,
            model: (string)($response->json('modelVersion') ?? $request->model),
        );
    }

    private function encodePart(AiPart $part): array
    {
        return match ($part->type) {
            AiPart::TYPE_TEXT => ['text' => $part->text],
            AiPart::TYPE_INLINE => ['inlineData' => ['mimeType' => $part->mimeType, 'data' => $part->data]],
            AiPart::TYPE_FILE => ['fileData' => ['mimeType' => $part->mimeType, 'fileUri' => $part->uri]],
            default => throw new RuntimeException("Unknown part type: {$part->type}"),
        };
    }
}
