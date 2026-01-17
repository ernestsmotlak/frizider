<script setup lang="ts">
import {ref, onMounted} from "vue";

const videoRef = ref<HTMLVideoElement | null>(null);

onMounted(() => {
    if (!videoRef.value) return;

    // optional: slow it down a bit
    videoRef.value.playbackRate = 0.8;

    // autoplay safely (muted is required)
    videoRef.value.play().catch(() => {
    });
});
</script>

<template>
    <div
        class="inline-flex items-center text-sm font-medium text-gray-700 bg-white border-2 border-gray-200 shadow-sm hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition-all duration-200 active:scale-95"
    >
        <!-- animated icon -->
        <video
            ref="videoRef"
            src="/fridge_video.mp4"
            class="w-9 h-9"
            muted
            loop
            playsinline
            preload="auto"
        />
    </div>
</template>

<style scoped>
video {
    display: block; /* avoids inline video weird spacing */
}
</style>
