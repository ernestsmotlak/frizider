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
            <div class="w-full max-w-md">
                <div class="bg-white rounded-2xl shadow-xl p-8 space-y-6">
                    <div class="text-center">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Login</h1>
                        <p class="text-gray-600">Sign in to your account</p>
                    </div>

                    <form @submit.prevent="handleLogin" class="space-y-5">
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Email
                            </label>
                            <div class="relative">
                                <input ref="emailInputRef"
                                       @focus="errors.email = ''"
                                       v-model="email"
                                       type="email"
                                       placeholder="Enter your email"
                                       class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-[#646cff] focus:border-transparent transition-all">
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
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Password
                            </label>
                            <div class="relative">
                                <input @focus="errors.password = ''"
                                       v-model="password"
                                       type="password"
                                       placeholder="Enter your password"
                                       class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-[#646cff] focus:border-transparent transition-all">
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
                                class="w-full px-6 py-3 bg-[#646cff] text-white rounded-lg text-base font-semibold cursor-pointer transition-all duration-200 hover:bg-[#535bf2] hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0">
                            Login
                        </button>
                    </form>

                    <div class="text-center pt-4">
                        <p class="text-sm text-gray-600">
                            Don't have an account?
                            <button @click="goToRegister()"
                                    class="cursor-pointer text-[#646cff] font-semibold hover:text-[#535bf2] transition-colors">
                                Sign up
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </LoginLayout>
</template>
