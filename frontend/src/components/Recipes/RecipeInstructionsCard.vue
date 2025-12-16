<script setup lang="ts">
defineProps<{
    instructions: string | null
}>();
</script>

<template>
    <div v-if="instructions" class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Instructions</h2>
        <div class="prose max-w-none">
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ instructions }}</p>
        </div>
    </div>
</template>

<style scoped>

</style>

