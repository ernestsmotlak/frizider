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

    private function initializeCookingSession(Request $request): void
    {
        $this->cookingSession = auth()->user()
            ->cookingSession()
            ->firstOrFail();

        if ($request->has('location')) {
            $validated = $request->validate([
                'location.timer_fab_x_percent' => 'required|numeric|min:0|max:100',
                'location.timer_fab_y_percent' => 'required|numeric|min:0|max:100',
            ]);

            $this->cookingSession->update([
                'timer_fab_x_percent' => (float)$validated['location']['timer_fab_x_percent'],
                'timer_fab_y_percent' => (float)$validated['location']['timer_fab_y_percent'],
            ]);
        }
    }

    public function createTimer(Request $request)
    {
        $this->initializeCookingSession($request);

        $validated = $request->validate([
            'note' => 'required|string',
            'duration_seconds' => 'required|integer|min:1',
        ]);

        $timer = $this->cookingSession
            ->cookingSessionTimers()
            ->create(array_merge($validated, [
                'status' => 'running',
                'started_at' => Carbon::now(),
                'paused_at' => null,
                'remaining_seconds_at_pause' => null,
                'completed_at' => null,
            ]));

        return response()->json([
            'message' => 'Cooking session timer created!',
            'data' => $this->cookingSession->refresh()->load('cookingSessionTimers'),
        ]);
    }

    public function startTimer(Request $request)
    {
        $this->initializeCookingSession($request);

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
        $this->initializeCookingSession($request);

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
        $this->initializeCookingSession($request);

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
        $this->initializeCookingSession($request);

        $validated = $request->validate([
            'timer_id' => 'required|integer|exists:cooking_session_timers,id',
            'note' => 'sometimes|string',
            'sort_order' => 'sometimes|integer',
            'duration_seconds' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:running,paused,idle,completed',
        ]);

        $timer = $this->cookingSession
            ->cookingSessionTimers()
            ->whereKey($validated['timer_id'])
            ->firstOrFail();

        if (isset($validated['status'])) {

            switch ($validated['status']) {

                case 'completed':
                    $validated['completed_at'] = now();
                    $validated['paused_at'] = null;
                    $validated['remaining_seconds_at_pause'] = null;
                    break;

                case 'running':
                    $validated['started_at'] = now();
                    $validated['completed_at'] = null;
                    $validated['paused_at'] = null;
                    $validated['remaining_seconds_at_pause'] = null;
                    break;

                case 'paused':
                    if ($timer->started_at) {
                        $elapsed = $timer->started_at->diffInSeconds(now());
                        $remaining = max(0, $timer->duration_seconds - $elapsed);
                        $validated['remaining_seconds_at_pause'] = $remaining;
                    }
                    $validated['paused_at'] = now();
                    $validated['completed_at'] = null;
                    break;

                case 'idle':
                    $validated['started_at'] = null;
                    $validated['paused_at'] = null;
                    $validated['completed_at'] = null;
                    $validated['remaining_seconds_at_pause'] = null;
                    break;
            }
        }

        $timer->update($validated);

        return response()->json([
            'data' => $this->cookingSession->refresh()->load('cookingSessionTimers'),
        ]);
    }

    public function deleteTimer(Request $request)
    {
        $this->initializeCookingSession($request);

        $validated = $request->validate([
            'timer_id' => 'required|integer|exists:cooking_session_timers,id',
        ]);

        $timer = $this->cookingSession
            ->cookingSessionTimers()
            ->whereKey($validated['timer_id'])
            ->firstOrFail();

        $timer->delete();

        return response()->json([
            'data' => $this->cookingSession->refresh()->load('cookingSessionTimers'),
        ]);
    }

    public function reorderTimers(Request $request)
    {
        $this->initializeCookingSession($request);

        $validated = $request->validate([
            'orders' => 'required|array|min:1',
            'orders.*.timer_id' => 'required|integer|exists:cooking_session_timers,id',
            'orders.*.sort_order' => 'required|integer',
        ]);

        DB::transaction(function () use ($validated) {
            $timerIds = collect($validated['orders'])->pluck('timer_id')->values();

            $count = $this->cookingSession
                ->cookingSessionTimers()
                ->whereIn('id', $timerIds)
                ->count();

            if ($count !== $timerIds->count()) {
                abort(response()->json([
                    'message' => 'One or more timers do not belong to this cooking session.',
                ], 403));
            }

            foreach ($validated['orders'] as $row) {
                $this->cookingSession
                    ->cookingSessionTimers()
                    ->whereKey($row['timer_id'])
                    ->update(['sort_order' => (int)$row['sort_order']]);
            }
        });

        return response()->json([
            'data' => $this->cookingSession->refresh()->load('cookingSessionTimers'),
        ]);
    }

    public function resetTimer(Request $request)
    {
        $this->initializeCookingSession($request);

        $validated = $request->validate([
            'timer_id' => 'required|integer|exists:cooking_session_timers,id',
        ]);

        $timer = $this->cookingSession
            ->cookingSessionTimers()
            ->whereKey($validated['timer_id'])
            ->firstOrFail();

        $timer->update([
            'status' => 'idle',
            'duration_seconds' => $timer->original_duration_seconds ?? $timer->duration_seconds,
            'started_at' => null,
            'paused_at' => null,
            'completed_at' => null,
            'remaining_seconds_at_pause' => null,
        ]);

        return response()->json([
            'data' => $this->cookingSession->refresh()->load('cookingSessionTimers'),
        ]);
    }
}
