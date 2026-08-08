<script setup lang="ts">
import {computed, onUnmounted, ref, watch} from "vue";
import {useRouter} from "vue-router";
import Modal from "../Modal.vue";
import {useAiGenerationStore} from "../../stores/aiGeneration.ts";

export interface SelectedPantryItem {
    id: number;
    name: string;
    quantity: number | string | null;
    unit: string | null;
}

const MAX_INGREDIENTS = 30;

const props = defineProps<{
    isOpen: boolean;
    items: SelectedPantryItem[];
}>();

const emit = defineEmits<{
    close: [];
    done: [];
}>();

const router = useRouter();
const store = useAiGenerationStore();

const removedIds = ref<number[]>([]);

// Opening is always a fresh start now that several generations can run at
// once: the modal is for starting work, the pill is for watching it. The pill
// stays quiet while the modal is the one talking.
watch(() => props.isOpen, (open) => {
    store.modalOpen = open;

    if (open) {
        removedIds.value = [];
        store.clearSubmission();
    }
});

onUnmounted(() => {
    store.modalOpen = false;
});

const chips = computed(() => props.items.filter((item) => !removedIds.value.includes(item.id)));
const overLimit = computed(() => chips.value.length > MAX_INGREDIENTS);

const removeChip = (id: number) => {
    removedIds.value.push(id);
};

const formatAmount = (item: SelectedPantryItem): string => {
    if (item.quantity === null) return "";
    const amount = parseFloat(String(item.quantity));
    if (Number.isNaN(amount)) return "";
    return `${amount}${item.unit ? " " + item.unit : ""}`;
};

const generate = () => {
    if (chips.value.length === 0 || overLimit.value || store.submitting) return;

    store.submit(chips.value.map((item) => ({
        id: item.id,
        name: item.name,
        quantity: item.quantity,
        unit: item.unit,
    })));
};

const tryAgain = () => {
    store.clearSubmission();
    generate();
};

// Completion while the modal is open takes the user straight to the recipe;
// while it is closed, the pill announces it instead. Only this modal's own
// submission counts — another job finishing must not hijack the view.
watch(() => store.submissionStatus, (status) => {
    if (status === "completed" && store.submissionRecipeId !== null && props.isOpen) {
        const id = store.submissionRecipeId;
        emit("done");
        store.acknowledge(store.submissionId!);
        router.push(`/recipe/${id}`);
    }
});
</script>

<template>
    <Modal :isOpen="isOpen" @close="emit('close')">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Generate Recipe</h2>
        </template>

        <template #body>
            <!-- Pick & confirm -->
            <div v-if="store.submissionStatus === 'idle'" class="space-y-4">
                <p class="text-sm text-gray-600">
                    The recipe will use only these ingredients, plus basic staples like water, salt, pepper, oil, and sugar.
                </p>

                <div v-if="chips.length > 0" class="flex flex-wrap gap-2">
                    <span
                        v-for="item in chips"
                        :key="item.id"
                        class="inline-flex items-center gap-1.5 pl-3 pr-1.5 py-1.5 bg-violet-50 border border-violet-200 text-violet-800 rounded-full text-sm"
                    >
                        {{ item.name }}
                        <span v-if="formatAmount(item)" class="text-violet-500 text-xs">{{ formatAmount(item) }}</span>
                        <button
                            @click="removeChip(item.id)"
                            class="p-0.5 rounded-full hover:bg-violet-200 transition-colors"
                            :title="`Remove ${item.name}`"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </span>
                </div>

                <p v-else class="text-sm text-gray-500">No ingredients left — close and select some pantry items.</p>

                <p v-if="overLimit" class="text-sm text-amber-600">
                    A recipe can use at most {{ MAX_INGREDIENTS }} ingredients — remove {{ chips.length - MAX_INGREDIENTS }} to continue.
                </p>

                <p class="text-xs text-gray-400">Uses 1 AI credit.</p>
            </div>

            <!-- In flight -->
            <div v-else-if="store.submissionStatus === 'submitting' || store.submissionStatus === 'generating'" class="py-6 text-center space-y-3">
                <svg class="w-10 h-10 mx-auto text-violet-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <p class="text-gray-900 font-semibold">Cooking up your recipe…</p>
                <p class="text-sm text-gray-500">This usually takes about 10 seconds. You can close this — the recipe will be saved either way.</p>
            </div>

            <!-- Failed -->
            <div v-else-if="store.submissionStatus === 'failed'" class="space-y-3">
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-700">{{ store.submissionError }}</p>
                </div>
                <p class="text-xs text-gray-400">If a generation fails, the credit is refunded automatically.</p>
            </div>
        </template>

        <template #footer>
            <div class="flex justify-between gap-3">
                <button
                    @click="emit('close')"
                    class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors"
                >
                    {{ store.submissionStatus === 'generating' ? "Continue in background" : "Close" }}
                </button>

                <button
                    v-if="store.submissionStatus === 'idle'"
                    @click="generate"
                    :disabled="chips.length === 0 || overLimit"
                    class="flex items-center gap-2 px-4 py-2.5 bg-violet-600 text-white rounded-lg font-semibold hover:bg-violet-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"></path>
                    </svg>
                    Generate
                </button>

                <button
                    v-if="store.submissionStatus === 'failed'"
                    @click="tryAgain"
                    class="px-4 py-2.5 bg-violet-600 text-white rounded-lg font-semibold hover:bg-violet-700 transition-colors"
                >
                    Try again
                </button>
            </div>
        </template>
    </Modal>
</template>
