<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import RecipeTitleCard from "../../components/Recipes/RecipeTitleCard.vue";
import RecipeIngredientCard from "../../components/Recipes/RecipeIngredientCard.vue";
import RecipeInstructionsCard from "../../components/Recipes/RecipeInstructionsCard.vue";
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
                <RecipeTitleCard v-if="recipeData"
                                 :recipe="recipeData"
                />

                <RecipeIngredientCard
                    v-if="recipeData.recipe_ingredients"
                    :ingredients="recipeData.recipe_ingredients"
                />

                <RecipeInstructionsCard
                    v-if="recipeData?.instructions"
                    :instructions="recipeData.instructions"
                />
            </div>

            <div v-else-if="!errorMessage" class="text-center py-12">
                <p class="text-gray-600 text-lg">Loading recipe...</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>

</style>
