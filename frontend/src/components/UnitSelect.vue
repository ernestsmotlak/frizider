<script setup lang="ts">
import { computed } from "vue";
import { UNITS } from "../utils/units";

const model = defineModel<string | null>({
    get: (value) => (value === "" ? null : value),
});

// A pre-enum unit ("kos", "tablespoons"...) still on the row: shown but not selectable,
// so the form displays it until the user picks an allowed unit.
const legacyValue = computed(() => {
    const value = model.value;
    return value && !UNITS.includes(value) ? value : null;
});
</script>

<template>
    <select v-model="model">
        <option :value="null">—</option>
        <option v-if="legacyValue" :value="legacyValue" disabled>{{ legacyValue }} (old)</option>
        <option v-for="unit in UNITS" :key="unit" :value="unit">{{ unit }}</option>
    </select>
</template>
