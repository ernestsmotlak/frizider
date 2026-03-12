<script setup lang="ts">
import LoginLayout from "../layouts/LoginLayout.vue";
import router from "../router";

const goToLogin = () => {
    router.push("/login");
};

const goToRegister = () => {
    router.push("/register");
};
</script>

<template>
    <LoginLayout>
        <div
            class="min-h-screen flex items-center justify-center auth-page-bg px-4 py-12">
            <div class="w-full max-w-md space-y-4">
                <div class="auth-card bg-white app-surface-gradient rounded-2xl shadow-xl border-2 border-gray-200 p-8 space-y-6 text-center">
                    <div class="space-y-4">
                        <h1 class="brand-title text-4xl sm:text-5xl font-extrabold tracking-tight leading-none">Frizider</h1>
                        <p class="text-xs font-semibold tracking-[0.18em] uppercase text-[var(--brand-700)]">Welcome</p>
                        <p class="text-gray-600 text-base">Get started by logging in or creating a new account</p>
                    </div>

                    <div class="space-y-4 pt-4">
                        <button @click="goToLogin"
                                class="w-full px-6 py-3 bg-[var(--brand-600)] text-white rounded-lg text-base font-semibold cursor-pointer transition-all duration-200 hover:bg-[var(--brand-700)] hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0">
                            Login
                        </button>
                        <button @click="goToRegister"
                                class="w-full px-6 py-3 bg-white text-[var(--brand-600)] border-2 border-[var(--brand-600)] rounded-lg text-base font-semibold cursor-pointer transition-all duration-200 hover:bg-[var(--brand-600)] hover:text-white hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0">
                            Register
                        </button>
                    </div>
                </div>

                <ul class="flex flex-wrap items-center justify-center gap-3 text-xs text-gray-600">
                    <li class="feature-pill">
                        <span class="feature-icon">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        Calm workflow
                    </li>
                    <li class="feature-pill">
                        <span class="feature-icon">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18"/>
                            </svg>
                        </span>
                        Clean structure
                    </li>
                    <li class="feature-pill">
                        <span class="feature-icon">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="6" width="16" height="12" rx="2"/>
                            </svg>
                        </span>
                        Premium feel
                    </li>
                </ul>
            </div>
        </div>
    </LoginLayout>
</template>

<style scoped>
.auth-card {
    position: relative;
    overflow: hidden;
}

.auth-card::after {
    content: "";
    position: absolute;
    inset: -30% -60%;
    pointer-events: none;
    background: linear-gradient(100deg, transparent 40%, rgba(255, 255, 255, 0.48) 50%, transparent 60%);
    transform: translateX(-120%);
    animation: sweep 8s ease-in-out infinite;
}

.brand-title {
    position: relative;
    display: inline-block;
    color: transparent;
    background-image: linear-gradient(135deg, #111827 0%, #1f3327 48%, #047857 100%);
    -webkit-background-clip: text;
    background-clip: text;
    text-shadow: 0 2px 12px rgba(6, 95, 70, 0.16);
}

.brand-title::after {
    content: "";
    position: absolute;
    left: -4%;
    right: -4%;
    bottom: -0.22rem;
    height: 2px;
    border-radius: 9999px;
    background: linear-gradient(90deg, transparent 0%, rgba(6, 95, 70, 0.28) 24%, rgba(6, 95, 70, 0.56) 50%, rgba(6, 95, 70, 0.28) 76%, transparent 100%);
}

.feature-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.feature-icon {
    width: 1.1rem;
    height: 1.1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    border: 1px solid var(--line-soft);
    color: var(--brand-700);
    background: color-mix(in srgb, var(--brand-50) 65%, white 35%);
}

@keyframes sweep {
    0% {
        transform: translateX(-120%);
        opacity: 0;
    }
    18% {
        opacity: 0.75;
    }
    40%, 100% {
        transform: translateX(120%);
        opacity: 0;
    }
}
</style>






