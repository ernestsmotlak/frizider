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
                        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto"
                    >
                        <div class="p-6">
                            <slot />
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

