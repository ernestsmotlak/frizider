<script setup lang="ts">
import {onBeforeUnmount, onMounted, ref} from "vue";
import {useRouter} from "vue-router";
import { useToastStore } from "../../stores/toast";
import { useLoadingStore } from "../../stores/loading";

export interface RecipeCookingModalRecipe {
    id: number;
    name: string;
}

const props = defineProps<{
    isOpen: boolean;
    recipe: RecipeCookingModalRecipe | null;
}>();

const emit = defineEmits<{
    close: [];
}>();

const toasterStore = useToastStore();
const loadingStore = useLoadingStore();
const router = useRouter();
const modalPanelRef = ref<HTMLElement | null>(null);

const handleGoCooking = () => {
    if (!props.recipe?.id) {
        toasterStore.show('error', "Error occurred when creating cooking session.");
        return;
    }

    loadingStore.start();

    const url = '/api/create-cooking-session';
    const payload = {
        recipe_id: props.recipe.id
    };

    axios
        .post(url, payload)
        .then(() => {
            toasterStore.show("success", "Cooking session created.");
            router.push("/cooking");
            handleClose();
        })
        .catch(() => {
            toasterStore.show('error', "A problem occurred when creating a cooking session.");
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const handleClose = () => {
    emit("close");
};

const handleGlobalPointerDown = (event: PointerEvent) => {
    if (!props.isOpen) return;

    const target = event.target as Node | null;
    if (!target) return;

    if (modalPanelRef.value?.contains(target)) return;

    handleClose();
};

onMounted(() => {
    document.addEventListener('pointerdown', handleGlobalPointerDown, true);
});

onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', handleGlobalPointerDown, true);
});
</script>

<template>
    <Teleport to="body">
        <Transition name="action-picker-backdrop">
            <div
                v-if="isOpen"
                class="action-picker-backdrop fixed inset-0 z-50"
                @click="handleClose"
            ></div>
        </Transition>

        <Transition name="action-picker-panel" appear>
            <div
                v-if="isOpen"
                class="fixed left-0 right-0 z-[60] px-4"
                style="bottom: calc(5.5rem + env(safe-area-inset-bottom)); padding-bottom: max(0.5rem, env(safe-area-inset-bottom));"
            >
                <div class="mx-auto max-w-md">
                    <div ref="modalPanelRef" class="action-panel rounded-2xl p-3" @click.stop>
                        <div class="action-panel__header px-1 pb-2">
                            <div class="action-panel__title-row">
                                <h2 class="action-panel__title">Recipe Actions</h2>
                                <button
                                    type="button"
                                    @click="handleClose"
                                    class="action-panel__close"
                                    aria-label="Close"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="recipe" class="action-panel__context text-xs">Recipe: {{ recipe.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="handleGoCooking"
                                class="action-choice group flex flex-col items-center justify-center gap-2 rounded-xl px-3 py-4 transition-all duration-200 hover:-translate-y-1 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                                aria-label="Go cooking"
                            >
                                <div class="action-choice__icon flex items-center justify-center w-14 h-14 rounded-2xl transition-all duration-200 group-hover:scale-110">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                                    </svg>
                                </div>
                                <div class="action-choice__title text-sm font-semibold">Go cooking</div>
                                <div class="action-choice__subtitle text-xs">Start a cooking session</div>
                            </button>

                            <button
                                type="button"
                                @click="handleClose"
                                class="action-choice action-choice--muted group flex flex-col items-center justify-center gap-2 rounded-xl px-3 py-4 transition-all duration-200 hover:-translate-y-1 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                                aria-label="Close"
                            >
                                <div class="action-choice__icon action-choice__icon--muted flex items-center justify-center w-14 h-14 rounded-2xl transition-all duration-200 group-hover:scale-110">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div class="action-choice__title text-sm font-semibold">Close</div>
                                <div class="action-choice__subtitle text-xs">Dismiss modal</div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
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

.action-panel__header {
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

.action-panel__context {
    margin-top: 0.35rem;
    color: var(--text-muted);
}

.action-panel__close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.7rem;
    color: var(--text-muted);
    transition: background-color 0.16s ease, color 0.16s ease;
}

.action-panel__close:hover {
    color: var(--text-strong);
    background: color-mix(in srgb, var(--surface) 70%, white 30%);
}

.action-choice {
    position: relative;
    z-index: 1;
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

.action-choice--muted {
    background: linear-gradient(180deg, color-mix(in srgb, var(--surface-stronger) 95%, white 5%) 0%, var(--surface) 100%);
}

.action-choice__icon--muted {
    border-color: var(--line-soft);
    color: var(--text-muted);
}

.action-choice__title {
    color: var(--text-strong);
}

.action-choice__subtitle {
    color: var(--text-muted);
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
