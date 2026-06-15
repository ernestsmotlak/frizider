<script setup lang="ts">
import {onMounted, onUnmounted, ref} from "vue";
import Modal from "../Modal.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import {useConfirmStore} from "../../stores/confirm.ts";
import {useRouter} from "vue-router";
import type {SpaceStorage} from "../../pages/Pantry/StorageSpacesPage.vue";

const toastStore = useToastStore();
const loadingStore = useLoadingStore();
const confirmStore = useConfirmStore();
const router = useRouter();

const props = defineProps<{
    spaceStorage: SpaceStorage;
}>();

const emit = defineEmits<{
    'updated': [spaceStorage: SpaceStorage];
}>();

const isModalOpen = ref(false);
const isMenuOpen = ref(false);

const formData = ref({
    name: "",
    description: "",
});

const openModal = () => {
    formData.value = {
        name: props.spaceStorage.name || "",
        description: props.spaceStorage.description || "",
    };
    isModalOpen.value = true;
};

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

const updateSpaceStorage = () => {
    if (!formData.value.name.trim()) {
        toastStore.show('error', 'Storage space name is required.');
        return;
    }

    loadingStore.start();

    axios.patch(`/api/space-storages/${props.spaceStorage.id}`, {
        name: formData.value.name.trim(),
        description: formData.value.description?.trim() || null,
    })
        .then((response) => {
            emit('updated', response.data.data);
            toastStore.show('success', 'Storage space updated successfully.');
            closeModal();
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not update storage space.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const deleteSpaceStorage = async () => {
    closeMenu();
    const spaceName = props.spaceStorage.name || 'this storage space';
    const confirmed = await confirmStore.show(`Are you sure you want to delete "${spaceName}"? Pantry items in this space will become unassigned.`);

    if (!confirmed) {
        return;
    }

    loadingStore.start();

    axios.delete(`/api/space-storages/${props.spaceStorage.id}`)
        .then(() => {
            toastStore.show('success', 'Storage space deleted successfully.');
            router.push('/storage-spaces');
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not delete storage space.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const formatDate = (dateString: string | null): string => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'});
};
</script>

<template>
    <div class="bg-white app-surface-gradient rounded-2xl shadow-2xl overflow-hidden relative border-2 border-slate-300">
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
                        Edit Storage Space
                    </button>
                    <button @click.stop="deleteSpaceStorage"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Storage Space
                    </button>
                </div>
            </div>
        </div>

        <div class="p-8 space-y-4">
            <div class="flex items-start gap-4 max-[450px]:flex-col max-[450px]:items-center max-[450px]:gap-2">
                <div class="flex-shrink-0">
                    <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7l1-3h16l1 3"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 11h6"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0 max-[450px]:flex-1 max-[450px]:w-full max-[450px]:text-center max-[450px]:text-base">
                    <h1 class="text-4xl font-bold text-gray-900 mb-3 max-[450px]:text-3xl max-[450px]:mb-2 break-words">
                        {{ spaceStorage.name || "Unnamed Storage Space" }}</h1>
                    <p v-if="spaceStorage.description" class="text-lg text-gray-600 leading-relaxed max-[450px]:text-base">
                        {{ spaceStorage.description }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Created</p>
                    <p class="text-lg font-semibold text-gray-900">{{ formatDate(spaceStorage.created_at) }}</p>
                </div>
            </div>
        </div>
    </div>

    <Modal :isOpen="isModalOpen" @close="closeModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Edit Storage Space</h2>
        </template>
        <template #body>
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input
                        v-model="formData.name"
                        type="text"
                        class="w-full px-4 py-3 text-2xl font-bold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        placeholder="Enter storage space name"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea
                        v-model="formData.description"
                        rows="3"
                        class="w-full px-4 py-3 text-lg text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"
                        placeholder="Enter description"
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
                    @click.stop="updateSpaceStorage"
                    class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Save Changes
                </button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
.break-words {
    overflow-wrap: break-word;
    word-break: break-word;
}
</style>
