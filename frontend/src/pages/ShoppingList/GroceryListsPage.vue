<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {usePagination} from "../../composables/usePagination.ts";

export interface GroceryList {
    user_id: number;
    name: string;
    notes: string | null;
    completed_at: string | null;
}

const {items: groceryLists, isLoading, hasMore} = usePagination<GroceryList>({
    endpoint: '/api/get-grocery-lists',
    errorMessage: 'Could not fetch shopping lists.',
})
</script>

<template>
    <DashboardLayout>
        <div class="p-6 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Welcome to Shopping Lists</h1>
        </div>
        <pre>{{ groceryLists }}</pre>
        <div v-if="isLoading && groceryLists.length > 0" class="text-center py-4">
            <p class="text-gray-600">Loading more groceryLists...</p>
        </div>
        <div v-if="!hasMore && groceryLists.length > 0" class="text-center py-4">
            <p class="text-gray-600">No more groceryLists to load.</p>
        </div>
    </DashboardLayout>
</template>

