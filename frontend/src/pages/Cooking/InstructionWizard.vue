<script setup lang="ts">
import { ref, computed, watch } from "vue";
import type { RecipeInstruction } from "../Recipe/RecipePage.vue";

const props = defineProps<{
    instructions: RecipeInstruction[];
}>();

const emit = defineEmits<{
    (e: "toggle-instruction", instruction: RecipeInstruction): void;
    (e: "reset"): void;
}>();

const currentStep = ref(0);

watch(
    () => props.instructions.length,
    (len) => {
        if (currentStep.value >= len && len > 0) {
            currentStep.value = Math.max(0, len - 1);
        }
    }
);

const completedCount = computed(() =>
    props.instructions.filter((s) => s.completed).length
);
const progressPercent = computed(() => {
    const total = props.instructions.length;
    if (total === 0) return 0;
    return Math.round((completedCount.value / total) * 100);
});
const current = computed(() => {
    const list = props.instructions;
    if (list.length === 0) return null;
    const idx = Math.min(currentStep.value, list.length - 1);
    return list[idx];
});
const isFirst = computed(() => currentStep.value === 0);
const isLast = computed(
    () =>
        props.instructions.length === 0 ||
        currentStep.value === props.instructions.length - 1
);

function goToStep(i: number): void {
    if (i >= 0 && i < props.instructions.length) {
        currentStep.value = i;
    }
}

function goNext(): void {
    if (!isLast.value) currentStep.value++;
}

function goPrev(): void {
    if (!isFirst.value) currentStep.value--;
}

function toggleCurrent(): void {
    const c = current.value;
    if (c) emit("toggle-instruction", c);
}

function markDoneAndNext(): void {
    const c = current.value;
    if (!c) return;
    if (!c.completed) emit("toggle-instruction", c);
    if (!isLast.value) {
        setTimeout(() => {
            currentStep.value++;
        }, 300);
    }
}

function resetAll(): void {
    currentStep.value = 0;
    emit("reset");
}

const cardProgressPercent = computed(() => {
    const total = props.instructions.length;
    if (total === 0) return 0;
    return ((currentStep.value + 1) / total) * 100;
});
</script>

<template>
    <section class="wizard" aria-label="Cooking instructions wizard">
        <h2 class="wizard-heading">Instructions</h2>

        <div v-if="instructions.length > 0" class="wizard-progress-wrap">
            <div class="wizard-progress-header">
                <span class="wizard-progress-text">
                    {{ completedCount }} of {{ instructions.length }} complete
                </span>
                <span class="wizard-progress-pct">{{ progressPercent }}%</span>
            </div>
            <div class="wizard-progress-track" role="progressbar" :aria-valuenow="completedCount" :aria-valuemin="0" :aria-valuemax="instructions.length" :aria-label="`${completedCount} of ${instructions.length} steps complete`">
                <div
                    class="wizard-progress-fill"
                    :style="{ width: progressPercent + '%' }"
                />
            </div>
        </div>

        <div v-if="instructions.length > 0" class="wizard-dots">
            <button
                v-for="(step, i) in instructions"
                :key="step.id ?? i"
                type="button"
                class="wizard-dot"
                :class="{
                    'wizard-dot--active': i === currentStep,
                    'wizard-dot--complete': step.completed,
                }"
                :aria-label="`Step ${i + 1}`"
                @click="goToStep(i)"
            >
                <template v-if="step.completed">
                    <svg class="wizard-dot-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </template>
                <template v-else>{{ i + 1 }}</template>
            </button>
        </div>

        <div v-if="current" class="wizard-card">
            <div class="wizard-card-progress-track">
                <div
                    class="wizard-card-progress-fill"
                    :style="{ width: cardProgressPercent + '%' }"
                />
            </div>
            <div class="wizard-card-inner">
                <div class="wizard-card-content" :key="currentStep">
                    <div class="wizard-step-header">
                        <span class="wizard-step-badge">{{ currentStep + 1 }}</span>
                        <span class="wizard-step-label">
                            Step {{ currentStep + 1 }} of {{ instructions.length }}
                        </span>
                    </div>
                    <p class="wizard-step-text">{{ current.instruction }}</p>
                </div>
                <div v-if="current.completed" class="wizard-done-badge">
                    <svg class="wizard-done-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Done
                </div>
            </div>
            <div class="wizard-nav">
                <button
                    type="button"
                    class="wizard-nav-link"
                    :disabled="isFirst"
                    @click="goPrev"
                >
                    <svg class="wizard-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    Previous
                </button>
                <button
                    type="button"
                    class="wizard-nav-primary"
                    :class="{ 'wizard-nav-primary--undo': current.completed }"
                    @click="current.completed ? toggleCurrent() : markDoneAndNext()"
                >
                    {{ current.completed ? "Undo" : isLast ? "Complete" : "Done — Next" }}
                </button>
                <button
                    type="button"
                    class="wizard-nav-link"
                    :disabled="isLast"
                    @click="goNext"
                >
                    Next
                    <svg class="wizard-nav-icon wizard-nav-icon--right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </button>
            </div>
        </div>

        <p v-else class="wizard-empty">No instructions.</p>

        <div v-if="completedCount > 0 && instructions.length > 0" class="wizard-reset-wrap">
            <button type="button" class="wizard-reset" @click="resetAll">
                <svg class="wizard-reset-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                    <path d="M3 3v5h5" />
                </svg>
                Reset all steps
            </button>
        </div>
    </section>
</template>

<style scoped>
.wizard {
    width: 90%;
    max-width: 32rem;
    margin-left: auto;
    margin-right: auto;
}

.wizard-heading {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.025em;
    color: var(--instructions-heading-color, #111827);
    margin: 0 0 1.5rem 0;
}

.wizard-progress-wrap {
    margin-bottom: 2rem;
}

.wizard-progress-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.wizard-progress-text {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-muted, #64748b);
}

.wizard-progress-pct {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--instruction-next-bg, #0d9488);
}

.wizard-progress-track {
    height: 0.5rem;
    border-radius: 9999px;
    background: var(--progress-track-bg, #e2e8f0);
    overflow: hidden;
}

.wizard-progress-fill {
    height: 100%;
    border-radius: 9999px;
    background: var(--progress-fill-bg, #0d9488);
    transition: width 0.5s ease-out;
}

.wizard-dots {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.wizard-dot {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    border: 2px solid transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s, background 0.2s, border-color 0.2s, color 0.2s;
    background: var(--progress-track-bg, #e2e8f0);
    color: var(--text-muted, #64748b);
}

.wizard-dot:hover:not(.wizard-dot--active):not(.wizard-dot--complete) {
    border-color: rgba(13, 148, 136, 0.4);
}

.wizard-dot--active {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    border-color: var(--instruction-next-bg, #0d9488);
    background: var(--instruction-next-bg, #0d9488);
    color: #fff;
}

.wizard-dot--complete:not(.wizard-dot--active) {
    border-color: var(--ingredient-check-selected, #0d9488);
    background: var(--ingredient-selected-bg, #f1f5f9);
    color: var(--ingredient-check-selected, #0d9488);
}

.wizard-dot-check {
    width: 0.875rem;
    height: 0.875rem;
}

.wizard-card {
    position: relative;
    border-radius: 0.75rem;
    border: 1px solid var(--instruction-border, #e2e8f0);
    background: var(--card-bg, #fff);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.wizard-card-progress-track {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--progress-track-bg, #e2e8f0);
}

.wizard-card-progress-fill {
    height: 100%;
    background: var(--instruction-next-bg, #0d9488);
    transition: width 0.3s ease;
}

.wizard-card-inner {
    padding: 2rem 2rem 1rem;
    min-height: 220px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.wizard-card-content {
    animation: wizard-slide 0.25s ease-out;
}

@keyframes wizard-slide {
    from {
        opacity: 0.6;
        transform: translateX(4px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.wizard-step-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.wizard-step-badge {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: rgba(13, 148, 136, 0.1);
    color: var(--instruction-next-bg, #0d9488);
    font-weight: 700;
    font-size: 1.125rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.wizard-step-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-muted, #64748b);
}

.wizard-step-text {
    font-size: 1.125rem;
    line-height: 1.6;
    color: var(--instruction-text-color, #1e293b);
    margin: 0;
}

.wizard-done-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    margin-top: 1rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    background: var(--ingredient-selected-bg, #f1f5f9);
    color: var(--ingredient-check-selected, #0d9488);
    font-size: 0.875rem;
    font-weight: 500;
    width: fit-content;
}

.wizard-done-icon {
    width: 1rem;
    height: 1rem;
}

.wizard-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid var(--instruction-border, #e2e8f0);
    background: rgba(226, 232, 240, 0.3);
    padding: 1rem 1.5rem;
}

.wizard-nav-link {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-muted, #64748b);
    background: none;
    border: none;
    cursor: pointer;
    transition: color 0.2s;
}

.wizard-nav-link:hover:not(:disabled) {
    color: var(--instruction-text-color, #1e293b);
}

.wizard-nav-link:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.wizard-nav-icon {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}

.wizard-nav-icon--right {
    order: 1;
}

.wizard-nav-primary {
    padding: 0.5rem 1.25rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: opacity 0.2s, background 0.2s;
    background: var(--instruction-next-bg, #0d9488);
    color: #fff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.wizard-nav-primary:hover {
    opacity: 0.9;
}

.wizard-nav-primary--undo {
    background: var(--progress-track-bg, #e2e8f0);
    color: var(--instruction-text-color, #1e293b);
}

.wizard-nav-primary--undo:hover {
    background: rgba(226, 232, 240, 0.8);
}

.wizard-empty {
    font-size: 0.875rem;
    color: var(--text-muted, #64748b);
    margin: 0;
}

.wizard-reset-wrap {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
}

.wizard-reset {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    color: var(--text-muted, #64748b);
    background: none;
    border: none;
    cursor: pointer;
    transition: color 0.2s;
}

.wizard-reset:hover {
    color: var(--instruction-text-color, #1e293b);
}

.wizard-reset-icon {
    width: 0.875rem;
    height: 0.875rem;
}
</style>
