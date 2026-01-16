<script setup lang="ts">
import {ref, onMounted, onUnmounted} from "vue";
import Modal from "../Modal.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import {useConfirmStore} from "../../stores/confirm.ts";
import {useRouter} from "vue-router";
import "emoji-picker-element";

const toastStore = useToastStore();
const loadingStore = useLoadingStore();
const confirmStore = useConfirmStore();
const router = useRouter();

interface GroceryList {
    id: number;
    user_id: number;
    name: string | null;
    notes: string | null;
    image_url: string | null;
    completed_at: string | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
    grocery_list_data: [];
}

const props = defineProps<{
    groceryListData: GroceryList
}>();

const emit = defineEmits<{
    'updatedGroceryList': [groceryList: GroceryList]
}>();

const isModalOpen = ref(false);
const isMenuOpen = ref(false);
const showEmojiPicker = ref(false);

const formData = ref({
    name: "",
    notes: "",
    emoji: ""
});

const openModal = () => {
    formData.value = {
        name: props.groceryListData.name || "",
        notes: props.groceryListData.notes || "",
        emoji: props.groceryListData.image_url || ""
    };
    showEmojiPicker.value = false;
    isModalOpen.value = true;
};

const handleEmojiSelect = (event: CustomEvent) => {
    formData.value.emoji = event.detail?.unicode || "";
    showEmojiPicker.value = false;
};

const updateGroceryList = () => {
    const payload = {
        name: formData.value.name,
        notes: formData.value.notes || null,
        image_url: formData.value.emoji || null
    };

    loadingStore.start();

    axios.patch('/api/grocery-lists/' + props.groceryListData.id, payload)
        .then((response) => {
            const updatedGroceryList = response.data.data;
            emit('updatedGroceryList', updatedGroceryList);
            toastStore.show('success', 'Grocery list updated successfully.');
            closeModal();
        })
        .catch((error) => {
            console.error(error);
            toastStore.show('error', 'Could not update grocery list.');
        })
        .finally(() => {
            loadingStore.stop();
        })
}

const closeModal = () => {
    isModalOpen.value = false;
};

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
    isMenuOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (isMenuOpen.value && !target.closest('.menu-container')) {
        closeMenu();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const handleEdit = () => {
    closeMenu();
    openModal();
};

const deleteGroceryList = async () => {
    closeMenu();
    const listName = props.groceryListData.name || 'this grocery list';
    const confirmed = await confirmStore.show(`Are you sure you want to delete "${listName}"? This action cannot be undone.`);

    if (!confirmed) {
        return;
    }

    loadingStore.start();

    axios.delete(`/api/grocery-lists/${props.groceryListData.id}`)
        .then(() => {
            toastStore.show('success', 'Grocery list deleted successfully.');
            router.push('/grocery-lists');
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not delete grocery list.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const formatDate = (dateString: string | null): string => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden relative border-2 border-gray-200">
        <div class="absolute top-2 right-2">
            <div class="relative menu-container">
                <button @click.stop="toggleMenu"
                        class="p-2 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200">
                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                </button>

                <div v-if="isMenuOpen"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-1 z-50"
                     @click.stop>
                    <button @click.stop="handleEdit"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit List
                    </button>
                    <button @click.stop="deleteGroceryList"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path>
                        </svg>
                        Delete List
                    </button>
                </div>
            </div>
        </div>

        <div class="p-8 space-y-6">
            <div class="flex items-start gap-4 max-[450px]:flex-col max-[450px]:items-center max-[450px]:gap-2">
                <div v-if="groceryListData.image_url" class="flex-shrink-0">
                    <div
                        class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center text-5xl">
                        {{ groceryListData.image_url }}
                    </div>
                </div>
                <div
                    class="flex-1 min-w-0 max-[450px]:flex-1 max-[450px]:w-full max-[450px]:text-center max-[450px]:text-base">
                    <h1 class="text-4xl font-bold text-gray-900 mb-3 max-[450px]:text-3xl max-[450px]:mb-2 max-w-[220px] break-words mx-auto">
                        {{ groceryListData.name || "Unnamed List" }}</h1>
                    <p v-if="groceryListData.notes" class="text-lg text-gray-600 leading-relaxed max-[450px]:text-base">
                        {{ groceryListData.notes }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 pt-4 border-t border-gray-200">
                <div class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Status</p>
                        <p class="text-lg font-semibold text-gray-900">{{
                                groceryListData.completed_at ? "Completed" : "Active"
                            }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Created</p>
                        <p class="text-lg font-semibold text-gray-900">{{
                                formatDate(groceryListData.created_at)
                            }}</p>
                    </div>
                </div>

                <div v-if="groceryListData.completed_at" class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Completed</p>
                        <p class="text-lg font-semibold text-gray-900">{{
                                formatDate(groceryListData.completed_at)
                            }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Modal :isOpen="isModalOpen" @close="closeModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Edit Grocery List</h2>
        </template>
        <template #body>
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">List Emoji</label>
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
                            class="emoji-pulsate flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-3xl hover:bg-gray-200 transition-colors border-2 border-dashed border-gray-300"
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">List Name</label>
                    <input
                        v-model="formData.name"
                        type="text"
                        class="w-full px-4 py-3 text-2xl font-bold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        placeholder="Enter list name"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea
                        v-model="formData.notes"
                        rows="3"
                        class="w-full px-4 py-3 text-lg text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"
                        placeholder="Enter notes"
                    ></textarea>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-between gap-3">
                <button
                    @click="closeModal"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    @click.stop="updateGroceryList"
                    class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Save Changes
                </button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>

</style>
