<script setup lang="ts">
import {ref, nextTick, computed} from "vue";
import Modal from "../Modal.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import {useConfirmStore} from "../../stores/confirm.ts";

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

const isModalOpen = ref(false);
const isAddModalOpen = ref(false);

const formData = ref<GroceryListItem[]>([]);
const lastItemRef = ref<HTMLElement | null>(null);
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

const openModal = () => {
    formData.value = props.groceryListItems.map(item => ({
        ...item
    }));
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const addNewItemToForm = () => {
    const maxSortOrder = formData.value.length > 0
        ? Math.max(...formData.value.map(i => i.sort_order))
        : -1;
    const newItem: GroceryListItem = {
        id: 0,
        grocery_list_id: props.groceryListId,
        pantry_item_id: null,
        name: '',
        quantity: null,
        unit: null,
        notes: null,
        sort_order: maxSortOrder + 1,
        is_purchased: false,
        created_at: null,
        updated_at: null,
        deleted_at: null,
    };
    formData.value.push(newItem);
    nextTick(() => {
        if (lastItemRef.value) {
            lastItemRef.value.scrollIntoView({
                behavior: 'smooth',
                block: 'end'
            });
        }
    });
};

const deleteItem = async (item: GroceryListItem) => {
    if (!item) {
        toastStore.show('error', 'An error occurred while deleting the item.');
        return;
    }

    const itemName = item.name || 'this item';
    const confirmed = await confirmStore.show(`Are you sure you want to delete "${itemName}"?`);

    if (!confirmed) {
        return;
    }

    if (!item.id || item.id === 0) {
        const index = formData.value.findIndex(i => i === item);
        if (index > -1) {
            formData.value.splice(index, 1);
        }
        return;
    }

    loadingStore.start();

    axios.delete(`/api/grocery-list-items/${item.id}`)
        .then(() => {
            return axios.get(`/api/grocery-lists/${props.groceryListId}`);
        })
        .then((response) => {
            const updatedGroceryList = response.data.data;
            emit('updatedGroceryList', updatedGroceryList);
            if (isModalOpen.value) {
                formData.value = updatedGroceryList.grocery_list_items?.map(item => ({...item})) || [];
            }
            toastStore.show('success', 'Item successfully removed from grocery list.');
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

const sortedFormData = computed(() => sortedItems(formData.value));

const updateItems = async () => {
    const updatePromises = formData.value.map(async (item, index) => {
        const payload = {
            name: item.name,
            quantity: item.quantity ?? null,
            unit: item.unit ?? null,
            notes: item.notes ?? null,
            sort_order: item.sort_order ?? index,
            is_purchased: item.is_purchased ?? false,
        };

        if (!item.id || item.id === 0) {
            return axios.post('/api/grocery-list-items', {
                grocery_list_id: props.groceryListId,
                ...payload,
            });
        } else {
            return axios.patch(`/api/grocery-list-items/${item.id}`, payload);
        }
    });

    loadingStore.start();

    try {
        await Promise.all(updatePromises);
        const response = await axios.get(`/api/grocery-lists/${props.groceryListId}`);
        const updatedGroceryList = response.data.data;
        emit('updatedGroceryList', updatedGroceryList);
        toastStore.show('success', 'Items updated successfully.');
        closeModal();
    } catch (error: any) {
        console.error(error);
        const errorMessage = error?.response?.data?.message || 'Could not update items.';
        toastStore.show('error', errorMessage);
    } finally {
        loadingStore.stop();
    }
};

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
            <button v-if="groceryListItems && groceryListItems.length > 0" @click="openModal"
                    class="p-2 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Items</h2>
        <ul v-if="groceryListItems && groceryListItems.length > 0" class="space-y-2.5">
            <li v-for="(item, index) in sortedItems(groceryListItems)"
                :key="item.id ?? `item-${index}`"
                class="grocery-item flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-blue-200 transition-all duration-250"
                :class="{ 'opacity-60 line-through': item.is_purchased }">
                <div class="flex items-center gap-3 flex-1">
                    <svg v-if="item.is_purchased" class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                    <svg v-else class="w-2.5 h-2.5 text-gray-600 flex-shrink-0 mt-0.5" fill="currentColor"
                         viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3"/>
                    </svg>
                    <span class="text-gray-800 leading-relaxed text-[15px] font-medium">{{
                            formatItem(item)
                        }}</span>
                </div>
            </li>
        </ul>
        <div v-else class="text-center py-8">
            <p class="text-gray-500 mb-4">No items yet. Click the + button to add your first item.</p>
        </div>
    </div>

    <Modal :isOpen="isModalOpen" @close="closeModal">
        <template #header>
            <div class="flex items-center gap-4 flex-1">
                <h2 class="text-2xl font-bold text-gray-900">Edit Items</h2>
                <button
                    @click.stop="addNewItemToForm"
                    class="ml-auto p-2 rounded-lg active:scale-95 transition-transform duration-200"
                >
                    <svg class="w-6 h-6 text-gray-500 hover:text-blue-700 transition-all duration-200 hover:scale-150"
                         fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>
        </template>
        <template #body>
            <div class="space-y-4">
                <div v-for="(item, index) in sortedFormData"
                     :key="item.id ?? `new-${index}`"
                     :ref="(el) => { if (index === sortedFormData.length - 1) lastItemRef = el as HTMLElement }"
                     class="item-card p-3 sm:p-4 bg-white rounded-lg border border-gray-200 transition-all relative shadow-md">
                    <button
                        @click="deleteItem(item)"
                        class="absolute top-0.25 right-0 p-2 rounded-lg active:scale-95 transition-transform duration-200"
                    >
                        <svg
                            class="w-5 h-5 text-red-700 hover:text-red-700 transition-all duration-200 hover:scale-150"
                            fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path>
                        </svg>
                    </button>
                    <div class="flex items-start gap-3 mb-3 mt-1">
                        <div class="flex-1 space-y-3">
                            <div class="sm:hidden">
                                <label class="block text-xs text-gray-500 font-medium mb-1">Name</label>
                                <input
                                    v-model="item.name"
                                    type="text"
                                    class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                    placeholder="Milk"
                                />
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-12 gap-3">
                                <div class="hidden sm:block sm:col-span-5">
                                    <label class="block text-xs text-gray-500 font-medium mb-1">Name</label>
                                    <input
                                        v-model="item.name"
                                        type="text"
                                        class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                        placeholder="Milk"
                                    />
                                </div>
                                <div class="col-span-1 sm:col-span-3">
                                    <label class="block text-xs text-gray-500 font-medium mb-1">Quantity</label>
                                    <input
                                        v-model.number="item.quantity"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                        placeholder="2"
                                    />
                                </div>
                                <div class="col-span-1 sm:col-span-4">
                                    <label class="block text-xs text-gray-500 font-medium mb-1">Unit</label>
                                    <input
                                        v-model="item.unit"
                                        type="text"
                                        class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                        placeholder="gallons"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 font-medium mb-1">Notes (optional)</label>
                                <input
                                    v-model="item.notes"
                                    type="text"
                                    class="w-full px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                                    placeholder="e.g. organic, 2%"
                                />
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    v-model="item.is_purchased"
                                    type="checkbox"
                                    :id="`purchased-${item.id ?? index}`"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <label :for="`purchased-${item.id ?? index}`" class="text-sm text-gray-700 font-medium">
                                    Purchased
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex flex-col sm:flex-row justify-between gap-3">
                <button
                    @click="closeModal"
                    class="w-full sm:w-auto px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    @click.stop="updateItems"
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
.item-card {
    transition: all 0.1s ease-in-out;
}

.item-card:hover {
    border-color: rgb(148 163 184);
    border-width: 2px;
    box-shadow: 0 2px 4px -1px rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05);
}

.item-card:has(input:focus),
.item-card:has(input:focus-visible) {
    border-color: rgb(148 163 184);
    border-width: 2px;
    box-shadow: 0 4px 6px -2px rgb(0 0 0 / 0.08), 0 2px 4px -2px rgb(0 0 0 / 0.06);
    background-color: rgb(250 250 250);
}
</style>
