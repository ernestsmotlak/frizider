<script setup lang="ts">
import {computed} from 'vue';
import {useRouter} from 'vue-router';
import type {GroceryList} from '../pages/GroceryList/GroceryListsPage.vue';

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

const isCompleted = computed(() => !!props.groceryList.completed_at);

const handleComplete = () => {
    emit('complete');
    emit('close');
};

const handleGoShopping = () => {
    emit('goShopping');
    router.push('/shopping');
    emit('close');
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
                                <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="p-4 sm:p-6">
                            <div class="flex gap-3 sm:gap-4">
                                <button
                                    @click="handleComplete"
                                    class="flex-1 group relative bg-white rounded-xl border border-green-100 hover:border-green-200 active:scale-[0.97] transition-all duration-200 shadow-sm hover:shadow-md p-4 sm:p-5 flex flex-col items-center text-center touch-manipulation min-w-0"
                                >
                                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-green-50 flex items-center justify-center mb-3 group-hover:bg-green-100 transition-colors flex-shrink-0">
                                        <svg class="w-6 h-6 sm:w-7 sm:h-7 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="font-bold text-sm text-gray-900 mb-1">{{ isCompleted ? 'Reopen' : 'Complete' }}</div>
                                    <div class="text-xs text-gray-500 leading-snug text-center">{{ isCompleted ? 'Mark as active' : 'Mark as completed' }}</div>
                                </button>

                                <button
                                    @click="handleGoShopping"
                                    class="flex-1 group relative bg-white rounded-xl border border-green-100 hover:border-green-200 active:scale-[0.97] transition-all duration-200 shadow-sm hover:shadow-md p-4 sm:p-5 flex flex-col items-center text-center touch-manipulation min-w-0"
                                >
                                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-green-50 flex items-center justify-center mb-3 group-hover:bg-green-100 transition-colors flex-shrink-0">
                                        <svg class="w-6 h-6 sm:w-7 sm:h-7 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="font-bold text-sm text-gray-900 mb-1">Shopping</div>
                                    <div class="text-xs text-gray-500 leading-snug text-center">Check items and buy</div>
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
