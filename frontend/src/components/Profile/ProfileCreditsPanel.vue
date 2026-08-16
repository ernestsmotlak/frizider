<script setup lang="ts">
/**
 * Where the credits went.
 *
 * Deliberately not the balance — that lives in the strip above the tabs, where
 * it is visible from all three panels. Showing it twice on one screen reads as
 * a mistake, so this answers the other half of the question.
 */
import {onMounted, ref} from "vue";
import {useAiGenerationStore} from "../../stores/aiGeneration.ts";

type LedgerRow = {
    id: number;
    type: string;
    label: string;
    amount: number;
    balance_after: number;
    created_at: string;
};

const store = useAiGenerationStore();

const rows = ref<LedgerRow[]>([]);
const hasMore = ref(false);
const loading = ref(false);
const error = ref<string | null>(null);
let page = 0;

const load = () => {
    if (loading.value) return;

    loading.value = true;
    error.value = null;

    axios.get("/api/ai/credits/ledger", {params: {page: page + 1, per_page: 20}})
        .then((response) => {
            page += 1;
            rows.value = [...rows.value, ...(response.data.transactions ?? [])];
            hasMore.value = Boolean(response.data.has_more);

            // The strip above the tabs shows this number, and a refund changes
            // it server-side with no response to piggyback on. It rides on the
            // ledger, so keeping it honest costs no extra request.
            if (typeof response.data.credits_remaining === "number") {
                store.creditsRemaining = response.data.credits_remaining;
            }
        })
        .catch(() => {
            error.value = "Could not load your credit history.";
        })
        .finally(() => {
            loading.value = false;
        });
};

// The panel only exists while its tab is open, so mounting is the moment to
// ask — and re-opening the tab is how you check after spending one.
onMounted(load);

const rowDate = (row: LedgerRow) => new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
}).format(new Date(row.created_at));

/** Signed, always — the sign is the whole story of a ledger row. */
const rowAmount = (row: LedgerRow) => (row.amount > 0 ? `+${row.amount}` : String(row.amount));
</script>

<template>
    <section class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Credits</h2>
            <p class="panel-caption">Every one spent and refunded</p>
        </div>

        <p v-if="error" class="ledger-error">{{ error }}</p>

        <ul v-if="rows.length" class="ledger-list">
            <li v-for="row in rows" :key="row.id" class="ledger-item">
                <span
                    class="ledger-dot"
                    :class="row.amount > 0 ? 'ledger-dot--in' : 'ledger-dot--out'"
                    aria-hidden="true"
                ></span>

                <span class="ledger-text">
                    <span class="ledger-title">{{ row.label }}</span>
                    <span class="ledger-meta">{{ rowDate(row) }} · {{ row.balance_after }} left</span>
                </span>

                <span
                    class="ledger-amount tabular-nums"
                    :class="row.amount > 0 ? 'ledger-amount--in' : 'ledger-amount--out'"
                >
                    {{ rowAmount(row) }}
                </span>
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
            <p class="placeholder-copy">Loading your credits…</p>
        </div>

        <div v-else-if="!rows.length && !error" class="placeholder">
            <p class="placeholder-title">Nothing spent yet</p>
            <p class="placeholder-copy">
                Every generation you run will be listed here, along with the credit coming back
                whenever one fails.
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
    gap: 0.5rem;
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

.ledger-list {
    display: grid;
    gap: 0.52rem;
}

.ledger-item {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.7rem 0.78rem;
    border-radius: 0.9rem;
    border: 1px solid color-mix(in srgb, var(--line-soft) 82%, white 18%);
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 9px 18px rgba(7, 82, 58, 0.08);
}

.ledger-dot {
    flex-shrink: 0;
    width: 0.55rem;
    height: 0.55rem;
    border-radius: 9999px;
}

.ledger-dot--in {
    background: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-soft);
}

.ledger-dot--out {
    background: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
}

.ledger-text {
    display: grid;
    gap: 0.1rem;
    min-width: 0;
    flex: 1;
}

.ledger-title {
    font-size: 0.9rem;
    font-weight: 670;
    color: var(--text-strong);
    overflow-wrap: anywhere;
}

.ledger-meta {
    font-size: 0.74rem;
    color: var(--text-muted);
}

.ledger-amount {
    flex-shrink: 0;
    font-size: 0.92rem;
    font-weight: 750;
}

.ledger-amount--in {
    color: var(--accent-strong);
}

.ledger-amount--out {
    color: var(--text-muted);
}

.ledger-error {
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

.placeholder-title {
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
