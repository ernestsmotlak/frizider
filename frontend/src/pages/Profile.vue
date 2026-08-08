<script setup lang="ts">
import {computed, ref, watch} from "vue";
import {useRoute, useRouter} from "vue-router";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import {useAuthStore} from "../stores/auth.ts";
import {actionCopy} from "../stores/aiGeneration.ts";

type ProfileTab = "account" | "history" | "credits";

type GenerationRow = {
    generation_id: number;
    action: string;
    status: "pending" | "processing" | "completed" | "failed";
    recipe_id: number | null;
    recipe_name: string | null;
    error: string | null;
    created_at: string;
};

type ProfileUser = {
    name?: string;
    email?: string;
    created_at?: string;
};

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const tabs: { id: ProfileTab; label: string }[] = [
    {id: "account", label: "Account"},
    {id: "history", label: "AI history"},
    {id: "credits", label: "Credits"},
];

// The tab lives in the URL, so a refresh or a back-navigation lands on the
// panel you were reading instead of resetting to Account.
const activeTab = computed<ProfileTab>(() => {
    const requested = typeof route.query.tab === "string" ? route.query.tab : "";
    return tabs.some((tab) => tab.id === requested) ? requested as ProfileTab : "account";
});

const selectTab = (tab: ProfileTab) => {
    if (tab === activeTab.value) return;
    router.replace({query: {...route.query, tab}});
};

const user = computed(() => auth.user as ProfileUser | null);

const displayName = computed(() => user.value?.name || user.value?.email?.split("@")[0] || "Chef");

const initial = computed(() => displayName.value.trim().charAt(0).toUpperCase() || "?");

const memberSince = computed(() => {
    const created = user.value?.created_at;
    if (!created) return null;

    return new Intl.DateTimeFormat("en-US", {month: "long", year: "numeric"}).format(new Date(created));
});

const history = ref<GenerationRow[]>([]);
const historyTotals = ref<{ all: number; completed: number } | null>(null);
const historyHasMore = ref(false);
const historyLoading = ref(false);
const historyError = ref<string | null>(null);
let historyPage = 0;

const loadHistory = () => {
    if (historyLoading.value) return;

    historyLoading.value = true;
    historyError.value = null;

    axios.get("/api/recipe/ai/generations", {params: {page: historyPage + 1, per_page: 20}})
        .then((response) => {
            historyPage += 1;
            history.value = [...history.value, ...(response.data.generations ?? [])];
            historyTotals.value = response.data.totals ?? null;
            historyHasMore.value = Boolean(response.data.has_more);
        })
        .catch(() => {
            historyError.value = "Could not load your history.";
        })
        .finally(() => {
            historyLoading.value = false;
        });
};

// Fetched the first time the tab is opened, not on page load — most visits to
// this page are not about the history.
watch(activeTab, (tab) => {
    if (tab === "history" && historyPage === 0) loadHistory();
}, {immediate: true});

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
    <DashboardLayout>
        <div class="profile-page px-5 pt-6 pb-24">
            <section class="identity-card">
                <div class="identity-avatar" aria-hidden="true">{{ initial }}</div>
                <div class="identity-text">
                    <p class="identity-eyebrow">Your profile</p>
                    <h1 class="identity-name">{{ displayName }}</h1>
                    <p class="identity-email">{{ user?.email ?? "Not signed in" }}</p>
                </div>
            </section>

            <nav class="tab-strip" aria-label="Profile sections">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    class="tab-strip__item"
                    :class="tab.id === activeTab ? 'tab-strip__item--active' : ''"
                    :aria-current="tab.id === activeTab ? 'page' : undefined"
                    @click="selectTab(tab.id)"
                >
                    {{ tab.label }}
                </button>
            </nav>

            <section v-if="activeTab === 'account'" class="panel">
                <div class="section-head">
                    <h2 class="section-title">Account</h2>
                    <p class="section-caption">Your details</p>
                </div>

                <dl class="detail-list">
                    <div class="detail-row">
                        <dt class="detail-label">Name</dt>
                        <dd class="detail-value">{{ user?.name || "—" }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt class="detail-label">Email</dt>
                        <dd class="detail-value">{{ user?.email || "—" }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt class="detail-label">Member since</dt>
                        <dd class="detail-value">{{ memberSince || "—" }}</dd>
                    </div>
                </dl>
            </section>

            <section v-else-if="activeTab === 'history'" class="panel">
                <div class="section-head">
                    <h2 class="section-title">AI history</h2>
                    <p v-if="historyTotals" class="section-caption">
                        {{ historyTotals.completed }} of {{ historyTotals.all }} succeeded
                    </p>
                </div>

                <p v-if="historyError" class="history-error">{{ historyError }}</p>

                <ul v-if="history.length" class="history-list">
                    <li v-for="row in history" :key="row.generation_id">
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
                    v-if="historyHasMore"
                    type="button"
                    class="load-more"
                    :disabled="historyLoading"
                    @click="loadHistory"
                >
                    {{ historyLoading ? "Loading…" : "Load more" }}
                </button>

                <div v-else-if="historyLoading" class="placeholder">
                    <p class="placeholder-copy">Loading your history…</p>
                </div>

                <div v-else-if="!history.length && !historyError" class="placeholder">
                    <div class="placeholder-icon" aria-hidden="true">📜</div>
                    <p class="placeholder-title">No generations yet</p>
                    <p class="placeholder-copy">
                        Recipes you generate with AI will be listed here — when they ran, whether they
                        worked, and a link straight to the recipe.
                    </p>
                </div>
            </section>

            <section v-else class="panel">
                <div class="section-head">
                    <h2 class="section-title">Credits</h2>
                    <p class="section-caption">Generations left</p>
                </div>

                <div class="placeholder">
                    <div class="placeholder-icon" aria-hidden="true">🎟️</div>
                    <p class="placeholder-title">Nothing to show yet</p>
                    <p class="placeholder-copy">
                        Your remaining generations will show here, along with every credit spent and
                        every one refunded when a generation failed.
                    </p>
                </div>
            </section>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.profile-page {
    max-width: 32rem;
    margin: 0 auto;
}

.identity-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    border-radius: 1.2rem;
    border: 1px solid color-mix(in srgb, var(--line-soft) 80%, white 20%);
    padding: 1.15rem 1rem;
    background:
        radial-gradient(circle at 92% -20%, rgba(255, 234, 152, 0.28), transparent 37%),
        radial-gradient(circle at -4% 120%, rgba(142, 216, 164, 0.2), transparent 44%),
        linear-gradient(180deg, rgba(255, 255, 255, 1) 0%, rgba(251, 255, 252, 1) 100%);
    box-shadow: 0 14px 30px rgba(7, 82, 58, 0.14);
}

.identity-avatar {
    flex-shrink: 0;
    width: 3.4rem;
    height: 3.4rem;
    display: grid;
    place-items: center;
    border-radius: 9999px;
    border: 1px solid color-mix(in srgb, var(--accent) 30%, white 70%);
    background: linear-gradient(140deg, #2dc18f 0%, #229f75 100%);
    color: white;
    font-size: 1.45rem;
    font-weight: 750;
    box-shadow:
        0 8px 16px rgba(6, 95, 70, 0.26),
        inset 0 1px 0 rgba(255, 255, 255, 0.4);
}

.identity-text {
    min-width: 0;
}

.identity-eyebrow {
    font-size: 0.74rem;
    font-weight: 650;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--text-muted);
}

.identity-name {
    margin-top: 0.22rem;
    font-size: clamp(1.35rem, 3.2vw, 1.65rem);
    font-weight: 780;
    line-height: 1.16;
    letter-spacing: -0.015em;
    color: var(--text-strong);
    overflow-wrap: anywhere;
}

.identity-email {
    margin-top: 0.12rem;
    font-size: 0.85rem;
    color: var(--text-muted);
    overflow-wrap: anywhere;
}

.tab-strip {
    margin-top: 0.95rem;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.3rem;
    padding: 0.32rem;
    border-radius: 1rem;
    border: 1px solid color-mix(in srgb, var(--line-soft) 82%, white 18%);
    background: linear-gradient(180deg, rgba(255, 255, 255, 1) 0%, rgba(250, 255, 251, 1) 100%);
    box-shadow: 0 9px 18px rgba(7, 82, 58, 0.08);
}

.tab-strip__item {
    border-radius: 0.72rem;
    border: 1px solid transparent;
    padding: 0.5rem 0.3rem;
    font-size: 0.82rem;
    font-weight: 670;
    color: var(--text-muted);
    transition: color 170ms ease, background-color 170ms ease, border-color 170ms ease;
}

.tab-strip__item:hover {
    color: var(--accent);
}

.tab-strip__item--active {
    color: var(--accent-strong);
    background: linear-gradient(180deg, rgba(168, 244, 198, 0.45) 0%, rgba(222, 251, 233, 0.78) 100%);
    border-color: color-mix(in srgb, var(--accent) 30%, white 70%);
}

.panel {
    margin-top: 0.95rem;
}

.section-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.section-title {
    font-size: 1.12rem;
    font-weight: 750;
    letter-spacing: -0.01em;
    color: var(--text-strong);
}

.section-caption {
    font-size: 0.76rem;
    color: var(--text-muted);
}

.detail-list {
    display: grid;
    gap: 0.52rem;
}

.detail-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.72rem 0.78rem;
    border-radius: 0.9rem;
    border: 1px solid color-mix(in srgb, var(--line-soft) 82%, white 18%);
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 9px 18px rgba(7, 82, 58, 0.08);
}

.detail-label {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.detail-value {
    font-size: 0.9rem;
    font-weight: 670;
    color: var(--text-strong);
    text-align: right;
    overflow-wrap: anywhere;
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
