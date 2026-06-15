<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {onUnmounted, ref, watch} from "vue";
import router from "../../router";
import StorageSpaceCard from "../../components/Pantry/StorageSpaceCard.vue";
import {usePagination} from "../../composables/usePagination.ts";

export interface SpaceStorage {
    id: number;
    user_id: number;
    name: string;
    description: string | null;
    sort_order: number | null;
    created_at: string;
    updated_at: string;
}

const searchTerm = ref("");

const {items: spaceStorages, isLoading, hasMore, allRows, refresh} = usePagination<SpaceStorage>({
    endpoint: '/api/get-storage-spaces',
    errorMessage: 'Could not fetch storage spaces.',
    payload: () => ({
        searchTerm: searchTerm.value,
    }),
});

let searchTimeout: number | null = null;
watch(searchTerm, () => {
    if (searchTimeout) {
        window.clearTimeout(searchTimeout);
    }
    searchTimeout = window.setTimeout(() => {
        refresh();
    }, 400);
});

onUnmounted(() => {
    if (searchTimeout) {
        window.clearTimeout(searchTimeout);
    }
});

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
                    <p v-if="allRows > 0" class="text-sm text-gray-600">
                        {{ allRows }} storage space{{ allRows !== 1 ? 's' : '' }}
                    </p>

                    <div class="mt-4">
                        <label for="storage-spaces-search" class="sr-only">Search storage spaces</label>
                        <div class="relative w-full">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
                                    />
                                </svg>
                            </div>
                            <input
                                id="storage-spaces-search"
                                v-model="searchTerm"
                                type="text"
                                inputmode="search"
                                enterkeyhint="search"
                                role="searchbox"
                                placeholder="Search storage spaces"
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
                    </div>

                    <p v-if="hasMore && spaceStorages.length > 0 && !isLoading" class="text-sm text-gray-500 mt-2">
                        Scroll down for more storage spaces
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

                    <div v-if="isLoading && spaceStorages.length > 0" class="text-center py-8">
                        <div class="inline-flex items-center gap-2 text-gray-600">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm">Loading more storage spaces...</span>
                        </div>
                    </div>

                    <div v-if="!hasMore && spaceStorages.length > 0" class="text-center py-6">
                        <p class="text-sm text-gray-500">No more storage spaces to load</p>
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
