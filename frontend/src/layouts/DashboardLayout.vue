<script setup lang="ts">
import {computed, onMounted, ref, watch} from "vue";
import {useRoute, useRouter} from "vue-router";
import BackButton from "../components/BackButton.vue";
import LogoComponent from "../components/LogoComponent.vue";
import AiGenerationPill from "../components/AiGenerationPill.vue";
import ActionSheet, {type SheetAction} from "../components/ActionSheet.vue";
import {useAiGenerationStore} from "../stores/aiGeneration.ts";
import {useSelectionDockStore} from "../stores/selectionDock.ts";

const route = useRoute();
const router = useRouter();
const aiGenerationStore = useAiGenerationStore();
const selectionDock = useSelectionDockStore();

const isActionPickerOpen = ref(false);

/** A sheet action that navigates somewhere. */
type DashboardAction = SheetAction & {
    route: string;
};

const dashboardActions: DashboardAction[] = [
    {
        id: 'shopping',
        label: 'Shopping',
        description: 'Check items and buy',
        route: '/shopping',
        featured: true,
        icon: 'cart',
    },
    {
        id: 'cooking',
        label: 'Cooking',
        description: 'Follow steps and cook',
        route: '/cooking',
        featured: true,
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

const isPantryTab = computed(() => {
    if (route.path.startsWith('/storage-spaces')) return true;
    if (route.path.includes('/storage-space')) return true;
    if (route.path.startsWith('/pantry/')) return true;
    return false;
});

const isProfileTab = computed(() => route.path.startsWith('/profile'));

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

const openAction = (action: SheetAction) => {
    closeActionPicker();

    const target = dashboardActions.find((candidate) => candidate.id === action.id);
    if (target) {
        router.push(target.route);
    }
};

onMounted(() => {
    // Once per app load: pick up any generation left running (or finished
    // unseen) before a refresh.
    aiGenerationStore.bootCheck();
});

watch(() => route.path, () => {
    closeActionPicker();
});

// A card entering select mode swaps the nav for its own dock; a picker left
// open would sit above a bar that no longer exists.
watch(() => selectionDock.active, (active) => {
    if (active) {
        closeActionPicker();
    }
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

    <ActionSheet
        :is-open="isActionPickerOpen"
        title="Actions"
        :actions="dashboardActions"
        @close="closeActionPicker"
        @select="openAction"
    />

    <AiGenerationPill/>

    <Transition name="dock-swap">
    <nav
        v-if="!selectionDock.active"
        class="bottom-nav dock-shell"
        style="padding-bottom: max(0.45rem, env(safe-area-inset-bottom));"
    >
        <div class="bottom-nav__inner mx-auto max-w-md px-3">
            <div class="dock-track bottom-nav__track">
            <router-link
                to="/grocery-lists"
                class="bottom-nav__item flex flex-col items-center justify-center gap-1 flex-1"
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
                type="button"
                @click="toggleActionPicker"
                class="bottom-nav__item bottom-nav__item--action group relative flex flex-col items-center justify-center gap-1 flex-1"
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
                class="bottom-nav__item flex flex-col items-center justify-center gap-1 flex-1"
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
            <router-link
                to="/storage-spaces"
                class="bottom-nav__item flex flex-col items-center justify-center gap-1 flex-1"
                :class="isPantryTab ? 'bottom-nav__item--active' : '' "
            >
                <div class="bottom-nav__icon" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l1-3h16l1 3"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11h6"></path>
                    </svg>
                </div>
                <span class="bottom-nav__label text-xs font-semibold">Pantry</span>
            </router-link>
            <router-link
                to="/profile"
                class="bottom-nav__item flex flex-col items-center justify-center gap-1 flex-1"
                :class="isProfileTab ? 'bottom-nav__item--active' : '' "
            >
                <div class="bottom-nav__icon" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 20a7 7 0 0 1 14 0"></path>
                    </svg>
                </div>
                <span class="bottom-nav__label text-xs font-semibold">Profile</span>
            </router-link>
        </div>
        </div>
    </nav>
    </Transition>
</template>

<style scoped>
/* Only what is genuinely this layout's own lives here. The bottom nav, the
   selection dock and the action sheet share their styling from main.css —
   scoped rules cannot be shared, and three copies would drift apart. */

.dashboard-shell {
    background: linear-gradient(180deg, #f8fffb 0%, #eff9f2 52%, #e7f4eb 100%);
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
</style>
