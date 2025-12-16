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
        <Transition name="modal">
            <div
                v-if="isOpen"
                @click="handleBackdropClick"
                class="fixed inset-0 z-50 flex items-center justify-center bg-gray-200/10 backdrop-blur-sm p-4"
            >
                <Transition name="modal-content">
                    <div
                        v-if="isOpen"
                        @click.stop
                        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col"
                    >
                        <div v-if="$slots.header" class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                            <slot name="header" />
                            <button
                                @click="emit('close')"
                                class="ml-4 p-1 text-gray-400 hover:text-gray-600 transition-colors rounded-lg hover:bg-gray-100"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
.modal-enter-active {
    transition: opacity 0.3s ease-out;
}

.modal-leave-active {
    transition: opacity 0.3s ease-in;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-content-enter-active {
    transition: all 0.3s ease-out;
}

.modal-content-leave-active {
    transition: all 0.3s ease-in;
}

.modal-content-enter-from {
    opacity: 0;
    transform: scale(0.9) translateY(-20px);
}

.modal-content-leave-to {
    opacity: 0;
    transform: scale(0.9) translateY(-20px);
}
</style>

