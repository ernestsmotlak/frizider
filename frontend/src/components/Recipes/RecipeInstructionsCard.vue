<script setup lang="ts">
import {ref, watchEffect} from "vue";
import Modal from "../Modal.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import {VueDraggable} from 'vue-draggable-plus';

interface RecipeInstruction {
    id: number | null;
    recipe_id: number;
    instruction: string;
    sort_order: number;
    completed: boolean;
}

interface Recipe {
    id: number;
    name: string;
    description: string | null;
    servings: number | null;
    prep_time: number | null;
    cook_time: number | null;
    image_url: string | null;
    recipe_ingredients?: any[];
    recipe_instructions?: RecipeInstruction[];
}

const props = withDefaults(defineProps<{
    instructions: RecipeInstruction[];
    recipeId: number;
}>(), {
    instructions: () => []
});

const emit = defineEmits<{
    'updatedRecipe': [recipe: Recipe]
}>();

const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const isAddModalOpen = ref(false);

const newInstruction = ref<Omit<RecipeInstruction, 'id'>>({
    recipe_id: props.recipeId,
    instruction: '',
    sort_order: 0,
    completed: false,
});

const openAddModal = () => {
    const maxSortOrder = props.instructions.length > 0
        ? Math.max(...props.instructions.map(i => i.sort_order))
        : -1;
    newInstruction.value = {
        recipe_id: props.recipeId,
        instruction: '',
        sort_order: maxSortOrder + 1,
    };
    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    isAddModalOpen.value = false;
};

const sortedInstructions = (instructions: RecipeInstruction[]): RecipeInstruction[] => {
    return [...instructions].sort((a, b) => {
        if (a.sort_order !== b.sort_order) {
            return a.sort_order - b.sort_order;
        }
        const aId = a.id ?? 0;
        const bId = b.id ?? 0;
        return aId - bId;
    });
}

const draggableInstructions = ref<RecipeInstruction[]>([]);

watchEffect(() => {
    draggableInstructions.value = sortedInstructions(props.instructions).map(inst => ({...inst}));
});

const onDragEnd = () => {
    draggableInstructions.value.forEach((instruction, index) => {
        instruction.sort_order = index;
    });

    const recipeId = props.recipeId;
    const payload = {
        instructions: draggableInstructions.value.map((instruction) => ({
            id: instruction.id,
            instruction: instruction.instruction,
            sort_order: instruction.sort_order,
            completed: instruction.completed ?? false,
        }))
    };

    loadingStore.start();

    axios.post(`/api/recipes/${recipeId}/instructions`, payload)
        .then((response) => {
            const updatedRecipe = response.data.data;
            emit('updatedRecipe', updatedRecipe);
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not update instruction order.';
            toastStore.show('error', errorMessage);
            draggableInstructions.value = sortedInstructions(props.instructions).map(inst => ({...inst}));
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const addInstruction = () => {
    if (!newInstruction.value.instruction.trim()) {
        toastStore.show('error', 'Instruction text is required.');
        return;
    }

    const recipeId = props.recipeId;
    loadingStore.start();

    axios.post(`/api/recipes/${recipeId}/instructions`, {
        instructions: [{
            instruction: newInstruction.value.instruction,
            sort_order: newInstruction.value.sort_order,
            completed: false,
        }]
    })
        .then(() => {
            return axios.get(`/api/recipes/${recipeId}`);
        })
        .then((response) => {
            const updatedRecipe = response.data.data;
            emit('updatedRecipe', updatedRecipe);
            toastStore.show('success', 'Step added successfully.');
            closeAddModal();
        })
        .catch((error) => {
            const errorMessage = error?.response?.data?.message || 'Could not add step.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
}

const toggleStep = (instruction: RecipeInstruction) => {
    if (!instruction.id) {
        return;
    }

    const recipeId = props.recipeId;
    const instructionId = instruction.id;
    const url = `/api/recipe/${recipeId}/instruction/${instructionId}/toggle-completed`;

    axios.post(url)
        .then((response) => {
            const updatedRecipe = response.data.data;
            emit('updatedRecipe', updatedRecipe);
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not update step status.';
            toastStore.show('error', errorMessage);
        });
};
</script>

<template>
    <div class="bg-white rounded-2xl shadow-xl p-8 relative border-2 border-gray-200">
        <div class="absolute top-2 right-2 flex gap-2">
            <button @click="openAddModal"
                    class="p-2 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Instructions</h2>
        <template v-if="draggableInstructions && draggableInstructions.length > 0">
            <VueDraggable
                v-model="draggableInstructions"
                @end="onDragEnd"
                handle=".drag-handle"
                tag="ol"
                class="space-y-3"
            >
                <li
                    v-for="(step, index) in draggableInstructions"
                    :key="step.id ?? `tmp-${step.recipe_id}-${step.sort_order}-${index}`"
                    @click="toggleStep(step)"
                    :class="[
                        'flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-blue-200 transition-all duration-250 cursor-pointer',
                        step.completed ? 'line-through opacity-60' : ''
                    ]">
                    <div class="drag-handle cursor-move p-1 hover:bg-gray-100 rounded flex-shrink-0" @click.stop>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                        </svg>
                    </div>
                    <input
                        type="checkbox"
                        :checked="step.completed"
                        @click.stop="toggleStep(step)"
                        class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 cursor-pointer flex-shrink-0"
                    />
                    <span class="text-gray-800 leading-relaxed text-[15px] font-medium flex-1">
                        <span class="font-semibold text-gray-600">{{ index + 1 }}.</span> {{ step.instruction }}
                    </span>
                </li>
            </VueDraggable>
        </template>
        <div v-else class="text-center py-8">
            <p class="text-gray-500 mb-4">No instructions yet. Click the + button to add your first step.</p>
        </div>
    </div>

    <Modal :isOpen="isAddModalOpen" @close="closeAddModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Add Instruction Step</h2>
        </template>
        <template #body>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-1">Instruction *</label>
                    <textarea
                        v-model="newInstruction.instruction"
                        rows="3"
                        class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                        placeholder="Enter instruction step..."
                    />
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex flex-col sm:flex-row justify-between gap-3">
                <button
                    @click="closeAddModal"
                    class="w-full sm:w-auto px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    @click.stop="addInstruction"
                    class="w-full sm:w-auto px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Add Step
                </button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
/* Styles for dragged item */
:deep(.sortable-drag) {
    opacity: 0.8;
    box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.2), 0 4px 8px -2px rgba(0, 0, 0, 0.15);
    border: 2px solid rgb(59 130 246);
    background: rgb(239 246 255);
    z-index: 1000;
}

/* Styles for placeholder (where item will be dropped) */
:deep(.sortable-ghost) {
    opacity: 0.4;
    background: rgb(229 231 235);
    border: 2px dashed rgb(156 163 175);
    border-radius: 0.5rem;
}

/* Styles for item being dragged */
:deep(li.sortable-drag) {
    cursor: grabbing !important;
}

/* Styles for items NOT being dragged - when dragging is active */
:deep(ol.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost) {
    transition: all 0.2s ease;
    border-color: rgb(229 231 235);
    background: rgb(249 250 251);
}

/* When dragging, make non-dragged items slightly dimmed */
:deep(ol.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost) {
    opacity: 0.7;
    transform: scale(0.98);
}

/* Hover effect on non-dragged items during drag */
:deep(ol.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost):hover {
    opacity: 0.85;
    transform: scale(0.99);
    border-color: rgb(209 213 219);
    background: rgb(243 244 246);
}

/* Make the drag handle more prominent on non-dragged items during drag */
:deep(ol.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost) .drag-handle {
    background: rgb(243 244 246);
    border: 1px solid rgb(229 231 235);
    border-radius: 0.375rem;
}

/* Smooth transitions for all list items */
:deep(ol li) {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
