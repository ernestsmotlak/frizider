<script setup lang="ts">
export interface Timers {
    cooking_session_id: number | null;
    started_at: string | null;
    duration_seconds: number;
    note: string | null;
    status: string | null;
    sort_order: number | null;
    completed_at: string | null;
    paused_at: string | null;
    remaining_seconds_at_pause: number | null;
}

const props = defineProps<{
    timers: Timers[];
}>();

function formatSeconds(seconds: number): string {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`;
}

function displaySeconds(t: Timers): number {
    return t.remaining_seconds_at_pause ?? t.duration_seconds;
}
</script>

<template>
    <div class="timers-panel">
        <ul class="timers-list">
            <li
                v-for="(t, i) in props.timers"
                :key="i"
                class="timer-card"
            >
                <pre class="bg-red-100">{{ t }}</pre>
                <div class="timer-progress-ring">
                    <svg width="36" height="36" viewBox="0 0 36 36">
                        <circle
                            cx="18"
                            cy="18"
                            r="15"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="3"
                            class="timer-ring-bg"
                        />
                        <circle
                            cx="18"
                            cy="18"
                            r="15"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="3"
                            stroke-dasharray="94.25"
                            :stroke-dashoffset="94.25 * (1 - (t.duration_seconds > 0 ? displaySeconds(t) / t.duration_seconds : 0))"
                            stroke-linecap="round"
                            transform="rotate(-90 18 18)"
                            class="timer-ring-fill"
                        />
                    </svg>
                </div>
                <div class="timer-info">
                    <p class="timer-note">{{ t.note ?? "Timer" }}</p>
                    <p class="timer-time">{{ formatSeconds(displaySeconds(t)) }}</p>
                </div>
                <button type="button" class="timer-pause-btn" aria-label="Pause">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <rect x="6" y="4" width="4" height="16" rx="1" />
                        <rect x="14" y="4" width="4" height="16" rx="1" />
                    </svg>
                </button>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.timers-panel {
    width: 100%;
    height: 100%;
    box-sizing: border-box;
    overflow: auto;
}

.timers-list {
    list-style: none;
    margin: 0;
    padding: 0.5rem 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.timer-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.timer-progress-ring {
    flex-shrink: 0;
    color: #8b4513;
}

.timer-ring-bg {
    opacity: 0.2;
}

.timer-ring-fill {
    transition: stroke-dashoffset 0.3s ease;
}

.timer-info {
    flex: 1;
    min-width: 0;
}

.timer-note {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.timer-time {
    font-size: 1.125rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #8b4513;
    margin: 0.125rem 0 0 0;
}

.timer-pause-btn {
    flex-shrink: 0;
    width: 2.25rem;
    height: 2.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: rgba(139, 69, 19, 0.15);
    color: #8b4513;
    cursor: pointer;
}

.timer-pause-btn svg {
    width: 1rem;
    height: 1rem;
}

.timer-pause-btn:hover {
    background: rgba(139, 69, 19, 0.25);
}
</style>
