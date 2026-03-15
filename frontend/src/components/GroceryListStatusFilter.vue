<template>
    <div ref="dropdownRef" class="relative w-full h-full">
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="w-full h-11 px-4 text-base text-gray-900 border border-gray-300 rounded-lg
           focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
           bg-white hover:border-gray-400 transition-colors duration-200
           flex items-center justify-between"
        >
            <span class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                <span
                    v-if="selectedValue === 'completed'"
                    class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-green-300 bg-green-100 text-green-700"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <span
                    v-else-if="selectedValue === 'unfinished'"
                    class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-amber-300 bg-amber-100 text-amber-600"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <span v-if="selectedValue === 'all'">{{ selectedLabel }}</span>
            </span>
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
                    @click="selectOption('all')"
                    class="w-full px-4 py-2.5 text-left text-base text-gray-900 hover:bg-gray-50 transition-colors duration-150 flex items-center gap-2"
                    :class="{ 'bg-gray-100': selectedValue === 'all' }"
                >
                    <svg
                        class="w-5 h-5 text-gray-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>All</span>
                </button>
                <button
                    type="button"
                    @click="selectOption('completed')"
                    class="w-full px-4 py-2.5 text-left text-base text-gray-900 hover:bg-gray-50 transition-colors duration-150 flex items-center gap-2"
                    :class="{ 'bg-gray-100': selectedValue === 'completed' }"
                >
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-green-300 bg-green-100 text-green-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span>Completed</span>
                </button>
                <button
                    type="button"
                    @click="selectOption('unfinished')"
                    class="w-full px-4 py-2.5 text-left text-base text-gray-900 hover:bg-gray-50 transition-colors duration-150 flex items-center gap-2"
                    :class="{ 'bg-gray-100': selectedValue === 'unfinished' }"
                >
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-amber-300 bg-amber-100 text-amber-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span>Unfinished</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from "vue";

type FilterValue = "all" | "completed" | "unfinished";

const props = defineProps<{
    modelValue?: FilterValue;
}>();

const emit = defineEmits<{
    "update:modelValue": [value: FilterValue];
}>();

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);
const selectedValue = ref<FilterValue>(props.modelValue ?? "all");
const selectedLabel = ref("All");

const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

const selectOption = (value: FilterValue) => {
    selectedValue.value = value;
    selectedLabel.value = value === "completed" ? "Completed" : value === "unfinished" ? "Unfinished" : "All";
    emit("update:modelValue", value);
    isOpen.value = false;
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    selectedLabel.value = selectedValue.value === "completed" ? "Completed" : selectedValue.value === "unfinished" ? "Unfinished" : "All";
});

watch(
    () => props.modelValue,
    (value) => {
        if (!value) return;
        selectedValue.value = value;
        selectedLabel.value = value === "completed" ? "Completed" : value === "unfinished" ? "Unfinished" : "All";
    }
);

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
