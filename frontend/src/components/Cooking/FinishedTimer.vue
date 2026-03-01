<script setup lang="ts">
const props = defineProps<{
    modelValue: boolean;
    note: string | null;
    duration_seconds: number;
}>();

const emit = defineEmits<{
    "update:modelValue": [value: boolean];
}>();

function formatDuration(seconds: number): string {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`;
}

function close(): void {
    emit("update:modelValue", false);
}

function onBackdropClick(event: MouseEvent): void {
    if (event.target === event.currentTarget) {
        close();
    }
}
</script>

<template>
    <Teleport to="body">
        <Transition name="finished-timer-backdrop">
            <div
                v-if="modelValue"
                class="finished-timer-overlay"
                role="dialog"
                aria-modal="true"
                aria-labelledby="finished-timer-title"
                @click="onBackdropClick"
            >
                <div
                    class="finished-timer-modal"
                    @click.stop
                >
                    <h2 id="finished-timer-title" class="finished-timer-title">
                        Timer complete
                    </h2>
                    <p class="finished-timer-note">
                        {{ note ?? "Timer" }}
                    </p>
                    <p class="finished-timer-duration">
                        {{ formatDuration(duration_seconds) }}
                    </p>
                    <button
                        type="button"
                        class="finished-timer-close"
                        aria-label="Close"
                        @click="close"
                    >
                        OK
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.finished-timer-overlay {
    position: fixed;
    inset: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.finished-timer-modal {
    background: var(--card-bg, #fff);
    border-radius: 1rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    padding: 1.5rem;
    min-width: 16rem;
    max-width: 20rem;
    text-align: center;
}

.finished-timer-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--instruction-text-color, #1e293b);
    margin: 0 0 0.75rem 0;
}

.finished-timer-note {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--instruction-text-color, #1e293b);
    margin: 0 0 0.5rem 0;
}

.finished-timer-duration {
    font-size: 1.5rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #0d9488;
    margin: 0 0 1rem 0;
}

.finished-timer-close {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #fff;
    background: #8b4513;
    border: none;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
}

.finished-timer-close:hover {
    background: #a0522d;
}

.finished-timer-close:active {
    transform: scale(0.98);
}

.finished-timer-close:focus-visible {
    outline: none;
    box-shadow:
        0 0 0 2px var(--card-bg, #fff),
        0 0 0 4px #8b4513;
}

.finished-timer-backdrop-enter-active,
.finished-timer-backdrop-leave-active {
    transition: opacity 0.2s ease;
}

.finished-timer-backdrop-enter-from,
.finished-timer-backdrop-leave-to {
    opacity: 0;
}
</style>
