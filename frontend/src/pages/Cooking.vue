<script setup lang="ts">
import { onMounted, ref, computed } from "vue";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import { useLoadingStore } from "../stores/loading";
import { useToastStore } from "../stores/toast";
import { formatTime } from "../utils/formatTime";
import type { Recipe, RecipeIngredient, RecipeInstruction } from "./Recipe/RecipePage.vue";

const loadingStore = useLoadingStore();
const toasterStore = useToastStore();

const recipe = ref<Recipe | null>(null);
const currentStepIndex = ref(0);
const selectedIngredientIds = ref<Set<number>>(new Set());

const sortedIngredients = computed((): RecipeIngredient[] => {
    const list = recipe.value?.recipe_ingredients ?? [];
    return [...list].sort((a, b) => a.sort_order - b.sort_order);
});

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

function toggleIngredient(id: number): void {
    const next = new Set(selectedIngredientIds.value);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    selectedIngredientIds.value = next;
}

function prevStep(): void {
    if (currentStepIndex.value > 0) currentStepIndex.value--;
}

function nextStep(): void {
    const max = sortedInstructions.value.length - 1;
    if (currentStepIndex.value < max) currentStepIndex.value++;
}

const fetchCookingSession = () => {
    loadingStore.start();
    axios
        .get("/api/get-cooking-session")
        .then((result) => {
            recipe.value = result.data.data as Recipe;
            currentStepIndex.value = 0;
            selectedIngredientIds.value = new Set();
        })
        .catch(() => {
            toasterStore.show("error", "Error fetching cooking session data.");
        })
        .finally(() => {
            loadingStore.stop();
        });
};

onMounted(() => {
    fetchCookingSession();
});
</script>

<template>
    <DashboardLayout>
        <div class="cooking-page">
            <div v-if="recipe" class="cooking-card">
                <header class="cooking-title">
                    <div v-if="recipe.image_url" class="cooking-emoji" aria-hidden="true">
                        {{ recipe.image_url }}
                    </div>
                    <h1 class="cooking-name">{{ recipe.name }}</h1>
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
                </header>

                <section class="cooking-ingredients">
                    <h2 class="cooking-section-heading">Ingredients</h2>
                    <ul class="ingredient-list">
                        <li
                            v-for="ing in sortedIngredients"
                            :key="ing.id"
                            class="ingredient-row"
                            :class="{ selected: selectedIngredientIds.has(ing.id) }"
                            @click="toggleIngredient(ing.id)"
                        >
                            <span class="ingredient-check" aria-hidden="true">
                                <span v-if="selectedIngredientIds.has(ing.id)" class="check-mark">✓</span>
                                <span v-else class="check-empty">○</span>
                            </span>
                            <span class="ingredient-text">{{ formatIngredient(ing) }}</span>
                        </li>
                    </ul>
                    <p v-if="sortedIngredients.length === 0" class="empty-hint">No ingredients.</p>
                </section>

                <section class="cooking-instructions">
                    <h2 class="cooking-section-heading">Instructions</h2>
                    <div v-if="currentInstruction" class="step-wizard">
                        <p class="step-indicator">
                            Step {{ currentStepIndex + 1 }} of {{ sortedInstructions.length }}
                        </p>
                        <p class="step-text">{{ currentInstruction.instruction }}</p>
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
                    <p v-else class="empty-hint">No instructions.</p>
                </section>
            </div>

            <div v-else class="cooking-loading">Loading recipe…</div>
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
    overflow: hidden;
}

.cooking-title {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid var(--border-color, #eee);
    text-align: center;
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

.cooking-ingredients,
.cooking-instructions {
    padding: 1rem;
}

.cooking-section-heading {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted, #666);
    margin: 0 0 0.75rem 0;
}

.ingredient-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.ingredient-row {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.5rem 0;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
    border-radius: 0.5rem;
}

.ingredient-row:hover {
    background: var(--hover-bg, #f5f5f5);
}

.ingredient-row.selected .ingredient-text {
    color: var(--text-muted, #666);
    text-decoration: line-through;
}

.ingredient-check {
    flex-shrink: 0;
    width: 1.25rem;
    height: 1.25rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}

.check-mark {
    color: var(--success-color, #0a0);
}

.check-empty {
    color: var(--text-muted, #999);
}

.ingredient-text {
    flex: 1;
    font-size: 1rem;
    line-height: 1.4;
}

.empty-hint {
    font-size: 0.875rem;
    color: var(--text-muted, #666);
    margin: 0;
}

.cooking-instructions {
    border-top: 1px solid var(--border-color, #eee);
}

.step-wizard {
    padding: 0.25rem 0;
}

.step-indicator {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted, #666);
    margin: 0 0 0.5rem 0;
}

.step-text {
    font-size: 1.125rem;
    line-height: 1.5;
    margin: 0 0 1.25rem 0;
}

.step-nav {
    display: flex;
    gap: 0.75rem;
}

.step-btn {
    flex: 1;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 0.5rem;
    border: 1px solid var(--border-color, #ddd);
    background: var(--card-bg, #fff);
    cursor: pointer;
}

.step-btn:not(:disabled):hover {
    background: var(--hover-bg, #f5f5f5);
}

.step-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.cooking-loading {
    text-align: center;
    padding: 2rem;
    color: var(--text-muted, #666);
}
</style>
