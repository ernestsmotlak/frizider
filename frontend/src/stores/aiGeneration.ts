import {defineStore} from "pinia";
import {computed, ref} from "vue";

export type AiGenerationStatus = "idle" | "submitting" | "generating" | "completed" | "failed";

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
 * Highest generation id the user has already been shown — a watermark, not a
 * single id. Generation ids only ever go up, so burying one buries everything
 * older with it and a stale failure can never resurface after a newer success.
 */
const SEEN_KEY = "ai-generation-dismissed";

const seenWatermark = () => Number(localStorage.getItem(SEEN_KEY) ?? 0) || 0;

/**
 * App-wide watcher for the current AI generation. The modal and the pill are
 * both just viewers of this store — the generation itself always completes
 * (or refunds) server-side, whether or not anyone is watching.
 */
export const useAiGenerationStore = defineStore("aiGeneration", () => {
    const status = ref<AiGenerationStatus>("idle");
    const recipeId = ref<number | null>(null);
    const error = ref<string | null>(null);
    const creditsRemaining = ref<number | null>(null);
    const modalOpen = ref(false);

    let generationId: number | null = null;
    let idempotencyKey: string | null = null;
    let pollTimer: ReturnType<typeof setTimeout> | null = null;
    let abortController: AbortController | null = null;
    let startedAt = 0;
    let consecutiveErrors = 0;
    let booted = false;

    const isBusy = computed(() => status.value === "submitting" || status.value === "generating");

    const submit = (ingredients: AiIngredientInput[]) => {
        if (isBusy.value) return;

        // Reused on retry after a network error (same intent, no double
        // charge); cleared once a generation reaches a terminal state, so a
        // fresh attempt is a fresh generation.
        idempotencyKey = idempotencyKey ?? crypto.randomUUID();
        status.value = "submitting";
        error.value = null;

        axios.post("/api/recipe/ai/generate-recipe", {
            ingredients,
            idempotency_key: idempotencyKey,
        })
            .then((response) => {
                generationId = response.data.generation_id;
                creditsRemaining.value = response.data.credits_remaining ?? null;
                startWatching();
            })
            .catch((err) => {
                status.value = "failed";
                error.value = err?.response?.data?.message || "Could not start the generation.";
            });
    };

    /** Pick up a generation this session did not start (e.g. after a refresh). */
    const resume = (id: number) => {
        generationId = id;
        startWatching();
    };

    /**
     * Once per app load: ask the server whether anything is running or
     * finished while nobody was watching, and pick up where things stand.
     */
    const bootCheck = () => {
        if (booted || status.value !== "idle") return;
        booted = true;

        axios.get("/api/recipe/ai/active-generations")
            .then((response) => {
                const rows = response.data.generations ?? [];

                const running = rows.find((row: any) => row.status === "pending" || row.status === "processing");

                if (running) {
                    resume(running.generation_id);
                    return;
                }

                const seen = seenWatermark();
                const unseen = rows.find((row: any) => Number(row.generation_id) > seen);

                if (!unseen) return;

                generationId = unseen.generation_id;

                if (unseen.status === "completed" && unseen.recipe_id) {
                    status.value = "completed";
                    recipeId.value = unseen.recipe_id;
                } else if (unseen.status === "failed") {
                    status.value = "failed";
                    error.value = unseen.error || "Recipe generation failed. Your credit was refunded.";
                }
            })
            .catch(() => {
                // Best-effort — a failed boot check must never break the app.
            });
    };

    /** The user has seen this result — never announce it, or anything older, again. */
    const acknowledge = () => {
        if (generationId !== null) {
            localStorage.setItem(SEEN_KEY, String(Math.max(seenWatermark(), generationId)));
        }

        reset();
    };

    const startWatching = () => {
        status.value = "generating";
        startedAt = Date.now();
        consecutiveErrors = 0;
        schedulePoll(POLL_FIRST_MS);
    };

    const poll = () => {
        if (status.value !== "generating" || generationId === null) return;

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
        status.value = result;
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

    const reset = () => {
        clearTimer();
        abortController?.abort();
        status.value = "idle";
        generationId = null;
        idempotencyKey = null;
        recipeId.value = null;
        error.value = null;
    };

    return {
        status,
        isBusy,
        recipeId,
        error,
        creditsRemaining,
        modalOpen,
        submit,
        bootCheck,
        acknowledge,
        reset,
    };
});
