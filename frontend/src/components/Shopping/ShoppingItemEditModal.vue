<script setup lang="ts">
import {ref, watch} from "vue";
import Modal from "../Modal.vue";
import type {ShoppingItem} from "./ShoppingItemCard.vue";

const props = defineProps<{
    isOpen: boolean;
    item: ShoppingItem | null;
}>();

const emit = defineEmits<{
    close: [];
    save: [item: ShoppingItem];
}>();

const editingItem = ref<ShoppingItem | null>(null);

watch(() => props.item, (newItem) => {
    if (newItem) {
        editingItem.value = {...newItem};
    } else {
        editingItem.value = null;
    }
}, {immediate: true});

const handleSave = () => {
    if (editingItem.value) {
        emit("save", editingItem.value);
    }
};

const handleClose = () => {
    emit("close");
};
</script>

<template>
    <Modal :isOpen="isOpen" @close="handleClose">
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
                            placeholder="L"
                        />
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-1">Notes (optional)</label>
                    <input
                        v-model="editingItem.notes"
                        type="text"
                        class="w-full px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors bg-white"
                        placeholder="e.g. organic, 2%"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <input
                        v-model="editingItem.is_purchased"
                        type="checkbox"
                        id="edit-purchased"
                        class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                    />
                    <label for="edit-purchased" class="text-sm text-gray-700 font-medium">
                        Purchased
                    </label>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex flex-col sm:flex-row justify-between gap-3">
                <button
                    @click="handleClose"
                    class="w-full sm:w-auto px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    @click="handleSave"
                    class="w-full sm:w-auto px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Save Changes
                </button>
            </div>
        </template>
    </Modal>
</template>
