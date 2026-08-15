<script setup lang="ts">
import {onUnmounted, ref, watch} from "vue";
import {useRouter} from "vue-router";
import Modal from "../Modal.vue";
import {useAiGenerationStore} from "../../stores/aiGeneration.ts";
import {formatBytes, preparePhoto} from "../../utils/downscaleImage.ts";

/**
 * Confirm a shelf photo before spending a credit on it.
 *
 * The photo is picked outside this modal — a file input needs a real tap to
 * open, and a watcher is not one — so the modal opens already holding a file
 * and its job is the preview, the price, and the send.
 */
const props = defineProps<{
    isOpen: boolean;
    file: File | null;
}>();

const emit = defineEmits<{
    close: [];
}>();

const router = useRouter();
const store = useAiGenerationStore();

const retakeInput = ref<HTMLInputElement | null>(null);
const preview = ref<string | null>(null);
const size = ref(0);
const resized = ref(false);
const preparing = ref(false);

const releasePreview = () => {
    if (preview.value !== null) URL.revokeObjectURL(preview.value);
    preview.value = null;
};

/** The bytes that will actually be uploaded, kept aside from the picked file. */
let photo: Blob | null = null;

const prepare = async (file: File) => {
    preparing.value = true;
    releasePreview();

    const prepared = await preparePhoto(file);

    photo = prepared.blob;
    preview.value = prepared.previewUrl;
    size.value = prepared.blob.size;
    resized.value = prepared.resized;
    preparing.value = false;
};

watch(() => props.file, (file) => {
    if (file !== null) prepare(file);
}, {immediate: true});

// Opening is a fresh start, same as the recipe modal: the modal is for starting
// work, the pill is for watching it, and the pill stays quiet while this is up.
watch(() => props.isOpen, (open) => {
    store.modalOpen = open;

    if (open) store.clearSubmission();
});

onUnmounted(() => {
    store.modalOpen = false;
    releasePreview();
});

const retake = () => retakeInput.value?.click();

const pickRetake = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (file) prepare(file);

    // Same file twice in a row must still fire a change event.
    input.value = "";
};

const scan = () => {
    if (photo === null || preparing.value || store.submitting) return;

    store.submitPhoto(photo);
};

const tryAgain = () => {
    store.clearSubmission();
    scan();
};

// A finished scan while the modal is open goes straight to the review screen;
// while it is closed the pill announces it instead. Only this modal's own
// submission counts — another job finishing must not hijack the view.
watch(() => store.submissionStatus, (status) => {
    if (status === "completed" && store.submissionId !== null && props.isOpen) {
        const id = store.submissionId;
        store.acknowledge(id);
        emit("close");
        router.push(`/pantry/scan/${id}`);
    }
});
</script>

<template>
    <Modal :isOpen="isOpen" @close="emit('close')">
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900">Scan a shelf</h2>
        </template>

        <template #body>
            <!-- Confirm the photo -->
            <div v-if="store.submissionStatus === 'idle'" class="space-y-4">
                <div class="flex gap-4">
                    <div class="w-24 h-24 shrink-0 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 flex items-center justify-center">
                        <img v-if="preview" :src="preview" alt="The photo about to be scanned" class="w-full h-full object-cover"/>
                        <svg v-else class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h4l2-2h6l2 2h4v12H3z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"></path>
                        </svg>
                    </div>

                    <div class="min-w-0 space-y-1.5">
                        <p class="text-sm text-gray-600">
                            Everything visible on the shelf becomes a pantry item. You review the list before anything is added.
                        </p>
                        <p v-if="preparing" class="text-xs text-gray-400">Preparing the photo…</p>
                        <p v-else-if="size > 0" class="text-xs text-gray-400">
                            {{ formatBytes(size) }}<span v-if="resized"> · resized to 1200px</span>
                        </p>
                    </div>
                </div>

                <p class="text-sm text-gray-500">
                    Quantities and expiry dates are not read from a photo — add those later if you need them.
                </p>

                <p class="text-xs text-gray-400">Uses 1 AI credit.</p>
            </div>

            <!-- In flight -->
            <div v-else-if="store.submissionStatus === 'submitting' || store.submissionStatus === 'generating'" class="py-6 text-center space-y-3">
                <svg class="w-10 h-10 mx-auto text-violet-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <p class="text-gray-900 font-semibold">
                    {{ store.submissionStatus === 'submitting' ? "Uploading the photo…" : "Reading the photo…" }}
                </p>
                <p class="text-sm text-gray-500">
                    You can close this — the scan will be waiting for you either way.
                </p>
            </div>

            <!-- Failed -->
            <div v-else-if="store.submissionStatus === 'failed'" class="space-y-3">
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-700">{{ store.submissionError }}</p>
                </div>
                <p class="text-xs text-gray-400">If a scan fails, the credit is refunded automatically.</p>
            </div>

            <input
                ref="retakeInput"
                type="file"
                accept="image/*"
                capture="environment"
                class="hidden"
                @change="pickRetake"
            />
        </template>

        <template #footer>
            <div class="flex justify-between gap-3">
                <button
                    @click="emit('close')"
                    class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors"
                >
                    {{ store.submissionStatus === 'generating' ? "Continue in background" : "Close" }}
                </button>

                <div v-if="store.submissionStatus === 'idle'" class="flex gap-2">
                    <button
                        @click="retake"
                        class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors"
                    >
                        Retake
                    </button>
                    <button
                        @click="scan"
                        :disabled="preparing || photo === null"
                        class="flex items-center gap-2 px-4 py-2.5 bg-violet-600 text-white rounded-lg font-semibold hover:bg-violet-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"></path>
                        </svg>
                        Scan
                    </button>
                </div>

                <button
                    v-if="store.submissionStatus === 'failed'"
                    @click="tryAgain"
                    class="px-4 py-2.5 bg-violet-600 text-white rounded-lg font-semibold hover:bg-violet-700 transition-colors"
                >
                    Try again
                </button>
            </div>
        </template>
    </Modal>
</template>
