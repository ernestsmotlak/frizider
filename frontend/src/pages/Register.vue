<script setup lang="ts">
import LoginLayout from "../layouts/LoginLayout.vue";
import {ref, onMounted} from "vue";
import router from "../router";
import {useToastStore} from '../stores/toast';
import {useLoadingStore} from "../stores/loading.ts";

const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const userData = ref({
    username: '',
    email: '',
    password: '',
    repeatPassword: ''
});

const responseData = ref({
    message: "",
    status: 0,
})

const errors = ref({
    username: "",
    email: "",
    password: "",
})

const validateUsername = (username: string) => {
    if (!username) return 'Username can not be empty.';
    if (!/^[a-zA-Z][a-zA-Z0-9_ ]{2,19}$/.test(username)) return 'Username contains invalid character/s.';
    return '';
}

const validateEmail = (email: string) => {
    if (!email) return 'Email can not be empty!';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return 'Email contains invalid character/s.';
    return '';
}

const validatePassword = (password: string, repeatPassword: string) => {
    if (!password || !repeatPassword) return 'Password cannot be empty.';
    if (password !== repeatPassword) return 'Password do not match.';
    return '';
}

const isUsernameValid = () => !validateUsername(userData.value.username);
const isEmailValid = () => !validateEmail(userData.value.email);
const isPasswordValid = () => !validatePassword(userData.value.password, userData.value.repeatPassword);

const validateForm = () => {
    errors.value.username = validateUsername(userData.value.username);
    errors.value.email = validateEmail(userData.value.email);
    errors.value.password = validatePassword(userData.value.password, userData.value.repeatPassword);
    return Object.values(errors.value).some(value => value !== "");
}

const usernameInputRef = ref<HTMLInputElement | null>(null);

onMounted(() => {
    // Only autofocus on md breakpoint and wider (768px+)
    if (window.innerWidth >= 768 && usernameInputRef.value) {
        usernameInputRef.value.focus();
    }
});

const goToLogin = () => {
    router.push('/login');
}

const handleRegister = () => {
    if (validateForm()) {
        return;
    }

    const payload = {
        name: userData.value.username,
        email: userData.value.email,
        password: userData.value.password,
    }

    loadingStore.start();

    axios.post('/api/register', payload)
        .then((response: any) => {
            responseData.value.message = response.data.message;
            responseData.value.status = response.status;
            toastStore.show('success', response.data.message || 'Registration successful!');
            router.push('/login');
        })
        .catch((error: any) => {
            const status = error?.response?.status ?? 0;
            responseData.value.status = status;
            if (status === 422) {
                responseData.value.message = error.response.data.message;
                toastStore.show('error', error.response.data.message || 'Validation failed. Please check your input.');
            } else {
                responseData.value.message = 'Server error.';
                toastStore.show('error', 'Registration failed. Please try again.');
            }
            console.error(error);
        })
        .finally(() => {
            loadingStore.stop();
        })
}


</script>

<template>
    <LoginLayout>
        <div
            class="min-h-screen flex items-center justify-center auth-page-bg px-4 py-12">
            <div class="w-full max-w-md space-y-4">
                <div class="auth-card bg-white app-surface-gradient rounded-2xl shadow-xl border-2 border-gray-200 p-8 space-y-6">
                    <div class="text-center space-y-2">
                        <h1 class="brand-title text-4xl sm:text-5xl font-extrabold tracking-tight leading-none">Frizider</h1>
                        <p class="text-xs font-semibold tracking-[0.18em] uppercase text-[var(--brand-700)]">Create Account</p>
                        <p class="text-gray-600">Sign up to get started</p>
                    </div>

                    <form @submit.prevent="handleRegister" class="space-y-5">
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold tracking-[0.1em] uppercase text-gray-700 mb-1">
                                Username
                            </label>
                            <div class="relative">
                                <input ref="usernameInputRef"
                                       @focus="errors.username = ''"
                                       type="text"
                                       v-model="userData.username"
                                       placeholder="Enter your username"
                                       class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-[var(--brand-500)] focus:border-transparent transition-all">
                                <div v-if="isUsernameValid()"
                                     class="absolute right-3 top-1/2 transform -translate-y-1/2 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                              d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <span v-if="errors.username" class="text-sm text-red-600 font-medium">{{
                                    errors.username
                                }}</span>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-semibold tracking-[0.1em] uppercase text-gray-700 mb-1">
                                Email
                            </label>
                            <div class="relative">
                                <input @focus="errors.email = ''"
                                       type="email"
                                       v-model="userData.email"
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
                                       type="password"
                                       v-model="userData.password"
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

                        <div class="space-y-1">
                            <label class="block text-xs font-semibold tracking-[0.1em] uppercase text-gray-700 mb-1">
                                Repeat Password
                            </label>
                            <div class="relative">
                                <input @focus="errors.password = ''"
                                       type="password"
                                       v-model="userData.repeatPassword"
                                       placeholder="Confirm your password"
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
                            Register
                        </button>
                    </form>

                    <div class="text-center pt-4">
                        <p class="text-sm text-gray-600">
                            Already have an account?
                            <button @click="goToLogin()"
                                    class="cursor-pointer text-[var(--brand-600)] font-semibold hover:text-[var(--brand-700)] transition-colors">
                                Sign in
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
