<script setup lang="ts">
import DashboardLayout from "../layouts/DashboardLayout.vue";
import {computed, onMounted, ref} from "vue";
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

const groceryLists = ref<GroceryListInfo[]>([]);
const shoppingItems = ref<ShoppingItem[]>([]);
const isLoading = ref(false);
const selectedListId = ref<number | null>(null);
const isTogglingItem = ref(false);
const isEditModalOpen = ref(false);
const editingItem = ref<ShoppingItem | null>(null);

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
        const sorted = [...shoppingItems.value].sort((a, b) => a.sort_order - b.sort_order);
        if (selectedListId.value === null) {
            return sorted;
        }
        return sorted.filter(item => {
            return item.grocery_list_item?.grocery_list_id === selectedListId.value;
        });
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
            toastStore.show("info", "No shopping lists selected. Please select lists from the grocery lists page.");
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

onMounted(() => {
    fetchShoppingSession();
});
</script>

<template>
    <DashboardLayout>
        <div class="pt-7 px-5 pb-20">
            <div class="bg-gray-50 rounded-2xl border-2 border-gray-200">
                <div class="px-4 pt-6 pb-5">
                    <h2 class="text-3xl sm:text-3xl font-bold tracking-tight text-gray-900 mb-4">
                        Shopping
                    </h2>

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
                            :disabled="selectedListId !== null"
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

                        <div v-if="filteredItems.length === 0 && selectedListId !== null"
                             class="text-center py-8 text-gray-500">
                            No items in this list
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
</style>
