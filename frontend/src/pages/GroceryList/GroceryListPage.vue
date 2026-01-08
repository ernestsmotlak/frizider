<script setup lang="ts">
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import {ref} from "vue";
import GroceryListTitleCard from "../../components/GroceryLists/GroceryListTitleCard.vue";

const errorMessage = ref("");
const groceryListData = ref({});
</script>

<template>
    <DashboardLayout>
        <div class="p-6 max-w-4xl mx-auto">
            GL page!


            <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-600 font-medium">{{ errorMessage }}</p>
            </div>

            <div v-if="groceryListData" class="space-y-6">
                <GroceryListTitleCard/>
            </div>

            <div v-else-if="!errorMessage" class="text-center py-12">
                <p class="text-gray-600 text-lg">Loading recipe...</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>

</style>
