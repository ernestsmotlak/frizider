<script setup lang="ts">
/**
 * The fridge mark, and — since AiJobsBadge is the only thing that renders it —
 * the surface that carries AI job state.
 *
 * The corner badge alone was too quiet: 16px of spinner on a white tile is
 * easy to walk straight past, and a scan takes long enough that a user who
 * misses it assumes nothing happened. Tinting the whole mark is what makes
 * "something is going on" readable from across the screen.
 */
withDefaults(defineProps<{
    tone?: 'none' | 'running' | 'ready' | 'failed';
}>(), {
    tone: 'none',
});
</script>

<template>
    <div
        class="logo-mark inline-flex items-center text-sm font-medium p-1 rounded-md border-1 shadow-sm transition-colors duration-300 active:scale-95"
        :class="{
            'text-gray-700 bg-white border-gray-200 hover:bg-green-50 hover:border-green-300 hover:text-green-700': tone === 'none',
            'bg-violet-100 border-violet-400 logo-mark--running': tone === 'running',
            'bg-emerald-100 border-emerald-500': tone === 'ready',
            'bg-red-100 border-red-500': tone === 'failed',
        }"
    >
        <img
            src="/fridge_icon.png"
            alt="Fridge icon"
            class="w-8 h-8"
        />
    </div>
</template>

<style scoped>
img {
    display: block;
}

/*
 * A ring that breathes outward rather than a flashing fill — visible in
 * peripheral vision, but it sits in a header the user is not looking at, so
 * it must not demand attention the way the pill deliberately does.
 */
.logo-mark--running {
    animation: logo-mark-pulse 1.8s ease-in-out infinite;
}

@keyframes logo-mark-pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(124, 58, 237, 0.4);
    }
    50% {
        box-shadow: 0 0 0 7px rgba(124, 58, 237, 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .logo-mark--running {
        animation: none;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.25);
    }
}
</style>
