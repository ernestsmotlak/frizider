<script setup lang="ts">
import {useLoadingStore} from "../stores/loading";

const loadingStore = useLoadingStore();
</script>

<template>
    <Transition name="loader">
        <div
            v-if="loadingStore.loading"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
        >
            <div class="flex flex-col items-center gap-4">
                <div class="relative w-16 h-16">
                    <div class="absolute inset-0 border-4 border-gray-200 rounded-full"></div>
                    <div
                        class="absolute inset-0 border-4 border-[#646cff] rounded-full border-t-transparent animate-spin"
                    ></div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.loader-enter-active {
    transition: opacity 0.3s ease-out;
}

.loader-leave-active {
    transition: opacity 0.3s ease-in;
}

.loader-enter-from,
.loader-leave-to {
    opacity: 0;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>

