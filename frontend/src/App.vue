<template>
    <router-view/>
    <Toaster />
    <Loader />
    <ConfirmModal />
</template>

<script setup lang="ts">
import Toaster from "./components/Toaster.vue";
import Loader from "./components/Loader.vue";
import ConfirmModal from "./components/ConfirmModal.vue";
</script>
