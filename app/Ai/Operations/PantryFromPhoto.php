<?php

namespace App\Ai\Operations;

use App\Ai\AiPart;
use App\Ai\AiRequest;
use App\Ai\AiResponse;
use App\Ai\PermanentAiException;
use App\Ai\PromptRepository;
use App\Ai\Schemas\PantryItemsSchema;
use App\Contracts\AiOperationHandler;
use App\Models\SpaceStorage;
use App\Models\UserAiRecipeLog;
use App\Services\ScanPhotoStorage;

/**
 * A shelf photo turned into a list of pantry items.
 *
 * The one thing that makes this different from every other operation: persist()
 * writes nothing to the pantry. The list is a suggestion the user has not seen
 * yet, so it goes to result_json and waits. PantryScanController is what turns
 * it into real rows, and only once someone has agreed to it.
 */
class PantryFromPhoto implements AiOperationHandler
{
    public function __construct(
        protected PromptRepository $prompts,
        protected ScanPhotoStorage $photos,
    )
    {
    }

    public function buildRequest(UserAiRecipeLog $log): AiRequest
    {
        $photo = $this->photos->contents($log);

        // Nothing to send and nothing a retry would recover — the file is gone
        // or was never written. Fail now and refund rather than spend two
        // backoffs discovering the same thing.
        if ($photo === null) {
            throw new PermanentAiException("Scan photo missing for generation {$log->id}.");
        }

        $spaces = $this->spaces($log);

        return new AiRequest(
            systemInstruction: $this->prompts->get('system')
                ."\n\n".$this->prompts->get('pantry_from_photo'),
            parts: [
                AiPart::inline($this->photos->mime($log), base64_encode($photo)),
                AiPart::text($this->describeSpaces($spaces)),
            ],
            // The user's own space ids become the enum, so an item cannot be
            // assigned to somebody else's fridge.
            schema: PantryItemsSchema::get($spaces->pluck('id')->all()),
            model: config('services.ai.model'),
        );
    }

    /**
     * Hold the list for review. Names are trimmed and unnamed entries dropped
     * here rather than on the way out, so the review screen never has to render
     * a row that cannot become an item.
     */
    public function persist(AiResponse $response, UserAiRecipeLog $log): array
    {
        $owned = $this->spaces($log)->pluck('id')->flip();

        $items = [];

        foreach ($response->data['items'] ?? [] as $item) {
            $name = trim((string)($item['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            // The schema enum should make a foreign id impossible; this is what
            // makes that a guarantee rather than a trust in the provider.
            $spaceId = isset($item['space_id']) && $item['space_id'] !== ''
                ? (int)$item['space_id']
                : null;

            $notes = trim((string)($item['notes'] ?? ''));

            $items[] = [
                'name' => $name,
                'space_id' => $spaceId !== null && $owned->has($spaceId) ? $spaceId : null,
                'notes' => $notes === '' ? null : $notes,
            ];
        }

        return ['result_json' => $items];
    }

    public function promptVersion(): string
    {
        return $this->prompts->version('system')
            .'.'.$this->prompts->version('pantry_from_photo');
    }

    /**
     * The photo has done its work. Called when the run fails, and again when
     * the user settles the review — see PantryScanController.
     */
    public function releaseInputs(UserAiRecipeLog $log): void
    {
        $this->photos->delete($log);
    }

    /** @return \Illuminate\Support\Collection<int, SpaceStorage> */
    private function spaces(UserAiRecipeLog $log)
    {
        return SpaceStorage::where('user_id', $log->user_id)
            ->orderByRaw('sort_order is null, sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'description']);
    }

    /**
     * The spaces as data for the model: an id, a name, and whatever the user
     * wrote about what belongs there.
     *
     * The description is the part that actually earns its keep. "Fridge" alone
     * says nothing a model could not guess; "Fridge — dairy, leftovers, opened
     * jars" is what turns assignment from a coin flip into a decision.
     *
     * @param \Illuminate\Support\Collection<int, SpaceStorage> $spaces
     */
    private function describeSpaces($spaces): string
    {
        if ($spaces->isEmpty()) {
            return "Storage spaces: none. Leave every item unassigned.";
        }

        $lines = $spaces->map(function (SpaceStorage $space) {
            $description = trim((string)$space->description);

            return $description === ''
                ? "{$space->id} = {$space->name}"
                : "{$space->id} = {$space->name} — {$description}";
        });

        return "Storage spaces:\n".$lines->implode("\n");
    }
}
