<script setup lang="ts">
import {onMounted, ref} from "vue";
import {useRouter} from "vue-router";
import {actionCopy} from "../../stores/aiGeneration.ts";

type GenerationRow = {
    generation_id: number;
    action: string;
    status: "pending" | "processing" | "completed" | "failed";
    recipe_id: number | null;
    recipe_name: string | null;
    error: string | null;
    created_at: string;
};

const router = useRouter();

const rows = ref<GenerationRow[]>([]);
const totals = ref<{ all: number; completed: number } | null>(null);
const hasMore = ref(false);
const loading = ref(false);
const error = ref<string | null>(null);
let page = 0;

const load = () => {
    if (loading.value) return;

    loading.value = true;
    error.value = null;

    axios.get("/api/recipe/ai/generations", {params: {page: page + 1, per_page: 20}})
        .then((response) => {
            page += 1;
            rows.value = [...rows.value, ...(response.data.generations ?? [])];
            totals.value = response.data.totals ?? null;
            hasMore.value = Boolean(response.data.has_more);
        })
        .catch(() => {
            error.value = "Could not load your history.";
        })
        .finally(() => {
            loading.value = false;
        });
};

// The panel only exists while its tab is open, so mounting is the moment to
// ask. Re-opening the tab refetches, which is what you want after generating
// something and coming back to look at it.
onMounted(load);

const rowTitle = (row: GenerationRow) => {
    const copy = actionCopy(row.action);

    if (row.status === "completed") return row.recipe_name ?? copy.done;
    if (row.status === "failed") return copy.failed;

    return copy.running;
};

// A completed run whose recipe has since been deleted keeps its place in the
// history — there is just nothing left to open.
const rowNote = (row: GenerationRow) => {
    if (row.status === "completed" && row.recipe_id === null) return "Recipe deleted";
    if (row.status === "failed") return "Credit refunded";

    return null;
};

const rowDate = (row: GenerationRow) => new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
}).format(new Date(row.created_at));

const openRow = (row: GenerationRow) => {
    if (row.recipe_id !== null) router.push(`/recipe/${row.recipe_id}`);
};
</script>

<template>
    <section class="panel">
        <div class="panel-head">
            <h2 class="panel-title">AI history</h2>
            <p v-if="totals" class="panel-caption">
                {{ totals.completed }} of {{ totals.all }} succeeded
            </p>
        </div>

        <p v-if="error" class="history-error">{{ error }}</p>

        <ul v-if="rows.length" class="history-list">
            <li v-for="row in rows" :key="row.generation_id">
                <component
                    :is="row.recipe_id !== null ? 'button' : 'div'"
                    class="history-item"
                    :class="row.recipe_id !== null ? 'history-item--open' : ''"
                    @click="row.recipe_id !== null ? openRow(row) : undefined"
                >
                    <span class="history-dot" :class="`history-dot--${row.status}`" aria-hidden="true"></span>

                    <span class="history-text">
                        <span class="history-title">{{ rowTitle(row) }}</span>
                        <span class="history-meta">
                            {{ rowDate(row) }}<template v-if="rowNote(row)"> · {{ rowNote(row) }}</template>
                        </span>
                    </span>

                    <svg
                        v-if="row.recipe_id !== null"
                        class="history-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </component>
            </li>
        </ul>

        <button
            v-if="hasMore"
            type="button"
            class="load-more"
            :disabled="loading"
            @click="load"
        >
            {{ loading ? "Loading…" : "Load more" }}
        </button>

        <div v-else-if="loading" class="placeholder">
            <p class="placeholder-copy">Loading your history…</p>
        </div>

        <div v-else-if="!rows.length && !error" class="placeholder">
            <div class="placeholder-icon" aria-hidden="true">📜</div>
            <p class="placeholder-title">No generations yet</p>
            <p class="placeholder-copy">
                Recipes you generate with AI will be listed here — when they ran, whether they
                worked, and a link straight to the recipe.
            </p>
        </div>
    </section>
</template>

<style scoped>
.panel-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.panel-title {
    font-size: 1.12rem;
    font-weight: 750;
    letter-spacing: -0.01em;
    color: var(--text-strong);
}

.panel-caption {
    font-size: 0.76rem;
    color: var(--text-muted);
}

.history-list {
    display: grid;
    gap: 0.52rem;
}

.history-item {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.7rem 0.78rem;
    border-radius: 0.9rem;
    border: 1px solid color-mix(in srgb, var(--line-soft) 82%, white 18%);
    background: rgba(255, 255, 255, 1);
    text-align: left;
    box-shadow: 0 9px 18px rgba(7, 82, 58, 0.08);
    transition: border-color 170ms ease, transform 170ms ease, box-shadow 170ms ease;
}

.history-item--open:hover {
    border-color: var(--line-strong);
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(7, 82, 58, 0.13);
}

.history-dot {
    flex-shrink: 0;
    width: 0.55rem;
    height: 0.55rem;
    border-radius: 9999px;
}

.history-dot--completed {
    background: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-soft);
}

.history-dot--failed {
    background: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
}

.history-dot--pending,
.history-dot--processing {
    background: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
}

.history-text {
    display: grid;
    gap: 0.1rem;
    min-width: 0;
    flex: 1;
}

.history-title {
    font-size: 0.9rem;
    font-weight: 670;
    color: var(--text-strong);
    overflow-wrap: anywhere;
}

.history-meta {
    font-size: 0.74rem;
    color: var(--text-muted);
}

.history-arrow {
    flex-shrink: 0;
    width: 1rem;
    height: 1rem;
    color: var(--text-muted);
}

.history-error {
    padding: 0.7rem 0.78rem;
    border-radius: 0.9rem;
    border: 1px solid rgba(220, 38, 38, 0.22);
    background: rgba(254, 242, 242, 0.9);
    color: #b91c1c;
    font-size: 0.82rem;
}

.load-more {
    width: 100%;
    margin-top: 0.52rem;
    padding: 0.62rem 0.78rem;
    border-radius: 0.9rem;
    border: 1px solid color-mix(in srgb, var(--accent) 24%, white 76%);
    background: color-mix(in srgb, var(--accent-soft) 40%, white 60%);
    color: var(--accent-strong);
    font-size: 0.85rem;
    font-weight: 670;
    transition: background-color 170ms ease;
}

.load-more:hover:not(:disabled) {
    background: color-mix(in srgb, var(--accent-soft) 62%, white 38%);
}

.load-more:disabled {
    opacity: 0.6;
}

.placeholder {
    padding: 1.6rem 1.1rem;
    text-align: center;
    border-radius: 1rem;
    border: 1px dashed color-mix(in srgb, var(--line-strong) 70%, white 30%);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(250, 255, 251, 0.9) 100%);
}

.placeholder-icon {
    font-size: 1.5rem;
    line-height: 1;
}

.placeholder-title {
    margin-top: 0.55rem;
    font-size: 0.94rem;
    font-weight: 700;
    color: var(--text-strong);
}

.placeholder-copy {
    margin-top: 0.28rem;
    font-size: 0.8rem;
    line-height: 1.45;
    color: var(--text-muted);
}
</style>
