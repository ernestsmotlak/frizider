<script setup lang="ts">
import {computed} from "vue";

export interface ShoppingItem {
    id: number;
    shopping_session_id: number;
    grocery_list_item_id: number;
    name: string;
    quantity: number | null;
    unit: string | null;
    notes: string | null;
    sort_order: number;
    is_purchased: boolean;
    created_at: string | null;
    updated_at: string | null;
    grocery_list_item?: {
        id: number;
        grocery_list_id: number;
        grocery_list?: {
            id: number;
            name: string;
        };
    };
}

const props = defineProps<{
    item: ShoppingItem;
    listName: string;
    listColor: string;
    isToggling: boolean;
}>();

const emit = defineEmits<{
    toggle: [item: ShoppingItem];
    edit: [item: ShoppingItem];
}>();

const quantityDisplay = computed(() => {
    const parts: string[] = [];
    if (props.item.quantity !== null) {
        parts.push(props.item.quantity.toString());
    }
    if (props.item.unit) {
        parts.push(props.item.unit);
    }
    return parts.join(" ");
});

const handleToggle = () => {
    if (!props.isToggling) {
        emit("toggle", props.item);
    }
};

const handleEdit = (event: Event) => {
    event.stopPropagation();
    emit("edit", props.item);
};
</script>

<template>
    <div
        @click="handleToggle"
        :class="[
            'bg-white rounded-xl border-2 p-4 transition-all duration-200 cursor-pointer relative',
            item.is_purchased
                ? 'border-green-200 bg-green-50/50'
                : 'border-gray-200 hover:border-gray-300 hover:shadow-sm'
        ]"
    >
        <div
            v-if="item.is_purchased"
            class="absolute left-0 top-0 bottom-0 w-1 bg-green-500 rounded-l-xl"
        ></div>

        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 pt-0.5">
                <div
                    :class="[
                        'w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all duration-200',
                        item.is_purchased
                            ? 'bg-green-500 border-green-500'
                            : 'bg-white border-gray-300'
                    ]"
                >
                    <svg
                        v-if="item.is_purchased"
                        class="w-4 h-4 text-white"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="3"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1">
                        <h3
                            :class="[
                                'text-base font-semibold leading-tight',
                                item.is_purchased ? 'text-gray-500 line-through' : 'text-gray-900'
                            ]"
                        >
                            {{ item.name }}
                        </h3>
                        <p
                            v-if="quantityDisplay"
                            :class="[
                                'text-sm mt-0.5',
                                item.is_purchased ? 'text-gray-400' : 'text-gray-500'
                            ]"
                        >
                            {{ quantityDisplay }}
                        </p>
                        <p
                            v-if="item.notes"
                            :class="[
                                'text-xs mt-1 italic',
                                item.is_purchased ? 'text-gray-400' : 'text-gray-400'
                            ]"
                        >
                            {{ item.notes }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border"
                            :style="{
                                backgroundColor: `${listColor}15`,
                                borderColor: `${listColor}40`,
                                color: listColor
                            }"
                        >
                            <span
                                class="w-1.5 h-1.5 rounded-full"
                                :style="{ backgroundColor: listColor }"
                            ></span>
                            {{ listName }}
                        </span>

                        <button
                            @click="handleEdit"
                            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                            title="Edit item"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
