<script setup lang="ts">
import LoginLayout from "../layouts/LoginLayout.vue";
import {ref} from "vue";

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

const validateForm = () => {
    const toValidate = userData.value;

    if (!toValidate.password || !toValidate.repeatPassword) {
        errors.value.password = 'Password cannot be empty.'
    } else if (toValidate.password !== toValidate.repeatPassword) {
        errors.value.password = 'Password do not match.';
    }

    if (!toValidate.username) {
        errors.value.username = 'Username can not be empty.';
    } else if (!/^[a-zA-Z][a-zA-Z0-9_]{2,19}$/.test(toValidate.username)) {
        errors.value.username = 'Username contains invalid character/s.';
    }

    if (!toValidate.email) {
        errors.value.email = 'Email can not be empty!';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(toValidate.email)) {
        errors.value.email = 'Email contains invalid character/s.'
    }

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
        <pre>Errors: {{ errors }}</pre>
        <pre>UserData: {{ userData }}</pre>
        <div class="max-w-[400px] mx-auto p-8">
            <h4 class="text-2xl font-bold mb-6">Register here</h4>
            <form class="flex flex-col gap-6">
                <div class="flex flex-col gap-2">
                    <label class="font-medium">
                        Username:
                        <input @focus="errors.username = ''" type="text" v-model="userData.username"
                               class="w-full px-3 py-3 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
                    </label>
                    <span v-if="errors.username" class="text-red-500">{{ errors.username }}</span>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">
                        Email:
                        <input @focus="errors.email = ''" type="email" v-model="userData.email"
                               class="w-full px-3 py-3 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
                    </label>
                    <span v-if="errors.email" class="text-red-500">{{ errors.email }}</span>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">
                        Password:
                        <input @focus="errors.password = ''" type="password" v-model="userData.password"
                               class="w-full px-3 py-3 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
                    </label>
                    <span v-if="errors.password" class="text-red-500">{{ errors.password }}</span>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium">
                        Repeat password:
                        <input @focus="errors.password = ''" type="password" v-model="userData.repeatPassword"
                               class="w-full px-3 py-3 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
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
