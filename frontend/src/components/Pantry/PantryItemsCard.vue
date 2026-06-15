<script setup lang="ts">
import {computed, onMounted, onUnmounted, ref} from "vue";
import Modal from "../Modal.vue";
import PantryItemStatusFilter, {type PantryStatusFilterValue} from "./PantryItemStatusFilter.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import {useConfirmStore} from "../../stores/confirm.ts";

export interface PantryItem {
    id: number;
    user_id: number;
    space_id: number | null;
    name: string;
    quantity: number | null;
    unit: string | null;
    expiry_date: string | null;
    purchase_date: string | null;
    notes: string | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
}

const props = defineProps<{
    pantryItems: PantryItem[];
    spaceId: number;
}>();

const emit = defineEmits<{
    'refresh': [];
}>();

const toastStore = useToastStore();
const loadingStore = useLoadingStore();
const confirmStore = useConfirmStore();

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const openDropdownId = ref<number | null>(null);
const editingItem = ref<PantryItem | null>(null);

const emptyItem = (): Omit<PantryItem, 'id' | 'user_id' | 'created_at' | 'updated_at' | 'deleted_at'> => ({
    space_id: props.spaceId,
    name: '',
    quantity: null,
    unit: null,
    expiry_date: null,
    purchase_date: null,
    notes: null,
});

const newItem = ref(emptyItem());

const statusFilter = ref<PantryStatusFilterValue>('all');
const searchTerm = ref('');

const sortedItems = computed(() => {
    const search = searchTerm.value.trim().toLowerCase();

    return [...props.pantryItems]
        .filter((item) => statusFilter.value === 'all' || expiryStatus(item) === statusFilter.value)
        .filter((item) => !search || item.name.toLowerCase().includes(search))
        .sort((a, b) => {
            if (a.expiry_date && b.expiry_date) {
                return a.expiry_date.localeCompare(b.expiry_date);
            }
            if (a.expiry_date) return -1;
            if (b.expiry_date) return 1;
            return a.name.localeCompare(b.name);
        });
});

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

const openAddModal = () => {
    newItem.value = emptyItem();
    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    isAddModalOpen.value = false;
};

const openEditModal = (item: PantryItem) => {
    editingItem.value = {...item};
    closeDropdown();
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editingItem.value = null;
};

const formatItem = (item: PantryItem): string => {
    const parts: string[] = [];

    if (item.quantity !== null) {
        parts.push(String(item.quantity));
    }

    if (item.unit) {
        parts.push(item.unit);
    }

    parts.push(item.name);

    return parts.join(" ");
};

const formatDate = (dateString: string | null): string => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'});
};

const expiryStatus = (item: PantryItem): 'expired' | 'soon' | 'ok' => {
    if (!item.expiry_date) return 'expired';

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const expiry = new Date(item.expiry_date);
    expiry.setHours(0, 0, 0, 0);

    const diffDays = Math.round((expiry.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));

    if (diffDays < 0) return 'expired';
    if (diffDays < 2) return 'soon';
    return 'ok';
};

const addItem = (addAnother: boolean = false) => {
    if (!newItem.value.name.trim()) {
        toastStore.show('error', 'Item name is required.');
        return;
    }

    loadingStore.start();

    const payload = {
        ...newItem.value,
        name: newItem.value.name.trim(),
        unit: newItem.value.unit?.trim() || null,
        notes: newItem.value.notes?.trim() || null,
    };

    axios.post('/api/pantry-items', payload)
        .then(() => {
            emit('refresh');
            toastStore.show('success', 'Item added successfully.');
            if (addAnother) {
                newItem.value = emptyItem();
            } else {
                closeAddModal();
            }
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not add item.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const updateItem = () => {
    if (!editingItem.value || !editingItem.value.name.trim()) {
        toastStore.show('error', 'Item name is required.');
        return;
    }

    const itemId = editingItem.value.id;
    loadingStore.start();

    const payload = {
        name: editingItem.value.name.trim(),
        quantity: editingItem.value.quantity ?? null,
        unit: editingItem.value.unit?.trim() || null,
        expiry_date: editingItem.value.expiry_date || null,
        purchase_date: editingItem.value.purchase_date || null,
        notes: editingItem.value.notes?.trim() || null,
    };

    axios.patch(`/api/pantry-items/${itemId}`, payload)
        .then(() => {
            emit('refresh');
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

const deleteItem = async (item: PantryItem) => {
    const confirmed = await confirmStore.show(`Are you sure you want to delete "${item.name}"?`);

    if (!confirmed) {
        return;
    }

    loadingStore.start();

    axios.delete(`/api/pantry-items/${item.id}`)
        .then(() => {
            emit('refresh');
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
</script>

<template>
    <div class="bg-white app-surface-gradient rounded-2xl shadow-xl p-8 relative border-2 border-slate-300">
        <div class="absolute top-2 right-2 flex gap-2">
            <button @click="openAddModal"
                    class="p-2 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
        </div>
        <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-200">
            <div class="flex-1">
                <h2 class="text-2xl sm:text-2xl font-bold tracking-tight text-gray-900">
                    Pantry Items
                </h2>
                <p class="text-xs text-gray-500">
                    Sorted by expiry date
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    <span class="font-medium text-gray-700 tabular-nums">{{ sortedItems.length }}</span>
                    item{{ sortedItems.length !== 1 ? 's' : '' }}
                </p>
            </div>
        </div>

        <div class="flex flex-col gap-3 mb-5 sm:flex-row sm:items-stretch">
            <label for="pantry-items-search" class="sr-only">Search pantry items</label>
            <div class="relative w-full sm:w-[75%] h-full">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
                        />
                    </svg>
                </div>
                <input
                    id="pantry-items-search"
                    v-model="searchTerm"
                    type="text"
                    inputmode="search"
                    enterkeyhint="search"
                    role="searchbox"
                    placeholder="Search pantry items"
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
            <div class="w-full sm:w-[25%] sm:pl-3">
                <PantryItemStatusFilter v-model="statusFilter"/>
            </div>
        </div>

        <template v-if="sortedItems.length > 0">
            <ul class="space-y-2.5">
                <li
                    v-for="item in sortedItems"
                    :key="item.id"
                    @click="openEditModal(item)"
                    :class="[
                        'flex items-center gap-3 px-4 py-3 rounded-lg border border-l-4 transition-all duration-200 cursor-pointer relative overflow-visible',
                        openDropdownId === item.id ? 'z-30' : 'z-0',
                        expiryStatus(item) === 'expired'
                            ? 'bg-red-50 border-red-200 border-l-red-500 ring-1 ring-red-100'
                            : expiryStatus(item) === 'soon'
                                ? 'bg-amber-50 border-amber-200 border-l-amber-500 ring-1 ring-amber-100'
                                : 'bg-green-50 border-gray-200 border-l-green-500 ring-1 ring-green-300 hover:bg-gray-50 hover:border-blue-200'
                    ]"
                >
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="leading-relaxed text-[15px] font-medium text-gray-800">
                                {{ formatItem(item) }}
                            </span>
                            <span
                                v-if="expiryStatus(item) === 'expired'"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-200 text-red-700"
                            >
                                Expired
                            </span>
                            <span
                                v-else-if="expiryStatus(item) === 'soon'"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-200 text-orange-700"
                            >
                                Expires soon
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-200 text-green-700"
                            >
                                Fresh
                            </span>
                        </div>
                        <p v-if="item.expiry_date || item.notes" class="text-xs text-gray-500 mt-0.5 line-clamp-1">
                            <template v-if="item.expiry_date">Expires {{ formatDate(item.expiry_date) }}</template>
                            <template v-if="item.expiry_date && item.notes"> &middot; </template>
                            <template v-if="item.notes">{{ item.notes }}</template>
                        </p>
                    </div>
                    <div class="relative flex-shrink-0 dropdown-container opacity-100" @click.stop>
                        <button
                            @click="toggleDropdown(item.id)"
                            class="p-1.5 text-gray-500 hover:text-gray-900 hover:bg-black/10 rounded transition-colors opacity-100"
                            title="More options"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div
                            v-if="openDropdownId === item.id"
                            class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-40 opacity-100"
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
            </ul>
        </template>
        <div v-else class="text-center py-2">
            <p class="text-gray-500 mb-4">No items yet. Click the + button to add your first item.</p>
        </div>
    </div>

    <Modal :isOpen="isEditModalOpen" @close="closeEditModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Edit Item</h2>
        </template>
        <template #body>
            <div class="space-y-4" v-if="editingItem">
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-1">Name *</label>
                    <input
                        v-model="editingItem.name"
                        type="text"
                        class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                        placeholder="Milk"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
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
                            placeholder="liters"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1">Expiry Date</label>
                        <input
                            v-model="editingItem.expiry_date"
                            type="date"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1">Purchase Date</label>
                        <input
                            v-model="editingItem.purchase_date"
                            type="date"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                        />
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-1">Notes (optional)</label>
                    <input
                        v-model="editingItem.notes"
                        type="text"
                        class="w-full px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                        placeholder="e.g. organic, opened"
                    />
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
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1">Expiry Date</label>
                        <input
                            v-model="newItem.expiry_date"
                            type="date"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 font-medium mb-1">Purchase Date</label>
                        <input
                            v-model="newItem.purchase_date"
                            type="date"
                            class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                        />
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-1">Notes (optional)</label>
                    <input
                        v-model="newItem.notes"
                        type="text"
                        class="w-full px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                        placeholder="e.g. organic, opened"
                    />
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
                <div class="flex flex-row gap-2">
                    <button
                        @click.stop="addItem(false)"
                        class="w-full sm:w-auto px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                    >
                        Add Item
                    </button>
                    <button
                        @click.stop="addItem(true)"
                        class="w-full sm:w-auto px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                    >
                        Add Another Item
                    </button>
                </div>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
