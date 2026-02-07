<script setup lang="ts">
import { onMounted, ref } from "vue";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import { useLoadingStore } from "../stores/loading";
import { useToastStore } from "../stores/toast";
import type { Recipe, RecipeIngredient, RecipeInstruction } from "./Recipe/RecipePage.vue";

const loadingStore = useLoadingStore();
const toasterStore = useToastStore();

const recipe = ref<Recipe | null>(null);


const fetchCookingSession = () => {
    loadingStore.start();

    const url = '/api/get-cooking-session';

    axios
        .get(url)
        .then((result) => {
            recipe.value = result.data.data as Recipe;
        })
        .catch((error) => {
            toasterStore.show('error', 'Error fetching cooking session data.');
        })
        .finally(() => {
            loadingStore.stop();
        })
}

onMounted(() => {
    fetchCookingSession();
})
</script>

<template>
    <DashboardLayout>
        Cooking started!!!
        <pre class="bg-red-200">{{ recipe }}</pre>
    </DashboardLayout>
</template>

<style scoped>

</style>
