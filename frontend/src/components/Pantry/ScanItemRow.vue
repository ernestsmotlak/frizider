<script setup lang="ts">
/**
 * One suggested pantry item, on its way in or out.
 *
 * The row stays a single line on purpose: a scan carries no quantity, unit or
 * expiry, so a name and two controls is the whole of it — which is what keeps a
 * dozen items on one phone screen instead of three.
 */
export interface ScanItem {
    /** Client-side identity. Names repeat and get edited; this does not. */
    key: string;
    name: string;
    spaceId: number | null;
    notes: string | null;
}

defineProps<{
    item: ScanItem;
    /** Unassigned rows are the ones the user is actually being asked about. */
    needsSpace: boolean;
}>();

const emit = defineEmits<{
    rename: [value: string];
    move: [];
    remove: [];
}>();
</script>

<template>
    <div
        class="flex items-center gap-1 py-1.5 border-b last:border-b-0 transition-colors"
        :class="needsSpace ? 'border-amber-100' : 'border-gray-100'"
    >
        <div class="flex-1 min-w-0">
            <input
                :value="item.name"
                @input="emit('rename', ($event.target as HTMLInputElement).value)"
                type="text"
                enterkeyhint="done"
                :aria-label="`Name of ${item.name || 'this item'}`"
                :placeholder="item.name ? '' : 'Name this item'"
                class="w-full bg-transparent px-1 py-1.5 text-base text-gray-900 rounded-md border border-transparent hover:border-gray-200 focus:border-violet-300 focus:bg-white focus:ring-2 focus:ring-violet-100 outline-none transition-colors"
            />
            <p v-if="item.notes" class="px-1 pb-0.5 text-xs text-gray-400 truncate">{{ item.notes }}</p>
        </div>

        <button
            @click="emit('move')"
            class="shrink-0 w-9 h-9 flex items-center justify-center rounded-lg text-gray-400 hover:text-violet-700 hover:bg-violet-50 active:scale-95 transition-all"
            :aria-label="`Move ${item.name || 'item'} to another space`"
            :title="needsSpace ? 'Pick a space' : 'Move to another space'"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h11"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4l3 3-3 3"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 17H9"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l-3 3 3 3"></path>
            </svg>
        </button>

        <button
            @click="emit('remove')"
            class="shrink-0 w-9 h-9 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-600 hover:bg-red-50 active:scale-95 transition-all"
            :aria-label="`Remove ${item.name || 'item'}`"
            title="Remove"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</template>
