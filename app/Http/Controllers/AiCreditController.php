<?php

namespace App\Http\Controllers;

use App\Enums\AiCreditTransactionType;
use App\Enums\AiOperation;
use App\Models\AiCreditTransaction;
use App\Models\AiUserData;
use App\Services\AiCreditService;
use Illuminate\Http\Request;

/**
 * What the user has left.
 *
 * The balance was only ever returned as a side effect of spending, so until a
 * session had already run a generation the client could not know it — which
 * made a 402 mid-scan the first sign of an empty account. This is the same
 * number, asked for directly.
 */
class AiCreditController extends Controller
{
    public function show(Request $request, AiCreditService $credits)
    {
        $userId = $request->user()->id;

        return response()->json([
            // Named to match what the spend endpoints already return, so the
            // client stores a balance the same way wherever it came from.
            'credits_remaining' => $credits->balance($userId),
            // Zero because they are spent and zero because the feature was
            // never switched on look identical otherwise, and only one of
            // them is worth showing a "get more" affordance for.
            'can_use_ai' => (bool)(AiUserData::where('user_id', $userId)->value('can_use_ai') ?? false),
        ]);
    }

    /**
     * Where the credits went.
     *
     * Same shape as RecipeController::generationHistory() — simplePaginate and
     * a has_more flag — so the two profile panels load the same way and one
     * can be read by anyone who has read the other.
     */
    public function ledger(Request $request, AiCreditService $credits)
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $rows = AiCreditTransaction::query()
            ->select(['id', 'amount', 'balance_after', 'type', 'metadata', 'created_at'])
            ->where('user_id', $request->user()->id)
            // By id, not created_at: two transactions inside one request share
            // a timestamp, and a refund must never sort above its own charge.
            ->orderByDesc('id')
            ->simplePaginate((int)($validated['per_page'] ?? 20));

        return response()->json([
            'transactions' => collect($rows->items())->map(fn(AiCreditTransaction $row) => [
                'id' => $row->id,
                'type' => $row->type,
                'label' => $this->describe($row),
                'amount' => $row->amount,
                'balance_after' => $row->balance_after,
                'created_at' => $row->created_at,
            ])->values(),
            'has_more' => $rows->hasMorePages(),
            // Rides along so opening this tab does not cost a second request
            // just to refresh the strip above it. A refund lands server-side
            // with no response to piggyback on, so the number can drift.
            'credits_remaining' => $credits->balance($request->user()->id),
        ]);
    }

    /**
     * "Generation" is true of every spend and useful about none of them. Both
     * spend paths record which operation they were, so a charge can say what
     * it bought — and falls back to the type when it cannot.
     */
    private function describe(AiCreditTransaction $row): string
    {
        if ($row->type !== AiCreditTransactionType::Consumption) {
            return $row->type->label();
        }

        $operation = $row->metadata['operation'] ?? null;

        return (is_string($operation) ? AiOperation::tryFrom($operation)?->label() : null)
            ?? $row->type->label();
    }
}
