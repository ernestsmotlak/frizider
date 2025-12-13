<script setup lang="ts">
import LoginLayout from "../layouts/LoginLayout.vue";
import {ref, onMounted} from "vue";
import router from "../router";
import {useToastStore} from '../stores/toast';

const toastStore = useToastStore();

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
            // loading = false
        })
}


</script>

<template>
    <LoginLayout>
        <div
            class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-12">
            <div class="w-full max-w-md">
                <div class="bg-white rounded-2xl shadow-xl p-8 space-y-6">
                    <div class="text-center">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Account</h1>
                        <p class="text-gray-600">Sign up to get started</p>
                    </div>

                    <form @submit.prevent="handleRegister" class="space-y-5">
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Username
                            </label>
                            <div class="relative">
                                <input ref="usernameInputRef"
                                       @focus="errors.username = ''"
                                       type="text"
                                       v-model="userData.username"
                                       placeholder="Enter your username"
                                       class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-[#646cff] focus:border-transparent transition-all">
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
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Email
                            </label>
                            <div class="relative">
                                <input @focus="errors.email = ''"
                                       type="email"
                                       v-model="userData.email"
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
                                       type="password"
                                       v-model="userData.password"
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

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Repeat Password
                            </label>
                            <div class="relative">
                                <input @focus="errors.password = ''"
                                       type="password"
                                       v-model="userData.repeatPassword"
                                       placeholder="Confirm your password"
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
                            Register
                        </button>
                    </form>

                    <div class="text-center pt-4">
                        <p class="text-sm text-gray-600">
                            Already have an account?
                            <button @click="goToLogin()"
                                    class="cursor-pointer text-[#646cff] font-semibold hover:text-[#535bf2] transition-colors">
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

</style>
