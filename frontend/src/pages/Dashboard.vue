<template>
  <DashboardLayout>
    <div class="p-6 text-center">
      <h1 class="text-3xl font-bold text-gray-800 mb-4">Welcome!</h1>
      <p class="text-lg text-gray-600">Manage your shopping lists, ingredients, and recipes all in one place.</p>
      <ul class="mt-6">
        <li class="mb-2">
          <router-link to="/shopping-list" class="text-green-600 hover:text-green-700 underline">Shopping Lists</router-link>
        </li>
        <li class="mb-2">
          <router-link to="/ingredients" class="text-green-600 hover:text-green-700 underline">Ingredients</router-link>
        </li>
        <li class="mb-2">
          <router-link to="/recipes" class="text-green-600 hover:text-green-700 underline">Recipes</router-link>
        </li>
      </ul>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import DashboardLayout from "../layouts/DashboardLayout.vue";
</script>

