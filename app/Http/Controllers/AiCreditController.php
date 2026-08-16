<?php

namespace App\Http\Controllers;

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
}
