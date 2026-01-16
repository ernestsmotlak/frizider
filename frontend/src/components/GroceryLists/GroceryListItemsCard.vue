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
</script>

<template>
    <pre class="bg-teal-200">{{groceryListItems}}</pre>
</template>

<style scoped>

</style>
