<script setup lang="ts">
import {computed} from 'vue';
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
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-backdrop">
            <div
                v-if="isOpen"
                @click="handleClose"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            >
                <Transition name="modal-content" appear>
                    <div
                        v-if="isOpen"
                        @click.stop
                        class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden flex flex-col"
                        style="box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(34, 197, 94, 0.1);"
                    >
                        <div class="px-6 py-5 flex items-center justify-between border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900">Choose an action</h2>
                            <button
                                @click="handleClose"
                                class="p-1.5 rounded-lg hover:bg-gray-100 active:scale-95 transition-all duration-200"
                            >
                                <svg class="w-7 h-7 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="p-4 sm:p-6">
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    @click="handleComplete"
                                    type="button"
                                    class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-b from-white to-green-50 px-3 py-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:border-green-300 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 touch-manipulation"
                                    :aria-label="isCompleted ? 'Reopen list' : 'Complete list'"
                                >
                                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white shadow-sm ring-1 ring-green-200 text-green-700 transition-all duration-200 group-hover:scale-110 group-hover:ring-green-300 group-hover:shadow-md">
                                        <svg v-if="isCompleted" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <svg v-else class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900">{{ isCompleted ? 'Reopen' : 'Complete' }}</div>
                                    <div class="text-xs text-gray-500">{{ isCompleted ? 'Mark as active' : 'Mark as completed' }}</div>
                                </button>

                                <button
                                    @click="handleGoShopping"
                                    type="button"
                                    class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-b from-white to-green-50 px-3 py-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:border-green-300 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 touch-manipulation"
                                    aria-label="Go shopping"
                                >
                                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white shadow-sm ring-1 ring-green-200 text-green-700 transition-all duration-200 group-hover:scale-110 group-hover:ring-green-300 group-hover:shadow-md">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17H18a2 2 0 0 0 2-1.6L21 8H6"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.5 20a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900">Shopping</div>
                                    <div class="text-xs text-gray-500">Check items and buy</div>
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.touch-manipulation {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

.modal-backdrop-enter-active {
    transition: opacity 0.25s ease-out;
}

.modal-backdrop-leave-active {
    transition: opacity 0.2s ease-in;
}

.modal-backdrop-enter-from,
.modal-backdrop-leave-to {
    opacity: 0;
}

.modal-content-enter-active {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-content-leave-active {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-content-enter-from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
}

.modal-content-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
}

</style>
