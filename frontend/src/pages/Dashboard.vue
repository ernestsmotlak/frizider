<script setup lang="ts">
import {computed} from "vue";
import DashboardLayout from "../layouts/DashboardLayout.vue";
import {useAuthStore} from "../stores/auth.ts";

const auth = useAuthStore();

const userDisplayName = computed(() => {
    const user = auth.user as { username?: string; name?: string; email?: string } | null;
    if (!user) return "Chef";
    return user.username || user.name || user.email?.split("@")[0] || "Chef";
});

const dayPart = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return "Good morning";
    if (hour < 18) return "Good afternoon";
    return "Good evening";
});

const todayLabel = computed(() =>
    new Intl.DateTimeFormat("en-US", {
        weekday: "long",
        month: "short",
        day: "numeric",
    }).format(new Date())
);

const shortcuts = [
    {to: "/grocery-lists", title: "Shopping Lists", subtitle: "Plan what to buy", icon: "🧺"},
    {to: "/recipes", title: "Recipes", subtitle: "Cook from your collection", icon: "🍳"},
    {to: "/ingredients", title: "Ingredients", subtitle: "Track what you have", icon: "🥕"},
    {to: "/shopping", title: "Start Shopping", subtitle: "Use selected lists", icon: "🛒"},
];

const quickCreates = [
    {to: "/new/grocery-list", title: "New Shopping List", icon: "➕"},
    {to: "/new/recipe", title: "New Recipe", icon: "✍️"},
    {to: "/cooking", title: "Cooking Mode", icon: "🔥"},
];
</script>

<template>
    <DashboardLayout>
        <div class="dashboard-page px-5 pt-6 pb-24">
            <section class="hero-card">
                <p class="hero-date">{{ todayLabel }}</p>
                <h1 class="hero-title">{{ dayPart }}, {{ userDisplayName }}.</h1>
                <p class="hero-copy">Keep your kitchen moving: organize lists, prep ingredients, and jump into cooking in one tap.</p>
                <div class="hero-badges">
                    <span class="hero-badge">Fresh mode</span>
                    <span class="hero-badge">Everything synced</span>
                </div>
            </section>

            <section class="section-block">
                <div class="section-head">
                    <h2 class="section-title">Jump Back In</h2>
                    <p class="section-caption">Your main workflows</p>
                </div>
                <div class="shortcut-grid">
                    <router-link
                        v-for="shortcut in shortcuts"
                        :key="shortcut.title"
                        :to="shortcut.to"
                        class="shortcut-card"
                    >
                        <div class="shortcut-icon" aria-hidden="true">{{ shortcut.icon }}</div>
                        <div class="shortcut-title">{{ shortcut.title }}</div>
                        <div class="shortcut-copy">{{ shortcut.subtitle }}</div>
                    </router-link>
                </div>
            </section>

            <section class="section-block">
                <div class="section-head">
                    <h2 class="section-title">Quick Start</h2>
                    <p class="section-caption">Create or continue instantly</p>
                </div>
                <div class="quick-create-list">
                    <router-link
                        v-for="action in quickCreates"
                        :key="action.title"
                        :to="action.to"
                        class="quick-create-item"
                    >
                        <span class="quick-create-icon" aria-hidden="true">{{ action.icon }}</span>
                        <span class="quick-create-title">{{ action.title }}</span>
                        <svg class="quick-create-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </router-link>
                </div>
            </section>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.dashboard-page {
    max-width: 32rem;
    margin: 0 auto;
}

.hero-card {
    position: relative;
    border-radius: 1.2rem;
    border: 1px solid color-mix(in srgb, var(--line-soft) 80%, white 20%);
    padding: 1.15rem 1rem;
    background:
        radial-gradient(circle at 92% -20%, rgba(255, 234, 152, 0.28), transparent 37%),
        radial-gradient(circle at -4% 120%, rgba(142, 216, 164, 0.2), transparent 44%),
        linear-gradient(180deg, rgba(255, 255, 255, 1) 0%, rgba(251, 255, 252, 1) 100%);
    box-shadow: 0 14px 30px rgba(7, 82, 58, 0.14);
}

.hero-date {
    font-size: 0.74rem;
    font-weight: 650;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--text-muted);
}

.hero-title {
    margin-top: 0.3rem;
    font-size: clamp(1.52rem, 3.5vw, 1.9rem);
    font-weight: 780;
    line-height: 1.16;
    letter-spacing: -0.015em;
    color: var(--text-strong);
}

.hero-copy {
    margin-top: 0.42rem;
    font-size: 0.95rem;
    line-height: 1.45;
    color: var(--text-muted);
}

.hero-badges {
    margin-top: 0.74rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
}

.hero-badge {
    padding: 0.25rem 0.58rem;
    border-radius: 999px;
    border: 1px solid color-mix(in srgb, var(--accent) 22%, white 78%);
    background: color-mix(in srgb, var(--accent-soft) 56%, white 44%);
    color: var(--accent-strong);
    font-size: 0.72rem;
    font-weight: 700;
}

.section-block {
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

.shortcut-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.62rem;
}

.shortcut-card {
    border-radius: 1rem;
    border: 1px solid color-mix(in srgb, var(--line-soft) 82%, white 18%);
    background: linear-gradient(180deg, rgba(255, 255, 255, 1) 0%, rgba(250, 255, 251, 1) 100%);
    padding: 0.85rem 0.78rem;
    text-decoration: none;
    transition: transform 170ms ease, border-color 170ms ease, box-shadow 170ms ease;
    box-shadow: 0 10px 22px rgba(7, 82, 58, 0.1);
}

.shortcut-card:hover {
    transform: translateY(-2px);
    border-color: var(--line-strong);
    box-shadow: var(--shadow-soft);
}

.shortcut-icon {
    font-size: 1.18rem;
    line-height: 1;
}

.shortcut-title {
    margin-top: 0.52rem;
    font-size: 0.94rem;
    font-weight: 700;
    color: var(--text-strong);
}

.shortcut-copy {
    margin-top: 0.15rem;
    font-size: 0.75rem;
    line-height: 1.35;
    color: var(--text-muted);
}

.quick-create-list {
    display: grid;
    gap: 0.52rem;
}

.quick-create-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.72rem 0.78rem;
    border-radius: 0.9rem;
    border: 1px solid color-mix(in srgb, var(--line-soft) 82%, white 18%);
    background: rgba(255, 255, 255, 1);
    text-decoration: none;
    color: var(--text-strong);
    transition: border-color 170ms ease, transform 170ms ease, box-shadow 170ms ease;
    box-shadow: 0 9px 18px rgba(7, 82, 58, 0.08);
}

.quick-create-item:hover {
    border-color: var(--line-strong);
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(7, 82, 58, 0.13);
}

.quick-create-icon {
    width: 2rem;
    height: 2rem;
    display: grid;
    place-items: center;
    border-radius: 0.64rem;
    border: 1px solid color-mix(in srgb, var(--accent) 18%, white 82%);
    background: color-mix(in srgb, var(--accent-soft) 38%, white 62%);
    font-size: 0.96rem;
}

.quick-create-title {
    font-size: 0.9rem;
    font-weight: 670;
}

.quick-create-arrow {
    margin-left: auto;
    width: 1rem;
    height: 1rem;
    color: var(--text-muted);
}
</style>
