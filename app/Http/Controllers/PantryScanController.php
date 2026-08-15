<?php

namespace App\Http\Controllers;

use App\Ai\OperationRegistry;
use App\Enums\AiGenerationStatus;
use App\Enums\AiOperation;
use App\Jobs\GenerateAiRecipe;
use App\Models\AiUserData;
use App\Models\SpaceStorage;
use App\Models\UserAiRecipeLog;
use App\Services\AiCreditService;
use App\Services\PantryIntakeService;
use App\Services\ScanPhotoStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Turning a photo of a shelf into pantry items.
 *
 * The whole point of this controller is the gap between the model answering and
 * anything becoming real. The job writes a suggestion; nothing reaches the
 * pantry until confirm() is called with a list the user has actually looked at.
 */
class PantryScanController extends Controller
{
    /** A shelf photo that needs more than 8MB has not been downscaled. */
    private const MAX_KILOBYTES = 8192;

    public function __construct(private readonly ScanPhotoStorage $photos)
    {
    }

    /**
     * Take the photo, charge for it, and queue the read.
     *
     * Deliberately the same shape as RecipeController::generateAiRecipeFromIngredients()
     * — same 202, same idempotency, same credit path — so the client's pill
     * picks a scan up exactly as it picks up a recipe.
     */
    public function store(Request $request, AiCreditService $credits)
    {
        $userId = $request->user()->id;
        $aiData = AiUserData::where('user_id', $userId)->first();

        abort_if($aiData === null || !$aiData->can_use_ai, 403, 'AI features are not enabled for this account.');

        $validated = $request->validate([
            'photo' => [
                'required',
                'file',
                'mimetypes:'.ScanPhotoStorage::ACCEPTED_MIMES,
                'max:'.self::MAX_KILOBYTES,
            ],
            'idempotency_key' => 'required|string|max:64',
        ]);

        // Same key already submitted — hand back the run that is already going
        // rather than charging twice for one shaky tap.
        $existing = UserAiRecipeLog::where('user_id', $userId)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();

        if ($existing !== null) {
            return response()->json([
                'generation_id' => $existing->id,
                'action' => $existing->action,
                'status' => $existing->status,
            ], 202);
        }

        $operation = AiOperation::PantryFromPhoto;
        $photo = $this->photos->store($request->file('photo'), $userId);

        try {
            $log = DB::transaction(function () use ($credits, $userId, $operation, $validated, $photo) {
                $log = UserAiRecipeLog::create([
                    'user_id' => $userId,
                    'action' => $operation->value,
                    'status' => AiGenerationStatus::Pending,
                    // The path, never the bytes: the queue is the database, and
                    // a base64 image would be re-serialised on every attempt.
                    'request_meta' => [
                        'photo_path' => $photo['path'],
                        'photo_mime' => $photo['mime'],
                    ],
                    'idempotency_key' => $validated['idempotency_key'],
                ]);

                $charge = $credits->spend(
                    $userId,
                    $operation->creditCost(),
                    $log,
                    $validated['idempotency_key'],
                    ['operation' => $operation->value],
                );

                GenerateAiRecipe::dispatch($log, $charge)->afterCommit();

                return $log;
            });
        } catch (Throwable $error) {
            // No log row means nothing will ever come back for this file, and
            // nothing will ever sweep it either — it is only reachable through
            // the row that failed to exist.
            $this->photos->deletePath($photo['path']);

            throw $error;
        }

        return response()->json([
            'generation_id' => $log->id,
            'action' => $log->action,
            'status' => $log->status,
            'credits_remaining' => $credits->balance($userId),
        ], 202);
    }

    /**
     * Everything the review screen needs: the list, the spaces to sort it
     * into, and the photo it came from.
     */
    public function show(Request $request, UserAiRecipeLog $log)
    {
        $this->authorizeScan($request, $log);

        return response()->json([
            'generation_id' => $log->id,
            'status' => $log->status,
            'error' => $log->status === AiGenerationStatus::Failed
                ? 'The scan failed. Your credit was refunded.'
                : null,
            // Absent once released, so the review screen simply stops showing a
            // thumbnail rather than rendering a broken one.
            'photo_url' => $this->photos->exists($log)
                ? "/api/pantry/ai/generations/{$log->id}/photo"
                : null,
            'confirmed_at' => $log->confirmed_at,
            'spaces' => SpaceStorage::where('user_id', $log->user_id)
                ->orderByRaw('sort_order is null, sort_order')
                ->orderBy('id')
                ->get(['id', 'name']),
            'items' => $log->result_json ?? [],
        ]);
    }

    /**
     * The photo itself, behind the same auth as everything else. Never a
     * public /storage path — this is a picture of the inside of someone's
     * fridge, and a guessable URL is the wrong default for that.
     */
    public function photo(Request $request, UserAiRecipeLog $log)
    {
        $this->authorizeScan($request, $log);

        $path = $this->photos->absolutePath($log);

        abort_if($path === null || !is_file($path), 404);

        return response()->file($path, ['Content-Type' => $this->photos->mime($log)]);
    }

    /**
     * Make the list real.
     *
     * Takes the edited items, not result_json — the user may have renamed,
     * moved or dropped rows, and what they are looking at is the truth. The
     * stored suggestion is not consulted here at all.
     */
    public function confirm(Request $request, UserAiRecipeLog $log, PantryIntakeService $intake)
    {
        $this->authorizeScan($request, $log);

        abort_if($log->status !== AiGenerationStatus::Completed, 409, 'This scan has not finished.');

        $validated = $request->validate([
            'items' => 'required|array|min:1|max:60',
            'items.*.name' => 'required|string|max:120',
            'items.*.space_id' => 'nullable|integer',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        // The schema enum should already make a foreign space impossible, and
        // the user can only pick from their own — but the request is a request,
        // and one query is cheaper than an item in a stranger's cupboard.
        $ownedSpaceIds = SpaceStorage::where('user_id', $log->user_id)->pluck('id')->flip();

        foreach ($validated['items'] as $item) {
            $spaceId = $item['space_id'] ?? null;

            if ($spaceId !== null && !$ownedSpaceIds->has((int)$spaceId)) {
                return response()->json([
                    'message' => 'One or more of these storage spaces is not yours.',
                ], 403);
            }
        }

        $added = DB::transaction(function () use ($log, $validated, $intake) {
            // The lock is what makes a double tap add one pantry, not two.
            $settled = UserAiRecipeLog::whereKey($log->getKey())->lockForUpdate()->first();

            if ($settled->confirmed_at !== null) {
                return 0;
            }

            foreach ($validated['items'] as $item) {
                $intake->add($log->user_id, [
                    'name' => $item['name'],
                    'space_id' => $item['space_id'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $settled->update([
                'confirmed_at' => now(),
                'acknowledged_at' => $settled->acknowledged_at ?? now(),
            ]);

            return count($validated['items']);
        });

        // The review is over, so the photo has nothing left to be looked at
        // for. Outside the transaction: a deleted file cannot be rolled back.
        $this->release($log);

        return response()->json(['added' => $added]);
    }

    /**
     * The user looked and wants none of it. Drop the suggestion and the photo
     * now rather than making them wait for the sweep.
     */
    public function destroy(Request $request, UserAiRecipeLog $log)
    {
        $this->authorizeScan($request, $log);

        // The run itself stays in history — it happened, and it was paid for.
        // Only what it was holding goes.
        $log->update([
            'result_json' => null,
            'acknowledged_at' => $log->acknowledged_at ?? now(),
        ]);

        $this->release($log);

        return response()->json(['discarded' => true]);
    }

    /**
     * A scan is only ever the caller's own, and only ever a scan — the recipe
     * endpoints share this table, and neither should answer for the other.
     */
    private function authorizeScan(Request $request, UserAiRecipeLog $log): void
    {
        abort_if($log->user_id !== $request->user()->id, 404);
        abort_if($log->action !== AiOperation::PantryFromPhoto->value, 404);
    }

    /** Hand the photo back to the operation that knows what it was. */
    private function release(UserAiRecipeLog $log): void
    {
        try {
            app(OperationRegistry::class)->forLog($log)->releaseInputs($log);
        } catch (Throwable) {
            // The sweep will find it. Never fail a confirm over a leftover file.
        }
    }
}
