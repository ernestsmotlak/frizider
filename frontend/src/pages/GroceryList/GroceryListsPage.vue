<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {usePagination} from "../../composables/usePagination.ts";
import GroceryListCard from "../../components/GroceryLists/GroceryListCard.vue";
import router from "../../router";

export interface GroceryList {
    id: number;
    user_id: number;
    name: string;
    notes: string | null;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

const handleGroceryListClick = (grocery_list_id: number) => {
    if ((grocery_list_id < 1)) {
        return;
    }
    router.push('/grocery-list/' + grocery_list_id);
}

const handleAddGroceryList = () => {
    router.push('/new/grocery-list');
}

const {items: groceryLists, isLoading, hasMore, allRows} = usePagination<GroceryList>({
    endpoint: '/api/get-grocery-lists',
    errorMessage: 'Could not fetch shopping lists.',
});

</script>

<template>
    <DashboardLayout>
        <div class="pt-7 px-5">
            <div class="bg-gray-50 rounded-2xl border-2 border-gray-200">
                <div class="px-4 pt-6 pb-4">
                    <div class="flex items-center justify-between mb-1">
                        <h1 class="text-3xl font-bold text-gray-900">Shopping Lists</h1>
                        <button
                            @click="handleAddGroceryList"
                            class="p-2 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200"
                        >
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                    <p v-if="allRows > 0" class="text-sm text-gray-600">
                        {{ allRows }} shopping list{{ allRows !== 1 ? 's' : '' }}
                    </p>
                    <p v-if="hasMore && groceryLists.length > 0 && !isLoading" class="text-sm text-gray-500 mt-2">
                        Scroll down for more shopping lists
                    </p>
                </div>

                <div v-if="groceryLists.length === 0 && !isLoading"
                     class="flex flex-col items-center justify-center py-16 px-4">
                    <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <p class="text-gray-500 text-center text-lg font-medium mb-1">No shopping lists yet</p>
                    <p class="text-gray-400 text-center text-sm">Start by creating your first shopping list</p>
                </div>

                <div v-else class="px-4">
                    <div class="grid grid-cols-1 gap-4">
                        <GroceryListCard
                            v-for="groceryList in groceryLists"
                            :key="groceryList.id"
                            :grocery-list="groceryList"
                            @click="handleGroceryListClick"
                        />
                    </div>

                    <div v-if="isLoading && groceryLists.length > 0" class="text-center py-8">
                        <div class="inline-flex items-center gap-2 text-gray-600">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm">Loading more shopping lists...</span>
                        </div>
                    </div>

                    <div v-if="!hasMore && groceryLists.length > 0" class="text-center py-6">
                        <p class="text-sm text-gray-500">No more shopping lists to load</p>
                    </div>
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>

