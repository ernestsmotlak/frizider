<script setup lang="ts">
import {useRoute, useRouter} from "vue-router";
import DashboardLayout from "../layouts/DashboardLayout.vue";

const route = useRoute();
const router = useRouter();

const errorCode = route.params.code || route.query.code || "404";
const errorMessage = "The page you're looking for doesn't exist.";

const goHome = () => {
    router.push("/");
};
</script>

<template>
    <DashboardLayout>
        <div class="px-6 py-6 text-center shadow-2xl mt-5 bg-gray-50 rounded-2xl border-2 border-gray-200">
            <div class="mb-8">
                <h1 class="text-5xl font-bold text-red-600 mb-4">{{ errorCode }}</h1>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Oops! Something went wrong.</h2>
                <p class="text-lg text-gray-600 mb-6">
                    {{ errorMessage }}
                </p>
            </div>

            <div class="flex flex-col gap-4 items-center">
                <button
                    @click="goHome"
                    class="px-6 py-3 bg-green-600 text-white rounded-lg text-base font-medium cursor-pointer transition-colors duration-200 hover:bg-green-700">
                    Go to Home
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>

</style>
