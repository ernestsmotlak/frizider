<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
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

const formatTime = (minutes: number | null): string => {
    if (!minutes) return 'N/A';
    if (minutes < 60) return `${minutes} min`;
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return mins > 0 ? `${hours}h ${mins}min` : `${hours}h`;
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
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div v-if="recipeData.image_url" class="w-full h-64 md:h-96 bg-gray-200">
                        <img :src="recipeData.image_url" :alt="recipeData.name" class="w-full h-full object-cover">
                    </div>

                    <div class="p-8 space-y-6">
                        <div>
                            <h1 class="text-4xl font-bold text-gray-900 mb-3">{{ recipeData.name }}</h1>
                            <p v-if="recipeData.description" class="text-lg text-gray-600 leading-relaxed">
                                {{ recipeData.description }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Servings</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ recipeData.servings || 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Prep Time</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ formatTime(recipeData.prep_time) }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Cook Time</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ formatTime(recipeData.cook_time) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="recipeData.recipe_ingredients && recipeData.recipe_ingredients.length > 0" class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Ingredients</h2>
                    <ul class="space-y-2">
                        <li v-for="ingredient in sortedIngredients(recipeData.recipe_ingredients)" :key="ingredient.id" class="flex items-start gap-2">
                            <span class="text-gray-400 mt-1">•</span>
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
