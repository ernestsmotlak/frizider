<template>
    <div class="max-w-[400px] mx-auto p-8 bg-cyan-200">
        <h1>Login</h1>
        <form @submit.prevent="handleLogin" class="flex flex-col gap-6">
            <div class="flex flex-col gap-2">
                <label class="font-medium">
                    Email:
                    <input v-model="email" type="email" required class="px-3 py-3 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]"/>
                </label>
            </div>
            <div class="flex flex-col gap-2">
                <label class="font-medium">
                    Password:
                    <input v-model="password" type="password" required class="px-3 py-3 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]"/>
                </label>
            </div>
            <button type="submit" class="px-6 py-3 bg-[#646cff] text-white border-none rounded text-base font-medium cursor-pointer transition-colors duration-250 hover:bg-[#535bf2]">Login</button>
        </form>
    </div>
</template>

<script setup lang="ts">
import {ref} from "vue";

const email = ref("");
const password = ref("");

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

