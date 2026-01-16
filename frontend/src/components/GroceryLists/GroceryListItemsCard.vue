<script setup lang="ts">

export interface GroceryListItem {
    id: number;
    grocery_list_id: number;
    pantry_item_id: number | null;
    name: string;
    quantity: number | null;
    unit: string | null;
    notes: string | null;
    sort_order: number;
    is_purchased: boolean;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
}

const props = defineProps<{
    groceryListItems: GroceryListItem[]
}>()

const formatItem = (item: GroceryListItem): string => {
    const parts: string[] = [];

    if (item.quantity !== null) {
        parts.push(item.quantity.toString());
    }

    if (item.unit) {
        parts.push(item.unit);
    }

    parts.push(item.name);

    if (item.notes) {
        parts.push(`(${item.notes})`);
    }

    return parts.join(" ");
}

const sortedItems = (items: GroceryListItem[]): GroceryListItem[] => {
    return [...items].sort((a, b) => {
        if (a.sort_order !== b.sort_order) {
            return a.sort_order - b.sort_order;
        }
        return a.id - b.id;
    });
}
</script>

<template>
    <div class="bg-white rounded-2xl shadow-xl p-8 relative border-2 border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Items</h2>
        <ul v-if="groceryListItems && groceryListItems.length > 0" class="space-y-2.5">
            <li v-for="(item, index) in sortedItems(groceryListItems)"
                :key="item.id ?? `item-${index}`"
                class="grocery-item flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-blue-200 transition-all duration-250"
                :class="{ 'opacity-60 line-through': item.is_purchased }">
                <div class="flex items-center gap-3 flex-1">
                    <svg v-if="item.is_purchased" class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                    <svg v-else class="w-2.5 h-2.5 text-gray-600 flex-shrink-0 mt-0.5" fill="currentColor"
                         viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3"/>
                    </svg>
                    <span class="text-gray-800 leading-relaxed text-[15px] font-medium">{{
                            formatItem(item)
                        }}</span>
                </div>
            </li>
        </ul>
        <div v-else class="text-center py-8">
            <p class="text-gray-500 mb-4">No items yet. Add items to your shopping list.</p>
        </div>
    </div>
</template>

<style scoped>

</style>
