<script setup lang="ts">
const props = defineProps<{
    groceryListData: {
        type: Object,
        required: true,
    }
}>()
</script>

<template>
    Here GL main!
    <pre class="bg-red-200">{{groceryListData}}</pre>
</template>

<style scoped>

</style>
