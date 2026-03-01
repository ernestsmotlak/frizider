<script setup lang="ts">
import { ref, computed, watch, onUnmounted } from "vue";

export interface Timers {
    id?: number;
    cooking_session_id: number | null;
    started_at: string | null;
    duration_seconds: number;
    original_duration_seconds?: number | null;
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

const emit = defineEmits<{
    start: [id: number];
    pause: [id: number];
    resume: [id: number];
    reset: [id: number];
    delete: [id: number];
    add: [payload: { note: string; duration_seconds: number }];
    complete: [id: number];
}>();

const showAddForm = ref(false);
const newNote = ref("");
const newMinutes = ref(0);
const newSeconds = ref(30);
const noteError = ref("");
const tick = ref(0);
let intervalId: ReturnType<typeof setInterval> | null = null;

watch(
    () => props.timers.some((t) => t.status === "running"),
    (hasRunning) => {
        if (hasRunning && !intervalId) {
            intervalId = setInterval(() => {
                tick.value++;
            }, 250);
        } else if (!hasRunning && intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    },
    { immediate: true },
);
onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
    completionTimeouts.forEach((tid) => clearTimeout(tid));
    completionTimeouts.clear();
});

const completionTimeouts = new Map<number, ReturnType<typeof setTimeout>>();

watch(
    () =>
        props.timers
            .filter(
                (t): t is Timers & { id: number } =>
                    t.status === "running" &&
                    t.id != null &&
                    t.started_at != null,
            )
            .map((t) => ({
                id: t.id,
                endMs:
                    new Date(t.started_at).getTime() +
                    t.duration_seconds * 1000,
            })),
    (running) => {
        completionTimeouts.forEach((tid, id) => {
            if (!running.some((r) => r.id === id)) {
                clearTimeout(tid);
                completionTimeouts.delete(id);
            }
        });
        for (const { id, endMs } of running) {
            if (completionTimeouts.has(id)) continue;
            const delay = endMs - Date.now();
            const tid = setTimeout(
                () => {
                    completionTimeouts.delete(id);
                    emit("complete", id);
                },
                Math.max(0, delay),
            );
            completionTimeouts.set(id, tid);
        }
    },
    { immediate: true },
);

function formatTime(seconds: number): string {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`;
}

function getRemaining(t: Timers): number {
    if (t.status === "completed") return 0;
    if (t.status === "paused" && t.remaining_seconds_at_pause != null) {
        return t.remaining_seconds_at_pause;
    }
    if (t.status === "running" && t.started_at) {
        const elapsed = Math.floor(
            (Date.now() - new Date(t.started_at).getTime()) / 1000,
        );
        return Math.max(0, t.duration_seconds - elapsed);
    }
    return t.duration_seconds;
}

const runningCount = computed(() =>
    props.timers.filter((t) => t.status === "running").length,
);

const sortedTimers = computed(() =>
    [...props.timers].sort((a, b) => {
        const aCompleted = a.status === "completed" ? 1 : 0;
        const bCompleted = b.status === "completed" ? 1 : 0;
        return aCompleted - bCompleted;
    }),
);

const remainingSeconds = computed(() => {
    void tick.value;
    return (t: Timers) => getRemaining(t);
});

function openAddForm(): void {
    noteError.value = "";
    showAddForm.value = true;
}

function addTimer(): void {
    const note = newNote.value.trim();
    if (!note) {
        noteError.value = "Please enter a timer name.";
        return;
    }
    noteError.value = "";
    const mins = Math.max(0, Number(newMinutes.value) || 0);
    const secs = Math.max(0, Math.min(59, Number(newSeconds.value) || 0));
    const totalSeconds = mins * 60 + secs;
    if (totalSeconds < 1) return;
    emit("add", {
        note,
        duration_seconds: totalSeconds,
    });
    newNote.value = "";
    newMinutes.value = 0;
    newSeconds.value = 30;
    showAddForm.value = false;
}

function statusClass(status: string | null): string {
    switch (status) {
        case "running":
            return "timer-status-running";
        case "paused":
            return "timer-status-paused";
        case "completed":
            return "timer-status-completed";
        default:
            return "timer-status-idle";
    }
}

function strokeClass(status: string | null): string {
    switch (status) {
        case "running":
            return "timer-ring-running";
        case "paused":
            return "timer-ring-paused";
        case "completed":
            return "timer-ring-completed";
        default:
            return "timer-ring-idle";
    }
}

function hasId(t: Timers): t is Timers & { id: number } {
    return t.id !== undefined && t.id !== null;
}

function handleStart(t: Timers): void {
    if (hasId(t)) emit("start", t.id);
}
function handlePause(t: Timers): void {
    if (hasId(t)) emit("pause", t.id);
}
function handleResume(t: Timers): void {
    if (hasId(t)) emit("resume", t.id);
}
function handleReset(t: Timers): void {
    if (hasId(t)) emit("reset", t.id);
}
function handleDelete(t: Timers): void {
    if (hasId(t)) emit("delete", t.id);
}
</script>

<template>
    <div class="timers-panel">
        <ul class="timers-list">
            <li
                v-for="(t, i) in sortedTimers"
                :key="t.id ?? i"
                class="timer-card"
            >
                <div class="timer-progress-wrap">
                    <svg
                        viewBox="0 0 64 64"
                        class="timer-progress-svg"
                        aria-hidden="true"
                    >
                        <circle
                            cx="32"
                            cy="32"
                            r="28"
                            fill="none"
                            stroke-width="4"
                            class="timer-ring-bg"
                        />
                        <circle
                            cx="32"
                            cy="32"
                            r="28"
                            fill="none"
                            stroke-width="4"
                            stroke-linecap="round"
                            class="timer-ring-fill"
                            :class="strokeClass(t.status)"
                            :stroke-dasharray="2 * Math.PI * 28"
                            :stroke-dashoffset="
                                2 * Math.PI * 28 * (1 - (t.duration_seconds > 0 ? remainingSeconds(t) / t.duration_seconds : 0))
                            "
                        />
                    </svg>
                    <span
                        class="timer-time-label"
                        :class="{ 'timer-time-running': t.status === 'running' }"
                        role="timer"
                        :aria-live="t.status === 'running' ? 'polite' : 'off'"
                        :aria-valuenow="remainingSeconds(t)"
                        :aria-valuemin="0"
                        :aria-valuemax="t.duration_seconds"
                        :aria-label="`Time remaining: ${formatTime(remainingSeconds(t))}`"
                    >
                        {{ formatTime(remainingSeconds(t)) }}
                    </span>
                </div>
                <div class="timer-info">
                    <p class="timer-original">{{ formatTime(t.original_duration_seconds ?? t.duration_seconds) }}</p>
                    <p class="timer-note">{{ t.note ?? "Timer" }}</p>
                    <div class="timer-meta">
                        <span
                            class="timer-status-dot"
                            :class="statusClass(t.status)"
                            aria-hidden="true"
                        />
                        <span class="timer-status-text">{{
                            t.status ?? "idle"
                        }}</span>
                    </div>
                </div>
                <div class="timer-actions">
                    <template v-if="t.status === 'completed'">
                        <button
                            type="button"
                            class="timer-btn timer-btn-reset"
                            aria-label="Reset timer"
                            @click="handleReset(t)"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                <path d="M3 3v5h5" />
                            </svg>
                        </button>
                    </template>
                    <template v-else-if="t.status === 'running'">
                        <button
                            type="button"
                            class="timer-btn timer-btn-pause"
                            aria-label="Pause timer"
                            @click="handlePause(t)"
                        >
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <rect x="6" y="4" width="4" height="16" rx="1" />
                                <rect x="14" y="4" width="4" height="16" rx="1" />
                            </svg>
                        </button>
                    </template>
                    <template v-else-if="t.status === 'paused'">
                        <button
                            type="button"
                            class="timer-btn timer-btn-resume"
                            aria-label="Resume timer"
                            @click="handleResume(t)"
                        >
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <polygon points="5 3 19 12 5 21 5 3" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="timer-btn timer-btn-reset"
                            aria-label="Reset timer"
                            @click="handleReset(t)"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                <path d="M3 3v5h5" />
                            </svg>
                        </button>
                    </template>
                    <template v-else>
                        <button
                            type="button"
                            class="timer-btn timer-btn-start"
                            aria-label="Start timer"
                            @click="handleStart(t)"
                        >
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <polygon points="5 3 19 12 5 21 5 3" />
                            </svg>
                        </button>
                    </template>
                    <button
                        type="button"
                        class="timer-btn timer-btn-delete"
                        aria-label="Delete timer"
                        @click="handleDelete(t)"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18" />
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                            <line x1="10" y1="11" x2="10" y2="17" />
                            <line x1="14" y1="11" x2="14" y2="17" />
                        </svg>
                    </button>
                </div>
            </li>
        </ul>
        <p v-if="props.timers.length === 0 && !showAddForm" class="timers-empty">
            No timers yet
        </p>
        <div v-if="showAddForm" class="timer-add-form">
            <input
                v-model="newNote"
                type="text"
                placeholder="Timer name..."
                class="timer-add-input"
                :class="{ 'timer-add-input-error': noteError }"
                :aria-invalid="!!noteError"
                aria-describedby="note-error"
                @keydown.enter="addTimer"
                @input="noteError = ''"
            />
            <p v-if="noteError" id="note-error" class="timer-add-error">
                {{ noteError }}
            </p>
            <div class="timer-add-row timer-add-duration">
                <div class="timer-add-field">
                    <label class="timer-add-label">Min</label>
                    <input
                        v-model.number="newMinutes"
                        type="number"
                        min="0"
                        max="999"
                        class="timer-add-input-num"
                    />
                </div>
                <div class="timer-add-field">
                    <label class="timer-add-label">Sec</label>
                    <input
                        v-model.number="newSeconds"
                        type="number"
                        min="0"
                        max="59"
                        class="timer-add-input-num"
                    />
                </div>
            </div>
            <div class="timer-add-buttons">
                <button
                    type="button"
                    class="timer-add-submit"
                    @click.stop="addTimer"
                >
                    Add Timer
                </button>
                <button
                    type="button"
                    class="timer-add-cancel"
                    @click.stop="showAddForm = false"
                >
                    Cancel
                </button>
            </div>
        </div>
        <button
            v-else
            type="button"
            class="timer-add-dashed"
            @click.stop="openAddForm"
        >
            <svg class="timer-add-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Add Timer
        </button>
    </div>
</template>

<style scoped>
.timers-panel {
    width: 100%;
    min-width: 280px;
    max-height: 70vh;
    overflow-y: auto;
    padding: 0.5rem 0;
    box-sizing: border-box;
}

.timers-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.timer-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--card-bg, #fff);
    border: 1px solid var(--instruction-border, #e2e8f0);
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.timer-progress-wrap {
    position: relative;
    width: 5rem;
    height: 5rem;
    flex-shrink: 0;
}

.timer-progress-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.timer-ring-bg {
    stroke: var(--text-muted, #94a3b8);
    opacity: 0.2;
}

.timer-ring-fill {
    transition: stroke-dashoffset 0.3s ease;
}

.timer-ring-running {
    stroke: #0d9488;
}

.timer-ring-paused {
    stroke: #d97706;
}

.timer-ring-completed {
    stroke: #6b7280;
}

.timer-ring-idle {
    stroke: var(--text-muted, #94a3b8);
}

.timer-time-label {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: var(--instruction-text-color, #1e293b);
    letter-spacing: 0.02em;
}

.timer-time-running {
    animation: timer-pulse 2s ease-in-out infinite;
}

@keyframes timer-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.timer-info {
    flex: 1;
    min-width: 0;
}

.timer-original {
    font-size: 0.8125rem;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    color: var(--text-muted, #64748b);
    margin: 0 0 0.125rem 0;
}

.timer-note {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--instruction-text-color, #1e293b);
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.timer-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.25rem;
}

.timer-status-dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
}

.timer-status-running {
    background: #0d9488;
}

.timer-status-paused {
    background: #d97706;
}

.timer-status-completed {
    background: #6b7280;
}

.timer-status-idle {
    background: var(--text-muted, #94a3b8);
}

.timer-status-text {
    font-size: 0.75rem;
    color: var(--text-muted, #64748b);
    text-transform: capitalize;
}


.timer-actions {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    flex-shrink: 0;
}

.timer-btn {
    width: 2.5rem;
    height: 2.5rem;
    padding: 0;
    border: none;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
}

.timer-btn svg {
    width: 1.125rem;
    height: 1.125rem;
}

.timer-btn:active {
    transform: scale(0.95);
}

.timer-btn-start,
.timer-btn-resume {
    background: rgba(13, 148, 136, 0.15);
    color: #0d9488;
}

.timer-btn-start:hover,
.timer-btn-resume:hover {
    background: rgba(13, 148, 136, 0.25);
}

.timer-btn-pause {
    background: rgba(217, 119, 6, 0.15);
    color: #d97706;
}

.timer-btn-pause:hover {
    background: rgba(217, 119, 6, 0.25);
}

.timer-btn-reset {
    background: rgba(100, 116, 139, 0.15);
    color: var(--text-muted, #64748b);
}

.timer-btn-reset:hover {
    background: rgba(100, 116, 139, 0.25);
}

.timer-btn-delete {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
}

.timer-btn-delete:hover {
    background: rgba(220, 38, 38, 0.2);
}

.timers-empty {
    text-align: center;
    font-size: 0.875rem;
    color: var(--text-muted, #94a3b8);
    padding: 1.5rem 0;
    margin: 0;
}

.timer-add-form {
    margin-top: 0.75rem;
    padding: 1rem;
    background: var(--card-bg, #fff);
    border: 1px solid var(--instruction-border, #e2e8f0);
    border-radius: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.timer-add-input {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: var(--instruction-text-color, #1e293b);
    background: var(--hover-bg, #f1f5f9);
    border: 1px solid var(--instruction-border, #e2e8f0);
    border-radius: 0.75rem;
    outline: none;
    box-sizing: border-box;
}

.timer-add-input::placeholder {
    color: var(--text-muted, #94a3b8);
}

.timer-add-input:focus {
    border-color: #0d9488;
    box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.2);
}

.timer-add-input-error {
    border-color: #dc2626;
}

.timer-add-input-error:focus {
    border-color: #dc2626;
    box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);
}

.timer-add-error {
    font-size: 0.8125rem;
    color: #dc2626;
    margin: 0;
}

.timer-add-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.timer-add-duration {
    gap: 1rem;
}

.timer-add-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.timer-add-label {
    font-size: 0.75rem;
    color: var(--text-muted, #64748b);
}

.timer-add-input-num {
    width: 4rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    color: var(--instruction-text-color, #1e293b);
    background: var(--hover-bg, #f1f5f9);
    border: 1px solid var(--instruction-border, #e2e8f0);
    border-radius: 0.75rem;
    outline: none;
}

.timer-add-input-num:focus {
    border-color: #0d9488;
}

.timer-add-buttons {
    display: flex;
    gap: 0.5rem;
}

.timer-add-submit {
    flex: 1;
    padding: 0.625rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #fff;
    background: #8b4513;
    border: none;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
}

.timer-add-submit:hover {
    background: #a0522d;
}

.timer-add-submit:active {
    transform: scale(0.98);
}

.timer-add-cancel {
    padding: 0.625rem 1rem;
    font-size: 0.875rem;
    color: var(--text-muted, #64748b);
    background: var(--hover-bg, #f1f5f9);
    border: 1px solid var(--instruction-border, #e2e8f0);
    border-radius: 0.75rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
}

.timer-add-cancel:hover {
    background: #e2e8f0;
}

.timer-add-cancel:active {
    transform: scale(0.98);
}

.timer-add-dashed {
    margin-top: 0.75rem;
    width: 100%;
    padding: 0.875rem 1.25rem;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #475569;
    background: rgba(139, 69, 19, 0.06);
    border: 2px dashed #94a3b8;
    border-radius: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: border-color 0.2s, color 0.2s, background 0.2s, transform 0.1s;
}

.timer-add-dashed:hover {
    border-color: #8b4513;
    color: #8b4513;
    background: rgba(139, 69, 19, 0.1);
}

.timer-add-dashed:active {
    transform: scale(0.98);
}

.timer-add-dashed:focus-visible {
    outline: none;
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px #8b4513;
}

.timer-add-icon {
    width: 1.125rem;
    height: 1.125rem;
}
</style>
