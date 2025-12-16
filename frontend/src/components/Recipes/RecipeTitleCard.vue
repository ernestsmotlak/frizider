<script setup lang="ts">
interface Recipe {
    id: number;
    name: string;
    description: string | null;
    instructions: string | null;
    servings: number | null;
    prep_time: number | null;
    cook_time: number | null;
    image_url: string | null;
}
defineProps<{
    recipe: Recipe
}>();
const formatTime = (minutes: number | null): string => {
    if (!minutes) return "N/A";
    if (minutes < 60) return `${minutes} min`;
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return mins > 0 ? `${hours}h ${mins}min` : `${hours}h`;
}
</script>

<template>
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div v-if="recipe.image_url" class="w-full h-64 md:h-96 bg-gray-200">
            <img :src="recipe.image_url" :alt="recipe.name" class="w-full h-full object-cover">
        </div>

        <div class="p-8 space-y-6">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 mb-3">{{ recipe.name }}</h1>
                <p v-if="recipe.description" class="text-lg text-gray-600 leading-relaxed">
                    {{ recipe.description }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 pt-4 border-t border-gray-200">
                <div class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Servings</p>
                        <p class="text-lg font-semibold text-gray-900">{{
                                recipe.servings || "N/A"
                            }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Prep Time</p>
                        <p class="text-lg font-semibold text-gray-900">{{
                                formatTime(recipe.prep_time)
                            }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Cook Time</p>
                        <p class="text-lg font-semibold text-gray-900">{{
                                formatTime(recipe.cook_time)
                            }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
