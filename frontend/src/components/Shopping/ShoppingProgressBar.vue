<script setup lang="ts">
import {computed} from "vue";

const props = defineProps<{
    purchased: number;
    total: number;
}>();

const percentage = computed(() => {
    if (props.total === 0) return 0;
    return Math.round((props.purchased / props.total) * 100);
});
</script>

<template>
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-900">
                {{ purchased }} of {{ total }} items
            </span>
            <span class="text-sm font-medium text-gray-500">
                {{ percentage }}%
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
            <div
                class="bg-gray-900 h-2 rounded-full transition-all duration-300 ease-out"
                :style="{ width: `${percentage}%` }"
            ></div>
        </div>
    </div>
</template>
