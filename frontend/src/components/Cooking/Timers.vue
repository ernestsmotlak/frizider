<script setup lang="ts">
export interface Timers {
    cooking_session_id: number | null;
    started_at: string | null;
    duration_seconds: number;
    note: string | null;
    status: string | null;
    sort_order: number | null;
    completed_at: string | null;
    paused_at: string | null;
    remaining_seconds_at_pause: number | null;
}

const props = defineProps<{
    timers: Timers[];
}>();
</script>
<template>
    Timers here!!!
    <br />
    Hello
</template>
<style lang="css" scoped></style>
