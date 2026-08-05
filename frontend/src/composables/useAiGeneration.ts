import {computed, onUnmounted, ref} from "vue";

export type AiGenerationState = "idle" | "submitting" | "generating" | "completed" | "failed";

export interface AiIngredientInput {
    id: number | null;
    name: string;
    quantity: number | string | null;
    unit: string | null;
}

const POLL_FIRST_MS = 600;
const POLL_INTERVAL_MS = 2000;
const POLL_CEILING_MS = 3 * 60 * 1000;
const MAX_CONSECUTIVE_ERRORS = 5;

/**
 * Submit an AI generation and watch it until it finishes. Polling is just
 * watching — the generation always completes (or refunds) server-side,
 * whether or not anyone is looking.
 */
export function useAiGeneration() {
    const state = ref<AiGenerationState>("idle");
    const recipeId = ref<number | null>(null);
    const error = ref<string | null>(null);
    const creditsRemaining = ref<number | null>(null);

    let generationId: number | null = null;
    let idempotencyKey: string | null = null;
    let pollTimer: ReturnType<typeof setTimeout> | null = null;
    let abortController: AbortController | null = null;
    let startedAt = 0;
    let consecutiveErrors = 0;

    const isBusy = computed(() => state.value === "submitting" || state.value === "generating");

    const submit = (ingredients: AiIngredientInput[]) => {
        if (isBusy.value) return;

        // Reused on retry after a network error (same intent, no double
        // charge); cleared once a generation actually reaches a terminal
        // state, so a fresh attempt is a fresh generation.
        idempotencyKey = idempotencyKey ?? crypto.randomUUID();
        state.value = "submitting";
        error.value = null;

        axios.post("/api/recipe/ai/generate-recipe", {
            ingredients,
            idempotency_key: idempotencyKey,
        })
            .then((response) => {
                generationId = response.data.generation_id;
                creditsRemaining.value = response.data.credits_remaining ?? null;
                state.value = "generating";
                startedAt = Date.now();
                consecutiveErrors = 0;
                schedulePoll(POLL_FIRST_MS);
            })
            .catch((err) => {
                state.value = "failed";
                error.value = err?.response?.data?.message || "Could not start the generation.";
            });
    };

    const poll = () => {
        if (state.value !== "generating" || generationId === null) return;

        if (Date.now() - startedAt > POLL_CEILING_MS) {
            finish("failed", null, "This is taking longer than expected. If it finishes, the recipe will appear in your recipes.");
            return;
        }

        abortController = new AbortController();

        axios.get(`/api/recipe/ai/generations/${generationId}`, {signal: abortController.signal})
            .then((response) => {
                consecutiveErrors = 0;
                const data = response.data;

                if (data.status === "completed") {
                    finish("completed", data.recipe_id, null);
                } else if (data.status === "failed") {
                    finish("failed", null, data.error || "Generation failed. Your credit was refunded.");
                } else {
                    schedulePoll(POLL_INTERVAL_MS);
                }
            })
            .catch((err) => {
                if (err?.code === "ERR_CANCELED") return;

                consecutiveErrors += 1;

                if (consecutiveErrors >= MAX_CONSECUTIVE_ERRORS) {
                    finish("failed", null, "Lost connection while waiting. The recipe may still appear in your recipes.");
                } else {
                    schedulePoll(POLL_INTERVAL_MS);
                }
            });
    };

    const finish = (result: "completed" | "failed", recipe: number | null, message: string | null) => {
        state.value = result;
        recipeId.value = recipe;
        error.value = message;
        idempotencyKey = null;
    };

    const schedulePoll = (delay: number) => {
        clearTimer();
        pollTimer = setTimeout(poll, delay);
    };

    const clearTimer = () => {
        if (pollTimer !== null) {
            clearTimeout(pollTimer);
            pollTimer = null;
        }
    };

    const stop = () => {
        clearTimer();
        abortController?.abort();
    };

    const reset = () => {
        stop();
        state.value = "idle";
        generationId = null;
        idempotencyKey = null;
        recipeId.value = null;
        error.value = null;
    };

    onUnmounted(stop);

    return {state, isBusy, recipeId, error, creditsRemaining, submit, reset};
}
