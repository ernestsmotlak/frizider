<script setup lang="ts">
/**
 * The permanent home for AI work.
 *
 * The pill is transient by design — it interrupts once, and dismissing it used
 * to lose the thread entirely: a scan waiting to be reviewed had no route back
 * except the history tab. This badge is the ambient half. It is always where
 * the user last saw it, it says whether anything is happening without taking
 * any space, and it holds the list.
 *
 * Running work lives here and nowhere else. A pill that says "reading the
 * photo" covers the screen for ten seconds to offer nothing to tap.
 */
import {computed} from "vue";
import {useRouter} from "vue-router";
import LogoComponent from "./LogoComponent.vue";
import {actionCopy, isRunning, resultRoute, useAiGenerationStore, type AiJob} from "../stores/aiGeneration.ts";
import {useToastStore} from "../stores/toast.ts";

const store = useAiGenerationStore();
const router = useRouter();
const toastStore = useToastStore();

// Same rule the pill follows: while the modal is open it speaks for its own
// submission, so that row is not repeated here.
const rows = computed(() => (store.modalOpen
    ? store.jobs.filter((job) => job.id !== store.submissionId)
    : store.jobs));

const running = computed(() => rows.value.filter(isRunning));
const finished = computed(() => rows.value.filter((job) => !isRunning(job)));
const hasFailure = computed(() => finished.value.some((job) => job.status === "failed"));

/**
 * Finished wins over running: a result is something to act on, and work still
 * in flight is not. The dropdown shows both regardless.
 */
const state = computed<"none" | "running" | "ready" | "failed">(() => {
    if (finished.value.length > 0) return hasFailure.value ? "failed" : "ready";
    if (running.value.length > 0 || store.submitting) return "running";

    return "none";
});

const open = computed(() => store.expanded && rows.value.length > 0);

const toggle = () => {
    if (state.value === "none") return;

    store.expanded = !store.expanded;
};

function rowLabel(job: AiJob): string {
    const copy = actionCopy(job.action);

    if (job.status === "completed") return copy.done;
    if (job.status === "failed") return copy.failed;

    return copy.running;
}

const act = (job: AiJob) => {
    const route = resultRoute(job);

    if (route !== null) {
        store.acknowledge(job.id);
        store.expanded = false;
        router.push(route);
        return;
    }

    if (job.status === "failed") {
        toastStore.show("error", job.error || "Generation failed. Your credit was refunded.");
        store.acknowledge(job.id);
    }
};

const clearFinished = () => store.acknowledgeMany(finished.value.map((job) => job.id));
</script>

<template>
    <div class="relative">
        <!-- Tap-away. No dimming — this is a dropdown, not a modal. Guarded on
             the same condition as the panel: a stale `expanded` with no rows
             left would otherwise leave an invisible sheet over the whole app. -->
        <div
            v-if="open"
            class="fixed inset-0 z-30"
            @click="store.expanded = false"
        ></div>

        <button
            type="button"
            class="relative z-40 block"
            :class="state === 'none' ? 'cursor-default' : ''"
            :aria-label="state === 'none' ? 'Home' : 'AI jobs'"
            :aria-expanded="store.expanded"
            @click="toggle"
        >
            <LogoComponent :tone="state"/>

            <!-- Running: a ring, not a count. There is nothing to count yet. -->
            <span
                v-if="state === 'running'"
                class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-white ring-2 ring-white flex items-center justify-center"
            >
                <svg class="w-3 h-3 animate-spin text-violet-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </span>

            <!-- Waiting to be looked at: how many. -->
            <span
                v-else-if="state !== 'none'"
                class="absolute -top-1 -right-1 min-w-[1.15rem] h-[1.15rem] px-1 rounded-full ring-2 ring-white text-[0.65rem] font-bold text-white flex items-center justify-center tabular-nums"
                :class="state === 'failed' ? 'bg-red-600' : 'bg-emerald-600'"
            >
                {{ finished.length }}
            </span>
        </button>

        <Transition name="ai-jobs">
            <div
                v-if="open"
                class="absolute right-0 top-full mt-2 z-40 w-72 bg-white rounded-2xl shadow-xl ring-1 ring-black/5 overflow-hidden"
            >
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">AI jobs</span>
                    <button
                        @click="clearFinished"
                        :disabled="finished.length === 0"
                        class="p-1 rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:hover:bg-transparent transition-colors"
                        :title="finished.length > 0 ? 'Clear finished' : 'Nothing to clear'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <ul class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                    <li v-for="job in rows" :key="job.id">
                        <button
                            @click="act(job)"
                            :disabled="job.status === 'pending' || job.status === 'processing'"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm transition-colors enabled:hover:bg-gray-50 disabled:cursor-default"
                        >
                            <svg
                                v-if="job.status === 'pending' || job.status === 'processing'"
                                class="w-4 h-4 shrink-0 animate-spin text-violet-600" fill="none" viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>

                            <svg
                                v-else-if="job.status === 'completed'"
                                class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor"
                                stroke-width="2.5" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                            </svg>

                            <svg
                                v-else class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor"
                                stroke-width="2.5" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>

                            <span class="flex-1 font-medium text-gray-800">{{ rowLabel(job) }}</span>

                            <svg
                                v-if="job.status === 'completed'"
                                class="w-4 h-4 shrink-0 text-gray-300" fill="none" stroke="currentColor"
                                stroke-width="2.5" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path>
                            </svg>
                        </button>
                    </li>
                </ul>

                <p v-if="store.stalled" class="px-4 py-2.5 text-xs text-amber-700 bg-amber-50 border-t border-amber-100">
                    {{ store.stalled }}
                </p>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.ai-jobs-enter-active,
.ai-jobs-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.ai-jobs-enter-from,
.ai-jobs-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
