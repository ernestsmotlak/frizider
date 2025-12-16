<script setup lang="ts">
defineProps<{
    isOpen: boolean
}>();

const emit = defineEmits<{
    close: []
}>();

const handleBackdropClick = (event: MouseEvent) => {
    if (event.target === event.currentTarget) {
        emit('close');
    }
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-backdrop">
            <div
                v-if="isOpen"
                @click="handleBackdropClick"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            >
                <Transition name="modal-content" appear>
                    <div
                        v-if="isOpen"
                        @click.stop
                        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col"
                    >
                        <div v-if="$slots.header" class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                            <slot name="header" />
                            <button
                                @click="emit('close')"
                                class="ml-4 p-2 rounded-lg active:scale-95 transition-transform duration-200"
                            >
                                <svg class="w-6 h-6 text-gray-500 hover:text-red-700 transition-all duration-200 hover:scale-150" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex-1 overflow-y-auto px-6 py-6">
                            <slot name="body" />
                        </div>
                        <div v-if="$slots.footer" class="border-t border-gray-200 px-6 py-4">
                            <slot name="footer" />
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

