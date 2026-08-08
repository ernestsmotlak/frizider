<script setup lang="ts">
import {computed} from "vue";
import {useRouter} from "vue-router";
import {actionCopy, isRunning, useAiGenerationStore, type AiJob} from "../stores/aiGeneration.ts";
import {useToastStore} from "../stores/toast.ts";

const store = useAiGenerationStore();
const router = useRouter();
const toastStore = useToastStore();

// One voice at a time: while the modal is open it speaks for the submission it
// started, so the pill drops that row — but keeps every other job, or work
// started earlier would vanish the moment you opened the modal again.
const rows = computed(() => (store.modalOpen
    ? store.jobs.filter((job) => job.id !== store.submissionId)
    : store.jobs));

const running = computed(() => rows.value.filter(isRunning));
const finished = computed(() => rows.value.filter((job) => !isRunning(job)));
const hasFailure = computed(() => finished.value.some((job) => job.status === "failed"));

const visible = computed(() => rows.value.length > 0 || (store.submitting && !store.modalOpen));

// One row keeps the old one-tap flow; expanding only earns its place at two.
const single = computed<AiJob | null>(() => (rows.value.length === 1 ? rows.value[0] ?? null : null));

const label = computed(() => {
    const [firstRunning] = running.value;

    if (running.value.length === 1 && firstRunning) return actionCopy(firstRunning.action).running;
    if (running.value.length > 1) return `${running.value.length} running`;
    if (single.value) return rowLabel(single.value);

    return `${finished.value.length} results`;
});

const tone = computed(() => {
    if (running.value.length > 0 || store.submitting) return "running";
    return hasFailure.value ? "failed" : "completed";
});

function rowLabel(job: AiJob): string {
    const copy = actionCopy(job.action);

    if (job.status === "completed") return copy.done;
    if (job.status === "failed") return copy.failed;

    return copy.running;
}

const act = (job: AiJob) => {
    if (job.status === "completed" && job.recipeId !== null) {
        const id = job.recipeId;
        store.acknowledge(job.id);
        store.expanded = false;
        router.push(`/recipe/${id}`);
        return;
    }

    if (job.status === "failed") {
        toastStore.show("error", job.error || "Generation failed. Your credit was refunded.");
        store.acknowledge(job.id);
    }
};

// Looking is not being told: expanding acknowledges nothing.
const handlePillClick = () => {
    if (single.value) {
        act(single.value);
        return;
    }

    if (rows.value.length > 1) store.expanded = !store.expanded;
};

// Only what is on screen right now.
const clearFinished = () => store.acknowledgeMany(finished.value.map((job) => job.id));
</script>

<template>
    <!-- Tap-away layer. No dimming — the pill must not feel modal. -->
    <div v-if="visible && store.expanded" class="fixed inset-0 z-30" @click="store.expanded = false"></div>

    <Transition name="ai-pill">
        <div
            v-if="visible"
            class="fixed left-0 right-0 z-40 px-5 pointer-events-none"
            style="bottom: calc(6.25rem + env(safe-area-inset-bottom));"
        >
            <div class="mx-auto max-w-md flex justify-end">
                <!-- Expanded: the list -->
                <div
                    v-if="store.expanded"
                    class="pointer-events-auto w-full bg-white rounded-2xl shadow-xl ring-1 ring-black/5 overflow-hidden"
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
                                <!-- Running -->
                                <svg
                                    v-if="job.status === 'pending' || job.status === 'processing'"
                                    class="w-4 h-4 shrink-0 animate-spin text-violet-600" fill="none" viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>

                                <!-- Ready -->
                                <svg
                                    v-else-if="job.status === 'completed'"
                                    class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor"
                                    stroke-width="2.5" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                </svg>

                                <!-- Failed -->
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

                <!-- Collapsed: the pill -->
                <button
                    v-else
                    @click="handlePillClick"
                    class="pointer-events-auto relative flex items-center gap-2 pl-3 pr-4 py-2.5 rounded-full shadow-lg font-semibold text-sm text-white transition-all duration-200 hover:scale-105 active:scale-95"
                    :class="{
                        'bg-violet-600': tone === 'running',
                        'bg-emerald-600': tone === 'completed',
                        'bg-red-600': tone === 'failed',
                        'cursor-default': rows.length === 0 || (single !== null && tone === 'running'),
                    }"
                >
                    <svg v-if="tone === 'running'" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>

                    <svg v-else-if="tone === 'completed'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                    </svg>

                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>

                    {{ store.submitting && rows.length === 0 ? "Starting…" : label }}
                    <template v-if="single !== null && single.status === 'completed'">→</template>

                    <!-- Results waiting behind running work: an accent, never the pill's own colour. -->
                    <span
                        v-if="tone === 'running' && finished.length > 0"
                        class="absolute -top-1 -right-1 w-3 h-3 rounded-full ring-2 ring-white"
                        :class="hasFailure ? 'bg-red-500' : 'bg-emerald-500'"
                    ></span>
                </button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.ai-pill-enter-active,
.ai-pill-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.ai-pill-enter-from,
.ai-pill-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
