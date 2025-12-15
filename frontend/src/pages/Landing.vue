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
            class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-12">
            <div class="w-full max-w-md">
                <div class="bg-white rounded-2xl shadow-xl p-8 space-y-6 text-center">
                    <div class="space-y-4">
                        <h1 class="text-4xl font-bold text-gray-900">Welcome</h1>
                        <p class="text-gray-600 text-lg">Get started by logging in or creating a new account</p>
                    </div>

                    <div class="space-y-4 pt-4">
                        <button @click="goToLogin"
                                class="w-full px-6 py-3 bg-[#646cff] text-white rounded-lg text-base font-semibold cursor-pointer transition-all duration-200 hover:bg-[#535bf2] hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0">
                            Login
                        </button>
                        <button @click="goToRegister"
                                class="w-full px-6 py-3 bg-white text-[#646cff] border-2 border-[#646cff] rounded-lg text-base font-semibold cursor-pointer transition-all duration-200 hover:bg-[#646cff] hover:text-white hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0">
                            Register
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </LoginLayout>
</template>




