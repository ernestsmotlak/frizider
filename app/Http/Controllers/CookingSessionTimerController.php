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
            'data' => $this->cookingSession->refresh()->load('cookingSessionTimers'),
        ]);
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

        if ($timer->status !== 'idle') {
            return response()->json([
                'message' => 'Cooking session timer already started or completed!',
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
        $validated = $request->validate([
            'timer_id' => 'required|integer|exists:cooking_session_timers,id',
            'action' => 'required|string|in:pause,continue',
        ]);

        $timer = $this->cookingSession
            ->cookingSessionTimers()
            ->whereKey($validated['timer_id'])
            ->firstOrFail();

        if ($validated['action'] === 'continue') {
            if ($timer->status !== 'paused' || $timer->remaining_seconds_at_pause === null) {
                return response()->json([
                    'message' => 'Timer is not paused, cannot continue.',
                ], 422);
            }

            $timer->update([
                'status' => 'running',
                'started_at' => now(),
                'duration_seconds' => (int)$timer->remaining_seconds_at_pause,
                'paused_at' => null,
                'remaining_seconds_at_pause' => null,
                'completed_at' => null,
            ]);

        } else {
            if ($timer->status !== 'running' || !$timer->started_at) {
                return response()->json([
                    'message' => 'Timer is not running, cannot pause.',
                ], 422);
            }

            $pausedAt = Carbon::now();
            $elapsed = $timer->started_at->diffInSeconds($pausedAt);

            $remaining = max(0, (int)$timer->duration_seconds - (int)$elapsed);

            $timer->update([
                'status' => 'paused',
                'paused_at' => $pausedAt,
                'remaining_seconds_at_pause' => $remaining,
            ]);
        }

        return response()->json([
            'data' => $this->cookingSession->refresh()->load('cookingSessionTimers'),
        ]);
    }

    public function completeTimer(Request $request)
    {
        $validated = $request->validate([
            'timer_id' => 'required|integer|exists:cooking_session_timers,id',
        ]);

        $timer = $this->cookingSession
            ->cookingSessionTimers()
            ->whereKey($validated['timer_id'])
            ->firstOrFail();

        $timer->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
            'paused_at' => null,
            'remaining_seconds_at_pause' => null,
        ]);

        return response()->json([
            'data' => $this->cookingSession->refresh()->load('cookingSessionTimers'),
        ]);
    }

    public function updateTimer(Request $request)
    {

    }

    public function deleteTimer(Request $request)
    {

    }
}
