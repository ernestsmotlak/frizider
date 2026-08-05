<script setup lang="ts">
import {computed} from "vue";
import {useRouter} from "vue-router";
import {useAiGenerationStore} from "../stores/aiGeneration.ts";
import {useToastStore} from "../stores/toast.ts";

const store = useAiGenerationStore();
const router = useRouter();
const toastStore = useToastStore();

// The modal already shows the same information — one voice at a time.
const visible = computed(() => store.status !== "idle" && !store.modalOpen);

const handleClick = () => {
    if (store.status === "completed" && store.recipeId !== null) {
        const id = store.recipeId;
        store.acknowledge();
        router.push(`/recipe/${id}`);
        return;
    }

    if (store.status === "failed") {
        toastStore.show("error", store.error || "Recipe generation failed. Your credit was refunded.");
        store.acknowledge();
    }
};
</script>

<template>
    <Transition name="ai-pill">
        <div
            v-if="visible"
            class="fixed left-0 right-0 z-30 px-5 pointer-events-none"
            style="bottom: calc(6.25rem + env(safe-area-inset-bottom));"
        >
            <div class="mx-auto max-w-md flex justify-end">
                <button
                    @click="handleClick"
                    class="pointer-events-auto flex items-center gap-2 pl-3 pr-4 py-2.5 rounded-full shadow-lg font-semibold text-sm transition-all duration-200 hover:scale-105 active:scale-95"
                    :class="{
                        'bg-violet-600 text-white cursor-default': store.isBusy,
                        'bg-emerald-600 text-white': store.status === 'completed',
                        'bg-red-600 text-white': store.status === 'failed',
                    }"
                >
                    <!-- Cooking -->
                    <template v-if="store.isBusy">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Cooking…
                    </template>

                    <!-- Ready -->
                    <template v-else-if="store.status === 'completed'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                        </svg>
                        Recipe ready →
                    </template>

                    <!-- Failed -->
                    <template v-else-if="store.status === 'failed'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Generation failed
                    </template>
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
