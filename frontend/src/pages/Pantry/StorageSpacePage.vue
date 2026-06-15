<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {onMounted, ref} from "vue";
import {useRoute} from "vue-router";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import StorageSpaceTitleCard from "../../components/Pantry/StorageSpaceTitleCard.vue";
import PantryItemsCard from "../../components/Pantry/PantryItemsCard.vue";
import type {SpaceStorage} from "./StorageSpacesPage.vue";
import type {PantryItem} from "../../components/Pantry/PantryItemsCard.vue";

const route = useRoute();
const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const spaceStorageId = Number(route.params.id);
const errorMessage = ref("");
const spaceStorageData = ref<SpaceStorage | null>(null);
const pantryItems = ref<PantryItem[]>([]);

const handleSpaceStorageUpdate = (updated: SpaceStorage) => {
    spaceStorageData.value = updated;
};

const fetchPantryItems = () => {
    axios.get('/api/pantry-items', {params: {space_id: spaceStorageId}})
        .then((response) => {
            pantryItems.value = response.data.data;
        })
        .catch((error) => {
            console.error(error);
            toastStore.show('error', 'Could not fetch pantry items.');
        });
};

const fetchSpaceStorage = () => {
    if (!spaceStorageId) {
        errorMessage.value = "Storage space id does not exist.";
        return;
    }

    loadingStore.start();

    axios.get(`/api/space-storages/${spaceStorageId}`)
        .then((response) => {
            spaceStorageData.value = response.data.data;
            fetchPantryItems();
        })
        .catch((error) => {
            console.error(error);
            errorMessage.value = 'Could not fetch storage space data.';
            toastStore.show('error', 'Could not get storage space.');
        })
        .finally(() => {
            loadingStore.stop();
        });
};

onMounted(() => {
    fetchSpaceStorage();
});
</script>

<template>
    <DashboardLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-600 font-medium">{{ errorMessage }}</p>
            </div>

            <div v-if="spaceStorageData" class="space-y-6">
                <StorageSpaceTitleCard
                    :space-storage="spaceStorageData"
                    @updated="handleSpaceStorageUpdate"
                />

                <PantryItemsCard
                    :pantry-items="pantryItems"
                    :space-id="spaceStorageId"
                    @refresh="fetchPantryItems"
                />
            </div>

            <div v-else-if="!errorMessage" class="text-center py-12">
                <p class="text-gray-600 text-lg">Loading storage space...</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
</style>
