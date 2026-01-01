<script setup lang="ts">
import {useConfirmStore} from "../stores/confirm";

const confirmStore = useConfirmStore();

const handleBackdropClick = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
        confirmStore.cancel();
    }
};

const handleConfirm = () => {
    confirmStore.confirm();
};

const handleCancel = () => {
    confirmStore.cancel();
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-backdrop">
            <div
                v-if="confirmStore.isOpen"
                @click="handleBackdropClick"
                class="fixed inset-0 z-99999 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            >
                <Transition name="modal-content" appear>
                    <div
                        v-if="confirmStore.isOpen"
                        @click.stop
                        class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden flex flex-col"
                    >
                        <div class="px-6 py-6">
                            <p class="text-gray-900 text-base leading-relaxed">{{ confirmStore.message }}</p>
                        </div>
                        <div class="border-t border-gray-200 px-6 py-4 flex flex-col sm:flex-row justify-end gap-3">
                            <button
                                @click="handleCancel"
                                class="w-full sm:w-auto px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                            >
                                Cancel
                            </button>
                            <button
                                @click="handleConfirm"
                                class="w-full sm:w-auto px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                            >
                                Confirm
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
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
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-content-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-content-enter-from {
    opacity: 0;
    transform: translateY(100vh);
}

.modal-content-leave-to {
    opacity: 0;
    transform: translateY(100vh);
}
</style>

