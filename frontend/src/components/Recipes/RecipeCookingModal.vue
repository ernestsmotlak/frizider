<script setup lang="ts">
import {useRouter} from "vue-router";

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
    "go-cooking": [];
}>();

const router = useRouter();

const handleGoCooking = () => {
    emit("go-cooking");
    emit("close");
    router.push("/cooking");
};

const handleClose = () => {
    emit("close");
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
                        style="box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(234, 179, 8, 0.1);"
                    >
                        <div class="px-6 py-5 flex items-center justify-between border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900">Choose an action</h2>
                            <button
                                @click="handleClose"
                                class="p-1.5 rounded-lg hover:bg-gray-100 active:scale-95 transition-all duration-200"
                                aria-label="Close"
                            >
                                <svg class="w-7 h-7 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div v-if="recipe" class="p-4 sm:p-6">
                            <p class="text-sm text-gray-600 mb-4">Recipe: {{ recipe.name }}</p>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    @click="handleGoCooking"
                                    type="button"
                                    class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-b from-white to-amber-50 px-3 py-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:border-amber-300 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 touch-manipulation"
                                    aria-label="Go cooking"
                                >
                                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white shadow-sm ring-1 ring-amber-200 text-amber-700 transition-all duration-200 group-hover:scale-110 group-hover:ring-amber-300 group-hover:shadow-md">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900">Go cooking</div>
                                </button>
                                <button
                                    @click="handleClose"
                                    type="button"
                                    class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-b from-white to-gray-50 px-3 py-4 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg hover:border-gray-300 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2 touch-manipulation"
                                    aria-label="Close"
                                >
                                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 text-gray-600 transition-all duration-200 group-hover:scale-110 group-hover:ring-gray-300 group-hover:shadow-md">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900">Close</div>
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
