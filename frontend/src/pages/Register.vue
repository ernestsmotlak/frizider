<script setup lang="ts">
import LoginLayout from "../layouts/LoginLayout.vue";
import {ref} from "vue";
import router from "../router";

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
            router.push('/login');
        })
        .catch((error: any) => {
            const status = error?.response?.status ?? 0;
            responseData.value.status = status;
            if (status === 422) {
                responseData.value.message = error.response.data.message;
            } else {
                responseData.value.message = 'Server error.';
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
        <div class="max-w-[400px] mx-auto p-8">
            <h4 class="text-2xl font-bold mb-6">Register here</h4>
            <form class="flex flex-col gap-6">
                <div class="flex flex-col gap-2">
                    <label class="font-medium">
                        Username:
                        <div class="relative">
                            <input @focus="errors.username = ''" type="text" v-model="userData.username"
                                   class="w-full px-3 py-3 pr-10 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
                            <svg v-if="isUsernameValid()" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </label>
                    <span v-if="errors.username" class="text-red-500">{{ errors.username }}</span>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">
                        Email:
                        <div class="relative">
                            <input @focus="errors.email = ''" type="email" v-model="userData.email"
                                   class="w-full px-3 py-3 pr-10 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
                            <svg v-if="isEmailValid()" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </label>
                    <span v-if="errors.email" class="text-red-500">{{ errors.email }}</span>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">
                        Password:
                        <div class="relative">
                            <input @focus="errors.password = ''" type="password" v-model="userData.password"
                                   class="w-full px-3 py-3 pr-10 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
                            <svg v-if="isPasswordValid()" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </label>
                    <span v-if="errors.password" class="text-red-500">{{ errors.password }}</span>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">
                        Repeat password:
                        <div class="relative">
                            <input @focus="errors.password = ''" type="password" v-model="userData.repeatPassword"
                                   class="w-full px-3 py-3 pr-10 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
                            <svg v-if="isPasswordValid()" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </label>
                    <span v-if="errors.password" class="text-red-500">{{ errors.password }}</span>
                </div>
            </form>
            <button @click="handleRegister"
                    class="px-6 py-3 bg-[#646cff] text-white border-none rounded text-base font-medium cursor-pointer transition-colors duration-250 hover:bg-[#535bf2]">
                Register
            </button>
        </div>
    </LoginLayout>
</template>

<style scoped>

</style>
