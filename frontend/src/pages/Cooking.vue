<script setup lang="ts">
import { onMounted, ref, computed, watchEffect } from "vue";
import { useRouter } from "vue-router";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import { useLoadingStore } from "../stores/loading";
import { useToastStore } from "../stores/toast";
import { formatTime } from "../utils/formatTime";
import { VueDraggable } from "vue-draggable-plus";
import type { Recipe, RecipeIngredient, RecipeInstruction } from "./Recipe/RecipePage.vue";

const router = useRouter();

type CookingIngredient = RecipeIngredient & { completed?: boolean };

const loadingStore = useLoadingStore();
const toasterStore = useToastStore();

const recipe = ref<Recipe | null>(null);
const isLoading = ref(true);
const currentStepIndex = ref(0);
const draggableIngredients = ref<CookingIngredient[]>([]);

watchEffect(() => {
    const list = recipe.value?.recipe_ingredients ?? [];
    draggableIngredients.value = [...list]
        .sort((a, b) => a.sort_order - b.sort_order)
        .map((ing) => ({ ...ing, completed: (ing as CookingIngredient).completed ?? false }));
});

const sortedIngredients = computed((): CookingIngredient[] => draggableIngredients.value);

const sortedInstructions = computed((): RecipeInstruction[] => {
    const list = recipe.value?.recipe_instructions ?? [];
    return [...list].sort((a, b) => a.sort_order - b.sort_order);
});

const currentInstruction = computed(() => {
    const list = sortedInstructions.value;
    if (list.length === 0) return null;
    const idx = Math.min(currentStepIndex.value, list.length - 1);
    return list[idx];
});

const stepProgressPercent = computed(() => {
    const total = sortedInstructions.value.length;
    if (total === 0) return 0;
    return Math.round(((currentStepIndex.value + 1) / total) * 100);
});

const hasTime = computed(() => {
    const r = recipe.value;
    return (r?.prep_time != null && r.prep_time > 0) || (r?.cook_time != null && r.cook_time > 0);
});

function formatIngredient(ing: RecipeIngredient): string {
    const parts: string[] = [];
    if (ing.quantity != null) parts.push(String(ing.quantity));
    if (ing.unit) parts.push(ing.unit);
    parts.push(ing.name);
    if (ing.notes) parts.push(`(${ing.notes})`);
    return parts.join(" ");
}

function toggleIngredient(ingredient: CookingIngredient): void {
    if (!ingredient.id || !recipe.value) return;
    const recipeId = recipe.value.id;
    const ingredientId = ingredient.id;
    axios
        .post(`/api/recipe/${recipeId}/ingredient/${ingredientId}/toggle-completed`)
        .then((response) => {
            recipe.value = response.data.data as Recipe;
        })
        .catch(() => {
            toasterStore.show("error", "Could not update ingredient status.");
        });
}

function onDragEnd(): void {
    draggableIngredients.value.forEach((ing, index) => {
        ing.sort_order = index;
    });
    if (!recipe.value) return;
    const recipeId = recipe.value.id;
    const payload = {
        ingredients: draggableIngredients.value.map((ing) => ({
            id: ing.id,
            name: ing.name,
            quantity: ing.quantity ?? null,
            unit: ing.unit ?? null,
            notes: ing.notes ?? null,
            sort_order: ing.sort_order,
            completed: ing.completed ?? false,
        })),
    };
    loadingStore.start();
    axios
        .post(`/api/recipes/${recipeId}/ingredients`, payload)
        .then((response) => {
            recipe.value = response.data.data as Recipe;
        })
        .catch(() => {
            toasterStore.show("error", "Could not update ingredient order.");
            draggableIngredients.value = [...(recipe.value?.recipe_ingredients ?? [])]
                .sort((a, b) => a.sort_order - b.sort_order)
                .map((ing) => ({ ...ing, completed: (ing as CookingIngredient).completed ?? false }));
        })
        .finally(() => {
            loadingStore.stop();
        });
}

function prevStep(): void {
    if (currentStepIndex.value > 0) currentStepIndex.value--;
}

function nextStep(): void {
    const max = sortedInstructions.value.length - 1;
    if (currentStepIndex.value < max) currentStepIndex.value++;
}

const fetchCookingSession = () => {
    isLoading.value = true;
    loadingStore.start();
    axios
        .get("/api/get-cooking-session")
        .then((result) => {
            recipe.value = (result.data.data ?? null) as Recipe | null;
            currentStepIndex.value = 0;
        })
        .catch(() => {
            recipe.value = null;
            toasterStore.show("error", "Error fetching cooking session data.");
        })
        .finally(() => {
            isLoading.value = false;
            loadingStore.stop();
        });
};

const goToRecipes = () => {
    router.push("/recipes");
};

onMounted(() => {
    fetchCookingSession();
});
</script>

<template>
    <DashboardLayout>
        <div class="cooking-page">
            <div class="cooking-card">
                <template v-if="isLoading">
                    <div class="cooking-loading">
                        <svg class="cooking-loading-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span class="cooking-loading-text">Loading recipe…</span>
                    </div>
                </template>
                <template v-else-if="recipe">
                <p class="cooking-mode-label" aria-label="Cooking mode">Cooking mode</p>
                <header
                class="cooking-title"
                :class="{ 'mt-2': !recipe.image_url }"
                >
                    <div class="cooking-title-inner">
                        <div v-if="recipe.image_url" class="cooking-emoji" aria-hidden="true">
                            {{ recipe.image_url }}
                        </div>
                        <h1 class="cooking-name">{{ recipe.name }}</h1>
                        <p v-if="recipe.description" class="cooking-description">{{ recipe.description }}</p>
                        <div v-if="recipe.servings || hasTime" class="cooking-meta-row">
                            <span v-if="recipe.servings" class="cooking-meta-item">
                                <svg class="cooking-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                                    <line x1="6" y1="1" x2="6" y2="4" />
                                    <line x1="10" y1="1" x2="10" y2="4" />
                                    <line x1="14" y1="1" x2="14" y2="4" />
                                </svg>
                                <span>{{ recipe.servings }}</span>
                            </span>
                            <span v-if="recipe.servings && hasTime" class="cooking-meta-sep" aria-hidden="true">·</span>
                            <span v-if="recipe.prep_time != null && recipe.prep_time > 0" class="cooking-meta-item">
                                <svg class="cooking-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                <span>{{ formatTime(recipe.prep_time) }}</span>
                            </span>
                            <span v-if="(recipe.prep_time != null && recipe.prep_time > 0) && (recipe.cook_time != null && recipe.cook_time > 0)" class="cooking-meta-sep" aria-hidden="true">·</span>
                            <span v-if="recipe.cook_time != null && recipe.cook_time > 0" class="cooking-meta-item">
                                <svg class="cooking-meta-icon cooking-meta-icon-cook" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z" />
                                </svg>
                                <span>{{ formatTime(recipe.cook_time) }}</span>
                            </span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="cooking-title-icon"
                        :class="{'go-to-lists-pulse': recipe.id}"
                        aria-label="Fridge"
                    >
                        <img src="/fridge_icon.png" alt="Fridge" class="cooking-title-icon-img" />
                    </button>
                </header>

                <div class="page-divider"></div>
                <section class="cooking-ingredients">
                    <h2 class="cooking-ingredients-heading">Ingredients</h2>
                    <VueDraggable
                        v-if="draggableIngredients.length > 0"
                        v-model="draggableIngredients"
                        tag="ul"
                        class="ingredient-list"
                        handle=".ingredient-drag-handle"
                        @end="onDragEnd"
                    >
                        <li
                            v-for="(ing, index) in draggableIngredients"
                            :key="ing.id ?? `tmp-${index}`"
                            class="ingredient-row"
                            :class="{ selected: ing.completed }"
                            @click="toggleIngredient(ing)"
                        >
                            <span class="ingredient-drag-handle" aria-hidden="true" @click.stop>
                                <svg class="ingredient-drag-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <circle cx="6" cy="9" r="1.5"/>
                                    <circle cx="12" cy="9" r="1.5"/>
                                    <circle cx="18" cy="9" r="1.5"/>
                                    <circle cx="6" cy="15" r="1.5"/>
                                    <circle cx="12" cy="15" r="1.5"/>
                                    <circle cx="18" cy="15" r="1.5"/>
                                </svg>
                            </span>
                            <span class="ingredient-check" aria-hidden="true">
                                <span v-if="ing.completed" class="check-mark">✓</span>
                                <span v-else class="check-empty"></span>
                            </span>
                            <span class="ingredient-text">{{ formatIngredient(ing) }}</span>
                        </li>
                    </VueDraggable>
                    <p v-else class="ingredient-empty">No ingredients.</p>
                </section>

                <hr class="page-divider" />

                <section class="cooking-instructions">
                    <h2 class="cooking-instructions-heading">Instructions</h2>
                    <div v-if="currentInstruction" class="step-wizard">
                        <div class="step-badge">
                            Step {{ currentStepIndex + 1 }} of {{ sortedInstructions.length }}
                        </div>
                        <div class="step-progress" role="progressbar" :aria-valuenow="currentStepIndex + 1" :aria-valuemin="1" :aria-valuemax="sortedInstructions.length" :aria-label="`Step ${currentStepIndex + 1} of ${sortedInstructions.length}`">
                            <div class="step-progress-track">
                                <div class="step-progress-fill" :style="{ width: stepProgressPercent + '%' }"></div>
                            </div>
                            <p class="step-progress-label">{{ stepProgressPercent }}% complete</p>
                        </div>
                        <div class="step-content">
                            <p class="step-text">{{ currentInstruction.instruction }}</p>
                        </div>
                        <div class="step-nav">
                            <button
                                type="button"
                                class="step-btn step-prev"
                                :disabled="currentStepIndex === 0"
                                @click="prevStep"
                            >
                                Previous
                            </button>
                            <button
                                type="button"
                                class="step-btn step-next"
                                :disabled="currentStepIndex === sortedInstructions.length - 1"
                                @click="nextStep"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                    <p v-else class="instructions-empty">No instructions.</p>
                </section>
                </template>
                <template v-else>
                <p class="cooking-mode-label" aria-label="Cooking mode">Cooking mode</p>
                    <div class="cooking-empty">
                        <svg class="cooking-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <p class="cooking-empty-title">No active cooking session</p>
                        <p class="cooking-empty-subtitle">Choose a recipe to start cooking</p>
                        <button
                            type="button"
                            class="cooking-empty-btn"
                            aria-label="Choose a recipe to cook"
                            @click="goToRecipes"
                        >
                            <svg class="cooking-empty-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Choose a recipe to cook
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.cooking-page {
    padding: 1rem;
    max-width: 28rem;
    margin-left: auto;
    margin-right: auto;
}

.cooking-card {
    background: var(--card-bg, #fff);
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    overflow: visible;
    border: 2px solid var(--card-border, #e5e7eb);
}

.cooking-title {
    position: relative;
    padding: 1.25rem 1rem;
    text-align: center;
}

.cooking-mode-label {
    display: block;
    width: fit-content;
    margin: auto;
    padding: 0.4rem 1rem;
    font-size: 0.8125rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--cooking-mode-color, #64748b);
    background: var(--card-bg, #fff);
    border: 2px solid var(--card-border, #e5e7eb);
    border-top: none;
    border-radius: 0 0 0.5rem 0.5rem;
}

.cooking-title-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.cooking-title-icon {
    position: absolute;
    top: 1.25rem;
    right: 1rem;
    padding: 0.5rem;
    border: 2px solid var(--card-border, #e5e7eb);
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.cooking-title-icon:hover {
    border-color: #d1d5db;
    background: #fff;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    transform: scale(1.1);
}

.cooking-title-icon:active {
    transform: scale(0.95);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.cooking-title-icon-img {
    width: 1.25rem;
    height: 1.25rem;
    display: block;
}

.cooking-emoji {
    font-size: 3rem;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.cooking-name {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}

.cooking-description {
    font-size: 0.9375rem;
    line-height: 1.5;
    color: var(--text-muted, #6b7280);
    margin: 0 0 0.5rem 0;
    text-align: center;
}

.cooking-meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.25rem 0.5rem;
    font-size: 0.8125rem;
    color: var(--text-muted, #666);
}

.cooking-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.cooking-meta-icon {
    width: 1.5rem;
    height: 1.5rem;
    flex-shrink: 0;
    color: var(--text-muted, #888);
}

.cooking-meta-icon-cook {
    color: var(--cook-icon-color, #c2410c);
}

.cooking-meta-sep {
    color: var(--text-muted, #bbb);
    user-select: none;
}

.cooking-ingredients {
    padding: 1.25rem 1rem;
    /*background: var(--ingredients-bg, #f8fafc);*/
}

.cooking-ingredients-heading {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.025em;
    color: var(--ingredients-heading-color, #111827);
    margin: 0 0 1rem 0;
}

.ingredient-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.ingredient-drag-handle {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: grab;
    -webkit-tap-highlight-color: transparent;
}

.ingredient-drag-handle:active {
    cursor: grabbing;
}

.ingredient-drag-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--text-muted, #94a3b8);
}

.ingredient-row {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.75rem 1rem;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
    border-radius: 0.75rem;
    background: var(--card-bg, #fff);
    border: 1px solid var(--ingredient-border, #e2e8f0);
    transition: border-color 0.15s ease, background 0.15s ease;
}

.ingredient-row:hover {
    border-color: var(--ingredient-border-hover, #cbd5e1);
}

.ingredient-row.selected {
    background: var(--ingredient-selected-bg, #f1f5f9);
    border-color: var(--ingredient-selected-border, #cbd5e1);
}

.ingredient-row.selected .ingredient-text {
    color: var(--text-muted, #64748b);
    text-decoration: line-through;
}

.ingredient-check {
    flex-shrink: 0;
    width: 1.5rem;
    height: 1.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    border: 2px solid var(--ingredient-check-border, #94a3b8);
    background: var(--card-bg, #fff);
    transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
}

.ingredient-row.selected .ingredient-check {
    border-color: var(--ingredient-check-selected, #0d9488);
    background: var(--ingredient-check-selected, #0d9488);
}

.check-mark {
    color: #fff;
    font-size: 0.875rem;
    font-weight: 700;
    line-height: 1;
}

.check-empty {
    display: block;
    width: 100%;
    height: 100%;
}

.ingredient-text {
    flex: 1;
    font-size: 1rem;
    line-height: 1.45;
    color: var(--ingredient-text-color, #1e293b);
}

.ingredient-empty {
    font-size: 0.875rem;
    color: var(--text-muted, #64748b);
    margin: 0;
}

.page-divider {
    width: 90%;
    margin: 0 auto;
    border: none;
    border-top: 2px solid var(--border-color, #cbd5e1);
}

.cooking-instructions {
    padding: 1.25rem 1rem;
    /*background: var(--instructions-bg, #f8fafc);*/
    /*border-top: 1px solid var(--border-color, #e2e8f0);*/
}

.cooking-instructions-heading {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.025em;
    color: var(--instructions-heading-color, #111827);
    margin: 0 0 1rem 0;
}

.step-wizard {
    background: var(--card-bg, #fff);
    border: 1px solid var(--instruction-border, #e2e8f0);
    border-radius: 0.75rem;
    padding: 1.25rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.step-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--step-badge-color, #64748b);
    background: var(--step-badge-bg, #f1f5f9);
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    margin-bottom: 1rem;
}

.step-content {
    margin-bottom: 1.25rem;
}

.step-text {
    font-size: 1.1875rem;
    line-height: 1.6;
    color: var(--instruction-text-color, #1e293b);
    margin: 0;
}

.step-nav {
    display: flex;
    gap: 0.75rem;
}

.step-btn {
    flex: 1;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, opacity 0.15s ease;
}

.step-prev {
    border: 1px solid var(--instruction-btn-border, #cbd5e1);
    background: var(--card-bg, #fff);
    color: var(--instruction-btn-text, #475569);
}

.step-prev:not(:disabled):hover {
    background: var(--hover-bg, #f1f5f9);
    border-color: var(--instruction-btn-border-hover, #94a3b8);
}

.step-next {
    border: none;
    background: var(--instruction-next-bg, #0d9488);
    color: #fff;
}

.step-next:not(:disabled):hover {
    background: var(--instruction-next-bg-hover, #0f766e);
}

.step-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.step-progress {
    margin-top: 0.0rem;
    margin-bottom: 1rem;
}

.step-progress-track {
    height: 0.625rem;
    width: 100%;
    background: var(--progress-track-bg, #e2e8f0);
    border-radius: 9999px;
    overflow: hidden;
    border: 1px solid var(--progress-track-border, #e5e7eb);
}

.step-progress-fill {
    height: 100%;
    background: var(--progress-fill-bg, #0d9488);
    border-radius: 9999px;
    transition: width 0.3s ease;
}

.step-progress-label {
    font-size: 0.75rem;
    color: var(--text-muted, #6b7280);
    margin: 0.25rem 0 0 0;
    font-variant-numeric: tabular-nums;
}

.instructions-empty {
    font-size: 0.875rem;
    color: var(--text-muted, #64748b);
    margin: 0;
}

.cooking-section-heading {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted, #666);
    margin: 0 0 0.75rem 0;
}

.empty-hint {
    font-size: 0.875rem;
    color: var(--text-muted, #666);
    margin: 0;
}

.cooking-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 2.5rem 1rem;
    color: #4b5563;
}

.cooking-loading-spinner {
    width: 1.25rem;
    height: 1.25rem;
    animation: cooking-spin 1s linear infinite;
}

.cooking-loading-spinner .opacity-25 {
    opacity: 0.25;
}

.cooking-loading-spinner .opacity-75 {
    opacity: 0.75;
}

@keyframes cooking-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.cooking-loading-text {
    font-size: 0.875rem;
}

.cooking-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem 1rem 1rem;
    margin-bottom: 0.75rem;
}

.cooking-empty-icon {
    width: 5rem;
    height: 5rem;
    color: #d1d5db;
    margin-bottom: 1rem;
    flex-shrink: 0;
}

.cooking-empty-title {
    color: #6b7280;
    text-align: center;
    font-size: 1.125rem;
    font-weight: 500;
    margin: 0 0 0.25rem 0;
}

.cooking-empty-subtitle {
    color: #9ca3af;
    text-align: center;
    font-size: 0.875rem;
    margin: 0 0 1rem 0;
}

.cooking-empty-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}

.cooking-empty-btn:hover {
    border-color: #d1d5db;
    background: #f9fafb;
}

.cooking-empty-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px #3b82f6;
}

.cooking-empty-btn-icon {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}
</style>
