import {defineStore} from "pinia";
import {computed, ref} from "vue";

export type AiJobStatus = "pending" | "processing" | "completed" | "failed";

/** How the modal sees the one generation it started. */
export type AiSubmissionStatus = "idle" | "submitting" | "generating" | "completed" | "failed";

export interface AiJob {
    id: number;
    action: string;
    status: AiJobStatus;
    recipeId: number | null;
    error: string | null;
}

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
 * Operations whose result is a list to review rather than a recipe to open.
 * They land on their own page keyed by generation id, because the items do not
 * exist anywhere yet — there is nothing else to link to.
 */
const REVIEW_ACTIONS = new Set(["pantry_from_photo"]);

/**
 * Wording per operation. An action with no entry falls back to neutral copy,
 * so the server can ship a new operation before the frontend knows its name.
 */
const ACTION_COPY: Record<string, { running: string; done: string; failed: string }> = {
    generate_recipe_from_ingredients: {
        running: "Cooking…",
        done: "Recipe ready",
        failed: "Recipe generation failed",
    },
    turn_vegetarian: {
        running: "Turning vegetarian…",
        done: "Vegetarian version ready",
        failed: "Vegetarian conversion failed",
    },
    turn_vegan: {
        running: "Turning vegan…",
        done: "Vegan version ready",
        failed: "Vegan conversion failed",
    },
    pantry_from_photo: {
        running: "Reading the photo…",
        done: "Scan ready to review",
        failed: "Could not read the photo",
    },
};

const FALLBACK_COPY = {running: "Working…", done: "Ready", failed: "Something went wrong"};

export const actionCopy = (action: string) => ACTION_COPY[action] ?? FALLBACK_COPY;

export const isRunning = (job: AiJob) => job.status === "pending" || job.status === "processing";

/**
 * Where a finished job wants to be looked at, or null if there is nothing to
 * show. Every viewer asks this rather than reading recipeId itself, so adding
 * an operation is an entry here and nowhere else.
 */
export const resultRoute = (job: AiJob): string | null => {
    if (job.status !== "completed") return null;

    if (REVIEW_ACTIONS.has(job.action)) return `/pantry/scan/${job.id}`;

    return job.recipeId === null ? null : `/recipe/${job.recipeId}`;
};

const toJob = (row: any): AiJob => ({
    id: row.generation_id,
    action: row.action ?? "",
    status: row.status,
    recipeId: row.recipe_id ?? null,
    error: row.error ?? null,
});

/**
 * App-wide watcher for the user's AI jobs. The pill and the modal are both
 * viewers — the work always completes (or refunds) server-side, whether or not
 * anyone is watching, and the server alone decides what is still worth
 * announcing.
 */
export const useAiGenerationStore = defineStore("aiGeneration", () => {
    /** Everything the server is announcing: running, plus finished-but-unacknowledged. */
    const jobs = ref<AiJob[]>([]);
    /** A submit request is in flight. The only thing that blocks another submit. */
    const submitting = ref(false);
    /** A submission that never became a job — distinct from a job that failed. */
    const submitError = ref<string | null>(null);
    /** Set when we stop watching before the work finished. */
    const stalled = ref<string | null>(null);
    const creditsRemaining = ref<number | null>(null);
    const modalOpen = ref(false);
    const expanded = ref(false);

    /** The job this session started, so the modal can follow its own work. */
    const submittedId = ref<number | null>(null);

    let idempotencyKey: string | null = null;
    let pollTimer: ReturnType<typeof setTimeout> | null = null;
    let pollingSince = 0;
    let consecutiveErrors = 0;
    let inFlight = false;
    let booted = false;

    // Dismissed here but not yet confirmed by the server. Without this, a poll
    // already in flight when the user taps would put the row straight back.
    const dismissed = new Set<number>();

    const running = computed(() => jobs.value.filter(isRunning));
    const finished = computed(() => jobs.value.filter((job) => !isRunning(job)));
    const hasFailure = computed(() => finished.value.some((job) => job.status === "failed"));
    const isBusy = computed(() => submitting.value || running.value.length > 0);

    const submission = computed(() => jobs.value.find((job) => job.id === submittedId.value) ?? null);

    const submissionStatus = computed<AiSubmissionStatus>(() => {
        if (submitting.value) return "submitting";
        if (submitError.value !== null) return "failed";

        const job = submission.value;

        if (job === null) return "idle";

        return job.status === "completed" || job.status === "failed" ? job.status : "generating";
    });

    const submissionRecipeId = computed(() => submission.value?.recipeId ?? null);
    const submissionError = computed(() => submitError.value ?? submission.value?.error ?? null);

    const applyRows = (rows: any[]) => {
        const incoming = rows.map(toJob);

        jobs.value = incoming.filter((job) => !dismissed.has(job.id));

        // Once the server agrees a row is gone, stop holding it back.
        const present = new Set(incoming.map((job) => job.id));
        dismissed.forEach((id) => {
            if (!present.has(id)) dismissed.delete(id);
        });
    };

    const refresh = (): Promise<void> => {
        if (inFlight) return Promise.resolve();
        inFlight = true;

        return axios.get("/api/recipe/ai/active-generations")
            .then((response) => {
                consecutiveErrors = 0;
                applyRows(response.data.generations ?? []);
            })
            .finally(() => {
                inFlight = false;
            });
    };

    /**
     * Every submit path ends here: take the 202, adopt the new job as this
     * session's own, and start watching. The operations differ only in what
     * they post.
     *
     * @param request the POST, already built by the caller
     * @param failure what to say if it never lands
     */
    const start = (request: Promise<any>, failure: string) => {
        // Only an outstanding request blocks — a running job does not, or
        // starting a second generation would be impossible.
        if (submitting.value) return;

        submitting.value = true;
        submitError.value = null;

        request
            .then((response) => {
                idempotencyKey = null;
                creditsRemaining.value = response.data.credits_remaining ?? null;

                const id = response.data.generation_id;
                submittedId.value = id;

                // Show it now rather than at the first poll.
                jobs.value = [
                    {
                        id,
                        action: response.data.action ?? "",
                        status: "pending",
                        recipeId: null,
                        error: null,
                    },
                    ...jobs.value.filter((job) => job.id !== id),
                ];

                startPolling();
            })
            .catch((err) => {
                submitError.value = err?.response?.data?.message || failure;
            })
            .finally(() => {
                submitting.value = false;
            });
    };

    /**
     * A key per submission, held only across a failed submit so retrying a
     * request that may already have landed cannot charge twice. Cleared on
     * success, so the next submission is genuinely a new generation rather than
     * a duplicate of the last one.
     */
    const nextKey = () => {
        idempotencyKey = idempotencyKey ?? crypto.randomUUID();

        return idempotencyKey;
    };

    const submit = (ingredients: AiIngredientInput[]) => {
        if (submitting.value) return;

        start(
            axios.post("/api/recipe/ai/generate-recipe", {
                ingredients,
                idempotency_key: nextKey(),
            }),
            "Could not start the generation.",
        );
    };

    /**
     * A shelf photo, already downscaled by the caller. Multipart rather than
     * JSON — the bytes are the request, and base64 would inflate them by a
     * third for no gain.
     */
    const submitPhoto = (photo: Blob) => {
        if (submitting.value) return;

        const body = new FormData();
        body.append("photo", photo, "shelf.jpg");
        body.append("idempotency_key", nextKey());

        start(
            axios.post("/api/pantry/ai/from-photo", body),
            "Could not upload the photo.",
        );
    };

    /**
     * Once per app load: ask what is running or waiting to be announced, and
     * pick up from there. Nothing is stored client-side between loads — the
     * server is asked who the user is and answers with their work.
     */
    const bootCheck = () => {
        if (booted) return;
        booted = true;

        refresh()
            .then(() => {
                if (running.value.length > 0) startPolling();
            })
            .catch(() => {
                // Best-effort — a failed boot check must never break the app.
            });
    };

    const startPolling = () => {
        // New work earns a fresh ceiling.
        pollingSince = Date.now();
        consecutiveErrors = 0;
        stalled.value = null;

        if (pollTimer === null) schedulePoll(POLL_FIRST_MS);
    };

    const poll = () => {
        pollTimer = null;

        if (running.value.length === 0) return;

        if (Date.now() - pollingSince > POLL_CEILING_MS) {
            stalled.value = "This is taking longer than expected. Anything that finishes will still appear in your recipes.";
            return;
        }

        refresh()
            .then(() => {
                if (running.value.length > 0) schedulePoll(POLL_INTERVAL_MS);
            })
            .catch(() => {
                consecutiveErrors += 1;

                if (consecutiveErrors >= MAX_CONSECUTIVE_ERRORS) {
                    stalled.value = "Lost connection while waiting. Anything that finishes will appear in your recipes.";
                } else {
                    schedulePoll(POLL_INTERVAL_MS);
                }
            });
    };

    const schedulePoll = (delay: number) => {
        if (pollTimer !== null) clearTimeout(pollTimer);
        pollTimer = setTimeout(poll, delay);
    };

    /**
     * The user has been told about this one — never announce it again, here or
     * on any other device. Best effort: if the call never lands the result is
     * announced once more, which beats the client holding a private opinion
     * the server cannot see.
     */
    const acknowledge = (id: number) => {
        forget([id]);
        axios.post(`/api/recipe/ai/generations/${id}/acknowledge`).catch(() => {
        });
    };

    /**
     * The pill's clear button. The caller passes the ids it actually had on
     * screen rather than asking to clear everything, so a run that finishes
     * between render and tap is not dismissed unseen. The server ignores any
     * that are still running.
     */
    const acknowledgeMany = (ids: number[]) => {
        if (ids.length === 0) {
            expanded.value = false;
            return;
        }

        forget(ids);
        axios.post("/api/recipe/ai/generations/acknowledge", {ids}).catch(() => {
        });
    };

    const forget = (ids: number[]) => {
        ids.forEach((id) => dismissed.add(id));

        jobs.value = jobs.value.filter((job) => !ids.includes(job.id));

        if (submittedId.value !== null && ids.includes(submittedId.value)) {
            submittedId.value = null;
        }

        if (jobs.value.length === 0) expanded.value = false;
    };

    /** Drop the modal's view of its own submission. Watching continues. */
    const clearSubmission = () => {
        submittedId.value = null;
        submitError.value = null;
    };

    return {
        jobs,
        running,
        finished,
        hasFailure,
        isBusy,
        submitting,
        stalled,
        expanded,
        creditsRemaining,
        modalOpen,
        submissionId: submittedId,
        submissionStatus,
        submissionRecipeId,
        submissionError,
        submit,
        submitPhoto,
        bootCheck,
        acknowledge,
        acknowledgeMany,
        clearSubmission,
    };
});
