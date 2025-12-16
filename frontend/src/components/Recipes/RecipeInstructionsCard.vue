<script setup lang="ts">
defineProps<{
    instructions: string | null
}>();
</script>

<template>
    <div v-if="instructions" class="bg-white rounded-2xl shadow-xl p-8 relative">
        <button class="absolute top-2 right-2 p-2 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:bg-white hover:shadow-lg transition-all duration-200">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
        </button>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Instructions</h2>
        <div class="prose max-w-none">
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ instructions }}</p>
        </div>
    </div>
</template>

<style scoped>

</style>

