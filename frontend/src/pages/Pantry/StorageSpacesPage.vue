<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {onMounted, ref} from "vue";
import router from "../../router";
import StorageSpaceCard from "../../components/Pantry/StorageSpaceCard.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";

export interface SpaceStorage {
    id: number;
    user_id: number;
    name: string;
    description: string | null;
    sort_order: number | null;
    created_at: string;
    updated_at: string;
}

const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const spaceStorages = ref<SpaceStorage[]>([]);
const isLoading = ref(false);

const fetchSpaceStorages = () => {
    isLoading.value = true;
    loadingStore.start();

    axios.get('/api/space-storages')
        .then((response) => {
            spaceStorages.value = response.data.data;
        })
        .catch((error) => {
            console.error(error);
            toastStore.show('error', 'Could not fetch storage spaces.');
        })
        .finally(() => {
            isLoading.value = false;
            loadingStore.stop();
        });
};

const handleStorageSpaceClick = (id: number) => {
    router.push('/storage-spaces/' + id);
};

const handleStorageSpaceUpdated = (updated: SpaceStorage) => {
    const index = spaceStorages.value.findIndex(space => space.id === updated.id);
    if (index !== -1) {
        spaceStorages.value[index] = updated;
    }
};

const handleStorageSpaceDeleted = (id: number) => {
    spaceStorages.value = spaceStorages.value.filter(space => space.id !== id);
};

const handleAddStorageSpace = () => {
    router.push('/new/storage-space');
};

onMounted(() => {
    fetchSpaceStorages();
});
</script>

<template>
    <DashboardLayout>
        <div class="pt-7 px-5">
            <div class="recipes-hero-card rounded-2xl">
                <div class="px-4 pt-6 pb-4">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex-1">
                            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">
                                Storage Spaces
                            </h2>
                            <p class="text-xs text-gray-500">
                                Organize your pantry by fridge, freezer, shelves, and more
                            </p>
                        </div>
                        <button
                            @click="handleAddStorageSpace"
                            class="w-11 h-11 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200 flex items-center justify-center"
                        >
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                    <hr class="border-gray-300 my-3"/>
                    <p v-if="spaceStorages.length > 0" class="text-sm text-gray-600">
                        {{ spaceStorages.length }} storage space{{ spaceStorages.length !== 1 ? 's' : '' }}
                    </p>
                </div>

                <div v-if="spaceStorages.length === 0 && !isLoading"
                     class="flex flex-col items-center justify-center py-16 px-4">
                    <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7l1-3h16l1 3"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 11h6"></path>
                    </svg>
                    <p class="text-gray-500 text-center text-lg font-medium mb-1">No storage spaces yet</p>
                    <p class="text-gray-400 text-center text-sm">Add a fridge, freezer, or pantry shelf to get started</p>
                </div>

                <div v-else class="px-4 pb-4">
                    <div class="grid grid-cols-1 gap-4">
                        <StorageSpaceCard
                            v-for="spaceStorage in spaceStorages"
                            :key="spaceStorage.id"
                            :space-storage="spaceStorage"
                            @click="handleStorageSpaceClick"
                            @updated="handleStorageSpaceUpdated"
                            @deleted="handleStorageSpaceDeleted"
                        />
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
</style>
