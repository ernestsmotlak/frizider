<script setup lang="ts">
import {computed, ref, watch} from 'vue';
import Modal from './Modal.vue';
import {useToastStore} from '../stores/toast.ts';
import {useLoadingStore} from '../stores/loading.ts';

export type ConvertSourceType = 'pantry_item' | 'recipe_ingredient' | 'grocery_list_item' | 'shopping_item';
export type ConvertTargetType = 'pantry_item' | 'grocery_list_item' | 'recipe_ingredient';

interface DestinationOption {
    id: number;
    name: string;
}

interface TargetOption {
    type: ConvertTargetType;
    title: string;
    subtitle: string;
}

const props = defineProps<{
    isOpen: boolean;
    sourceType: ConvertSourceType;
    sourceIds: number[];
}>();

const emit = defineEmits<{
    close: [];
    converted: [targetType: ConvertTargetType];
}>();

const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const step = ref(1);
const targetType = ref<ConvertTargetType | null>(null);
const destinationOptions = ref<DestinationOption[]>([]);
const isLoadingOptions = ref(false);
const selectedDestinationId = ref<number | null>(null);
const expiryDate = ref<string>('');
const destinationSearch = ref<string>('');

const filteredDestinationOptions = computed(() => {
    const q = destinationSearch.value.trim().toLowerCase();
    if (!q) return destinationOptions.value;
    return destinationOptions.value.filter(o => o.name.toLowerCase().includes(q));
});

const targetOptions = computed<TargetOption[]>(() => {
    switch (props.sourceType) {
        case 'pantry_item':
            return [
                {type: 'grocery_list_item', title: 'Grocery List', subtitle: 'Add to a shopping list'},
                {type: 'recipe_ingredient', title: 'Recipe', subtitle: 'Add as an ingredient'},
            ];
        case 'recipe_ingredient':
            return [
                {type: 'pantry_item', title: 'Pantry', subtitle: 'Add to a storage space'},
                {type: 'grocery_list_item', title: 'Grocery List', subtitle: 'Add to a shopping list'},
            ];
        case 'grocery_list_item':
        case 'shopping_item':
        default:
            return [
                {type: 'pantry_item', title: 'Pantry', subtitle: 'Add to a storage space'},
                {type: 'recipe_ingredient', title: 'Recipe', subtitle: 'Add as an ingredient'},
            ];
    }
});

const destinationLabel = computed(() => {
    switch (targetType.value) {
        case 'pantry_item':
            return 'storage space';
        case 'grocery_list_item':
            return 'grocery list';
        case 'recipe_ingredient':
            return 'recipe';
        default:
            return 'destination';
    }
});

const showExpiryStep = computed(() => targetType.value === 'pantry_item' && props.sourceIds.length === 1);

const totalSteps = computed(() => showExpiryStep.value ? 3 : 2);

const canGoNext = computed(() => {
    if (step.value === 1) {
        return targetType.value !== null;
    }
    if (step.value === 2) {
        return selectedDestinationId.value !== null;
    }
    return true;
});

const itemCountLabel = computed(() => `${props.sourceIds.length} item${props.sourceIds.length !== 1 ? 's' : ''}`);

const resetState = () => {
    step.value = 1;
    targetType.value = null;
    destinationOptions.value = [];
    selectedDestinationId.value = null;
    expiryDate.value = '';
    destinationSearch.value = '';
};

watch(() => props.isOpen, (open) => {
    if (open) {
        resetState();
    }
});

const selectTarget = (type: ConvertTargetType) => {
    targetType.value = type;
};

const fetchDestinationOptions = async () => {
    isLoadingOptions.value = true;
    destinationOptions.value = [];

    try {
        if (targetType.value === 'pantry_item') {
            const response = await axios.get('/api/space-storages');
            destinationOptions.value = response.data.data.map((space: any) => ({id: space.id, name: space.name}));
        } else if (targetType.value === 'grocery_list_item') {
            const response = await axios.get('/api/grocery-lists');
            destinationOptions.value = response.data.data.map((list: any) => ({id: list.id, name: list.name}));
        } else if (targetType.value === 'recipe_ingredient') {
            const response = await axios.post('/api/get-recipes', {per_page: 100});
            const recipes = response.data?.data?.data ?? [];
            destinationOptions.value = recipes.map((recipe: any) => ({id: recipe.id, name: recipe.name}));
        }
    } catch (error: any) {
        console.error(error);
        toastStore.show('error', `Could not load ${destinationLabel.value} options.`);
    } finally {
        isLoadingOptions.value = false;
    }
};

const endpointForTarget = (type: ConvertTargetType): string => {
    switch (type) {
        case 'pantry_item':
            return '/api/convert/to-pantry-item';
        case 'grocery_list_item':
            return '/api/convert/to-grocery-list-item';
        case 'recipe_ingredient':
            return '/api/convert/to-recipe-ingredient';
    }
};

const submit = async () => {
    if (!targetType.value || selectedDestinationId.value === null) {
        return;
    }

    const payload: Record<string, unknown> = {
        source_type: props.sourceType,
        source_ids: props.sourceIds,
    };

    if (targetType.value === 'pantry_item') {
        payload.space_id = selectedDestinationId.value;
        if (expiryDate.value) {
            payload.expiry_date = expiryDate.value;
        }
    } else if (targetType.value === 'grocery_list_item') {
        payload.grocery_list_id = selectedDestinationId.value;
    } else if (targetType.value === 'recipe_ingredient') {
        payload.recipe_id = selectedDestinationId.value;
    }

    loadingStore.start();

    try {
        const response = await axios.post(endpointForTarget(targetType.value), payload);
        toastStore.show('success', response.data?.message ?? 'Items converted successfully.');
        emit('converted', targetType.value);
        emit('close');
    } catch (error: any) {
        console.error(error);
        const message = error.response?.data?.message || 'Could not convert items.';
        toastStore.show('error', message);
    } finally {
        loadingStore.stop();
    }
};

const goNext = async () => {
    if (!canGoNext.value) {
        return;
    }

    if (step.value === 1) {
        step.value = 2;
        await fetchDestinationOptions();
    } else if (step.value === 2) {
        if (showExpiryStep.value) {
            step.value = 3;
        } else {
            await submit();
        }
    } else if (step.value === 3) {
        await submit();
    }
};

const goBack = () => {
    if (step.value > 1) {
        step.value -= 1;
        destinationSearch.value = '';
    }
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <Modal :is-open="isOpen" @close="handleClose">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Move {{ itemCountLabel }}</h2>
        </template>
        <template #body>
            <div class="space-y-4">
                <p class="text-xs text-gray-500">Step {{ step }} of {{ totalSteps }}</p>

                <template v-if="step === 1">
                    <p class="text-sm text-gray-600 mb-2">Where do you want to move {{ itemCountLabel }}?</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            v-for="option in targetOptions"
                            :key="option.type"
                            type="button"
                            @click="selectTarget(option.type)"
                            :class="[
                                'flex flex-col items-center justify-center gap-2 rounded-xl border-2 px-3 py-5 transition-all duration-200 hover:-translate-y-1 active:translate-y-0 active:scale-[0.99]',
                                targetType === option.type
                                    ? 'border-violet-500 bg-violet-50 ring-2 ring-violet-200'
                                    : 'border-gray-200 bg-white hover:border-violet-300 hover:bg-violet-50/30'
                            ]"
                        >
                            <div :class="[
                                'flex items-center justify-center w-12 h-12 rounded-2xl',
                                targetType === option.type ? 'bg-violet-100 text-violet-600' : 'bg-gray-100 text-gray-500'
                            ]">
                                <svg v-if="option.type === 'pantry_item'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7l1-3h16l1 3"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7z"></path>
                                </svg>
                                <svg v-else-if="option.type === 'grocery_list_item'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17H18a2 2 0 0 0 2-1.6L21 8H6"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                                </svg>
                                <svg v-else class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div class="text-sm font-semibold text-gray-900">{{ option.title }}</div>
                            <div class="text-xs text-gray-500 text-center">{{ option.subtitle }}</div>
                        </button>
                    </div>
                </template>

                <template v-else-if="step === 2">
                    <p class="text-sm text-gray-600 mb-3">Choose a {{ destinationLabel }}:</p>
                    <div v-if="isLoadingOptions" class="flex items-center justify-center gap-2 py-8 text-sm text-gray-400">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Loading…
                    </div>
                    <div v-else-if="destinationOptions.length === 0" class="text-center py-8 text-sm text-gray-400">
                        No {{ destinationLabel }}s found. Create one first.
                    </div>
                    <template v-else>
                        <div class="relative mb-2">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                            <input
                                v-model="destinationSearch"
                                type="text"
                                :placeholder="`Search ${destinationLabel}s…`"
                                class="w-full pl-9 pr-9 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-400 focus:border-violet-400 outline-none bg-white transition-colors"
                            />
                            <button
                                v-if="destinationSearch"
                                type="button"
                                @click="destinationSearch = ''"
                                aria-label="Clear search"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex flex-col gap-1 max-h-48 overflow-y-auto pr-0.5">
                            <div v-if="filteredDestinationOptions.length === 0" class="text-center py-5 text-sm text-gray-400">
                                No results for "{{ destinationSearch }}"
                            </div>
                            <button
                                v-for="option in filteredDestinationOptions"
                                :key="option.id"
                                type="button"
                                @click="selectedDestinationId = option.id"
                                :class="[
                                    'flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm text-left transition-all duration-150',
                                    selectedDestinationId === option.id
                                        ? 'bg-violet-50 border border-violet-400 text-violet-900 font-medium'
                                        : 'bg-white border border-gray-200 text-gray-800 hover:bg-gray-50 hover:border-gray-300'
                                ]"
                            >
                                <span class="truncate">{{ option.name }}</span>
                                <svg v-if="selectedDestinationId === option.id" class="flex-shrink-0 w-4 h-4 text-violet-600 ml-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="targetType === 'pantry_item' && !showExpiryStep" class="text-xs text-gray-400 mt-2">
                            Expiry dates can be set individually in Pantry after moving.
                        </p>
                    </template>
                </template>

                <template v-else-if="step === 3">
                    <p class="text-sm text-gray-600 mb-2">Optional details:</p>
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1">Expiry Date (optional)</label>
                        <input
                            v-model="expiryDate"
                            type="date"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                        />
                    </div>
                </template>
            </div>
        </template>
        <template #footer>
            <div class="flex flex-row justify-between gap-3">
                <button
                    v-if="step > 1"
                    @click="goBack"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Back
                </button>
                <button
                    v-else
                    @click="handleClose"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    @click="goNext"
                    :disabled="!canGoNext"
                    :class="[
                        'px-4 py-2 text-white rounded-lg transition-colors font-medium',
                        canGoNext ? 'bg-violet-600 hover:bg-violet-700' : 'bg-violet-300 cursor-not-allowed'
                    ]"
                >
                    {{ step === totalSteps ? 'Confirm' : 'Next' }}
                </button>
            </div>
        </template>
    </Modal>
</template>
