<script setup lang="ts">
import {useLoadingStore} from "../stores/loading";

const loadingStore = useLoadingStore();
</script>

<template>
    <Transition name="loader">
        <div
            v-if="loadingStore.loading"
            class="loader-overlay fixed inset-0 z-[120] flex items-center justify-center px-5"
        >
            <div class="loader-card">
                <div class="loader-visual" aria-hidden="true">
                    <div class="loader-ring loader-ring--outer"></div>
                    <div class="loader-ring loader-ring--inner"></div>
                    <div class="loader-dot loader-dot--a"></div>
                    <div class="loader-dot loader-dot--b"></div>
                    <div class="loader-core">
                        <span class="loader-core-icon">🍃</span>
                    </div>
                </div>

                <h2 class="loader-title">Preparing your kitchen</h2>
                <p class="loader-copy">Syncing recipes, ingredients, and shopping lists</p>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.loader-overlay {
    background:
        radial-gradient(circle at 20% 18%, rgba(142, 216, 164, 0.2), transparent 40%),
        radial-gradient(circle at 80% 72%, rgba(255, 199, 159, 0.2), transparent 36%),
        rgba(15, 34, 27, 0.34);
    backdrop-filter: blur(8px);
}

.loader-card {
    width: min(92vw, 22rem);
    border-radius: 1.2rem;
    border: 1px solid var(--line-soft);
    background: linear-gradient(180deg, var(--surface-strong) 0%, var(--surface) 100%);
    box-shadow: var(--shadow-elevated);
    padding: 1.3rem 1.2rem 1.1rem;
    text-align: center;
}

.loader-visual {
    position: relative;
    width: 6.4rem;
    height: 6.4rem;
    margin: 0 auto 0.85rem;
}

.loader-ring {
    position: absolute;
    border-radius: 9999px;
    border: 1.5px solid transparent;
}

.loader-ring--outer {
    inset: 0;
    border-top-color: color-mix(in srgb, var(--accent) 65%, white 35%);
    border-right-color: color-mix(in srgb, var(--accent) 34%, white 66%);
    border-bottom-color: rgba(16, 185, 129, 0.12);
    animation: loader-spin 1.9s linear infinite;
}

.loader-ring--inner {
    inset: 0.7rem;
    border-top-color: rgba(255, 199, 159, 0.88);
    border-left-color: rgba(255, 234, 152, 0.74);
    border-bottom-color: rgba(255, 199, 159, 0.14);
    animation: loader-spin-reverse 1.35s linear infinite;
}

.loader-dot {
    position: absolute;
    width: 0.66rem;
    height: 0.66rem;
    border-radius: 9999px;
}

.loader-dot--a {
    background: #7ad8bc;
    left: 0.2rem;
    top: 2.7rem;
    box-shadow: 0 0 16px rgba(16, 185, 129, 0.4);
    animation: loader-orbit-a 2.1s ease-in-out infinite;
}

.loader-dot--b {
    background: #ffc79f;
    right: 0.2rem;
    top: 2.8rem;
    box-shadow: 0 0 16px rgba(255, 199, 159, 0.52);
    animation: loader-orbit-b 1.9s ease-in-out infinite;
}

.loader-core {
    position: absolute;
    inset: 1.72rem;
    border-radius: 9999px;
    background: radial-gradient(circle at 35% 30%, #ffffff 0%, #eff9f2 72%, #e4f3ea 100%);
    border: 1px solid color-mix(in srgb, var(--accent) 20%, white 80%);
    box-shadow: inset 0 2px 10px rgba(255, 255, 255, 0.66), 0 8px 16px rgba(7, 82, 58, 0.12);
    display: grid;
    place-items: center;
}

.loader-core-icon {
    font-size: 1.35rem;
    animation: loader-breathe 1.5s ease-in-out infinite;
}

.loader-title {
    font-size: 1rem;
    line-height: 1.3;
    font-weight: 700;
    color: var(--text-strong);
}

.loader-copy {
    margin-top: 0.18rem;
    font-size: 0.82rem;
    line-height: 1.35;
    color: var(--text-muted);
}

.loader-enter-active {
    transition: opacity 0.22s ease;
}

.loader-leave-active {
    transition: opacity 0.18s ease;
}

.loader-enter-from,
.loader-leave-to {
    opacity: 0;
}

@keyframes loader-spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes loader-spin-reverse {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(-360deg);
    }
}

@keyframes loader-orbit-a {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(3.8rem, 0.3rem) scale(0.88);
    }
}

@keyframes loader-orbit-b {
    0%, 100% {
        transform: translate(0, 0) scale(0.9);
    }
    50% {
        transform: translate(-3.8rem, -0.3rem) scale(1.04);
    }
}

@keyframes loader-breathe {
    0%, 100% {
        transform: scale(0.96);
    }
    50% {
        transform: scale(1.05);
    }
}

@media (prefers-reduced-motion: reduce) {
    .loader-ring,
    .loader-dot,
    .loader-core-icon {
        animation: none;
    }
}
</style>









