<script setup lang="ts">
import {formatTime} from "../../utils/formatTime.ts";
import {ref} from "vue";
import Modal from "../Modal.vue";

interface Recipe {
    id: number;
    name: string;
    description: string | null;
    instructions: string | null;
    servings: number | null;
    prep_time: number | null;
    cook_time: number | null;
    image_url: string | null;
}

const props = defineProps<{
    recipe: Recipe
}>();

const isModalOpen = ref(false);

const formData = ref({
    name: "",
    description: "",
    servings: "",
    prep_time: "",
    cook_time: ""
});

const openModal = () => {
    formData.value = {
        name: props.recipe.name || "",
        description: props.recipe.description || "",
        servings: props.recipe.servings?.toString() || "",
        prep_time: props.recipe.prep_time?.toString() || "",
        cook_time: props.recipe.cook_time?.toString() || ""
    };
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};
</script>

<template>
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden relative">
        <button @click="openModal"
                class="absolute top-2 right-2 p-2 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:bg-white hover:shadow-lg transition-all duration-200">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
        </button>

        <div v-if="recipe.image_url" class="w-full h-64 md:h-96 bg-gray-200">
            <img :src="recipe.image_url" :alt="recipe.name" class="w-full h-full object-cover">
        </div>

        <div class="p-8 space-y-6">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 mb-3 max-w-[330px]">{{ recipe.name }}</h1>
                <p v-if="recipe.description" class="text-lg text-gray-600 leading-relaxed">
                    {{ recipe.description }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 pt-4 border-t border-gray-200">
                <div class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Servings</p>
                        <p class="text-lg font-semibold text-gray-900">{{
                                recipe.servings || "N/A"
                            }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Prep Time</p>
                        <p class="text-lg font-semibold text-gray-900">{{
                                formatTime(recipe.prep_time)
                            }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Cook Time</p>
                        <p class="text-lg font-semibold text-gray-900">{{
                                formatTime(recipe.cook_time)
                            }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Modal :isOpen="isModalOpen" @close="closeModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Edit Recipe</h2>
        </template>
        <template #body>
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recipe Name</label>
                    <input
                        v-model="formData.name"
                        type="text"
                        class="w-full px-4 py-3 text-2xl font-bold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        placeholder="Enter recipe name"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea
                        v-model="formData.description"
                        rows="3"
                        class="w-full px-4 py-3 text-lg text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"
                        placeholder="Enter recipe description"
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm text-gray-500 font-medium mb-1">Servings</label>
                            <input
                                v-model="formData.servings"
                                type="number"
                                min="1"
                                class="w-full px-3 py-2 text-lg font-semibold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                placeholder="N/A"
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm text-gray-500 font-medium mb-1">Prep Time (minutes)</label>
                            <input
                                v-model="formData.prep_time"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 text-lg font-semibold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="N/A"
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm text-gray-500 font-medium mb-1">Cook Time (minutes)</label>
                            <input
                                v-model="formData.cook_time"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 text-lg font-semibold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                                placeholder="N/A"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex justify-end gap-3">
                <button
                    @click="closeModal"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Save Changes
                </button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>

</style>
