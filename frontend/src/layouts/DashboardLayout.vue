<script setup lang="ts">
import {computed} from "vue";
import {useRoute} from "vue-router";

const route = useRoute();

const isRecipesTab = computed(() => {
    if (route.path.startsWith('/recipes')) return true;
    if (route.path.includes('/recipe')) return true;
    return false;
});

const isGroceryList = computed(() => {
    if (route.path.startsWith('/grocery-lists')) return true;
    if (route.path.includes('/grocery-list')) return true;
    return false;
});

const isIngredientsTab = computed(() => {
    if (route.path.startsWith('/ingredients')) return true;
    if (route.path.includes('/ingredient')) return true;
    return false;
});
</script>

<template>
    <div class="min-h-screen bg-green-100 pb-16">
        <div class="max-w-md mx-auto min-h-screen">
            <slot/>
        </div>
    </div>
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg">
        <div class="flex justify-around items-center h-16">
            <router-link
                to="/grocery-list"
                class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-green-600 transition-colors"
                :class="isGroceryList ? '!text-green-600 !bg-green-50 border-t-2 border-green-600' : 'text-black' "
            >
                <span class="text-sm font-medium">Grocery list</span>
            </router-link>
            <router-link
                to="/ingredients"
                class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-green-600 transition-colors"
                :class="isIngredientsTab ? '!text-green-600 !bg-green-50 border-t-2 border-green-600' : 'text-black' "
            >
                <span class="text-sm font-medium">Ingredients</span>
            </router-link>
            <router-link
                to="/recipes"
                class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-green-600 transition-colors"
                :class="isRecipesTab ? '!text-green-600 !bg-green-50 border-t-2 border-green-600' : 'text-black' "
            >
                <span class="text-sm font-medium">Recipes</span>
            </router-link>
        </div>
    </nav>
</template>

