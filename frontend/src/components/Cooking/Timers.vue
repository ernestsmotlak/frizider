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
    <div class="timers-panel">
        Timers here!!!
        <br />
        <div>{{ timers }}</div>
    </div>
</template>
<style lang="css" scoped>
.timers-panel {
    z-index: 10;
    width: 100%;
    height: 100%;
    box-sizing: border-box;
    overflow: auto;
    background: rgba(254, 202, 202, 0.7);
}
</style>
