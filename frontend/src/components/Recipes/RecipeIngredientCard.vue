<script setup lang="ts">
import {ref} from "vue";
import Modal from "../Modal.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";

interface RecipeIngredient {
    id: number | null;
    recipe_id: number;
    name: string;
    quantity: number | null;
    unit: string | null;
    notes: string | null;
    sort_order: number;
}

interface Recipe {
    id: number;
    name: string;
    description: string | null;
    instructions: string | null;
    servings: number | null;
    prep_time: number | null;
    cook_time: number | null;
    image_url: string | null;
    recipe_ingredients?: RecipeIngredient[];
}

const props = defineProps<{
    ingredients: RecipeIngredient[]
}>();

const emit = defineEmits<{
    'updatedRecipe': [recipe: Recipe]
}>();

const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const isModalOpen = ref(false);

const formData = ref<RecipeIngredient[]>([]);

const openModal = () => {
    formData.value = props.ingredients.map(ingredient => ({
        ...ingredient
    }));
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
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

const sortedIngredients = (ingredients: RecipeIngredient[]): RecipeIngredient[] => {
    return [...ingredients].sort((a, b) => {
        if (a.sort_order !== b.sort_order) {
            return a.sort_order - b.sort_order;
        }
        return a.id - b.id;
    });
}

const updateIngredients = () => {
    if (!props.ingredients || props.ingredients.length === 0) {
        toastStore.show('error', 'No recipe ID available.');
        return;
    }

    const recipeId = props.ingredients[0].recipe_id;
    const payload = {
        ingredients: formData.value.map((ingredient, index) => ({
            id: ingredient.id,
            name: ingredient.name,
            quantity: ingredient.quantity ?? null,
            unit: ingredient.unit ?? null,
            notes: ingredient.notes ?? null,
            sort_order: ingredient.sort_order ?? index,
        }))
    };

    loadingStore.start();

    axios.post(`/api/recipes/${recipeId}/ingredients`, payload)
        .then((response) => {
            const updatedRecipe = response.data.data;
            emit('updatedRecipe', updatedRecipe);
            toastStore.show('success', 'Ingredients updated successfully.');
            closeModal();
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not update ingredients.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
}

</script>

<template>
    <div v-if="ingredients && ingredients.length > 0" class="bg-white rounded-2xl shadow-xl p-8 relative">
        <button @click="openModal"
                class="absolute top-2 right-2 p-2 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:bg-white hover:shadow-lg transition-all duration-200">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
        </button>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Ingredients</h2>
        <ul class="space-y-2">
            <li v-for="ingredient in sortedIngredients(ingredients)" :key="ingredient.id"
                class="flex items-center gap-2">
                <svg class="w-2 h-2 text-black flex-shrink-0" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3"/>
                </svg>
                <span class="text-gray-700 leading-relaxed">{{ formatIngredient(ingredient) }}</span>
            </li>
        </ul>
    </div>
    <!--    <pre>{{formData}}</pre>-->

    <Modal :isOpen="isModalOpen" @close="closeModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Edit Ingredients</h2>
        </template>
        <template #body>
            <div class="space-y-4">
                <div v-for="(ingredient, index) in sortedIngredients(formData)" :key="ingredient.id"
                     class="p-3 sm:p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">
                    <div class="flex items-start gap-3 mb-3">
                        <!--                        <svg class="w-2 h-2 text-gray-600 flex-shrink-0 mt-3" fill="currentColor" viewBox="0 0 8 8">-->
                        <!--                            <circle cx="4" cy="4" r="3"/>-->
                        <!--                        </svg>-->
                        <div class="flex-1 space-y-3">
                            <div class="sm:hidden">
                                <label class="block text-xs text-gray-500 font-medium mb-1">Name</label>
                                <input
                                    v-model="ingredient.name"
                                    type="text"
                                    class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                                    placeholder="Flour"
                                />
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-12 gap-3">
                                <div class="hidden sm:block sm:col-span-5">
                                    <label class="block text-xs text-gray-500 font-medium mb-1">Name</label>
                                    <input
                                        v-model="ingredient.name"
                                        type="text"
                                        class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                                        placeholder="Flour"
                                    />
                                </div>
                                <div class="col-span-1 sm:col-span-3">
                                    <label class="block text-xs text-gray-500 font-medium mb-1">Quantity</label>
                                    <input
                                        v-model.number="ingredient.quantity"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                                        placeholder="2"
                                    />
                                </div>
                                <div class="col-span-1 sm:col-span-4">
                                    <label class="block text-xs text-gray-500 font-medium mb-1">Unit</label>
                                    <input
                                        v-model="ingredient.unit"
                                        type="text"
                                        class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                                        placeholder="cups"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 font-medium mb-1">Notes (optional)</label>
                                <input
                                    v-model="ingredient.notes"
                                    type="text"
                                    class="w-full px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors"
                                    placeholder="e.g. sifted, room temperature"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex flex-col sm:flex-row justify-between gap-3">
                <button
                    @click="closeModal"
                    class="w-full sm:w-auto px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    @click.stop="updateIngredients"
                    class="w-full sm:w-auto px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Save Changes
                </button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>

</style>

