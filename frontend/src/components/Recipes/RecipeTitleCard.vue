<script setup lang="ts">
import {formatTime} from "../../utils/formatTime.ts";
import {ref, onMounted, onUnmounted} from "vue";
import Modal from "../Modal.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import {useConfirmStore} from "../../stores/confirm.ts";
import {useRouter} from "vue-router";

const toastStore = useToastStore();
const loadingStore = useLoadingStore();
const confirmStore = useConfirmStore();
const router = useRouter();

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

const emit = defineEmits<{
    'updatedRecipe': [recipe: Recipe]
}>();

const isModalOpen = ref(false);
const isMenuOpen = ref(false);

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

const updateRecipe = () => {
    const payload = {
        name: formData.value.name,
        description: formData.value.description,
        servings: formData.value.servings,
        prep_time: formData.value.prep_time,
        cook_time: formData.value.cook_time
    };

    loadingStore.start();

    axios.patch('/api/recipes/' + props.recipe.id, payload)
        .then((response) => {
            const updatedRecipe = response.data.data;
            emit('updatedRecipe', updatedRecipe);
            toastStore.show('success', 'Recipe updated successfully.');
            closeModal();
        })
        .catch((error) => {
            console.error(error);
            toastStore.show('error', 'Could not update recipe.');
        })
        .finally(() => {
            loadingStore.stop();
        })
}

const closeModal = () => {
    isModalOpen.value = false;
};

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
    isMenuOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (isMenuOpen.value && !target.closest('.menu-container')) {
        closeMenu();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const handleEdit = () => {
    closeMenu();
    openModal();
};

const deleteRecipe = async () => {
    closeMenu();
    const recipeName = props.recipe.name || 'this recipe';
    const confirmed = await confirmStore.show(`Are you sure you want to delete "${recipeName}"? This action cannot be undone.`);

    if (!confirmed) {
        return;
    }

    loadingStore.start();

    axios.delete(`/api/recipes/${props.recipe.id}`)
        .then(() => {
            toastStore.show('success', 'Recipe deleted successfully.');
            router.push('/recipes');
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not delete recipe.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};
</script>

<template>
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden relative border-2 border-gray-200">
        <div class="absolute top-2 right-2">
            <div class="relative menu-container">
                <button @click.stop="toggleMenu"
                        class="p-2 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200">
                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                </button>
                
                <div v-if="isMenuOpen" 
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-1 z-50"
                     @click.stop>
                    <button @click.stop="handleEdit"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Recipe
                    </button>
                    <button @click.stop="deleteRecipe"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path>
                        </svg>
                        Delete Recipe
                    </button>
                </div>
            </div>
        </div>

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
            <div class="flex justify-between gap-3">
                <button
                    @click="closeModal"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    @click.stop="updateRecipe"
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
