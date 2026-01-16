<script setup lang="ts">

interface groceryListData {
    id: number;
    user_id: number;
    name: string | null;
    notes: string | null;
    completed_at: string | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
    grocery_list_data: [];
}

const props = defineProps<{
    groceryListData: {
        type: groceryListData,
        required: true,
    }
}>()
</script>

<template>
    Here GL main!
    <pre class="bg-red-200">{{props.groceryListData}}</pre>
</template>

<style scoped>

</style>
