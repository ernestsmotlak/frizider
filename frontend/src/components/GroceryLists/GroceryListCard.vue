<script setup lang="ts">
import type {GroceryList} from '../../pages/GroceryList/GroceryListsPage.vue';

const props = defineProps<{
    groceryList: GroceryList
}>();

const emit = defineEmits<{
    click: [id: number]
}>();

const handleClick = () => {
    emit('click', props.groceryList.id);
}

const truncateNotes = (text: string | null, maxLength: number = 100): string => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength).trim() + '...';
}

</script>

<template>
    <div
        @click="handleClick"
        class="rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer overflow-hidden border border-gray-100 active:scale-[0.98] flex flex-row"
    >
        <div
            class="relative w-24 h-full flex-shrink-0 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden self-stretch">
            <div class="w-full h-full flex items-center justify-center">
                <div v-if="groceryList.image_url" class="text-5xl">
                    {{ groceryList.image_url }}
                </div>
                <svg v-else class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
        </div>
        <div class="p-4 flex-1 min-w-0 flex flex-col justify-center">
            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 break-words">
                {{ groceryList.name }}
            </h3>
            <p class="text-sm text-gray-600 line-clamp-1 leading-relaxed break-words min-h-[1.25rem]">
                <template v-if="groceryList.notes">{{ truncateNotes(groceryList.notes, 100) }}</template>
<!--                <template v-else>&nbsp;</template>-->
            </p>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.break-words {
    overflow-wrap: break-word;
    word-break: break-word;
    hyphens: auto;
}
</style>
