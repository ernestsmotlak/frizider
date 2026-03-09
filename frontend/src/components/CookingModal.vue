<script setup lang="ts">
import { useRouter } from "vue-router";

const props = defineProps<{
    isOpen: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const router = useRouter();

const handleClose = () => {
    emit("close");
};

const handleReturn = () => {
    emit("close");
    router.back();
};

const handleAddTimer = () => {
    // No-op for now
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-backdrop">
            <div
                v-if="isOpen"
                @click="handleClose"
                class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
            >
                <Transition name="modal-content" appear>
                    <div
                        v-if="isOpen"
                        class="fixed left-0 right-0 bottom-16 z-50 px-4 pb-3"
                    >
                        <div class="mx-auto max-w-md" @click.stop>
                            <div class="rounded-2xl border border-gray-200 bg-white/95 shadow-2xl ring-1 ring-black/5 w-full overflow-hidden flex flex-col">
                                <div
                                    class="px-6 py-5 flex items-center justify-between border-b border-gray-300"
                                >
                                    <h2 class="text-lg font-semibold text-gray-900">
                                        Cooking
                                    </h2>
                                    <button
                                        @click="handleClose"
                                        type="button"
                                        class="p-1.5 rounded-lg hover:bg-gray-100 active:scale-95 transition-all duration-200"
                                        aria-label="Close"
                                    >
                                        <svg
                                            class="w-7 h-7 text-gray-400 hover:text-gray-600"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12"
                                            ></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="p-3">
                                    <div class="grid grid-cols-2 gap-3">
                                <button
                                    @click="handleReturn"
                                    type="button"
                                    class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-b from-white to-green-50 px-3 py-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:border-green-300 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 touch-manipulation"
                                    aria-label="Go back"
                                >
                                    <div
                                        class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white shadow-sm ring-1 ring-green-200 text-green-700 transition-all duration-200 group-hover:scale-110 group-hover:ring-green-300 group-hover:shadow-md"
                                    >
                                        <svg
                                            class="w-8 h-8"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                            ></path>
                                        </svg>
                                    </div>
                                    <div
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        Return
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Go back
                                    </div>
                                </button>

                                <button
                                    @click="handleAddTimer"
                                    type="button"
                                    class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-b from-white to-green-50 px-3 py-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:border-green-300 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 touch-manipulation"
                                    aria-label="Add timer"
                                >
                                    <div
                                        class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white shadow-sm ring-1 ring-green-200 text-green-700 transition-all duration-200 group-hover:scale-110 group-hover:ring-green-300 group-hover:shadow-md"
                                    >
                                        <svg
                                            class="w-8 h-8"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                    </div>
                                    <div
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        Add timer
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Set a timer
                                    </div>
                                </button>
                                    </div>
                                </div>
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
    transform: translateY(14px);
}

.modal-content-leave-to {
    opacity: 0;
    transform: translateY(14px);
}
</style>
