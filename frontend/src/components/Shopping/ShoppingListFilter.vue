<script setup lang="ts">
export interface GroceryListInfo {
    id: number;
    name: string;
    color: string;
}

const props = defineProps<{
    lists: GroceryListInfo[];
    selectedListId: number | null;
}>();

const emit = defineEmits<{
    select: [listId: number | null];
}>();

const handleSelect = (listId: number | null) => {
    emit("select", listId);
};
</script>

<template>
    <div class="flex flex-wrap gap-2 mb-6">
        <button
            @click="handleSelect(null)"
            :class="[
                'px-4 py-2 rounded-full text-sm font-medium transition-all duration-200',
                selectedListId === null
                    ? 'bg-gray-900 text-white'
                    : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'
            ]"
        >
            All Lists
        </button>
        <button
            v-for="list in lists"
            :key="list.id"
            @click="handleSelect(list.id)"
            :class="[
                'px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 flex items-center gap-2',
                selectedListId === list.id
                    ? 'bg-gray-900 text-white'
                    : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'
            ]"
        >
            <span
                class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                :style="{ backgroundColor: list.color }"
            ></span>
            <span>{{ list.name }}</span>
        </button>
    </div>
</template>
