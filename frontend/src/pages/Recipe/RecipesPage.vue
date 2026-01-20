<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {usePagination} from "../../composables/usePagination.ts";
import RecipeCard from "../../components/Recipes/RecipeCard.vue";
import router from "../../router";
import {onUnmounted, ref, watch} from "vue";

export interface Recipe {
    id: number,
    name: string,
    description: string | null,
    image_url: string | null,
}

const searchTerm = ref("");

const handleRecipeClick = (recipe_id: number) => {
    if ((recipe_id < 1)) {
        return;
    }
    router.push('/recipe/' + recipe_id);
}

const handleAddRecipe = () => {
    router.push('/new/recipe');
}

const {items: recipes, isLoading, hasMore, allRows, refresh} = usePagination<Recipe>({
    endpoint: '/api/get-recipes',
    errorMessage: 'Could not fetch recipes.',
    payload: () => ({
        searchTerm: searchTerm.value,
    }),
});

let searchTimeout: number | null = null;
watch(searchTerm, () => {
    if (searchTimeout) {
        window.clearTimeout(searchTimeout);
    }
    searchTimeout = window.setTimeout(() => {
        refresh();
    }, 400);
});

onUnmounted(() => {
    if (searchTimeout) {
        window.clearTimeout(searchTimeout);
    }
});

</script>

<template>
    <DashboardLayout>
        <div class="pt-7 px-5">
            <div class="bg-gray-50 rounded-2xl border-2 border-gray-200">
                <div class="px-4 pt-6 pb-4">
                    <div class="flex items-center justify-between mb-1">
                        <h1 class="text-3xl font-bold text-gray-900">Recipes</h1>
                        <button
                            @click="handleAddRecipe"
                            class="p-2 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200"
                        >
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                    <p v-if="allRows > 0" class="text-sm text-gray-600">
                        {{ allRows }} recipe{{ allRows !== 1 ? 's' : '' }}
                    </p>

                    <div class="mt-4">
                        <label for="recipes-search" class="sr-only">Search recipes</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
                                    />
                                </svg>
                            </div>
                            <input
                                id="recipes-search"
                                v-model="searchTerm"
                                type="text"
                                inputmode="search"
                                enterkeyhint="search"
                                role="searchbox"
                                placeholder="Search recipes"
                                autocomplete="off"
                                class="relative z-0 w-full px-4 py-2.5 pl-10 pr-10 text-base text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            />
                            <button
                                v-if="searchTerm"
                                type="button"
                                class="absolute inset-y-0 right-0 z-10 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                @click="searchTerm = ''"
                                aria-label="Clear search"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <p v-if="hasMore && recipes.length > 0 && !isLoading" class="text-sm text-gray-500 mt-2">
                        Scroll down for more recipes
                    </p>
                </div>

                <div v-if="recipes.length === 0 && !isLoading"
                     class="flex flex-col items-center justify-center py-16 px-4">
                    <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <p class="text-gray-500 text-center text-lg font-medium mb-1">No recipes yet</p>
                    <p class="text-gray-400 text-center text-sm">Start by creating your first recipe</p>
                </div>

                <div v-else class="px-4">
                    <div
                        class="grid grid-cols-1 gap-4"
                        :class="{ 'mb-10': hasMore}"
                    >
                        <RecipeCard
                            v-for="recipe in recipes"
                            :key="recipe.id"
                            :recipe="recipe"
                            @click="handleRecipeClick"
                        />
                    </div>

                    <div v-if="isLoading && recipes.length > 0" class="text-center py-8">
                        <div class="inline-flex items-center gap-2 text-gray-600">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm">Loading more recipes...</span>
                        </div>
                    </div>

                    <div v-if="!hasMore && recipes.length > 0" class="text-center py-6">
                        <p class="text-sm text-gray-500">No more recipes to load</p>
                    </div>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>

