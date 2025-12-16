<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {useRoute} from "vue-router";
import {onMounted, ref} from "vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";

const route = useRoute();
const toasterStore = useToastStore();
const loadingStore = useLoadingStore();

const recipeId = Number(route.params.id);
const errorMessage = ref("");
const recipeData = ref({});
const getRecipe = () => {
    if (!recipeId) {
        errorMessage.value = "Recipe id does not exist.";
        return;
    }

    loadingStore.start();
    const url = '/api/recipes/' + recipeId;


    axios.get(url)
        .then((response) => {
            recipeData.value = response.data.data;
        })
        .catch((error) => {
            console.error(error);
            errorMessage.value = "Could not fetch recipe data."
            toasterStore.show('error', 'Could not get recipe.');
        })
        .finally(() => {
            loadingStore.stop();
        })
}

onMounted(() => {
    getRecipe();
})
</script>

<template>
    <DashboardLayout>
        <h2>Recipe page here !</h2>
        <pre>Recipe ID: {{ recipeId }}</pre>
        <pre class="bg-fuchsia-400">{{ recipeData }}</pre>
        <pre>{{ errorMessage }}</pre>
    </DashboardLayout>
</template>

<style scoped>

</style>
