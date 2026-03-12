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
            <div class="recipes-hero-card rounded-2xl overflow-hidden relative">
                <div class="p-8 space-y-6">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-6">Create New Shopping List</h1>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shopping List Emoji</label>
                        <div class="flex items-center gap-3">
                            <div
                                v-if="formData.emoji"
                                class="emoji-pulsate flex-shrink-0 w-16 h-16 rounded-xl flex items-center justify-center text-4xl cursor-pointer transition-all duration-200 border-2 border-dashed border-gray-300 bg-white/90 shadow-sm hover:bg-white hover:border-gray-400 hover:shadow-md hover:scale-105 active:scale-95"
                                @click="showEmojiPicker = !showEmojiPicker"
                            >
                                {{ formData.emoji }}
                            </div>
                            <button
                                v-else
                                type="button"
                                @click="showEmojiPicker = !showEmojiPicker"
                                class="emoji-pulsate flex-shrink-0 w-16 h-16 rounded-xl flex items-center justify-center text-4xl transition-all duration-200 border-2 border-dashed border-gray-300 bg-white/90 shadow-sm hover:bg-white hover:border-gray-400 hover:shadow-md hover:scale-105 active:scale-95"
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
                                class="top-form-input w-full px-4 py-3 text-2xl font-bold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="Enter shopping list name"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea
                                v-model="formData.notes"
                                rows="3"
                                class="top-form-input w-full px-4 py-3 text-lg text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"
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
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold rounded-xl border-2 border-emerald-100 bg-gradient-to-b from-white to-emerald-50 text-emerald-600 shadow-sm hover:-translate-y-0.5 hover:border-emerald-200/80 hover:shadow-md active:translate-y-0 active:scale-[0.98] transition-all duration-200"
                            >
                                + Add Item
                            </button>
                        </div>
                        <p v-if="items.length > 0" class="text-xs text-gray-500 -mt-2">
                            You can edit the order of items later.
                        </p>
                        <div v-if="items.length === 0"
                             class="text-sm text-gray-500 italic">
                            No items added yet. Click "Add Item" to get started.
                        </div>
                        <div v-for="(item, index) in items" :key="index"
                             class="ingredient-card relative rounded-2xl p-4 space-y-3 border">
                            <button
                                type="button"
                                @click="removeItem(index)"
                                class="absolute top-4 right-4 w-10 h-10 inline-flex items-center justify-center text-red-600 border border-red-200 bg-white rounded-xl shadow-sm hover:bg-red-50 hover:border-red-300 hover:shadow-md hover:scale-105 active:scale-95 transition-all duration-200"
                                title="Remove item"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            <div class="pr-14 space-y-3">
                                <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                        <input
                                            v-model="item.name"
                                            type="text"
                                            class="ingredient-input w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                            placeholder="e.g., Milk"
                                        />
                                    </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                        <input
                                            v-model="item.quantity"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="ingredient-input w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                            placeholder="2"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                        <input
                                            v-model="item.unit"
                                            type="text"
                                            class="ingredient-input w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                            placeholder="liters"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <input
                                    v-model="item.notes"
                                    type="text"
                                    class="ingredient-input w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                    placeholder="e.g., organic, whole milk"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between gap-3 pt-4 border-t border-gray-200">
                        <button
                            @click="handleCancel"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border-2 border-gray-200 bg-white/90 text-gray-700 font-semibold shadow-sm hover:-translate-y-0.5 hover:border-gray-300 hover:bg-white hover:shadow-md active:translate-y-0 active:scale-[0.98] transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            @click="createGroceryList"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border-2 border-blue-600 bg-gradient-to-b from-blue-500 to-blue-600 text-white font-semibold shadow-md hover:-translate-y-0.5 hover:shadow-lg hover:from-blue-600 hover:to-blue-700 hover:border-blue-700 active:translate-y-0 active:scale-[0.98] transition-all duration-200"
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
.recipes-hero-card {
    border: 4px solid color-mix(in srgb, var(--line-soft) 58%, var(--accent-soft) 42%);
    background:
        radial-gradient(85% 75% at 100% 0%, rgba(255, 200, 120, 0.3) 0%, rgba(255, 200, 120, 0) 70%),
        radial-gradient(70% 85% at 0% 100%, rgba(116, 221, 164, 0.12) 0%, rgba(116, 221, 164, 0) 72%),
        linear-gradient(135deg, #ffffff 0%, #fcfffc 45%, #f8fbf9 100%);
    box-shadow: 0 18px 38px rgba(11, 96, 68, 0.2);
}

.top-form-input {
    background: #ffffff;
}

.ingredient-card {
    border-color: #d9efe2;
    background:
        radial-gradient(120% 120% at 110% -12%, rgba(53, 196, 125, 0.14) 0%, rgba(53, 196, 125, 0) 72%),
        linear-gradient(180deg, #ffffff 0%, #f7fff9 100%);
    box-shadow: 0 8px 20px rgba(16, 93, 61, 0.12);
}

.ingredient-input {
    background: #ffffff;
}
</style>
