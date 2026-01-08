<script setup lang="ts">
import DashboardLayout from "../layouts/DashboardLayout.vue";
import {ref} from "vue";
import {useRouter} from "vue-router";
import {useToastStore} from "../stores/toast.ts";
import {useLoadingStore} from "../stores/loading.ts";
import "emoji-picker-element";

const router = useRouter();
const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const formData = ref({
    name: "",
    description: "",
    servings: "",
    prep_time: "",
    cook_time: "",
    emoji: ""
});

interface Ingredient {
    name: string;
    quantity: string;
    unit: string;
    notes: string;
}

interface Instruction {
    instruction: string;
}

const ingredients = ref<Ingredient[]>([]);
const instructions = ref<Instruction[]>([]);

const showEmojiPicker = ref(false);

const handleEmojiSelect = (event: CustomEvent) => {
    formData.value.emoji = event.detail?.unicode || "";
    showEmojiPicker.value = false;
};

const createRecipe = () => {
    if (!formData.value.name.trim()) {
        toastStore.show('error', 'Recipe name is required.');
        return;
    }

    const emptyIngredients = ingredients.value.filter(ing => !ing.name.trim());
    if (emptyIngredients.length > 0) {
        toastStore.show('error', 'Please fill in the name for all ingredients or remove empty ones.');
        return;
    }

    const emptyInstructions = instructions.value.filter(inst => !inst.instruction.trim());
    if (emptyInstructions.length > 0) {
        toastStore.show('error', 'Please fill in all instructions or remove empty ones.');
        return;
    }

    const parseInteger = (value: string | number | null | undefined): number | null => {
        if (value === null || value === undefined) {
            return null;
        }
        if (typeof value === 'number') {
            return isNaN(value) ? null : value;
        }
        const strValue = String(value).trim();
        if (strValue === '') {
            return null;
        }
        const parsed = parseInt(strValue, 10);
        return isNaN(parsed) ? null : parsed;
    };

    const validIngredients = ingredients.value
        .filter(ing => ing.name.trim())
        .map((ing, index) => ({
            name: ing.name.trim(),
            quantity: ing.quantity && String(ing.quantity).trim() ? parseFloat(String(ing.quantity)) : null,
            unit: ing.unit?.trim() || null,
            notes: ing.notes?.trim() || null,
            sort_order: index
        }));

    const validInstructions = instructions.value
        .filter(inst => inst.instruction.trim())
        .map((inst, index) => ({
            instruction: inst.instruction.trim(),
            sort_order: index
        }));

    const payload = {
        name: formData.value.name,
        description: formData.value.description || null,
        servings: parseInteger(formData.value.servings),
        prep_time: parseInteger(formData.value.prep_time),
        cook_time: parseInteger(formData.value.cook_time),
        image_url: formData.value.emoji || null,
        ingredients: validIngredients,
        instructions: validInstructions
    };

    loadingStore.start();

    axios.post('/api/save-recipe-data', payload)
        .then((response) => {
            const createdRecipe = response.data.data;
            toastStore.show('success', 'Recipe created successfully.');
            router.push(`/recipe/${createdRecipe.id}`);
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not create recipe.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const handleCancel = () => {
    router.push('/recipes');
};

const addIngredient = () => {
    ingredients.value.push({
        name: "",
        quantity: "",
        unit: "",
        notes: ""
    });
};

const removeIngredient = (index: number) => {
    ingredients.value.splice(index, 1);
};

const addInstruction = () => {
    instructions.value.push({
        instruction: ""
    });
};

const removeInstruction = (index: number) => {
    instructions.value.splice(index, 1);
};
</script>

<template>
    <DashboardLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden relative border-2 border-gray-200">
                <div class="p-8 space-y-6">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-6">Create New Recipe</h1>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipe Emoji</label>
                        <div class="flex items-center gap-3">
                            <div
                                v-if="formData.emoji"
                                class="emoji-pulsate flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-4xl cursor-pointer hover:bg-gray-200 transition-colors border-2 border-dashed border-gray-300"
                                @click="showEmojiPicker = !showEmojiPicker"
                            >
                                {{ formData.emoji }}
                            </div>
                            <button
                                v-else
                                type="button"
                                @click="showEmojiPicker = !showEmojiPicker"
                                class="emoji-pulsate flex-shrink-0 w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-4xl hover:bg-gray-200 transition-colors border-2 border-dashed border-gray-300"
                            >
                                🥪
                            </button>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600">
                                    {{ formData.emoji ? "Click emoji to change" : "Click to select an emoji" }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="showEmojiPicker"
                            class="mt-3 relative"
                        >
                            <emoji-picker
                                @emoji-click="handleEmojiSelect"
                                class="w-full"
                            ></emoji-picker>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recipe Name *</label>
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
                                <div
                                    class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
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
                                        placeholder="4"
                                    />
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
                                <div class="flex-1">
                                    <label class="block text-sm text-gray-500 font-medium mb-1">Prep Time
                                        (minutes)</label>
                                    <input
                                        v-model="formData.prep_time"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 text-lg font-semibold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                        placeholder="15"
                                    />
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
                                <div class="flex-1">
                                    <label class="block text-sm text-gray-500 font-medium mb-1">Cook Time
                                        (minutes)</label>
                                    <input
                                        v-model="formData.cook_time"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 text-lg font-semibold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none"
                                        placeholder="30"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-900">Ingredients</h2>
                            <button
                                type="button"
                                @click="addIngredient"
                                class="px-3 py-1.5 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors font-medium"
                            >
                                + Add Ingredient
                            </button>
                        </div>
                        <div v-if="ingredients.length === 0" class="text-sm text-gray-500 italic">
                            No ingredients added yet. Click "Add Ingredient" to get started.
                        </div>
                        <div v-for="(ingredient, index) in ingredients" :key="index"
                             class="bg-gray-50 rounded-lg p-4 space-y-3 border border-gray-200">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-3">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                        <input
                                            v-model="ingredient.name"
                                            type="text"
                                            class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                            placeholder="e.g., Flour"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                        <input
                                            v-model="ingredient.quantity"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                            placeholder="2"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                        <input
                                            v-model="ingredient.unit"
                                            type="text"
                                            class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                            placeholder="cups"
                                        />
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="removeIngredient(index)"
                                    class="flex-shrink-0 mt-6 px-2 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Remove ingredient"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <input
                                    v-model="ingredient.notes"
                                    type="text"
                                    class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                    placeholder="e.g., sifted, room temperature"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-900">Instructions</h2>
                            <button
                                type="button"
                                @click="addInstruction"
                                class="px-3 py-1.5 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                            >
                                + Add Instruction
                            </button>
                        </div>
                        <div v-if="instructions.length === 0" class="text-sm text-gray-500 italic">
                            No instructions added yet. Click "Add Instruction" to get started.
                        </div>
                        <div v-for="(instruction, index) in instructions" :key="index"
                             class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center font-bold text-blue-600 mt-1">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <textarea
                                        v-model="instruction.instruction"
                                        rows="2"
                                        class="w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"
                                        placeholder="Enter instruction step..."
                                    ></textarea>
                                </div>
                                <button
                                    type="button"
                                    @click="removeInstruction(index)"
                                    class="flex-shrink-0 mt-1 px-2 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Remove instruction"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between gap-3 pt-4 border-t border-gray-200">
                        <button
                            @click="handleCancel"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                        >
                            Cancel
                        </button>
                        <button
                            @click="createRecipe"
                            class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                        >
                            Create Recipe
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>

</style>
