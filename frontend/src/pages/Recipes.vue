<script setup lang="ts">
import DashboardLayout from "../layouts/DashboardLayout.vue";
import {onMounted, onUnmounted, ref} from "vue";
import {useToastStore} from "../stores/toast.ts";
import {useLoadingStore} from "../stores/loading.ts";

const toastStore = useToastStore();
const loadingStore = useLoadingStore();
const recipes = ref<any[]>([]);
const currentPage = ref(0);
const hasMore = ref(true);
const isLoading = ref(false);
let scrollTimeout: number | null = null;

const fetchRecipes = (page: number = 1) => {
    if (isLoading.value || !hasMore.value) return;
    if (page > 1 && page <= currentPage.value) return;

    isLoading.value = true;
    if (page === 1) loadingStore.start();

    axios.post('/api/get-recipes', {page})
        .then((response: any) => {
            const paginator = response.data.data;

            if (page === 1) {
                recipes.value = paginator.data;
            } else {
                recipes.value.push(...paginator.data);
            }

            currentPage.value = page;
            hasMore.value = paginator.next_page_url !== null;
        })
        .catch(() => {
            toastStore.show('error', 'Could not fetch recipes.');
        })
        .finally(() => {
            isLoading.value = false;
            if (page === 1) loadingStore.stop();
        });
}

const handleScroll = () => {
    if (scrollTimeout) clearTimeout(scrollTimeout);

    scrollTimeout = window.setTimeout(() => {
        if (!hasMore.value || isLoading.value) return;

        const scrolledToBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 300;

        if (scrolledToBottom) {
            fetchRecipes(currentPage.value + 1);
        }
    }, 200);
}

onMounted(() => {
    fetchRecipes(1);
    window.addEventListener('scroll', handleScroll, {passive: true});
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    if (scrollTimeout) clearTimeout(scrollTimeout);
})
</script>

<template>
    <DashboardLayout>
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4 text-center">Welcome to Recipes</h1>
            <div>
                <pre>{{ recipes }}</pre>
            </div>
            <div v-if="isLoading && currentPage > 0" class="text-center py-4">
                <p class="text-gray-600">Loading more recipes...</p>
            </div>
            <div v-if="!hasMore && recipes.length > 0" class="text-center py-4">
                <p class="text-gray-600">No more recipes to load.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

