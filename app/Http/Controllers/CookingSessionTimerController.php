<?php

namespace App\Http\Controllers;

use App\Models\CookingSession;
use App\Models\CookingSessionTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CookingSessionTimerController extends Controller
{
    private ?CookingSession $cookingSession = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->cookingSession = auth()->user()
                ->cookingSession()
                ->firstOrFail();

            return $next($request);
        });
    }

    public function createTimer(Request $request)
    {
        $validated = $request->validate([
            'note' => 'required|string',
            'duration_seconds' => 'required|integer|min:1',
        ]);

        $timer = $this->cookingSession
            ->cookingSessionTimers()
            ->create($validated);

        return response()->json([
            'message' => 'Cooking session timer created!',
            'timer' => $timer,
        ]);
    }

    public function startTimer(Request $request)
    {

    }

    public function pauseOrContinueTimer(Request $request)
    {

    }

    public function completeTimer(Request $request)
    {

    }

    public function deleteTimer(Request $request)
    {

    }
}
