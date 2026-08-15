<script setup lang="ts">
/**
 * The icon set the action sheet and the docks draw from. Actions are described
 * as data (see SheetAction), so they carry an icon *name* rather than markup —
 * this is where a name becomes a path. An unknown name falls back to the dots
 * glyph, so an action can ship before its icon does.
 */
export type ActionIconName =
    | 'cart'
    | 'pot'
    | 'move'
    | 'sparkles'
    | 'trash'
    | 'list'
    | 'check'
    | 'close'
    | 'dots';

defineProps<{
    name?: ActionIconName | string;
}>();
</script>

<template>
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <template v-if="name === 'cart'">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17H18a2 2 0 0 0 2-1.6L21 8H6"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
        </template>

        <template v-else-if="name === 'pot'">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10V8a4 4 0 0 1 8 0v2"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 10h12v8a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-8z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h2"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12h2"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6h4"></path>
        </template>

        <template v-else-if="name === 'move'">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
        </template>

        <template v-else-if="name === 'sparkles'">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"></path>
        </template>

        <template v-else-if="name === 'trash'">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4a1 1 0 0 1 1 1v2H9V5a1 1 0 0 1 1-1z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 11v6"></path>
        </template>

        <template v-else-if="name === 'list'">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 17h10"></path>
        </template>

        <template v-else-if="name === 'check'">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </template>

        <template v-else-if="name === 'close'">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 6l12 12"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M18 6L6 18"></path>
        </template>

        <template v-else>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M6 12h.01"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 12h.01"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M18 12h.01"></path>
        </template>
    </svg>
</template>
