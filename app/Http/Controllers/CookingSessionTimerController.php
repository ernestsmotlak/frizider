<?php

namespace App\Http\Controllers;

use App\Models\CookingSession;
use App\Models\CookingSessionTimer;
use Carbon\Carbon;
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

//        set timer status to idle or something.
    }

    public function startTimer(Request $request)
    {
        $validated = $request->validate([
            'timer_id' => 'required|integer|exists:cooking_session_timers,id',
        ]);

        $timer = $this->cookingSession
            ->cookingSessionTimers()
            ->whereKey($validated['timer_id'])
            ->firstOrFail();

        if ($timer->status === 'running') {
            return response()->json([
                'message' => 'Cooking session timer already started!',
            ]);
        }

        if ($timer->status === 'completed') {
            return response()->json([
                'message' => 'Cooking session timer already completed!',
            ]);
        }

        $timer->update([
            'started_at' => Carbon::now(),
            'status' => 'running',
            'paused_at' => null,
            'remaining_seconds_at_pause' => null,
            'completed_at' => null,
        ]);

        return response()->json([
            'data' => $this->cookingSession->refresh()->load('cookingSessionTimers'),
        ]);
    }

    public function pauseOrContinueTimer(Request $request)
    {

    }

    public function completeTimer(Request $request)
    {

    }

    public function updateTimer(Request $request)
    {

    }

    public function deleteTimer(Request $request)
    {

    }
}
