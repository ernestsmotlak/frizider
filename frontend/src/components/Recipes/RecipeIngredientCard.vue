<script setup lang="ts">
interface RecipeIngredient {
    id: number;
    recipe_id: number;
    name: string;
    quantity: number | null;
    unit: string | null;
    notes: string | null;
    sort_order: number;
}

defineProps<{
    ingredients: RecipeIngredient[]
}>();
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

    return parts.join(" ");
}

const sortedIngredients = (ingredients: RecipeIngredient[]): RecipeIngredient[] => {
    return [...ingredients].sort((a, b) => {
        if (a.sort_order !== b.sort_order) {
            return a.sort_order - b.sort_order;
        }
        return a.id - b.id;
    });
}
</script>

<template>
    <div v-if="ingredients && ingredients.length > 0" class="bg-white rounded-2xl shadow-xl p-8 relative">
        <button class="absolute top-8 right-8 p-2 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:bg-white hover:shadow-lg transition-all duration-200">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
        </button>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Ingredients</h2>
        <ul class="space-y-2">
            <li v-for="ingredient in sortedIngredients(ingredients)" :key="ingredient.id"
                class="flex items-center gap-2">
                <svg class="w-2 h-2 text-black flex-shrink-0" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3"/>
                </svg>
                <span class="text-gray-700 leading-relaxed">{{ formatIngredient(ingredient) }}</span>
            </li>
        </ul>
    </div>
</template>

<style scoped>

</style>

