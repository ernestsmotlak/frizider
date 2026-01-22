<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {usePagination} from "../../composables/usePagination.ts";
import GroceryListCard from "../../components/GroceryLists/GroceryListCard.vue";
import GroceryListStatusFilter from "../../components/GroceryListStatusFilter.vue";
import router from "../../router";
import type {GroceryListItem} from "../../components/GroceryLists/GroceryListItemsCard.vue";
import {onUnmounted, ref, watch} from "vue";

export interface GroceryList {
    id: number;
    user_id: number;
    name: string;
    notes: string | null;
    image_url: string | null;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    grocery_list_items: GroceryListItem[]
}

const searchTerm = ref("");
type GroceryListStatusFilter = "all" | "completed" | "unfinished";
const statusFilter = ref<GroceryListStatusFilter>("all");

const selectMode = ref(false);
const selectedGroceryLists = ref<number[]>([]);

const handleGroceryListClick = (grocery_list_id: number) => {
    if ((grocery_list_id < 1)) {
        return;
    }
    router.push('/grocery-list/' + grocery_list_id);
}

const handleGroceryListUpdated = (updatedGroceryList: GroceryList) => {
    const index = groceryLists.value.findIndex(list => list.id === updatedGroceryList.id);
    if (index !== -1) {
        groceryLists.value[index] = updatedGroceryList;
    }
}

const handleSelectMode = () => {
    selectMode.value = !selectMode.value;
    if (!selectMode.value) {
        selectedGroceryLists.value = [];
    }
}

const handleGLIdEmit = (id: number) => {
    if (selectedGroceryLists.value.includes(id)) {
        selectedGroceryLists.value.splice(selectedGroceryLists.value.indexOf(id), 1)
    } else {
        selectedGroceryLists.value.push(id);
    }
}


const handleAddGroceryList = () => {
    router.push('/new/grocery-list');
}

const handleGoShopping = () => {
    if (selectedGroceryLists.value.length > 0) {
        router.push('/shopping');
    }
}

const {items: groceryLists, isLoading, hasMore, allRows, refresh} = usePagination<GroceryList>({
    endpoint: '/api/get-grocery-lists',
    errorMessage: 'Could not fetch shopping lists.',
    payload: () => {
        const payload: Record<string, unknown> = {
            searchTerm: searchTerm.value,
        };

        if (statusFilter.value === "completed") {
            payload.status = "completed";
        } else if (statusFilter.value === "unfinished") {
            payload.status = "unfinished";
        }

        return payload;
    },
});

let searchTimeout: number | null = null;
watch(searchTerm, () => {
    if (searchTimeout) {
        window.clearTimeout(searchTimeout);
    }
    searchTimeout = window.setTimeout(() => {
        refresh();
    }, 400);
});

watch(statusFilter, () => {
    refresh();
});

onUnmounted(() => {
    if (searchTimeout) {
        window.clearTimeout(searchTimeout);
    }
});

</script>

<template>
    <DashboardLayout>
        <div class="pt-7 px-5">
            <div class="bg-gray-50 rounded-2xl border-2 border-gray-200">
                <div class="px-4 pt-6 pb-4">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex-1">
                            <h2 class="text-3xl sm:text-3xl font-bold tracking-tight" :class="selectMode ? 'text-blue-700' : 'text-gray-900'">
                                {{ selectMode ? 'Select Lists' : 'Grocery Lists' }}
                            </h2>
                            <p class="text-xs" :class="selectMode ? 'text-blue-400' : 'text-gray-500'">
                                {{ selectMode ? `Select items (${selectedGroceryLists.length} selected)` : 'Long press to change status' }}
                            </p>
                        </div>
                        <button
                            @click="handleSelectMode"
                            :class="[
                                'me-1 p-2 border-2 rounded-lg shadow-md hover:shadow-xl hover:scale-110 active:scale-95 transition-all duration-200',
                                selectMode
                                    ? 'border-blue-600 bg-gradient-to-b from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700 hover:border-blue-700'
                                    : 'border-gray-200 bg-white/90 backdrop-blur-sm hover:border-gray-300 hover:bg-white'
                            ]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </button>
                        <button
                            v-if="!selectMode"
                            @click="handleAddGroceryList"
                            class="p-2 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200"
                        >
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                    <hr :class="selectMode ? 'border-blue-200 my-3' : 'border-gray-300 my-3'"/>
                    <p v-if="allRows > 0" class="text-sm" :class="selectMode ? 'text-blue-500' : 'text-gray-600'">
                        {{ selectMode ? `${selectedGroceryLists.length} of ${allRows} selected` : `${allRows} shopping list${allRows !== 1 ? 's' : ''}` }}
                    </p>

                    <div class="mt-4 flex flex-row">
                        <label for="grocery-lists-search" class="sr-only">Search shopping lists</label>
                        <div class="relative w-[75%] h-full">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
                                    />
                                </svg>
                            </div>
                            <input
                                id="grocery-lists-search"
                                v-model="searchTerm"
                                type="text"
                                inputmode="search"
                                enterkeyhint="search"
                                role="searchbox"
                                placeholder="Search shopping lists"
                                autocomplete="off"
                                class="relative z-0 w-full px-4 py-2.5 pl-10 pr-10 text-base text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            />
                            <button
                                v-if="searchTerm"
                                type="button"
                                class="absolute inset-y-0 right-0 z-10 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                @click="searchTerm = ''"
                                aria-label="Clear search"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="w-[25%] pl-3">
                            <GroceryListStatusFilter v-model="statusFilter"/>
                        </div>
                    </div>

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
                    <div
                        class="grid grid-cols-1 gap-4"
                        :class="{ 'mb-10': hasMore}"
                    >
                        <GroceryListCard
                            v-for="groceryList in groceryLists"
                            :key="groceryList.id"
                            :grocery-list="groceryList"
                            :select-mode="selectMode"
                            :is-selected="selectedGroceryLists.includes(groceryList.id)"
                            @click="handleGroceryListClick"
                            @updated="handleGroceryListUpdated"
                            @emit-id="handleGLIdEmit"
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

        <div v-if="selectMode && selectedGroceryLists.length > 0" class="fixed bottom-16 left-0 right-0 z-50 px-5 pb-4">
            <div class="max-w-xs mx-auto">
                <div class="drop-shadow-[0_0_40px_rgba(34,197,94,0.4)]">
                    <button
                        @click="handleGoShopping"
                        class="w-full flex items-center justify-center gap-3 rounded-xl border-2 border-green-100 bg-gradient-to-b from-white to-green-50 px-5 py-4 shadow-xl ring-1 ring-green-200/60 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-2xl hover:border-green-200 hover:ring-green-300/80 active:translate-y-0 active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2"
                    >
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white shadow-md ring-1 ring-green-200 text-green-700 transition-all duration-200">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17H18a2 2 0 0 0 2-1.6L21 8H6"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col items-start flex-1">
                        <div class="text-base font-bold text-gray-900">Go Shopping</div>
                        <div class="text-xs text-gray-500">With {{ selectedGroceryLists.length }} selected list{{ selectedGroceryLists.length !== 1 ? 's' : '' }}</div>
                    </div>
                    <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
