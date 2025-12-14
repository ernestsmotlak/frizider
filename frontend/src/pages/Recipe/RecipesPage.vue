<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {usePagination} from "../../composables/usePagination.ts";
import RecipeCard from "../../components/Recipes/RecipeCard.vue";
import router from "../../router";

export interface Recipe {
    id: number,
    name: string,
    description: string | null,
    image_url: string | null,
}

const handleRecipeClick = (recipe_id: number) => {
    if ((recipe_id < 1)) {
        return;
    }
    router.push('/recipe/' + recipe_id);
}

const {items: recipes, isLoading, hasMore} = usePagination<Recipe>({
    endpoint: '/api/get-recipes',
    errorMessage: 'Could not fetch recipes.',
});

</script>

<template>
    <DashboardLayout>
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4 text-center">Welcome to Recipes</h1>
            <div v-for="(recipe, index) in recipes" :key="recipe.id">
                <RecipeCard
                    :recipe="recipe"
                    @click="handleRecipeClick"
                />
            </div>
            <div v-if="isLoading && recipes.length > 0" class="text-center py-4">
                <p class="text-gray-600">Loading more recipes...</p>
            </div>
            <div v-if="!hasMore && recipes.length > 0" class="text-center py-4">
                <p class="text-gray-600">No more recipes to load.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

