<script setup lang="ts">
import {computed, ref, watch} from "vue";
import {useRoute, useRouter} from "vue-router";
import BackButton from "../components/BackButton.vue";
import LogoComponent from "../components/LogoComponent.vue";

const route = useRoute();
const router = useRouter();

const isActionPickerOpen = ref(false);

const isRecipesTab = computed(() => {
    if (route.path.startsWith('/recipes')) return true;
    if (route.path.includes('/recipe')) return true;
    return false;
});

const isGroceryList = computed(() => {
    if (route.path.startsWith('/grocery-lists')) return true;
    if (route.path.includes('/grocery-list')) return true;
    return false;
});

const isIngredientsTab = computed(() => {
    if (route.path.startsWith('/ingredients')) return true;
    if (route.path.includes('/ingredient')) return true;
    if (route.path.startsWith('/shopping')) return true;
    if (route.path.startsWith('/cooking')) return true;
    return false;
});

const closeActionPicker = () => {
    isActionPickerOpen.value = false;
};

const toggleActionPicker = () => {
    isActionPickerOpen.value = !isActionPickerOpen.value;
};

const goToShopping = () => {
    closeActionPicker();
    router.push('/shopping');
};

const goToCooking = () => {
    closeActionPicker();
    router.push('/cooking');
};

watch(() => route.path, () => {
    closeActionPicker();
});
</script>

<template>
    <div
        class="dashboard-shell relative z-0 min-h-screen transition-transform duration-300 ease-out"
        :class="isActionPickerOpen ? 'scale-[0.985] -translate-y-1' : ''"
        style="padding-bottom: calc(4rem + env(safe-area-inset-bottom));"
    >
        <div class="mesh-background" aria-hidden="true">
            <div class="mesh-background__blob mesh-background__blob--sage"></div>
            <div class="mesh-background__blob mesh-background__blob--mint"></div>
            <div class="mesh-background__blob mesh-background__blob--peach"></div>
            <div class="mesh-background__blob mesh-background__blob--lemon"></div>
            <div class="mesh-background__grain"></div>
        </div>

        <div class="relative z-10 max-w-md mx-auto flex flex-row justify-between px-5 pt-6">
            <div class="">
                <BackButton/>
            </div>
            <div class="">
                <LogoComponent/>
            </div>
        </div>
        <div class="relative z-10 max-w-md mx-auto min-h-screen">
            <slot/>
        </div>
    </div>

    <Transition name="action-picker-backdrop">
        <div
            v-if="isActionPickerOpen"
            class="action-picker-backdrop fixed inset-0 z-40"
            @click="closeActionPicker"
        ></div>
    </Transition>

    <Transition name="action-picker-panel">
        <div
            v-if="isActionPickerOpen"
            class="fixed left-0 right-0 z-50 px-4"
            style="bottom: calc(4rem + env(safe-area-inset-bottom)); padding-bottom: max(0.5rem, env(safe-area-inset-bottom));"
        >
            <div class="mx-auto max-w-md">
                <div class="action-panel rounded-2xl p-3">
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            @click="goToShopping"
                            class="action-choice group flex flex-col items-center justify-center gap-2 rounded-xl px-3 py-4 transition-all duration-200 hover:-translate-y-1 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                            aria-label="Go shopping"
                        >
                            <div class="action-choice__icon flex items-center justify-center w-14 h-14 rounded-2xl transition-all duration-200 group-hover:scale-110">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17H18a2 2 0 0 0 2-1.6L21 8H6"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                                </svg>
                            </div>
                            <div class="action-choice__title text-sm font-semibold">Shopping</div>
                            <div class="action-choice__subtitle text-xs">Check items and buy</div>
                        </button>

                        <button
                            type="button"
                            @click="goToCooking"
                            class="action-choice group flex flex-col items-center justify-center gap-2 rounded-xl px-3 py-4 transition-all duration-200 hover:-translate-y-1 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                            aria-label="Start cooking"
                        >
                            <div class="action-choice__icon flex items-center justify-center w-14 h-14 rounded-2xl transition-all duration-200 group-hover:scale-110">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10V8a4 4 0 0 1 8 0v2"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 10h12v8a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-8z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h2"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12h2"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6h4"></path>
                                </svg>
                            </div>
                            <div class="action-choice__title text-sm font-semibold">Cooking</div>
                            <div class="action-choice__subtitle text-xs">Follow steps and cook</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <nav
        class="bottom-nav fixed bottom-0 left-0 right-0 z-20"
        style="padding-bottom: env(safe-area-inset-bottom);"
    >
        <div class="flex justify-around items-center h-16">
            <router-link
                to="/grocery-lists"
                class="bottom-nav__item flex flex-col items-center justify-center flex-1 h-full transition-colors"
                :class="isGroceryList ? 'bottom-nav__item--active' : '' "
            >
                <span class="text-sm font-medium">Shopping lists</span>
            </router-link>
            <button
                type="button"
                @click="toggleActionPicker"
                class="bottom-nav__item bottom-nav__item--action group relative flex flex-col items-center justify-center flex-1 h-full transition-colors"
                :class="[
                    isIngredientsTab ? 'bottom-nav__item--active' : '',
                    isActionPickerOpen ? 'bottom-nav__item--open' : '',
                ]"
                aria-label="Open shopping and cooking"
                :aria-expanded="isActionPickerOpen"
            >
                <span class="sr-only">Go shopping or start cooking</span>
                <div class="flex items-center gap-2">
                    <div class="bottom-nav__mini-icon flex items-center justify-center w-9 h-9 rounded-xl transition-transform duration-200 group-hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17H18a2 2 0 0 0 2-1.6L21 8H6"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                        </svg>
                    </div>
                    <div class="bottom-nav__mini-icon flex items-center justify-center w-9 h-9 rounded-xl transition-transform duration-200 group-hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10V8a4 4 0 0 1 8 0v2"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 10h12v8a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-8z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h2"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12h2"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6h4"></path>
                        </svg>
                    </div>
                </div>
                <span
                    v-if="isActionPickerOpen"
                    class="absolute -top-1 w-2 h-2 rounded-full bg-[var(--accent)] shadow-[0_0_0_4px_rgba(16,185,129,0.12)]"
                ></span>
            </button>
            <router-link
                to="/recipes"
                class="bottom-nav__item flex flex-col items-center justify-center flex-1 h-full transition-colors"
                :class="isRecipesTab ? 'bottom-nav__item--active' : '' "
            >
                <span class="text-sm font-medium">Recipes</span>
            </router-link>
        </div>
    </nav>
</template>

<style scoped>
.dashboard-shell {
    background: linear-gradient(180deg, #f8fffb 0%, #eff9f2 52%, #e7f4eb 100%);
}

.action-picker-backdrop {
    background: rgba(6, 44, 33, 0.18);
    backdrop-filter: blur(3px);
}

.action-panel {
    position: relative;
    border: 1px solid var(--line-soft);
    background: var(--surface-strong);
    box-shadow: var(--shadow-elevated);
    backdrop-filter: blur(12px);
    isolation: isolate;
}

.action-panel::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    opacity: 0.06;
    background-image: var(--panel-grain);
    background-size: 4px 4px;
    pointer-events: none;
}

.action-choice {
    border: 1px solid var(--line-soft);
    background: linear-gradient(180deg, var(--surface-stronger) 0%, var(--surface) 100%);
    box-shadow: 0 8px 18px rgba(7, 82, 58, 0.08);
    color: var(--text-strong);
}

.action-choice:hover {
    border-color: var(--line-strong);
    box-shadow: var(--shadow-soft);
}

.action-choice:focus-visible {
    --tw-ring-color: color-mix(in srgb, var(--accent), white 10%);
}

.action-choice__icon {
    background: rgba(251, 255, 251, 0.8);
    border: 1px solid color-mix(in srgb, var(--accent) 22%, white 78%);
    color: var(--accent-strong);
    box-shadow: 0 7px 16px rgba(6, 95, 70, 0.12);
}

.action-choice:hover .action-choice__icon {
    border-color: color-mix(in srgb, var(--accent) 35%, white 65%);
}

.action-choice__title {
    color: var(--text-strong);
}

.action-choice__subtitle {
    color: var(--text-muted);
}

.mesh-background {
    position: fixed;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
}

.mesh-background__blob {
    position: absolute;
    width: min(64vw, 560px);
    height: min(64vw, 560px);
    border-radius: 9999px;
    filter: blur(55px);
    opacity: 0.44;
    mix-blend-mode: multiply;
}

.mesh-background__blob--sage {
    left: -22%;
    top: -14%;
    background: #8ed8a4;
    animation: mesh-drift-sage 26s ease-in-out infinite alternate;
}

.mesh-background__blob--mint {
    right: -24%;
    top: 20%;
    background: #7ad8bc;
    animation: mesh-drift-mint 30s ease-in-out infinite alternate;
}

.mesh-background__blob--peach {
    left: 12%;
    bottom: -30%;
    background: #ffc79f;
    animation: mesh-drift-peach 34s ease-in-out infinite alternate;
}

.mesh-background__blob--lemon {
    right: 2%;
    bottom: -24%;
    background: #ffea98;
    animation: mesh-drift-lemon 28s ease-in-out infinite alternate;
}

.mesh-background__grain {
    position: absolute;
    inset: -50%;
    opacity: 0.08;
    background-image: radial-gradient(circle at 2px 2px, rgba(4, 120, 87, 0.45) 1.1px, transparent 0);
    background-size: 4px 4px;
    animation: mesh-grain 8s steps(2, end) infinite;
}

.bottom-nav {
    border-top: 1px solid var(--line-soft);
    background: color-mix(in srgb, var(--surface-strong) 94%, white 6%);
    box-shadow: 0 -8px 24px rgba(7, 82, 58, 0.1);
    backdrop-filter: blur(10px);
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    isolation: isolate;
}

.bottom-nav::before {
    content: "";
    position: absolute;
    inset: 0;
    opacity: 0.05;
    background-image: var(--panel-grain);
    background-size: 4px 4px;
    pointer-events: none;
}

.bottom-nav__item {
    color: var(--text-muted);
}

.bottom-nav__item:hover {
    color: var(--accent);
}

.bottom-nav__item--active {
    color: var(--accent-strong);
    background: color-mix(in srgb, var(--accent-soft) 72%, white 28%);
    border-top: 2px solid var(--accent);
}

.bottom-nav__item--open {
    color: var(--accent-strong);
}

.bottom-nav__mini-icon {
    background: rgba(251, 255, 251, 0.72);
    border: 1px solid var(--line-soft);
    box-shadow: 0 6px 14px rgba(7, 82, 58, 0.1);
}

.bottom-nav__item--active .bottom-nav__mini-icon,
.bottom-nav__item--open .bottom-nav__mini-icon {
    border-color: color-mix(in srgb, var(--accent) 30%, white 70%);
    background: color-mix(in srgb, var(--accent-soft) 52%, white 48%);
}

@keyframes mesh-drift-sage {
    0% { transform: translate3d(0, 0, 0) scale(1); }
    100% { transform: translate3d(46px, 28px, 0) scale(1.08); }
}

@keyframes mesh-drift-mint {
    0% { transform: translate3d(0, 0, 0) scale(1); }
    100% { transform: translate3d(-52px, 40px, 0) scale(1.1); }
}

@keyframes mesh-drift-peach {
    0% { transform: translate3d(0, 0, 0) scale(1); }
    100% { transform: translate3d(36px, -48px, 0) scale(1.06); }
}

@keyframes mesh-drift-lemon {
    0% { transform: translate3d(0, 0, 0) scale(1); }
    100% { transform: translate3d(-38px, -34px, 0) scale(1.08); }
}

@keyframes mesh-grain {
    0% { transform: translate(0, 0); }
    50% { transform: translate(-2%, 1%); }
    100% { transform: translate(1%, -1%); }
}

@media (prefers-reduced-motion: reduce) {
    .mesh-background__blob,
    .mesh-background__grain {
        animation: none;
    }
}

.action-picker-backdrop-enter-active,
.action-picker-backdrop-leave-active {
    transition: opacity 0.2s ease;
}

.action-picker-backdrop-enter-from,
.action-picker-backdrop-leave-to {
    opacity: 0;
}

.action-picker-panel-enter-active {
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.28s ease;
}

.action-picker-panel-leave-active {
    transition: transform 0.18s ease, opacity 0.18s ease;
}

.action-picker-panel-enter-from,
.action-picker-panel-leave-to {
    opacity: 0;
    transform: translateY(14px);
}
</style>
