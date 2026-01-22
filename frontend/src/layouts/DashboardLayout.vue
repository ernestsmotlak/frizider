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
        class="min-h-screen bg-green-100 pb-16 transition-transform duration-300 ease-out"
        :class="isActionPickerOpen ? 'scale-[0.985] -translate-y-1' : ''"
    >
        <div class="max-w-md mx-auto flex flex-row justify-between px-5 pt-6">
            <div class="">
                <BackButton/>
            </div>
            <div class="">
                <LogoComponent/>
            </div>
        </div>
        <div class="max-w-md mx-auto min-h-screen">
            <slot/>
        </div>
    </div>

    <Transition name="action-picker-backdrop">
        <div
            v-if="isActionPickerOpen"
            class="fixed inset-0 z-40 bg-black/20 backdrop-blur-[1px]"
            @click="closeActionPicker"
        ></div>
    </Transition>

    <Transition name="action-picker-panel">
        <div
            v-if="isActionPickerOpen"
            class="fixed left-0 right-0 bottom-16 z-50 px-4 pb-3"
        >
            <div class="mx-auto max-w-md">
                <div class="rounded-2xl border border-gray-200 bg-white/95 shadow-2xl ring-1 ring-black/5 p-3">
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            @click="goToShopping"
                            class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-b from-white to-green-50 px-3 py-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:border-green-300 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2"
                            aria-label="Go shopping"
                        >
                            <div class="dashboard_layout__pulse flex items-center justify-center w-14 h-14 rounded-2xl bg-white shadow-sm ring-1 ring-green-200 text-green-700 transition-all duration-200 group-hover:scale-110 group-hover:ring-green-300 group-hover:shadow-md">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17H18a2 2 0 0 0 2-1.6L21 8H6"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                                </svg>
                            </div>
                            <div class="text-sm font-semibold text-gray-900">Shopping</div>
                            <div class="text-xs text-gray-500">Check items and buy</div>
                        </button>

                        <button
                            type="button"
                            @click="goToCooking"
                            class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-b from-white to-green-50 px-3 py-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:border-green-300 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2"
                            aria-label="Start cooking"
                        >
                            <div class="dashboard_layout__pulse dashboard_layout__pulse_delay flex items-center justify-center w-14 h-14 rounded-2xl bg-white shadow-sm ring-1 ring-green-200 text-green-700 transition-all duration-200 group-hover:scale-110 group-hover:ring-green-300 group-hover:shadow-md">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10V8a4 4 0 0 1 8 0v2"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 10h12v8a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-8z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h2"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12h2"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6h4"></path>
                                </svg>
                            </div>
                            <div class="text-sm font-semibold text-gray-900">Cooking</div>
                            <div class="text-xs text-gray-500">Follow steps and cook</div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg">
        <div class="flex justify-around items-center h-16">
            <router-link
                to="/grocery-lists"
                class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-green-600 transition-colors"
                :class="isGroceryList ? '!text-green-600 !bg-green-50 border-t-2 border-green-600' : 'text-black' "
            >
                <span class="text-sm font-medium">Shopping lists</span>
            </router-link>
            <button
                type="button"
                @click="toggleActionPicker"
                class="group relative flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-green-600 transition-colors"
                :class="[
                    isIngredientsTab ? '!text-green-600 !bg-green-50 border-t-2 border-green-600' : 'text-black',
                    isActionPickerOpen ? 'text-green-700' : '',
                ]"
                aria-label="Open shopping and cooking"
                :aria-expanded="isActionPickerOpen"
            >
                <span class="sr-only">Go shopping or start cooking</span>
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-white/70 ring-1 ring-gray-200 shadow-sm transition-transform duration-200 group-hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17H18a2 2 0 0 0 2-1.6L21 8H6"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                        </svg>
                    </div>
                    <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-white/70 ring-1 ring-gray-200 shadow-sm transition-transform duration-200 group-hover:scale-105">
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
                    class="absolute -top-1 w-2 h-2 rounded-full bg-green-600 shadow"
                ></span>
            </button>
            <router-link
                to="/recipes"
                class="flex flex-col items-center justify-center flex-1 h-full text-gray-600 hover:text-green-600 transition-colors"
                :class="isRecipesTab ? '!text-green-600 !bg-green-50 border-t-2 border-green-600' : 'text-black' "
            >
                <span class="text-sm font-medium">Recipes</span>
            </router-link>
        </div>
    </nav>
</template>

<style scoped>
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

.dashboard_layout__pulse {
    animation: dashboard_layout_pulse 1.35s ease-in-out infinite;
}

.dashboard_layout__pulse_delay {
    animation-delay: 0.18s;
}

@keyframes dashboard_layout_pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.08);
    }
    50% {
        transform: scale(1.04);
        box-shadow: 0 10px 26px rgba(16, 185, 129, 0.16);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.08);
    }
}

@media (prefers-reduced-motion: reduce) {
    .dashboard_layout__pulse,
    .dashboard_layout__pulse_delay {
        animation: none;
    }
}
</style>

