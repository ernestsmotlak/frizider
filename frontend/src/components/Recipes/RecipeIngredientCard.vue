<script setup lang="ts">
import {ref, watchEffect, onMounted, onUnmounted, computed} from "vue";
import Modal from "../Modal.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import {useConfirmStore} from "../../stores/confirm.ts";
import {VueDraggable} from 'vue-draggable-plus';
import "@vueup/vue-quill/dist/vue-quill.snow.css";

interface RecipeIngredient {
    id: number | null;
    recipe_id: number;
    name: string;
    quantity: number | null;
    unit: string | null;
    notes: string | null;
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
    recipe_ingredients?: RecipeIngredient[];
}

const props = defineProps<{
    ingredients: RecipeIngredient[];
    recipeId: number;
}>();

const emit = defineEmits<{
    'updatedRecipe': [recipe: Recipe]
}>();

const toastStore = useToastStore();
const loadingStore = useLoadingStore();
const confirmStore = useConfirmStore();

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const openDropdownId = ref<number | null>(null);
const editingIngredient = ref<RecipeIngredient | null>(null);

const newIngredient = ref<Omit<RecipeIngredient, 'id'>>({
    recipe_id: props.recipeId,
    name: '',
    quantity: null,
    unit: null,
    notes: null,
    sort_order: 0,
    completed: false,
});

const sortedIngredients = (ingredients: RecipeIngredient[]): RecipeIngredient[] => {
    return [...ingredients].sort((a, b) => {
        if (a.sort_order !== b.sort_order) {
            return a.sort_order - b.sort_order;
        }
        const aId = a.id ?? 0;
        const bId = b.id ?? 0;
        return aId - bId;
    });
}

const draggableIngredients = ref<RecipeIngredient[]>([]);

const numberOfSelectedDraggableIngredients = computed(() => {
    return draggableIngredients.value.filter(ingredient => ingredient.completed).length;
});

watchEffect(() => {
    draggableIngredients.value = sortedIngredients(props.ingredients).map(ing => ({...ing}));
});

const onDragEnd = () => {
    draggableIngredients.value.forEach((ingredient, index) => {
        ingredient.sort_order = index;
    });

    const recipeId = props.recipeId;
    const payload = {
        ingredients: draggableIngredients.value.map((ingredient) => ({
            id: ingredient.id,
            name: ingredient.name,
            quantity: ingredient.quantity ?? null,
            unit: ingredient.unit ?? null,
            notes: ingredient.notes ?? null,
            sort_order: ingredient.sort_order,
            completed: ingredient.completed ?? false,
        }))
    };

    loadingStore.start();

    axios.post(`/api/recipes/${recipeId}/ingredients`, payload)
        .then((response) => {
            const updatedRecipe = response.data.data;
            emit('updatedRecipe', updatedRecipe);
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not update ingredient order.';
            toastStore.show('error', errorMessage);
            draggableIngredients.value = sortedIngredients(props.ingredients).map(ing => ({...ing}));
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const toggleIngredient = (ingredient: RecipeIngredient) => {
    if (!ingredient.id) {
        return;
    }

    const recipeId = props.recipeId;
    const ingredientId = ingredient.id;
    const url = `/api/recipe/${recipeId}/ingredient/${ingredientId}/toggle-completed`;

    axios.post(url)
        .then((response) => {
            const updatedRecipe = response.data.data;
            emit('updatedRecipe', updatedRecipe);
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not update ingredient status.';
            toastStore.show('error', errorMessage);
        });
};

const toggleDropdown = (ingredientId: number | null) => {
    if (openDropdownId.value === ingredientId) {
        openDropdownId.value = null;
    } else {
        openDropdownId.value = ingredientId;
    }
};

const closeDropdown = () => {
    openDropdownId.value = null;
};

const openEditModal = (ingredient: RecipeIngredient) => {
    editingIngredient.value = {
        ...ingredient
    };
    closeDropdown();
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editingIngredient.value = null;
};

const updateIngredient = () => {
    if (!editingIngredient.value || !editingIngredient.value.name.trim()) {
        toastStore.show('error', 'Ingredient name is required.');
        return;
    }

    if (!editingIngredient.value.id) {
        toastStore.show('error', 'Cannot update ingredient without ID.');
        return;
    }

    const recipeId = props.recipeId;
    const ingredientId = editingIngredient.value.id;
    loadingStore.start();

    const payload = {
        ingredients: draggableIngredients.value.map((ing) => {
            if (ing.id === ingredientId) {
                return {
                    id: editingIngredient.value!.id,
                    name: editingIngredient.value!.name,
                    quantity: editingIngredient.value!.quantity ?? null,
                    unit: editingIngredient.value!.unit ?? null,
                    notes: editingIngredient.value!.notes ?? null,
                    sort_order: ing.sort_order,
                    completed: ing.completed ?? false,
                };
            }
            return {
                id: ing.id,
                name: ing.name,
                quantity: ing.quantity ?? null,
                unit: ing.unit ?? null,
                notes: ing.notes ?? null,
                sort_order: ing.sort_order,
                completed: ing.completed ?? false,
            };
        })
    };

    axios.post(`/api/recipes/${recipeId}/ingredients`, payload)
        .then((response) => {
            const updatedRecipe = response.data.data;
            emit('updatedRecipe', updatedRecipe);
            toastStore.show('success', 'Ingredient updated successfully.');
            closeEditModal();
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not update ingredient.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const deleteIngredient = async (ingredient: RecipeIngredient) => {
    if (!ingredient) {
        toastStore.show('error', 'An error occurred while deleting the ingredient.');
        return;
    }

    const confirmed = await confirmStore.show(`Are you sure you want to delete "${ingredient.name || 'this ingredient'}"?`);

    if (!confirmed) {
        return;
    }

    if (!ingredient.id) {
        const index = draggableIngredients.value.findIndex(i => i === ingredient);
        if (index > -1) {
            draggableIngredients.value.splice(index, 1);
        }
        closeDropdown();
        return;
    }

    const recipeId = props.recipeId;
    const url = `/api/recipe/${recipeId}/ingredient/${ingredient.id}`;
    loadingStore.start();

    axios.delete(url)
        .then((response) => {
            const updatedRecipe = response.data.data;
            emit('updatedRecipe', updatedRecipe);
            toastStore.show('success', 'Ingredient successfully removed.');
            closeDropdown();
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not delete ingredient.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.dropdown-container')) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const openAddModal = () => {
    const maxSortOrder = props.ingredients.length > 0
        ? Math.max(...props.ingredients.map(i => i.sort_order))
        : -1;
    newIngredient.value = {
        recipe_id: props.recipeId,
        name: '',
        quantity: null,
        unit: null,
        notes: null,
        sort_order: maxSortOrder + 1,
    };
    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    isAddModalOpen.value = false;
};

const formatIngredient = (ingredient: RecipeIngredient): string => {
    const parts: string[] = [];

    if (ingredient.quantity !== null) {
        parts.push(ingredient.quantity.toString());
    }

    if (ingredient.unit) {
        parts.push(ingredient.unit);
    }

    parts.push(ingredient.name);

    if (ingredient.notes) {
        parts.push(`(${ingredient.notes})`);
    }

    return parts.join(" ");
}


const addIngredient = () => {
    if (!newIngredient.value.name.trim()) {
        toastStore.show('error', 'Ingredient name is required.');
        return;
    }

    const recipeId = props.recipeId;
    loadingStore.start();

    axios.post('/api/recipe-ingredients', newIngredient.value)
        .then(() => {
            return axios.get(`/api/recipes/${recipeId}`);
        })
        .then((response) => {
            const updatedRecipe = response.data.data;
            emit('updatedRecipe', updatedRecipe);
            toastStore.show('success', 'Ingredient added successfully.');
            closeAddModal();
        })
        .catch((error) => {
            const errorMessage = error?.response?.data?.message || 'Could not add ingredient.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
}

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
        <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-200">
            <div class="flex-1">
                <h2 class="text-2xl sm:text-2xl font-bold tracking-tight text-gray-900">
                    Ingredients
                </h2>
                <p class="text-xs text-gray-500">
                    Drag to reorder • Tap to mark done
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    <span class="font-medium text-gray-700 tabular-nums">
                        {{ numberOfSelectedDraggableIngredients }}/{{ draggableIngredients.length }}
                    </span>
                    checked
                </p>
            </div>
        </div>

        <template v-if="draggableIngredients && draggableIngredients.length > 0">
            <VueDraggable
                v-model="draggableIngredients"
                @end="onDragEnd"
                handle=".drag-handle"
                tag="ul"
                class="space-y-2.5"
            >
                <li
                    v-for="(ingredient, index) in draggableIngredients"
                    :key="ingredient.id ?? `tmp-${ingredient.recipe_id}-${ingredient.sort_order}-${index}`"
                    @click="toggleIngredient(ingredient)"
                    class="flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-blue-200 transition-all duration-250 cursor-pointer relative"
                >
                    <div class="drag-handle cursor-move p-1 hover:bg-gray-100 rounded flex-shrink-0" @click.stop>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 8h16M4 16h16"></path>
                        </svg>
                    </div>
                    <input
                        type="checkbox"
                        :checked="ingredient.completed"
                        @click.stop="toggleIngredient(ingredient)"
                        class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 cursor-pointer flex-shrink-0"
                    />
                    <span :class="[
                        'text-gray-800 leading-relaxed text-[15px] font-medium flex-1',
                        ingredient.completed ? 'line-through opacity-60' : ''
                    ]">
                        {{ formatIngredient(ingredient) }}
                    </span>
                    <div class="relative flex-shrink-0 dropdown-container opacity-100" @click.stop>
                        <button
                            @click="toggleDropdown(ingredient.id)"
                            class="p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors opacity-100"
                            title="More options"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div
                            v-if="openDropdownId === ingredient.id"
                            class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10 opacity-100"
                        >
                            <button
                                @click="openEditModal(ingredient)"
                                class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </button>
                            <button
                                @click="deleteIngredient(ingredient)"
                                class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </li>
            </VueDraggable>
        </template>
        <div v-else class="text-center py-8">
            <p class="text-gray-500 mb-4">No ingredients yet. Click the + button to add your first ingredient.</p>
        </div>
    </div>

    <Modal :isOpen="isEditModalOpen" @close="closeEditModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Edit Ingredient</h2>
        </template>
        <template #body>
            <div class="space-y-4">
                <div v-if="editingIngredient">
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1">Name *</label>
                        <input
                            v-model="editingIngredient.name"
                            type="text"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                            placeholder="Flour"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="block text-xs text-gray-500 font-medium mb-1">Quantity</label>
                            <input
                                v-model.number="editingIngredient.quantity"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                placeholder="2"
                            />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 font-medium mb-1">Unit</label>
                            <input
                                v-model="editingIngredient.unit"
                                type="text"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                placeholder="cups"
                            />
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="block text-xs text-gray-500 font-medium mb-1">Notes (optional)</label>
                        <input
                            v-model="editingIngredient.notes"
                            type="text"
                            class="w-full px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                            placeholder="e.g. sifted, room temperature"
                        />
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex flex-col sm:flex-row justify-between gap-3">
                <button
                    @click="closeEditModal"
                    class="w-full sm:w-auto px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    @click.stop="updateIngredient"
                    class="w-full sm:w-auto px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Save Changes
                </button>
            </div>
        </template>
    </Modal>

    <Modal :isOpen="isAddModalOpen" @close="closeAddModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Add Ingredient</h2>
        </template>
        <template #body>
            <div class="space-y-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1">Name *</label>
                        <input
                            v-model="newIngredient.name"
                            type="text"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                            placeholder="Flour"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 font-medium mb-1">Quantity</label>
                            <input
                                v-model.number="newIngredient.quantity"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                placeholder="2"
                            />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 font-medium mb-1">Unit</label>
                            <input
                                v-model="newIngredient.unit"
                                type="text"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                placeholder="cups"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1">Notes (optional)</label>
                        <input
                            v-model="newIngredient.notes"
                            type="text"
                            class="w-full px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                            placeholder="e.g. sifted, room temperature"
                        />
                    </div>
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
                    @click.stop="addIngredient"
                    class="w-full sm:w-auto px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Add Ingredient
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
:deep(ul.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost) {
    transition: all 0.2s ease;
    border-color: rgb(229 231 235);
    background: rgb(249 250 251);
}

/* When dragging, make non-dragged items slightly dimmed */
:deep(ul.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost) {
    opacity: 0.7;
    transform: scale(0.98);
}

/* Hover effect on non-dragged items during drag */
:deep(ul.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost):hover {
    opacity: 0.85;
    transform: scale(0.99);
    border-color: rgb(209 213 219);
    background: rgb(243 244 246);
}

/* Make the drag handle more prominent on non-dragged items during drag */
:deep(ul.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost) .drag-handle {
    background: rgb(243 244 246);
    border: 1px solid rgb(229 231 235);
    border-radius: 0.375rem;
}

/* Smooth transitions for all list items */
:deep(ul li) {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

