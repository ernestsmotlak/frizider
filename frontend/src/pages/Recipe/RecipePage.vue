<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import RecipeTitleCard from "../../components/Recipes/RecipeTitleCard.vue";
import {useRoute} from "vue-router";
import {onMounted, ref} from "vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";

interface RecipeIngredient {
    id: number;
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

const route = useRoute();
const toasterStore = useToastStore();
const loadingStore = useLoadingStore();

const recipeId = Number(route.params.id);
const errorMessage = ref("");
const recipeData = ref<Recipe | null>(null);

const getRecipe = () => {
    if (!recipeId) {
        errorMessage.value = "Recipe id does not exist.";
        return;
    }

    loadingStore.start();
    const url = '/api/recipes/' + recipeId;

    axios.get(url)
        .then((response) => {
            recipeData.value = response.data.data;
        })
        .catch((error) => {
            console.error(error);
            errorMessage.value = "Could not fetch recipe data."
            toasterStore.show('error', 'Could not get recipe.');
        })
        .finally(() => {
            loadingStore.stop();
        })
}

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

    return parts.join(' ');
}

const sortedIngredients = (ingredients: RecipeIngredient[] | undefined): RecipeIngredient[] => {
    if (!ingredients) return [];
    return [...ingredients].sort((a, b) => {
        if (a.sort_order !== b.sort_order) {
            return a.sort_order - b.sort_order;
        }
        return a.id - b.id;
    });
}

onMounted(() => {
    getRecipe();
})
</script>

<template>
    <DashboardLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-600 font-medium">{{ errorMessage }}</p>
            </div>

            <div v-if="recipeData" class="space-y-6">
                <RecipeTitleCard :recipe="recipeData" />

                <div v-if="recipeData.recipe_ingredients && recipeData.recipe_ingredients.length > 0"
                     class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Ingredients</h2>
                    <ul class="space-y-2">
                        <li v-for="ingredient in sortedIngredients(recipeData.recipe_ingredients)" :key="ingredient.id"
                            class="flex items-center gap-2">
                            <svg class="w-2 h-2 text-black flex-shrink-0" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3"/>
                            </svg>
                            <span class="text-gray-700 leading-relaxed">{{ formatIngredient(ingredient) }}</span>
                        </li>
                    </ul>
                </div>

                <div v-if="recipeData.instructions" class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Instructions</h2>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ recipeData.instructions }}</p>
                    </div>
                </div>
            </div>

            <div v-else-if="!errorMessage" class="text-center py-12">
                <p class="text-gray-600 text-lg">Loading recipe...</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>

</style>
