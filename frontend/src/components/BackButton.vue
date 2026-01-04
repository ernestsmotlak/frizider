<script setup lang="ts">
import {useRouter} from "vue-router";

const props = defineProps<{
    to?: string;
    label?: string;
}>();

const router = useRouter();

const handleClick = () => {
    if (props.to) {
        router.push(props.to);
    } else {
        router.back();
    }
};
</script>

<template>
    <button
        @click="handleClick"
        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-200 shadow-sm hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition-all duration-200 active:scale-95"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        <span>{{ label || "Back" }}</span>
    </button>
</template>

