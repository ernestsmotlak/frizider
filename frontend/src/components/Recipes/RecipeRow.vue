<script setup lang="ts">
import type {Recipe} from '../../pages/RecipesPage.vue';

defineProps<{
    recipe: Recipe
}>();

</script>

<template>
    <pre>{{ recipe }}</pre>
</template>

<style scoped>

</style>
