<script setup lang="ts">
import {computed} from "vue";
import {useRoute, useRouter} from "vue-router";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import ProfileAccountPanel from "../components/Profile/ProfileAccountPanel.vue";
import ProfileHistoryPanel from "../components/Profile/ProfileHistoryPanel.vue";
import ProfileCreditsPanel from "../components/Profile/ProfileCreditsPanel.vue";
import {useAuthStore} from "../stores/auth.ts";
import {useAiGenerationStore} from "../stores/aiGeneration.ts";

type ProfileTab = "account" | "history" | "credits";

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

const user = computed(() => auth.user as { name?: string; email?: string } | null);

const displayName = computed(() => user.value?.name || user.value?.email?.split("@")[0] || "Chef");

const initial = computed(() => displayName.value.trim().charAt(0).toUpperCase() || "?");

// Read from the store, never fetched here. The app already asks once at boot,
// and the Credits panel refreshes it when opened — this page mounting is not a
// reason to ask again on the two tabs that do not show credits.
const ai = useAiGenerationStore();
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

            <!-- Above the tabs rather than inside the Credits panel, so the
                 number is there on all three. A balance you have to go
                 looking for is one you find out about from a 402. -->
            <div
                v-if="ai.creditsRemaining !== null"
                class="credit-strip"
                :class="ai.creditsRemaining === 0 ? 'credit-strip--empty' : ''"
            >
                <span class="credit-strip__count">{{ ai.creditsRemaining }}</span>
                <span>AI generation{{ ai.creditsRemaining === 1 ? '' : 's' }} left</span>
            </div>

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

            <!-- Each panel fetches what it needs when it mounts, so the page
                 itself never learns how any of them get their data. -->
            <div class="panel-slot">
                <ProfileAccountPanel v-if="activeTab === 'account'"/>
                <ProfileHistoryPanel v-else-if="activeTab === 'history'"/>
                <ProfileCreditsPanel v-else/>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.profile-page {
    max-width: 32rem;
    margin: 0 auto;
}

.credit-strip {
    display: flex;
    align-items: baseline;
    gap: 0.4rem;
    margin-top: 0.7rem;
    padding: 0.55rem 0.9rem;
    border-radius: 0.8rem;
    border: 1px solid color-mix(in srgb, var(--accent-soft) 60%, white 40%);
    background: color-mix(in srgb, var(--accent-soft) 22%, white 78%);
    font-size: 0.8rem;
    color: var(--text-soft, #4b5563);
}

.credit-strip__count {
    font-weight: 700;
    font-size: 0.95rem;
    color: #111827;
}

.credit-strip--empty {
    border-color: #fecaca;
    background: #fef2f2;
    color: #b91c1c;
}

.credit-strip--empty .credit-strip__count {
    color: #b91c1c;
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

.panel-slot {
    margin-top: 0.95rem;
}
</style>
