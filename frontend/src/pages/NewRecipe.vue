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
            <div class="recipes-hero-card rounded-2xl overflow-hidden relative">
                <div class="p-8 space-y-6">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-6">Create New Recipe</h1>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipe Emoji</label>
                        <div class="flex items-center gap-3">
                            <div
                                v-if="formData.emoji"
                                class="emoji-pulsate flex-shrink-0 w-16 h-16 rounded-xl flex items-center justify-center text-4xl cursor-pointer transition-all duration-200 border-2 border-dashed border-gray-300 bg-white/90 shadow-sm hover:bg-white hover:border-gray-400 hover:shadow-md hover:scale-105 active:scale-95"
                                @click="showEmojiPicker = !showEmojiPicker"
                            >
                                {{ formData.emoji }}
                            </div>
                            <button
                                v-else
                                type="button"
                                @click="showEmojiPicker = !showEmojiPicker"
                                class="emoji-pulsate flex-shrink-0 w-16 h-16 rounded-xl flex items-center justify-center text-4xl transition-all duration-200 border-2 border-dashed border-gray-300 bg-white/90 shadow-sm hover:bg-white hover:border-gray-400 hover:shadow-md hover:scale-105 active:scale-95"
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
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold rounded-xl border-2 border-emerald-100 bg-gradient-to-b from-white to-emerald-50 text-emerald-600 shadow-sm hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md active:translate-y-0 active:scale-[0.98] transition-all duration-200"
                            >
                                + Add Ingredient
                            </button>
                        </div>
                        <p v-if="ingredients.length > 0" class="text-xs text-gray-500 -mt-2">
                            You can edit the order of ingredients later.
                        </p>
                        <div v-if="ingredients.length === 0" class="text-sm text-gray-500 italic">
                            No ingredients added yet. Click "Add Ingredient" to get started.
                        </div>
                        <div v-for="(ingredient, index) in ingredients" :key="index"
                             class="ingredient-card relative rounded-2xl p-4 space-y-3 border">
                            <button
                                type="button"
                                @click="removeIngredient(index)"
                                class="absolute top-4 right-4 w-10 h-10 inline-flex items-center justify-center text-red-600 border border-red-200 bg-white rounded-xl shadow-sm hover:bg-red-50 hover:border-red-300 hover:shadow-md hover:scale-105 active:scale-95 transition-all duration-200"
                                title="Remove ingredient"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            <div class="pr-14 space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                    <input
                                        v-model="ingredient.name"
                                        type="text"
                                        class="ingredient-input w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                        placeholder="e.g., Flour"
                                    />
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                        <input
                                            v-model="ingredient.quantity"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="ingredient-input w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                            placeholder="2"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                        <input
                                            v-model="ingredient.unit"
                                            type="text"
                                            class="ingredient-input w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                                            placeholder="cups"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <input
                                    v-model="ingredient.notes"
                                    type="text"
                                    class="ingredient-input w-full px-3 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
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
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold rounded-xl border-2 border-blue-100 bg-gradient-to-b from-white to-blue-50 text-blue-600 shadow-sm hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md active:translate-y-0 active:scale-[0.98] transition-all duration-200"
                            >
                                + Add Instruction
                            </button>
                        </div>
                        <p v-if="instructions.length > 0" class="text-xs text-gray-500 -mt-2">
                            You can edit the order of instructions later.
                        </p>
                        <div v-if="instructions.length === 0" class="text-sm text-gray-500 italic">
                            No instructions added yet. Click "Add Instruction" to get started.
                        </div>
                        <div v-for="(instruction, index) in instructions" :key="index"
                             class="instruction-card rounded-2xl p-4 border">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div class="inline-flex items-center gap-2.5">
                                    <div
                                        class="instruction-index flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center font-bold">
                                        {{ index + 1 }}
                                    </div>
                                    <p class="text-sm font-semibold text-blue-800">Step {{ index + 1 }}</p>
                                </div>
                                <button
                                    type="button"
                                    @click="removeInstruction(index)"
                                    class="instruction-delete-btn flex-shrink-0 w-10 h-10 inline-flex items-center justify-center rounded-xl transition-all duration-200"
                                    title="Remove instruction"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            <textarea
                                v-model="instruction.instruction"
                                rows="3"
                                class="instruction-textarea w-full px-3.5 py-3 text-gray-900 rounded-xl outline-none resize-y min-h-[90px]"
                                placeholder="Enter instruction step..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex justify-between gap-3 pt-4 border-t border-gray-200">
                        <button
                            @click="handleCancel"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border-2 border-gray-200 bg-white/90 text-gray-700 font-semibold shadow-sm hover:-translate-y-0.5 hover:border-gray-300 hover:bg-white hover:shadow-md active:translate-y-0 active:scale-[0.98] transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            @click="createRecipe"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border-2 border-blue-600 bg-gradient-to-b from-blue-500 to-blue-600 text-white font-semibold shadow-md hover:-translate-y-0.5 hover:shadow-lg hover:from-blue-600 hover:to-blue-700 hover:border-blue-700 active:translate-y-0 active:scale-[0.98] transition-all duration-200"
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
.recipes-hero-card {
    border: 4px solid color-mix(in srgb, var(--line-soft) 58%, var(--accent-soft) 42%);
    background:
        radial-gradient(85% 75% at 100% 0%, rgba(255, 200, 120, 0.3) 0%, rgba(255, 200, 120, 0) 70%),
        radial-gradient(70% 85% at 0% 100%, rgba(116, 221, 164, 0.12) 0%, rgba(116, 221, 164, 0) 72%),
        linear-gradient(135deg, #ffffff 0%, #fcfffc 45%, #f8fbf9 100%);
    box-shadow: 0 18px 38px rgba(11, 96, 68, 0.2);
}

.instruction-card {
    border-color: #d9e4ff;
    background:
        radial-gradient(120% 120% at 110% -12%, rgba(72, 132, 255, 0.14) 0%, rgba(72, 132, 255, 0) 72%),
        linear-gradient(180deg, #ffffff 0%, #f7faff 100%);
    box-shadow: 0 8px 20px rgba(34, 81, 173, 0.12);
}

.ingredient-card {
    border-color: #d9efe2;
    background:
        radial-gradient(120% 120% at 110% -12%, rgba(53, 196, 125, 0.14) 0%, rgba(53, 196, 125, 0) 72%),
        linear-gradient(180deg, #ffffff 0%, #f7fff9 100%);
    box-shadow: 0 8px 20px rgba(16, 93, 61, 0.12);
}

.ingredient-input {
    background: #ffffff;
}

.instruction-index {
    color: #2f5bd8;
    background: #dfeaff;
}

.instruction-textarea {
    border: 1px solid #bfccdf;
    background: #ffffff;
    transition: all 0.2s ease;
}

.instruction-textarea:focus {
    border-color: #3a67e8;
    box-shadow: 0 0 0 3px rgba(58, 103, 232, 0.16);
}

.instruction-delete-btn {
    color: #dc2626;
    border: 1px solid #fecaca;
    background: #fff7f7;
}

.instruction-delete-btn:hover {
    background: #fee2e2;
    border-color: #fca5a5;
}
</style>
