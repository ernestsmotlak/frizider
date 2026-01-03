<script setup lang="ts">
import {ref, nextTick, watch} from "vue";
import Modal from "../Modal.vue";
import {useToastStore} from "../../stores/toast.ts";
import {useLoadingStore} from "../../stores/loading.ts";
import {QuillEditor} from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";

interface RecipeIngredient {
    id: number;
    recipe_id: number;
    name: string;
    quantity: number | null;
    unit: string | null;
    notes: string | null;
    sort_order: number;
}

interface Recipe {
    id: number;
    name: string;
    description: string | null;
    instructions: string | null;
    servings: number | null;
    prep_time: number | null;
    cook_time: number | null;
    image_url: string | null;
    recipe_ingredients?: RecipeIngredient[];
}

const props = defineProps<{
    instructions: string | null;
    recipeId: number;
}>();

const emit = defineEmits<{
    'updatedRecipe': [recipe: any]
}>();

const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const isModalOpen = ref(false);
const quillContent = ref("");
const quillKey = ref(0);

const openEditInstructionsModal = () => {
    quillContent.value = props.instructions || "";
    isModalOpen.value = true;
    nextTick(() => {
        quillKey.value += 1;
    });
};

watch(isModalOpen, (newVal) => {
    if (newVal) {
        nextTick(() => {
            quillKey.value += 1;
        });
    }
});

const closeModal = () => {
    isModalOpen.value = false;
};

const saveInstructions = () => {
    loadingStore.start();

    axios.patch(`/api/recipes/${props.recipeId}`, {
        instructions: quillContent.value || null
    })
        .then((response) => {
            const updatedRecipe = response.data.data;
            emit('updatedRecipe', updatedRecipe);
            toastStore.show('success', 'Instructions updated successfully.');
            closeModal();
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not update instructions.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};
</script>

<template>
    <div class="bg-white rounded-2xl shadow-xl p-8 relative">
        <button
            @click="openEditInstructionsModal"
            class="absolute top-2 right-2 p-2 border-2 border-gray-200 bg-white/90 backdrop-blur-sm rounded-lg shadow-md hover:border-gray-300 hover:bg-white hover:shadow-xl hover:scale-110 active:scale-95 active:shadow-md transition-all duration-200">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
        </button>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Instructions</h2>
        <div class="prose max-w-none">
            <div v-if="instructions" class="text-gray-700 leading-relaxed" v-html="instructions"></div>
            <div v-else class="text-gray-500 italic">No instructions added yet. Click the edit button to add instructions.</div>
        </div>
    </div>

    <Modal :isOpen="isModalOpen" @close="closeModal">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Edit Instructions</h2>
        </template>
        <template #body>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-2">Instructions</label>
                    <QuillEditor
                        v-if="isModalOpen"
                        :key="quillKey"
                        v-model:content="quillContent"
                        contentType="html"
                        theme="snow"
                        :options="{
                            placeholder: 'Enter recipe instructions...',
                            modules: {
                                toolbar: [
                                    ['bold', 'italic'],
                                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                    ['clean']
                                ]
                            }
                        }"
                        class="min-h-[400px]"
                    />
                </div>
                <div v-if="quillContent" class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <label class="block text-xs text-gray-500 font-medium mb-2">Preview</label>
                    <div class="prose max-w-none text-sm" v-html="quillContent"></div>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="flex flex-col sm:flex-row justify-between gap-3">
                <button
                    @click="closeModal"
                    class="w-full sm:w-auto px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                >
                    Cancel
                </button>
                <button
                    @click.stop="saveInstructions"
                    class="w-full sm:w-auto px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Save Changes
                </button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
:deep(.ql-editor) {
    min-height: 400px;
}

:deep(.ql-container) {
    font-size: 16px;
}

:deep(.ql-toolbar) {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
}

:deep(.ql-container) {
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}
</style>
