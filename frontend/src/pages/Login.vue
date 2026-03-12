<script setup lang="ts">
import {ref, onMounted} from "vue";
import LoginLayout from "../layouts/LoginLayout.vue";
import router from "../router";
import {useToastStore} from "../stores/toast";
import {useAuthStore} from "../stores/auth";
import {useLoadingStore} from "../stores/loading.ts";

const toastStore = useToastStore();
const authStore = useAuthStore();
const loadingStore = useLoadingStore();

const email = ref("");
const password = ref("");

const errors = ref({
    email: "",
    password: "",
});

const validateEmail = (email: string) => {
    if (!email) return 'Email can not be empty!';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return 'Email contains invalid character/s.';
    return '';
};

const validatePassword = (password: string) => {
    if (!password) return 'Password cannot be empty.';
    return '';
};

const isEmailValid = () => !validateEmail(email.value);
const isPasswordValid = () => !validatePassword(password.value);

const validateForm = () => {
    errors.value.email = validateEmail(email.value);
    errors.value.password = validatePassword(password.value);
    return Object.values(errors.value).some(value => value !== "");
};

const emailInputRef = ref<HTMLInputElement | null>(null);

onMounted(() => {
    // Only autofocus on md breakpoint and wider (768px+)
    if (window.innerWidth >= 768 && emailInputRef.value) {
        emailInputRef.value.focus();
    }
});

const goToRegister = () => {
    router.push('/register');
};

const handleLogin = () => {
    if (validateForm()) {
        return;
    }
    loadingStore.start();
    axios.post("/api/login", {
        email: email.value,
        password: password.value,
    })
        .then(async () => {
            await authStore.fetchUser();
            toastStore.show('success', 'Successfully logged in.');
            await router.push('/dashboard');
        })
        .catch((error: any) => {
            console.error(error);
            toastStore.show('error', 'Log in failed.');
        })
        .finally(() => {
            loadingStore.stop();
        });
};
</script>

<template>
    <LoginLayout>
        <div
            class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-12">
            <div class="w-full max-w-md space-y-4">
                <div class="auth-card bg-white app-surface-gradient rounded-2xl shadow-xl border-2 border-gray-200 p-8 space-y-6">
                    <div class="text-center space-y-2">
                        <p class="text-xs font-semibold tracking-[0.18em] uppercase text-gray-500">Frizider</p>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Login</h1>
                        <p class="text-gray-600">Sign in to your account</p>
                    </div>

                    <form @submit.prevent="handleLogin" class="space-y-5">
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold tracking-[0.1em] uppercase text-gray-700 mb-1">
                                Email
                            </label>
                            <div class="relative">
                                <input ref="emailInputRef"
                                       @focus="errors.email = ''"
                                       v-model="email"
                                       type="email"
                                       placeholder="Enter your email"
                                       class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent transition-all">
                                <div v-if="isEmailValid()"
                                     class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                              d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <span v-if="errors.email" class="text-sm text-red-600 font-medium">{{ errors.email }}</span>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-semibold tracking-[0.1em] uppercase text-gray-700 mb-1">
                                Password
                            </label>
                            <div class="relative">
                                <input @focus="errors.password = ''"
                                       v-model="password"
                                       type="password"
                                       placeholder="Enter your password"
                                       class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent transition-all">
                                <div v-if="isPasswordValid()"
                                     class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                              d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <span v-if="errors.password" class="text-sm text-red-600 font-medium">{{
                                    errors.password
                                }}</span>
                        </div>

                        <button type="submit"
                                class="w-full px-6 py-3 bg-[var(--brand-600)] text-white rounded-lg text-base font-semibold cursor-pointer transition-all duration-200 hover:bg-[var(--brand-700)] hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0">
                            Login
                        </button>
                    </form>

                    <div class="text-center pt-4">
                        <p class="text-sm text-gray-600">
                            Don't have an account?
                            <button @click="goToRegister()"
                                    class="cursor-pointer text-[var(--brand-600)] font-semibold hover:text-[var(--brand-700)] transition-colors">
                                Sign up
                            </button>
                        </p>
                    </div>
                </div>
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
