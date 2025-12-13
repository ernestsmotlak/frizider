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
                            <input v-model="email"
                                   type="email"
                                   required
                                   placeholder="Enter your email"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-[#646cff] focus:border-transparent transition-all"/>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Password
                            </label>
                            <input v-model="password"
                                   type="password"
                                   required
                                   placeholder="Enter your password"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-[#646cff] focus:border-transparent transition-all"/>
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

<script setup lang="ts">
import {ref} from "vue";
import LoginLayout from "../layouts/LoginLayout.vue";
import router from "../router";

const email = ref("");
const password = ref("");

const goToRegister = () => {
    router.push('/register');
};

const handleLogin = () => {
    axios.post("/api/login", {
        email: email.value,
        password: password.value,
    })
        .then((response: any) => {
            console.log(response.data);
        })
        .catch((error: any) => {
            console.error(error);
        })
        .finally(() => {
            // Cleanup or final actions
        });
};
</script>

