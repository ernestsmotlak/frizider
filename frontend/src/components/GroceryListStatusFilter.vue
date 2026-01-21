<template>
    <div ref="dropdownRef" class="relative w-full">
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="w-full px-4 py-2.5 text-base text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white hover:border-gray-400 transition-colors duration-200 flex items-center justify-between"
        >
            <svg
                class="w-5 h-5 text-gray-500"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <svg
                class="w-5 h-5 text-gray-500 transition-transform duration-200"
                :class="{ 'rotate-180': isOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div
            v-if="isOpen"
            class="absolute z-50 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg overflow-hidden"
            style="min-width: 100%;"
        >
            <div class="py-1">
                <button
                    type="button"
                    class="w-full px-4 py-2.5 text-left text-base text-gray-900 hover:bg-gray-50 transition-colors duration-150"
                >
                    All
                </button>
                <button
                    type="button"
                    class="w-full px-4 py-2.5 text-left text-base text-gray-900 hover:bg-gray-50 transition-colors duration-150"
                >
                    Completed
                </button>
                <button
                    type="button"
                    class="w-full px-4 py-2.5 text-left text-base text-gray-900 hover:bg-gray-50 transition-colors duration-150"
                >
                    Unfinished
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
/* Dropdown animation */
.absolute {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
