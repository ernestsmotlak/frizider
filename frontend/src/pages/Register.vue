<script setup lang="ts">
import LoginLayout from "../layouts/LoginLayout.vue";
import {ref} from "vue";

const userData = ref({
    username: '',
    email: '',
    password: '',
    repeatPassword: ''
});


</script>

<template>
    <div class="max-w-[400px] mx-auto p-8">
        <h4 class="text-2xl font-bold mb-6">Register here</h4>
        <form class="flex flex-col gap-6">
            <div class="flex flex-col gap-2">
                <label class="font-medium">
                    Username:
                    <input type="text" v-model="userData.username"
                           class="w-full px-3 py-3 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
                </label>
            </div>
            <div class="flex flex-col gap-2">
                <label class="font-medium">
                    Email:
                    <input type="email" v-model="userData.email"
                           class="w-full px-3 py-3 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
                </label>
            </div>
            <div class="flex flex-col gap-2">
                <label class="font-medium">
                    Password:
                    <input type="password" v-model="userData.password"
                           class="w-full px-3 py-3 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
                </label>
            </div>
            <div class="flex flex-col gap-2">
                <label class="font-medium">
                    Repeat password:
                    <input type="password" v-model="userData.repeatPassword"
                           class="w-full px-3 py-3 border border-gray-300 rounded text-base focus:outline-none focus:border-[#646cff]">
                </label>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
