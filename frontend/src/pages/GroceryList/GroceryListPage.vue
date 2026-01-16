<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {onMounted, ref} from "vue";
import GroceryListTitleCard from "../../components/GroceryLists/GroceryListTitleCard.vue";
import type {GroceryList} from "./GroceryListsPage.vue";
import {useRoute} from "vue-router";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";

const route = useRoute();
const toasterStore = useToastStore();
const loadingStore = useLoadingStore();

const groceryListId = Number(route.params.id);
const errorMessage = ref("");
const groceryListData = ref<GroceryList | null>(null);

const handleGroceryListUpdate = (updatedGroceryList: GroceryList) => {
    groceryListData.value = updatedGroceryList;
}

const getGroceryList = () => {
    if (!groceryListId) {
        errorMessage.value = "Grocery list id does not exist.";
        return;
    }

    loadingStore.start();
    const url = '/api/grocery-lists/' + groceryListId;

    axios.get(url)
        .then((response) => {
            groceryListData.value = response.data.data;
        })
        .catch((error) => {
            console.error(error);
            errorMessage.value = 'Could not fetch grocery list data.';
            toasterStore.show('error', 'Could not get recipe.');
        })
        .finally(() => {
            loadingStore.stop();
        })
}

onMounted(() => {
    getGroceryList();
})
</script>

<template>
    <DashboardLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-600 font-medium">{{ errorMessage }}</p>
            </div>

            <div v-if="groceryListData" class="space-y-6">
                <GroceryListTitleCard
                    v-if="groceryListData"
                    :grocery-list-data="groceryListData"
                />
            </div>

            <div v-else-if="!errorMessage" class="text-center py-12">
                <p class="text-gray-600 text-lg">Loading recipe...</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>

</style>
