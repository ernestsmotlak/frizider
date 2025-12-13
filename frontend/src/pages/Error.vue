<script setup lang="ts">
import {useRoute, useRouter} from "vue-router";

const route = useRoute();
const router = useRouter();

const errorCode = route.params.code || route.query.code || "404";
const errorMessage = route.query.message as string ||
    (errorCode === "404" ? "The page you're looking for doesn't exist." :
        errorCode === "403" ? "You don't have permission to access this page." :
            errorCode === "500" ? "Something went wrong on our end. Please try again later." :
                "An unexpected error occurred.");

const goHome = () => {
    router.push("/");
};
</script>

<template>
    <div class="max-w-[600px] mx-auto p-8 text-center">
        <div class="mb-8">
            <h1 class="text-6xl font-bold text-red-500 mb-4">{{ errorCode }}</h1>
            <h2 class="text-2xl font-semibold mb-4">Oops! Something went wrong</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                {{ errorMessage }}
            </p>
        </div>

        <div class="flex flex-col gap-4 items-center">
            <button
                @click="goHome"
                class="px-6 py-3 bg-[#646cff] text-white border-none rounded text-base font-medium cursor-pointer transition-colors duration-250 hover:bg-[#535bf2]">
                Go to Home
            </button>
            <button
                @click="$router.back()"
                class="px-6 py-3 bg-transparent text-[#646cff] border border-[#646cff] rounded text-base font-medium cursor-pointer transition-colors duration-250 hover:bg-[#646cff] hover:text-white">
                Go Back
            </button>
        </div>
    </div>
</template>

<style scoped>

</style>
