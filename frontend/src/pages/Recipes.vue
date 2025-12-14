<script setup lang="ts">
import DashboardLayout from "../layouts/DashboardLayout.vue";
import {onMounted, ref} from "vue";
import {useToastStore} from "../stores/toast.ts";
import {useLoadingStore} from "../stores/loading.ts";

const toastStore = useToastStore();
const loadingStore = useLoadingStore();
const recipes = ref([]);

const fetchRecipes = async (url?: string | null) => {
    loadingStore.start();

    const payload = url ?? null;

    axios.post('/api/get-recipes', payload)
        .then((response: any) => {
            recipes.value = response.data.data;
        })
        .catch((error) => {
            console.error(error);
            toastStore.show('error', 'Could not fetch recipes.');
        })
        .finally(() => {
            loadingStore.stop();
        })
}

onMounted(() => {
    fetchRecipes();
})
</script>

<template>
    <DashboardLayout>
        <div class="p-6 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Welcome to Recipes</h1>
            <div>
                <pre>{{recipes}}</pre>
            </div>
        </div>
    </DashboardLayout>
</template>

