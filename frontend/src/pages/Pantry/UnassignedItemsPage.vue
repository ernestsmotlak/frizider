<script setup lang="ts">
/**
 * Everything with no storage space.
 *
 * Not a space, so it has no row in space_storages and nothing to rename or
 * delete — but the pantry is organised by space, so without this page these
 * items exist in the database and nowhere on screen. The scan produces them
 * by design: an unclear item is left unassigned rather than forced somewhere
 * wrong, which only works if unassigned is somewhere you can actually look.
 */
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {onMounted, ref} from "vue";
import router from "../../router";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import PantryItemsCard from "../../components/Pantry/PantryItemsCard.vue";
import type {PantryItem} from "../../components/Pantry/PantryItemsCard.vue";

const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const items = ref<PantryItem[]>([]);
const errorMessage = ref("");

const fetchItems = () => {
    loadingStore.start();

    axios.get('/api/pantry-items', {params: {unassigned: 1}})
        .then((response) => {
            items.value = response.data.data;
        })
        .catch((error) => {
            console.error(error);
            errorMessage.value = 'Could not fetch unassigned items.';
            toastStore.show('error', 'Could not get unassigned items.');
        })
        .finally(() => {
            loadingStore.stop();
        });
};

onMounted(() => {
    fetchItems();
});
</script>

<template>
    <DashboardLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-600 font-medium">{{ errorMessage }}</p>
            </div>

            <div class="space-y-6">
                <div class="bg-white app-surface-gradient rounded-2xl shadow-xl p-8 border-2 border-slate-300">
                    <div class="flex items-start gap-3">
                        <span class="w-11 h-11 shrink-0 rounded-lg bg-amber-50 border border-amber-200 flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v3.75m0 3.75h.008M10.34 3.94l-8.1 14.02A1.5 1.5 0 0 0 3.54 20.2h16.92a1.5 1.5 0 0 0 1.3-2.24l-8.1-14.02a1.5 1.5 0 0 0-2.6 0z"></path>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Unassigned</h1>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Items with no storage space. Give them one and they move off this page.
                            </p>
                        </div>
                    </div>
                    <button
                        @click="router.push('/storage-spaces')"
                        class="mt-4 text-sm font-medium text-blue-600 hover:text-blue-700"
                    >
                        &larr; Back to storage spaces
                    </button>
                </div>

                <PantryItemsCard
                    :pantry-items="items"
                    :space-id="null"
                    @refresh="fetchItems"
                />
            </div>
        </div>
    </DashboardLayout>
</template>
