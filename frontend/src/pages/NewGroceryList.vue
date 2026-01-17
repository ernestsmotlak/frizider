<script setup lang="ts">
import DashboardLayout from "../layouts/DashboardLayout.vue";
import {ref} from "vue";
import {useRouter} from "vue-router";
import {useToastStore} from "../stores/toast.ts";
import {useLoadingStore} from "../stores/loading.ts";
import "emoji-picker-element";

const router = useRouter();
const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const formData = ref({
    name: "",
    notes: "",
    emoji: ""
});

interface Item {
    name: string;
    quantity: string;
    unit: string;
    notes: string;
}

const items = ref<Item[]>([]);

const showEmojiPicker = ref(false);

const handleEmojiSelect = (event: CustomEvent) => {
    formData.value.emoji = event.detail?.unicode || "";
    showEmojiPicker.value = false;
};

const createGroceryList = () => {
    if (!formData.value.name.trim()) {
        toastStore.show('error', 'Shopping list name is required.');
        return;
    }

    const emptyItems = items.value.filter(item => !item.name.trim());
    if (emptyItems.length > 0) {
        toastStore.show('error', 'Please fill in the name for all items or remove empty ones.');
        return;
    }

    const validItems = items.value
        .filter(item => item.name.trim())
        .map((item, index) => ({
            name: item.name.trim(),
            quantity: item.quantity && String(item.quantity).trim() ? parseFloat(String(item.quantity)) : null,
            unit: item.unit?.trim() || null,
            notes: item.notes?.trim() || null,
            sort_order: index
        }));

    const payload = {
        name: formData.value.name.trim(),
        notes: formData.value.notes?.trim() || null,
        image_url: formData.value.emoji || null,
        items: validItems
    };

    loadingStore.start();

    axios.post('/api/save-grocery-list-data', payload)
        .then((response) => {
            const createdGroceryList = response.data.data;
            toastStore.show('success', 'Shopping list created successfully.');
            router.push(`/grocery-list/${createdGroceryList.id}`);
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not create shopping list.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const handleCancel = () => {
    router.push('/grocery-lists');
};

const addItem = () => {
    items.value.push({
        name: "",
        quantity: "",
        unit: "",
        notes: ""
    });
};

const removeItem = (index: number) => {
    items.value.splice(index, 1);
};
</script>

<template>
    <DashboardLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden relative border-2 border-gray-200">
                <div class="p-8 space-y-6">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-6">Create New Shopping List</h1>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shopping List Emoji</label>
                        <div class="flex items-center gap-3">
                            <div
                                v-if="formData.emoji"
                                class="emoji-pulsate flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-4xl cursor-pointer hover:bg-gray-200 transition-colors border-2 border-dashed border-gray-300"
                                @click="showEmojiPicker = !showEmojiPicker"
                            >
                                {{ formData.emoji }}
                            </div>
                            <button
                                v-else
                                type="button"
                                @click="showEmojiPicker = !showEmojiPicker"
                                class="emoji-pulsate flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-4xl hover:bg-gray-200 transition-colors border-2 border-dashed border-gray-300"
                            >
                                🛒
                            </button>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600">
                                    {{ formData.emoji ? "Click emoji to change" : "Click to select an emoji" }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="showEmojiPicker"
                            class="mt-3 relative"
                        >
                            <emoji-picker
                                @emoji-click="handleEmojiSelect"
                                class="w-full"
                            ></emoji-picker>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Shopping List Name *</label>
                            <input
                                v-model="formData.name"
                                type="text"
                                class="w-full px-4 py-3 text-2xl font-bold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="Enter shopping list name"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea
                                v-model="formData.notes"
                                rows="3"
                                class="w-full px-4 py-3 text-lg text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"
                                placeholder="Enter notes (optional)"
                            ></textarea>
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-900">Items</h2>
                            <button
                                type="button"
                                @click="addItem"
                                class="px-3 py-1.5 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors font-medium"
                            >
                                + Add Item
                            </button>
                        </div>
                        <div v-if="items.length === 0" class="text-sm text-gray-500 italic">
                            No items added yet. Click "Add Item" to get started.
                        </div>
                        <div v-for="(item, index) in items" :key="index"
                             class="bg-gray-50 rounded-lg p-4 space-y-3 border border-gray-200">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-3">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                        <input
                                            v-model="item.name"
                                            type="text"
                                            class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                            placeholder="e.g., Milk"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                        <input
                                            v-model="item.quantity"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                            placeholder="2"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                        <input
                                            v-model="item.unit"
                                            type="text"
                                            class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                            placeholder="liters"
                                        />
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="removeItem(index)"
                                    class="flex-shrink-0 mt-6 px-2 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Remove item"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <input
                                    v-model="item.notes"
                                    type="text"
                                    class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                    placeholder="e.g., organic, whole milk"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between gap-3 pt-4 border-t border-gray-200">
                        <button
                            @click="handleCancel"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                        >
                            Cancel
                        </button>
                        <button
                            @click="createGroceryList"
                            class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                        >
                            Create Shopping List
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>

</style>