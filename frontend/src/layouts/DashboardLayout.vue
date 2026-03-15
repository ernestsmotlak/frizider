<script setup lang="ts">
import {computed, onBeforeUnmount, onMounted, ref, watch} from "vue";
import {useRoute, useRouter} from "vue-router";
import BackButton from "../components/BackButton.vue";
import LogoComponent from "../components/LogoComponent.vue";

const route = useRoute();
const router = useRouter();

const isActionPickerOpen = ref(false);
const actionSearchQuery = ref('');
const actionPanelRef = ref<HTMLElement | null>(null);
const actionToggleRef = ref<HTMLElement | null>(null);

type DashboardAction = {
    id: string;
    label: string;
    description: string;
    route: string;
    featured: boolean;
    keywords: string[];
    icon: 'cart' | 'pot';
};

const dashboardActions: DashboardAction[] = [
    {
        id: 'shopping',
        label: 'Shopping',
        description: 'Check items and buy',
        route: '/shopping',
        featured: true,
        keywords: ['buy', 'store', 'market', 'groceries', 'items'],
        icon: 'cart',
    },
    {
        id: 'cooking',
        label: 'Cooking',
        description: 'Follow steps and cook',
        route: '/cooking',
        featured: true,
        keywords: ['cook', 'kitchen', 'steps', 'recipe'],
        icon: 'pot',
    },
];

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
    actionSearchQuery.value = '';
};

const toggleActionPicker = () => {
    isActionPickerOpen.value = !isActionPickerOpen.value;
};

const openAction = (action: DashboardAction) => {
    closeActionPicker();
    router.push(action.route);
};

const normalizedActionQuery = computed(() => actionSearchQuery.value.trim().toLowerCase());

const filteredActions = computed(() => {
    if (!normalizedActionQuery.value) return dashboardActions;

    return dashboardActions.filter((action) => {
        const haystack = `${action.label} ${action.description} ${action.keywords.join(' ')}`.toLowerCase();
        return haystack.includes(normalizedActionQuery.value);
    });
});

const featuredActions = computed(() => filteredActions.value.filter((action) => action.featured));

const handleGlobalPointerDown = (event: PointerEvent) => {
    if (!isActionPickerOpen.value) return;

    const target = event.target as Node | null;
    if (!target) return;

    const clickedInsidePanel = actionPanelRef.value?.contains(target);
    const clickedOnToggle = actionToggleRef.value?.contains(target);

    if (clickedInsidePanel || clickedOnToggle) return;

    closeActionPicker();
};

onMounted(() => {
    document.addEventListener('pointerdown', handleGlobalPointerDown, true);
});

onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', handleGlobalPointerDown, true);
});

watch(() => route.path, () => {
    closeActionPicker();
});
</script>

<template>
    <div
        class="dashboard-shell relative z-0 min-h-screen transition-transform duration-300 ease-out"
        :class="isActionPickerOpen ? 'scale-[0.985] -translate-y-1' : ''"
        style="padding-bottom: calc(5.75rem + env(safe-area-inset-bottom));"
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
            style="bottom: calc(5.5rem + env(safe-area-inset-bottom)); padding-bottom: max(0.5rem, env(safe-area-inset-bottom));"
        >
            <div class="mx-auto max-w-md">
                <div ref="actionPanelRef" class="action-panel rounded-2xl p-3">
                    <div class="action-panel__header px-1 pb-2">
                        <div class="action-panel__title-row">
                            <h3 class="action-panel__title">Actions</h3>
                            <span class="action-panel__count text-xs font-semibold">{{ filteredActions.length }} available</span>
                        </div>
                        <label for="action-search" class="sr-only">Search actions</label>
                        <input
                            id="action-search"
                            v-model="actionSearchQuery"
                            type="text"
                            placeholder="Search actions..."
                            class="action-panel__search mt-2 w-full rounded-xl px-3 py-2 text-sm"
                        />
                    </div>

                    <div class="action-panel__body">
                        <div v-if="featuredActions.length" class="grid grid-cols-2 gap-3">
                        <button
                            v-for="action in featuredActions"
                            :key="action.id"
                            type="button"
                            @click="openAction(action)"
                            class="action-choice group flex flex-col items-center justify-center gap-2 rounded-xl px-3 py-4 transition-all duration-200 hover:-translate-y-1 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                            :aria-label="`Open ${action.label}`"
                        >
                            <div class="action-choice__icon flex items-center justify-center w-14 h-14 rounded-2xl transition-all duration-200 group-hover:scale-110">
                                <svg
                                    v-if="action.icon === 'cart'"
                                    class="w-8 h-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17H18a2 2 0 0 0 2-1.6L21 8H6"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                                </svg>
                                <svg
                                    v-else
                                    class="w-8 h-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10V8a4 4 0 0 1 8 0v2"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 10h12v8a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-8z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h2"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12h2"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6h4"></path>
                                </svg>
                            </div>
                            <div class="action-choice__title text-sm font-semibold">{{ action.label }}</div>
                            <div class="action-choice__subtitle text-xs">{{ action.description }}</div>
                        </button>
                        </div>

                        <div class="action-list mt-3">
                            <div class="action-list__heading px-1 pb-1 text-xs font-semibold uppercase tracking-[0.08em]">
                                All actions
                            </div>
                            <button
                                v-for="action in filteredActions"
                                :key="`${action.id}-list`"
                                type="button"
                                class="action-list__item"
                                @click="openAction(action)"
                            >
                                <span class="action-list__label">{{ action.label }}</span>
                                <span class="action-list__description">{{ action.description }}</span>
                            </button>
                        </div>

                        <p v-if="!filteredActions.length" class="action-panel__empty text-center text-sm">
                            No actions found yet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <nav
        class="bottom-nav fixed bottom-0 left-0 right-0 z-20"
        style="padding-bottom: max(0.45rem, env(safe-area-inset-bottom));"
    >
        <div class="bottom-nav__inner mx-auto max-w-md px-3">
            <div class="bottom-nav__track">
            <router-link
                to="/grocery-lists"
                class="bottom-nav__item flex items-center justify-center gap-2 flex-1"
                :class="isGroceryList ? 'bottom-nav__item--active' : '' "
            >
                <div class="bottom-nav__icon" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 17h10"></path>
                    </svg>
                </div>
                <span class="bottom-nav__label text-xs font-semibold">Lists</span>
            </router-link>
            <button
                ref="actionToggleRef"
                type="button"
                @click="toggleActionPicker"
                class="bottom-nav__item bottom-nav__item--action group relative flex items-center justify-center gap-1 flex-1"
                :class="[
                    isIngredientsTab ? 'bottom-nav__item--active' : '',
                    isActionPickerOpen ? 'bottom-nav__item--open' : '',
                ]"
                aria-label="Open actions hub"
                :aria-expanded="isActionPickerOpen"
            >
                <span class="sr-only">Open actions hub</span>
                <div class="bottom-nav__action-orb transition-transform duration-200 group-hover:scale-105">
                    <svg
                        v-if="isActionPickerOpen"
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M6 6l12 12"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M18 6L6 18"></path>
                    </svg>
                    <svg
                        v-else
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 7h3v3H7z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 7h3v3h-3z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 14h3v3H7z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 14h3v3h-3z"></path>
                    </svg>
                </div>
                <span class="bottom-nav__label text-xs font-semibold">Actions</span>
                <span
                    v-if="isActionPickerOpen"
                    class="absolute -top-1.5 right-[30%] w-2.5 h-2.5 rounded-full bg-[var(--accent)] shadow-[0_0_0_5px_rgba(16,185,129,0.14)]"
                ></span>
            </button>
            <router-link
                to="/recipes"
                class="bottom-nav__item flex items-center justify-center gap-2 flex-1"
                :class="isRecipesTab ? 'bottom-nav__item--active' : '' "
            >
                <div class="bottom-nav__icon" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 16h4"></path>
                    </svg>
                </div>
                <span class="bottom-nav__label text-xs font-semibold">Recipes</span>
            </router-link>
        </div>
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
    display: flex;
    flex-direction: column;
    max-height: min(62vh, 32rem);
    border: 1px solid var(--line-soft);
    background: var(--surface-strong);
    box-shadow: var(--shadow-elevated);
    backdrop-filter: blur(12px);
    isolation: isolate;
    overflow: hidden;
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

.action-panel__header,
.action-panel__body {
    position: relative;
    z-index: 1;
}

.action-panel__title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.action-panel__title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-strong);
}

.action-panel__count {
    color: var(--text-muted);
}

.action-panel__search {
    border: 1px solid var(--line-soft);
    background: color-mix(in srgb, var(--surface-stronger) 85%, white 15%);
    color: var(--text-strong);
}

.action-panel__search::placeholder {
    color: color-mix(in srgb, var(--text-muted) 92%, white 8%);
}

.action-panel__search:focus {
    outline: none;
    border-color: color-mix(in srgb, var(--accent) 38%, white 62%);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent-soft) 75%, white 25%);
}

.action-panel__body {
    overflow-y: auto;
    padding: 0.15rem 0.1rem 0.25rem;
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

.action-list__heading {
    color: color-mix(in srgb, var(--text-muted) 90%, black 10%);
}

.action-list__item {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    border-radius: 0.8rem;
    border: 1px solid transparent;
    background: transparent;
    padding: 0.62rem 0.72rem;
    color: var(--text-strong);
    text-align: left;
    transition: background-color 0.16s ease, border-color 0.16s ease;
}

.action-list__item:hover {
    background: color-mix(in srgb, var(--accent-soft) 58%, white 42%);
    border-color: color-mix(in srgb, var(--accent) 26%, white 74%);
}

.action-list__label {
    font-size: 0.86rem;
    font-weight: 600;
}

.action-list__description {
    font-size: 0.75rem;
    color: var(--text-muted);
    white-space: nowrap;
}

.action-panel__empty {
    color: var(--text-muted);
    padding: 0.8rem 0.3rem;
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
    background: linear-gradient(180deg, rgba(240, 250, 244, 0.08) 0%, rgba(240, 250, 244, 0.64) 100%);
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.bottom-nav__track {
    position: relative;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    align-items: center;
    gap: 0.35rem;
    min-height: 4.8rem;
    border-radius: 1.6rem;
    padding: 0.45rem;
    border: 1px solid color-mix(in srgb, var(--line-soft) 85%, white 15%);
    background:
        linear-gradient(140deg, rgba(254, 255, 252, 0.96) 0%, rgba(238, 249, 242, 0.94) 100%);
    box-shadow:
        0 14px 36px rgba(6, 95, 70, 0.16),
        inset 0 1px 0 rgba(255, 255, 255, 0.86);
    backdrop-filter: blur(12px);
    pointer-events: auto;
}

.bottom-nav__item {
    position: relative;
    min-height: 3.9rem;
    border-radius: 1.2rem;
    padding: 0.4rem 0.55rem;
    color: var(--text-muted);
    transition: transform 0.2s ease, color 0.2s ease, background-color 0.2s ease;
}

.bottom-nav__item:hover {
    color: var(--accent);
    transform: translateY(-1px);
}

.bottom-nav__item--active {
    color: var(--accent-strong);
    background: linear-gradient(180deg, rgba(168, 244, 198, 0.45) 0%, rgba(222, 251, 233, 0.78) 100%);
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--accent) 35%, white 65%);
}

.bottom-nav__item--open {
    color: var(--accent-strong);
}

.bottom-nav__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.7rem;
    border: 1px solid color-mix(in srgb, var(--line-soft) 88%, white 12%);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.88) 0%, rgba(247, 252, 248, 0.88) 100%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.78);
}

.bottom-nav__action-orb {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.2rem;
    height: 2.2rem;
    border-radius: 9999px;
    background: linear-gradient(140deg, #2dc18f 0%, #229f75 100%);
    border: 1px solid color-mix(in srgb, var(--accent) 70%, white 30%);
    color: white;
    box-shadow:
        0 8px 16px rgba(6, 95, 70, 0.26),
        inset 0 1px 0 rgba(255, 255, 255, 0.4);
}

.bottom-nav__label {
    letter-spacing: 0.01em;
}

.bottom-nav__item--active .bottom-nav__icon,
.bottom-nav__item--open .bottom-nav__icon {
    border-color: color-mix(in srgb, var(--accent) 30%, white 70%);
    background: color-mix(in srgb, var(--accent-soft) 56%, white 44%);
}

.bottom-nav__item--active .bottom-nav__action-orb,
.bottom-nav__item--open .bottom-nav__action-orb {
    background: linear-gradient(140deg, #22b383 0%, #1f8f69 100%);
}

@media (max-width: 420px) {
    .bottom-nav__item {
        min-height: 3.65rem;
        padding-inline: 0.35rem;
    }

    .bottom-nav__label {
        font-size: 0.68rem;
    }

    .bottom-nav__icon {
        width: 1.85rem;
        height: 1.85rem;
    }

    .bottom-nav__action-orb {
        width: 2rem;
        height: 2rem;
    }
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
