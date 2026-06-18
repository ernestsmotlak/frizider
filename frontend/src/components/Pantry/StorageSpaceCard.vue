<script lang="ts">
import {ref} from "vue";
const openCardId = ref<number | null>(null);
</script>

<script setup lang="ts">
import {onMounted, onUnmounted, computed} from "vue";
import Modal from "../Modal.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import {useConfirmStore} from "../../stores/confirm.ts";
import type {SpaceStorage} from "../../pages/Pantry/StorageSpacesPage.vue";

const props = defineProps<{
    spaceStorage: SpaceStorage;
}>();

const emit = defineEmits<{
    click: [id: number];
    updated: [spaceStorage: SpaceStorage];
    deleted: [id: number];
}>();

const toastStore = useToastStore();
const loadingStore = useLoadingStore();
const confirmStore = useConfirmStore();

const isMenuOpen = computed(() => openCardId.value === props.spaceStorage.id);
const isEditModalOpen = ref(false);

const formData = ref({
    name: "",
    description: "",
});

const handleClick = () => {
    emit('click', props.spaceStorage.id);
};

const toggleMenu = (event: Event) => {
    event.stopPropagation();
    openCardId.value = openCardId.value === props.spaceStorage.id ? null : props.spaceStorage.id;
};

const closeMenu = () => {
    if (openCardId.value === props.spaceStorage.id) openCardId.value = null;
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

const openEditModal = (event: Event) => {
    event.stopPropagation();
    closeMenu();
    formData.value = {
        name: props.spaceStorage.name || "",
        description: props.spaceStorage.description || "",
    };
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
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
            closeEditModal();
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

const deleteSpaceStorage = async (event: Event) => {
    event.stopPropagation();
    closeMenu();

    const confirmed = await confirmStore.show(`Are you sure you want to delete "${props.spaceStorage.name}"? Pantry items in this space will become unassigned.`);

    if (!confirmed) {
        return;
    }

    loadingStore.start();

    axios.delete(`/api/space-storages/${props.spaceStorage.id}`)
        .then(() => {
            emit('deleted', props.spaceStorage.id);
            toastStore.show('success', 'Storage space deleted successfully.');
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
</script>

<template>
    <div
        @click="handleClick"
        class="rounded-xl shadow-sm transition-all duration-300 ease-out border flex flex-row relative cursor-pointer border-slate-200/80 bg-white/85 hover:border-slate-300 hover:shadow-md md:hover:scale-[1.02] md:hover:-translate-y-0.5 md:hover:shadow-lg active:scale-[0.98]"
        :class="isMenuOpen ? 'z-10' : 'z-0'"
    >
        <div class="relative w-20 h-full flex-shrink-0 overflow-hidden self-stretch bg-gradient-to-br from-gray-100 to-gray-200 rounded-l-xl">
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7l1-3h16l1 3"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 11h6"></path>
                </svg>
            </div>
        </div>
        <div class="p-4 flex-1 min-w-0 flex flex-col justify-center">
            <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-semibold mb-1 line-clamp-2 break-words text-gray-900">
                    {{ spaceStorage.name }}
                </h3>
                <div class="relative menu-container flex-shrink-0">
                    <button
                        @click="toggleMenu"
                        class="w-7 h-7 -my-1 -mr-1 rounded-lg border border-gray-200/80 bg-white/70 text-gray-500 hover:text-slate-800 hover:bg-slate-50/90 hover:border-slate-300 hover:scale-110 active:scale-95 transition-all duration-200 flex items-center justify-center"
                        aria-label="Open storage space actions"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.05" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01"></path>
                        </svg>
                    </button>
                    <div
                        v-if="isMenuOpen"
                        class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-xl border border-gray-200 py-1 z-50"
                        @click.stop
                    >
                        <button
                            @click="openEditModal"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </button>
                        <button
                            @click="deleteSpaceStorage"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
            <p class="text-sm line-clamp-1 leading-relaxed break-words min-h-[1.25rem] text-gray-600">
                {{ spaceStorage.description }}
            </p>
        </div>
    </div>

    <Modal :isOpen="isEditModalOpen" @close="closeEditModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Edit Storage Space</h2>
        </template>
        <template #body>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input
                        v-model="formData.name"
                        type="text"
                        class="w-full px-4 py-3 text-lg font-semibold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        placeholder="e.g., Fridge, Freezer, Pantry shelf"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea
                        v-model="formData.description"
                        rows="3"
                        class="w-full px-4 py-3 text-base text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"
                        placeholder="Optional description"
                    ></textarea>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-between gap-3">
                <button
                    @click="closeEditModal"
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
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.break-words {
    overflow-wrap: break-word;
    word-break: break-word;
    hyphens: auto;
}
</style>
