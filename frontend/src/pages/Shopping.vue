<script setup lang="ts">
import DashboardLayout from "../layouts/DashboardLayout.vue";
import {onMounted, ref} from "vue";
import type {GroceryList} from "./GroceryList/GroceryListsPage.vue";
import {useToastStore} from "../stores/toast.ts";
import {useLoadingStore} from "../stores/loading.ts";

const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const groceryLists = ref<GroceryList[]>([]);
const isLoading = ref(false);

const fetchShoppingSession = async () => {
    isLoading.value = true;
    loadingStore.start();
    try {
        const response = await axios.get('/api/get-shopping-session');
        groceryLists.value = response.data.data.grocery_lists || [];
        
        if (groceryLists.value.length === 0) {
            toastStore.show('info', 'No shopping lists selected. Please select lists from the grocery lists page.');
        }
    } catch (error: any) {
        console.error(error);
        const message = error.response?.data?.message || 'Could not fetch shopping session.';
        toastStore.show('error', message);
    } finally {
        isLoading.value = false;
        loadingStore.stop();
    }
};

onMounted(() => {
    fetchShoppingSession();
});
</script>

<template>
    <DashboardLayout>
        <div class="pt-7 px-5">
            <div class="bg-gray-50 rounded-2xl border-2 border-gray-200">
                <div class="px-4 pt-6 pb-4">
                    <h2 class="text-3xl sm:text-3xl font-bold tracking-tight text-gray-900 mb-1">
                        Shopping
                    </h2>
                    <p class="text-xs text-gray-500">
                        Shopping with {{ groceryLists.length }} list{{ groceryLists.length !== 1 ? 's' : '' }}
                    </p>
                </div>

                <div v-if="isLoading" class="flex items-center justify-center py-16">
                    <div class="inline-flex items-center gap-2 text-gray-600">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm">Loading shopping lists...</span>
                    </div>
                </div>

                <div v-else-if="groceryLists.length === 0" class="flex flex-col items-center justify-center py-16 px-4">
                    <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-gray-500 text-center text-lg font-medium mb-1">No shopping lists selected</p>
                    <p class="text-gray-400 text-center text-sm">Go to grocery lists to select lists for shopping</p>
                </div>

                <div v-else class="px-4 pb-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div v-for="groceryList in groceryLists" :key="groceryList.id"
                             class="bg-white rounded-lg border-2 border-gray-200 p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ groceryList.name }}</h3>
                            <p v-if="groceryList.notes" class="text-sm text-gray-600 mb-3">{{ groceryList.notes }}</p>
                            <div v-if="groceryList.grocery_list_items && groceryList.grocery_list_items.length > 0">
                                <p class="text-xs text-gray-500 mb-2">
                                    {{ groceryList.grocery_list_items.length }} item{{ groceryList.grocery_list_items.length !== 1 ? 's' : '' }}
                                </p>
                            </div>
                            <div v-else class="text-sm text-gray-400">No items in this list</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>

</style>
