<template>
    <router-view/>
    <Toaster />
    <Loader />
</template>

<script setup lang="ts">
import Toaster from "./components/Toaster.vue";
import Loader from "./components/Loader.vue";
</script>
