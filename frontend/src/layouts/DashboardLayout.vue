<template>
    <div class="min-h-screen bg-green-100 pb-16">
        <slot/>
    </div>
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg">
        <div class="flex justify-around items-center h-16">
            <router-link
                to="/shopping-list"
                class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-green-600 transition-colors"
                active-class="!text-green-600 !bg-green-50 border-t-2 border-green-600"
            >
                <span class="text-sm font-medium">Shopping list</span>
            </router-link>
            <router-link
                to="/ingredients"
                class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-green-600 transition-colors"
                active-class="!text-green-600 !bg-green-50 border-t-2 border-green-600"
            >
                <span class="text-sm font-medium">Ingredients</span>
            </router-link>
            <router-link
                to="/recipes"
                class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-green-600 transition-colors"
                active-class="!text-green-600 !bg-green-50 border-t-2 border-green-600"
            >
                <span class="text-sm font-medium">Recipes</span>
            </router-link>
        </div>
    </nav>
</template>

<script setup lang="ts">
</script>

