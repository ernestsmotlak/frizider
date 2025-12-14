<script setup lang="ts">
import type {Recipe} from '../../pages/Recipe/RecipesPage.vue';

const props = defineProps<{
    recipe: Recipe
}>();

const emit = defineEmits<{
    click: [id: number]
}>();

const handleClick = () => {
    emit('click', props.recipe.id);
}

</script>

<template>
    <div @click="handleClick" class="bg-amber-300 cursor-pointer">
        {{ recipe }}
    </div>
</template>

<style scoped>

</style>
