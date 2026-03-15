<script setup lang="ts">
import {computed, onBeforeUnmount, onMounted, ref} from 'vue';
import {useRouter} from 'vue-router';
import type {GroceryList} from '../pages/GroceryList/GroceryListsPage.vue';
import {useToastStore} from '../stores/toast.ts';
import {useLoadingStore} from '../stores/loading.ts';

const props = defineProps<{
    isOpen: boolean;
    groceryList: GroceryList;
}>();

const emit = defineEmits<{
    close: [];
    complete: [];
    goShopping: [];
}>();

const router = useRouter();
const toastStore = useToastStore();
const loadingStore = useLoadingStore();
const modalPanelRef = ref<HTMLElement | null>(null);

const isCompleted = computed(() => !!props.groceryList.completed_at);

const handleComplete = () => {
    emit('complete');
    emit('close');
};

const handleGoShopping = async () => {
    loadingStore.start();
    try {
        await axios.post('/api/save-shopping-session', {
            grocery_list_ids: [props.groceryList.id],
        });
        emit('close');
        router.push('/shopping');
    } catch (error: any) {
        const message = error.response?.data?.message ?? 'Could not save shopping session.';
        toastStore.show('error', message);
    } finally {
        loadingStore.stop();
    }
};

const handleClose = () => {
    emit('close');
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
                                <h2 class="action-panel__title">List Actions</h2>
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
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="handleComplete"
                                class="action-choice group flex flex-col items-center justify-center gap-2 rounded-xl px-3 py-4 transition-all duration-200 hover:-translate-y-1 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                                :aria-label="isCompleted ? 'Reopen list' : 'Complete list'"
                            >
                                <div class="action-choice__icon flex items-center justify-center w-14 h-14 rounded-2xl transition-all duration-200 group-hover:scale-110">
                                    <svg v-if="isCompleted" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    <svg v-else class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="action-choice__title text-sm font-semibold">{{ isCompleted ? 'Reopen' : 'Complete' }}</div>
                                <div class="action-choice__subtitle text-xs">{{ isCompleted ? 'Mark as active' : 'Mark as completed' }}</div>
                            </button>

                            <button
                                type="button"
                                @click="handleGoShopping"
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
