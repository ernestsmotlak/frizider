<script setup lang="ts">
import DashboardLayout from "../layouts/DashboardLayout.vue";
import {computed, onMounted, ref} from "vue";
import {useRouter} from "vue-router";
import {useToastStore} from "../stores/toast.ts";
import {useLoadingStore} from "../stores/loading.ts";
import ShoppingProgressBar from "../components/Shopping/ShoppingProgressBar.vue";
import ShoppingListFilter from "../components/Shopping/ShoppingListFilter.vue";
import ShoppingItemCard from "../components/Shopping/ShoppingItemCard.vue";
import ShoppingItemEditModal from "../components/Shopping/ShoppingItemEditModal.vue";
import type {ShoppingItem} from "../components/Shopping/ShoppingItemCard.vue";
import type {GroceryListInfo} from "../components/Shopping/ShoppingListFilter.vue";
import {VueDraggable} from "vue-draggable-plus";

const toastStore = useToastStore();
const loadingStore = useLoadingStore();
const router = useRouter();

const groceryLists = ref<GroceryListInfo[]>([]);
const shoppingItems = ref<ShoppingItem[]>([]);
const isLoading = ref(false);
const selectedListId = ref<number | null>(null);
const searchTerm = ref("");
const isTogglingItem = ref(false);
const isEditModalOpen = ref(false);
const editingItem = ref<ShoppingItem | null>(null);
const isFridgeModalOpen = ref(false);

const listColors = [
    "#fb7185",
    "#3b82f6",
    "#f59e0b",
    "#ef4444",
    "#8b5cf6",
    "#ec4899",
    "#14b8a6",
    "#f97316",
];

const getListColor = (listId: number): string => {
    const index = groceryLists.value.findIndex(l => l.id === listId);
    return listColors[index % listColors.length];
};

const getListName = (item: ShoppingItem): string => {
    const listId = item.grocery_list_item?.grocery_list_id;
    if (!listId) return "Unknown";
    const list = groceryLists.value.find(l => l.id === listId);
    return list?.name ?? "Unknown";
};

const filteredItems = computed({
    get: () => {
        let sorted = [...shoppingItems.value].sort((a, b) => a.sort_order - b.sort_order);

        // Filter by selected list
        if (selectedListId.value !== null) {
            sorted = sorted.filter(item => {
                return item.grocery_list_item?.grocery_list_id === selectedListId.value;
            });
        }

        // Filter by search term
        if (searchTerm.value.trim()) {
            const searchLower = searchTerm.value.toLowerCase().trim();
            sorted = sorted.filter(item => {
                const nameMatch = item.name.toLowerCase().includes(searchLower);
                const quantityMatch = item.quantity?.toString().includes(searchLower);
                const unitMatch = item.unit?.toLowerCase().includes(searchLower);
                const notesMatch = item.notes?.toLowerCase().includes(searchLower);
                const listNameMatch = getListName(item).toLowerCase().includes(searchLower);
                return nameMatch || quantityMatch || unitMatch || notesMatch || listNameMatch;
            });
        }

        return sorted;
    },
    set: (newItems: ShoppingItem[]) => {
        newItems.forEach((item, index) => {
            const idx = shoppingItems.value.findIndex(i => i.id === item.id);
            if (idx !== -1) {
                shoppingItems.value[idx].sort_order = index;
            }
        });
        shoppingItems.value.sort((a, b) => a.sort_order - b.sort_order);
    }
});

const purchasedCount = computed(() => {
    return shoppingItems.value.filter(item => item.is_purchased).length;
});

const totalCount = computed(() => {
    return shoppingItems.value.length;
});

const fetchShoppingSession = async () => {
    isLoading.value = true;
    loadingStore.start();
    try {
        const response = await axios.get("/api/get-shopping-session");

        if (response.data.message === "No shopping session found!") {
            toastStore.show("info", "No shopping lists selected. Please select lists to start shopping.");
            groceryLists.value = [];
            shoppingItems.value = [];
            return;
        }

        const data = response.data.data;
        const rawLists = data.grocery_lists || [];

        groceryLists.value = rawLists.map((list: {id: number; name: string}, index: number) => ({
            id: list.id,
            name: list.name,
            color: listColors[index % listColors.length],
        }));

        shoppingItems.value = data.items || [];
    } catch (error: any) {
        console.error(error);
        const message = error.response?.data?.message || "Could not fetch shopping session.";
        toastStore.show("error", message);
    } finally {
        isLoading.value = false;
        loadingStore.stop();
    }
};

const handleToggleItem = async (item: ShoppingItem) => {
    if (isTogglingItem.value) return;

    isTogglingItem.value = true;
    const newPurchasedState = !item.is_purchased;

    const itemIndex = shoppingItems.value.findIndex(i => i.id === item.id);
    if (itemIndex !== -1) {
        shoppingItems.value[itemIndex].is_purchased = newPurchasedState;
    }

    try {
        await axios.patch(`/api/shopping-items/${item.id}`, {
            is_purchased: newPurchasedState,
        });
    } catch (error: any) {
        console.error(error);
        const errorMessage = error?.response?.data?.message || "Could not update item status.";
        toastStore.show("error", errorMessage);

        if (itemIndex !== -1) {
            shoppingItems.value[itemIndex].is_purchased = !newPurchasedState;
        }
    } finally {
        isTogglingItem.value = false;
    }
};

const handleEditItem = (item: ShoppingItem) => {
    editingItem.value = item;
    isEditModalOpen.value = true;
};

const handleCloseEditModal = () => {
    isEditModalOpen.value = false;
    editingItem.value = null;
};

const handleSaveItem = async (item: ShoppingItem) => {
    if (!item.name.trim()) {
        toastStore.show("error", "Item name is required.");
        return;
    }

    loadingStore.start();

    try {
        const response = await axios.patch(`/api/shopping-items/${item.id}`, {
            name: item.name,
            quantity: item.quantity,
            unit: item.unit,
            notes: item.notes,
            is_purchased: item.is_purchased,
        });

        const updatedItem = response.data.data;
        const itemIndex = shoppingItems.value.findIndex(i => i.id === item.id);
        if (itemIndex !== -1) {
            shoppingItems.value[itemIndex] = updatedItem;
        }

        toastStore.show("success", "Item updated successfully.");
        handleCloseEditModal();
    } catch (error: any) {
        console.error(error);
        const errorMessage = error?.response?.data?.message || "Could not update item.";
        toastStore.show("error", errorMessage);
    } finally {
        loadingStore.stop();
    }
};

const handleFilterSelect = (listId: number | null) => {
    selectedListId.value = listId;
};

const onDragEnd = async () => {
    const itemsToUpdate = shoppingItems.value.map((item) => ({
        id: item.id,
        sort_order: item.sort_order,
    }));

    loadingStore.start();

    try {
        await axios.post("/api/shopping-items/update-order", {
            items: itemsToUpdate,
        });
    } catch (error: any) {
        console.error(error);
        const errorMessage = error?.response?.data?.message || "Could not update item order.";
        toastStore.show("error", errorMessage);
        await fetchShoppingSession();
    } finally {
        loadingStore.stop();
    }
};

const toggleFridgeModal = () => {
    isFridgeModalOpen.value = !isFridgeModalOpen.value;
};

const closeFridgeModal = () => {
    isFridgeModalOpen.value = false;
};

const handleFinishShopping = async () => {
    loadingStore.start();
    try {
        await axios.post("/api/finish-shopping-session");
        toastStore.show("success", "Shopping completed. Original lists updated.");
        closeFridgeModal();
        router.push("/grocery-lists");
    } catch (error: any) {
        console.error(error);
        const errorMessage = error?.response?.data?.message || "Could not finish shopping session.";
        toastStore.show("error", errorMessage);
    } finally {
        loadingStore.stop();
    }
};

const handleDeleteShoppingSession = async () => {
    loadingStore.start();
    try {
        await axios.delete("/api/delete-shopping-session");
        toastStore.show("success", "Shopping session deleted.");
        closeFridgeModal();
        groceryLists.value = [];
        shoppingItems.value = [];
    } catch (error: any) {
        console.error(error);
        const errorMessage = error?.response?.data?.message || "Could not delete shopping session.";
        toastStore.show("error", errorMessage);
    } finally {
        loadingStore.stop();
    }
};

const handleFinishShoppingAndRoute = async () => {
    loadingStore.start();
    try {
        await axios.post("/api/finish-shopping-session");
        toastStore.show("success", "Shopping completed.");
        closeFridgeModal();
        router.push("/grocery-lists");
    } catch (error: any) {
        console.error(error);
        const errorMessage = error?.response?.data?.message || "Could not finish shopping session.";
        toastStore.show("error", errorMessage);
    } finally {
        loadingStore.stop();
    }
};

onMounted(() => {
    fetchShoppingSession();
});
</script>

<template>
    <DashboardLayout>
        <div class="pt-7 px-5 pb-20">
            <div class="bg-gray-50 rounded-2xl border-2 border-gray-200">
                <div class="px-4 pt-6 pb-5">
                    <div class="flex-1 mb-4">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </div>
                                <h2 class="text-3xl sm:text-3xl font-bold tracking-tight text-gray-900">
                                    Shopping
                                </h2>
                            </div>
                            <button
                                @click="toggleFridgeModal"
                                class="p-2 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200"
                            >
                                <img src="/fridge_icon.png" alt="Fridge" class="w-5 h-5" />
                            </button>
                        </div>
                        <p class="text-xs text-gray-500">
                            Tap items to mark as purchased
                        </p>
                    </div>
                    <hr class="border-gray-300 my-3">

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

                    <template v-else-if="shoppingItems.length === 0">
                        <div class="flex flex-col items-center justify-center py-16 px-4">
                            <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-gray-500 text-center text-lg font-medium mb-1">No shopping lists selected</p>
                            <p class="text-gray-400 text-center text-sm">Go to grocery lists to select lists for
                                shopping</p>
                        </div>
                    </template>

                    <template v-else>
                        <div class="mb-4">
                            <label for="shopping-items-search" class="sr-only">Search items</label>
                            <div class="relative">
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
                                    id="shopping-items-search"
                                    v-model="searchTerm"
                                    type="text"
                                    inputmode="search"
                                    enterkeyhint="search"
                                    role="searchbox"
                                    placeholder="Search items..."
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
                        </div>

                        <ShoppingProgressBar
                            :purchased="purchasedCount"
                            :total="totalCount"
                        />

                        <ShoppingListFilter
                            :lists="groceryLists"
                            :selected-list-id="selectedListId"
                            @select="handleFilterSelect"
                        />

                        <VueDraggable
                            v-model="filteredItems"
                            @end="onDragEnd"
                            tag="div"
                            class="space-y-3"
                            :disabled="selectedListId !== null || searchTerm.trim() !== ''"
                        >
                            <ShoppingItemCard
                                v-for="item in filteredItems"
                                :key="item.id"
                                :item="item"
                                :list-name="getListName(item)"
                                :list-color="getListColor(item.grocery_list_item?.grocery_list_id ?? 0)"
                                :is-toggling="isTogglingItem"
                                @toggle="handleToggleItem"
                                @edit="handleEditItem"
                            />
                        </VueDraggable>

                        <div v-if="filteredItems.length === 0 && (selectedListId !== null || searchTerm.trim() !== '')"
                             class="text-center py-8 text-gray-500">
                            <p v-if="searchTerm.trim()">No items found matching "{{ searchTerm }}"</p>
                            <p v-else>No items in this list</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <ShoppingItemEditModal
            :is-open="isEditModalOpen"
            :item="editingItem"
            @close="handleCloseEditModal"
            @save="handleSaveItem"
        />

        <Transition name="action-picker-backdrop">
            <div
                v-if="isFridgeModalOpen"
                class="fixed inset-0 z-40 bg-black/20 backdrop-blur-[1px]"
                @click="closeFridgeModal"
            ></div>
        </Transition>

        <Transition name="action-picker-panel">
            <div
                v-if="isFridgeModalOpen"
                class="fixed left-0 right-0 bottom-16 z-50 px-4 pb-3"
            >
                <div class="mx-auto max-w-md">
                    <div class="rounded-2xl border border-gray-200 bg-white/95 shadow-2xl ring-1 ring-black/5 p-3">
                        <div class="flex flex-col gap-3">
                            <button
                                type="button"
                                @click="handleFinishShoppingAndRoute"
                                class="w-[50%] mx-auto group flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-b from-white to-blue-50 px-3 py-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:border-blue-300 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                                aria-label="Finish shopping and go to shopping lists"
                            >
                                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white shadow-sm ring-1 ring-blue-200 text-blue-700 transition-all duration-200 group-hover:scale-110 group-hover:ring-blue-300 group-hover:shadow-md">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">Finish shopping</div>
                                <div class="text-xs text-gray-500">Go to shopping lists</div>
                            </button>

                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    @click="handleFinishShopping"
                                    class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-b from-white to-green-50 px-3 py-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:border-green-300 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2"
                                    aria-label="Finish shopping and update original lists"
                                >
                                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white shadow-sm ring-1 ring-green-200 text-green-700 transition-all duration-200 group-hover:scale-110 group-hover:ring-green-300 group-hover:shadow-md">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900">Finish shopping</div>
                                    <div class="text-xs text-gray-500">Update original lists</div>
                                </button>

                                <button
                                    type="button"
                                    @click="handleDeleteShoppingSession"
                                    class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-b from-white to-red-50 px-3 py-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:border-red-300 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
                                    aria-label="Close shopping and delete shopping data"
                                >
                                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white shadow-sm ring-1 ring-red-200 text-red-700 transition-all duration-200 group-hover:scale-110 group-hover:ring-red-300 group-hover:shadow-md">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">Finish shopping</div>
                                <div class="text-xs text-gray-500">Delete shopping data</div>
                            </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </DashboardLayout>
</template>

<style scoped>
:deep(.sortable-drag) {
    opacity: 0.9;
    box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.15);
    transform: scale(1.02);
}

:deep(.sortable-ghost) {
    opacity: 0.4;
}

.action-picker-backdrop-enter-active,
.action-picker-backdrop-leave-active {
    transition: opacity 0.2s ease;
}

.action-picker-backdrop-enter-from,
.action-picker-backdrop-leave-to {
    opacity: 0;
}

.action-picker-panel-enter-active {
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.28s ease;
}

.action-picker-panel-leave-active {
    transition: transform 0.18s ease, opacity 0.18s ease;
}

.action-picker-panel-enter-from,
.action-picker-panel-leave-to {
    opacity: 0;
    transform: translateY(14px);
}
</style>
