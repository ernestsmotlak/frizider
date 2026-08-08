<script setup lang="ts">
import {computed} from "vue";
import {useAuthStore} from "../../stores/auth.ts";

type AccountUser = {
    name?: string;
    email?: string;
    created_at?: string;
};

const auth = useAuthStore();

const user = computed(() => auth.user as AccountUser | null);

const memberSince = computed(() => {
    const created = user.value?.created_at;
    if (!created) return null;

    return new Intl.DateTimeFormat("en-US", {month: "long", year: "numeric"}).format(new Date(created));
});
</script>

<template>
    <section class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Account</h2>
            <p class="panel-caption">Your details</p>
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
</style>
