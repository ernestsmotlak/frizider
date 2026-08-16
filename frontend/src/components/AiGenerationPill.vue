<script setup lang="ts">
/**
 * The interrupt. One job only: say that something is ready, in the corner a
 * thumb already rests in.
 *
 * It used to show while work was running too, which meant ten seconds of a
 * floating bar over the list saying "reading the photo" and offering nothing
 * to tap. Running state lives on the header badge now, and this appears only
 * when there is a result — the one moment worth interrupting for.
 *
 * The list lives in AiJobsBadge; this hands off to it when there is more than
 * one result, and goes straight to the answer when there is exactly one.
 */
import {computed} from "vue";
import {useRouter} from "vue-router";
import {actionCopy, isRunning, resultRoute, useAiGenerationStore, type AiJob} from "../stores/aiGeneration.ts";
import {useToastStore} from "../stores/toast.ts";

const store = useAiGenerationStore();
const router = useRouter();
const toastStore = useToastStore();

// While the modal is open it speaks for the submission it started, so the pill
// drops that row — but keeps every other job, or work started earlier would
// vanish the moment you opened the modal again.
const rows = computed(() => (store.modalOpen
    ? store.jobs.filter((job) => job.id !== store.submissionId)
    : store.jobs));

const finished = computed(() => rows.value.filter((job) => !isRunning(job)));
const hasFailure = computed(() => finished.value.some((job) => job.status === "failed"));

// Results only. Running work is the badge's business, and the dropdown is
// where both are listed together.
const visible = computed(() => finished.value.length > 0 && !store.expanded);

const single = computed<AiJob | null>(() => (finished.value.length === 1 ? finished.value[0] ?? null : null));

const label = computed(() => (single.value
    ? rowLabel(single.value)
    : `${finished.value.length} results`));

function rowLabel(job: AiJob): string {
    const copy = actionCopy(job.action);

    return job.status === "completed" ? copy.done : copy.failed;
}

// Looking is not being told: opening the list acknowledges nothing.
const handlePillClick = () => {
    const job = single.value;

    if (job === null) {
        store.expanded = true;
        return;
    }

    const route = resultRoute(job);

    if (route !== null) {
        store.acknowledge(job.id);
        router.push(route);
        return;
    }

    if (job.status === "failed") {
        toastStore.show("error", job.error || "Generation failed. Your credit was refunded.");
        store.acknowledge(job.id);
    }
};
</script>

<template>
    <Transition name="ai-pill">
        <div
            v-if="visible"
            class="fixed left-0 right-0 z-40 px-5 pointer-events-none"
            style="bottom: calc(6.25rem + env(safe-area-inset-bottom));"
        >
            <div class="mx-auto max-w-md flex justify-end">
                <button
                    @click="handlePillClick"
                    class="pointer-events-auto relative flex items-center gap-2 pl-3 pr-4 py-2.5 rounded-full shadow-lg font-semibold text-sm text-white transition-all duration-200 hover:scale-105 active:scale-95"
                    :class="hasFailure ? 'bg-red-600' : 'bg-emerald-600'"
                >
                    <svg v-if="!hasFailure" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                    </svg>

                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>

                    {{ label }}
                    <template v-if="single !== null && single.status === 'completed'">→</template>
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
