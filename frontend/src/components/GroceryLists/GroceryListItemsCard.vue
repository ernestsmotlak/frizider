<script setup lang="ts">
import {ref, watchEffect, onMounted, onUnmounted} from "vue";
import Modal from "../Modal.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import {useConfirmStore} from "../../stores/confirm.ts";
import {VueDraggable} from 'vue-draggable-plus';

export interface GroceryListItem {
    id: number;
    grocery_list_id: number;
    pantry_item_id: number | null;
    name: string;
    quantity: number | null;
    unit: string | null;
    notes: string | null;
    sort_order: number;
    is_purchased: boolean;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
}

interface GroceryList {
    id: number;
    user_id: number;
    name: string;
    notes: string | null;
    image_url: string | null;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    grocery_list_items?: GroceryListItem[];
}

const props = defineProps<{
    groceryListItems: GroceryListItem[];
    groceryListId: number;
}>();

const emit = defineEmits<{
    'updatedGroceryList': [groceryList: GroceryList]
}>();

const toastStore = useToastStore();
const loadingStore = useLoadingStore();
const confirmStore = useConfirmStore();

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const openDropdownId = ref<number | null>(null);
const editingItem = ref<GroceryListItem | null>(null);

const newItem = ref<Omit<GroceryListItem, 'id' | 'created_at' | 'updated_at' | 'deleted_at'>>({
    grocery_list_id: props.groceryListId,
    pantry_item_id: null,
    name: '',
    quantity: null,
    unit: null,
    notes: null,
    sort_order: 0,
    is_purchased: false,
});

const sortedItems = (items: GroceryListItem[]): GroceryListItem[] => {
    return [...items].sort((a, b) => {
        if (a.sort_order !== b.sort_order) {
            return a.sort_order - b.sort_order;
        }
        const aId = a.id ?? 0;
        const bId = b.id ?? 0;
        return aId - bId;
    });
}

const draggableItems = ref<GroceryListItem[]>([]);

watchEffect(() => {
    draggableItems.value = sortedItems(props.groceryListItems).map(item => ({...item}));
});

const onDragEnd = async () => {
    draggableItems.value.forEach((item, index) => {
        item.sort_order = index;
    });

    loadingStore.start();

    try {
        const updatePromises = draggableItems.value.map((item) => {
            return axios.patch(`/api/grocery-list-items/${item.id}`, {
                sort_order: item.sort_order
            });
        });

        await Promise.all(updatePromises);
        const response = await axios.get(`/api/grocery-lists/${props.groceryListId}`);
        const updatedGroceryList = response.data.data;
        emit('updatedGroceryList', updatedGroceryList);
    } catch (error: any) {
        console.error(error);
        const errorMessage = error?.response?.data?.message || 'Could not update item order.';
        toastStore.show('error', errorMessage);
        draggableItems.value = sortedItems(props.groceryListItems).map(item => ({...item}));
    } finally {
        loadingStore.stop();
    }
};

const toggleItem = (item: GroceryListItem) => {
    if (!item.id) {
        return;
    }

    const itemId = item.id;
    const newPurchasedState = !item.is_purchased;

    axios.patch(`/api/grocery-list-items/${itemId}`, {
        is_purchased: newPurchasedState
    })
        .then(() => {
            return axios.get(`/api/grocery-lists/${props.groceryListId}`);
        })
        .then((response) => {
            const updatedGroceryList = response.data.data;
            emit('updatedGroceryList', updatedGroceryList);
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not update item status.';
            toastStore.show('error', errorMessage);
        });
};

const toggleDropdown = (itemId: number | null) => {
    if (openDropdownId.value === itemId) {
        openDropdownId.value = null;
    } else {
        openDropdownId.value = itemId;
    }
};

const closeDropdown = () => {
    openDropdownId.value = null;
};

const openAddModal = () => {
    const maxSortOrder = props.groceryListItems.length > 0
        ? Math.max(...props.groceryListItems.map(i => i.sort_order))
        : -1;
    newItem.value = {
        grocery_list_id: props.groceryListId,
        pantry_item_id: null,
        name: '',
        quantity: null,
        unit: null,
        notes: null,
        sort_order: maxSortOrder + 1,
        is_purchased: false,
    };
    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    isAddModalOpen.value = false;
};

const openEditModal = (item: GroceryListItem) => {
    editingItem.value = {
        ...item
    };
    closeDropdown();
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editingItem.value = null;
};

const formatItem = (item: GroceryListItem): string => {
    const parts: string[] = [];

    if (item.quantity !== null) {
        parts.push(item.quantity.toString());
    }

    if (item.unit) {
        parts.push(item.unit);
    }

    parts.push(item.name);

    if (item.notes) {
        parts.push(`(${item.notes})`);
    }

    return parts.join(" ");
}

const updateItem = () => {
    if (!editingItem.value || !editingItem.value.name.trim()) {
        toastStore.show('error', 'Item name is required.');
        return;
    }

    if (!editingItem.value.id) {
        toastStore.show('error', 'Cannot update item without ID.');
        return;
    }

    const itemId = editingItem.value.id;
    loadingStore.start();

    const payload = {
        name: editingItem.value.name,
        quantity: editingItem.value.quantity ?? null,
        unit: editingItem.value.unit ?? null,
        notes: editingItem.value.notes ?? null,
        is_purchased: editingItem.value.is_purchased ?? false,
    };

    axios.patch(`/api/grocery-list-items/${itemId}`, payload)
        .then(() => {
            return axios.get(`/api/grocery-lists/${props.groceryListId}`);
        })
        .then((response) => {
            const updatedGroceryList = response.data.data;
            emit('updatedGroceryList', updatedGroceryList);
            toastStore.show('success', 'Item updated successfully.');
            closeEditModal();
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not update item.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const deleteItem = async (item: GroceryListItem) => {
    if (!item) {
        toastStore.show('error', 'An error occurred while deleting the item.');
        return;
    }

    const confirmed = await confirmStore.show(`Are you sure you want to delete "${item.name || 'this item'}"?`);

    if (!confirmed) {
        return;
    }

    if (!item.id) {
        const index = draggableItems.value.findIndex(i => i === item);
        if (index > -1) {
            draggableItems.value.splice(index, 1);
        }
        closeDropdown();
        return;
    }

    const itemId = item.id;
    loadingStore.start();

    axios.delete(`/api/grocery-list-items/${itemId}`)
        .then(() => {
            return axios.get(`/api/grocery-lists/${props.groceryListId}`);
        })
        .then((response) => {
            const updatedGroceryList = response.data.data;
            emit('updatedGroceryList', updatedGroceryList);
            toastStore.show('success', 'Item successfully removed.');
            closeDropdown();
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not delete item.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.dropdown-container')) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const addItem = () => {
    if (!newItem.value.name.trim()) {
        toastStore.show('error', 'Item name is required.');
        return;
    }

    loadingStore.start();

    axios.post('/api/grocery-list-items', newItem.value)
        .then(() => {
            return axios.get(`/api/grocery-lists/${props.groceryListId}`);
        })
        .then((response) => {
            const updatedGroceryList = response.data.data;
            emit('updatedGroceryList', updatedGroceryList);
            toastStore.show('success', 'Item added successfully.');
            closeAddModal();
        })
        .catch((error) => {
            const errorMessage = error?.response?.data?.message || 'Could not add item.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};
</script>

<template>
    <div class="bg-white rounded-2xl shadow-xl p-8 relative border-2 border-gray-200">
        <div class="absolute top-2 right-2 flex gap-2">
            <button @click="openAddModal"
                    class="p-2 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Items</h2>
        <template v-if="draggableItems && draggableItems.length > 0">
            <VueDraggable
                v-model="draggableItems"
                @end="onDragEnd"
                handle=".drag-handle"
                tag="ul"
                class="space-y-2.5"
            >
                <li
                    v-for="(item, index) in draggableItems"
                    :key="item.id ?? `tmp-${item.grocery_list_id}-${item.sort_order}-${index}`"
                    @click="toggleItem(item)"
                    class="flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-blue-200 transition-all duration-250 cursor-pointer relative"
                >
                    <div class="drag-handle cursor-move p-1 hover:bg-gray-100 rounded flex-shrink-0" @click.stop>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 8h16M4 16h16"></path>
                        </svg>
                    </div>
                    <input
                        type="checkbox"
                        :checked="item.is_purchased"
                        @click.stop="toggleItem(item)"
                        class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 cursor-pointer flex-shrink-0"
                    />
                    <span :class="[
                        'text-gray-800 leading-relaxed text-[15px] font-medium flex-1',
                        item.is_purchased ? 'line-through opacity-60' : ''
                    ]">
                        {{ formatItem(item) }}
                    </span>
                    <div class="relative flex-shrink-0 dropdown-container opacity-100" @click.stop>
                        <button
                            @click="toggleDropdown(item.id)"
                            class="p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors opacity-100"
                            title="More options"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div
                            v-if="openDropdownId === item.id"
                            class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10 opacity-100"
                        >
                            <button
                                @click="openEditModal(item)"
                                class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </button>
                            <button
                                @click="deleteItem(item)"
                                class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </li>
            </VueDraggable>
        </template>
        <div v-else class="text-center py-8">
            <p class="text-gray-500 mb-4">No items yet. Click the + button to add your first item.</p>
        </div>
    </div>

    <Modal :isOpen="isEditModalOpen" @close="closeEditModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Edit Item</h2>
        </template>
        <template #body>
            <div class="space-y-4">
                <div v-if="editingItem">
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1">Name *</label>
                        <input
                            v-model="editingItem.name"
                            type="text"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                            placeholder="Milk"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="block text-xs text-gray-500 font-medium mb-1">Quantity</label>
                            <input
                                v-model.number="editingItem.quantity"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                placeholder="2"
                            />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 font-medium mb-1">Unit</label>
                            <input
                                v-model="editingItem.unit"
                                type="text"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                placeholder="gallons"
                            />
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="block text-xs text-gray-500 font-medium mb-1">Notes (optional)</label>
                        <input
                            v-model="editingItem.notes"
                            type="text"
                            class="w-full px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                            placeholder="e.g. organic, 2%"
                        />
                    </div>
                    <div class="flex items-center gap-2 mt-3">
                        <input
                            v-model="editingItem.is_purchased"
                            type="checkbox"
                            id="edit-purchased"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="edit-purchased" class="text-sm text-gray-700 font-medium">
                            Purchased
                        </label>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex flex-col sm:flex-row justify-between gap-3">
                <button
                    @click="closeEditModal"
                    class="w-full sm:w-auto px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    @click.stop="updateItem"
                    class="w-full sm:w-auto px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Save Changes
                </button>
            </div>
        </template>
    </Modal>

    <Modal :isOpen="isAddModalOpen" @close="closeAddModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Add Item</h2>
        </template>
        <template #body>
            <div class="space-y-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1">Name *</label>
                        <input
                            v-model="newItem.name"
                            type="text"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                            placeholder="Milk"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 font-medium mb-1">Quantity</label>
                            <input
                                v-model.number="newItem.quantity"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                placeholder="2"
                            />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 font-medium mb-1">Unit</label>
                            <input
                                v-model="newItem.unit"
                                type="text"
                                class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                placeholder="gallons"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1">Notes (optional)</label>
                        <input
                            v-model="newItem.notes"
                            type="text"
                            class="w-full px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                            placeholder="e.g. organic, 2%"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            v-model="newItem.is_purchased"
                            type="checkbox"
                            id="new-purchased"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="new-purchased" class="text-sm text-gray-700 font-medium">
                            Purchased
                        </label>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex flex-col sm:flex-row justify-between gap-3">
                <button
                    @click="closeAddModal"
                    class="w-full sm:w-auto px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    @click.stop="addItem"
                    class="w-full sm:w-auto px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Add Item
                </button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
/* Styles for dragged item */
:deep(.sortable-drag) {
    opacity: 0.8;
    box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.2), 0 4px 8px -2px rgba(0, 0, 0, 0.15);
    border: 2px solid rgb(59 130 246);
    background: rgb(239 246 255);
    z-index: 1000;
}

/* Styles for placeholder (where item will be dropped) */
:deep(.sortable-ghost) {
    opacity: 0.4;
    background: rgb(229 231 235);
    border: 2px dashed rgb(156 163 175);
    border-radius: 0.5rem;
}

/* Styles for item being dragged */
:deep(li.sortable-drag) {
    cursor: grabbing !important;
}

/* Styles for items NOT being dragged - when dragging is active */
:deep(ul.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost) {
    transition: all 0.2s ease;
    border-color: rgb(229 231 235);
    background: rgb(249 250 251);
}

/* When dragging, make non-dragged items slightly dimmed */
:deep(ul.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost) {
    opacity: 0.7;
    transform: scale(0.98);
}

/* Hover effect on non-dragged items during drag */
:deep(ul.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost):hover {
    opacity: 0.85;
    transform: scale(0.99);
    border-color: rgb(209 213 219);
    background: rgb(243 244 246);
}

/* Make the drag handle more prominent on non-dragged items during drag */
:deep(ul.sortable-drag) li:not(.sortable-drag):not(.sortable-ghost) .drag-handle {
    background: rgb(243 244 246);
    border: 1px solid rgb(229 231 235);
    border-radius: 0.375rem;
}

/* Smooth transitions for all list items */
:deep(ul li) {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
