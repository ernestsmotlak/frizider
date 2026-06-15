<script setup lang="ts">
import DashboardLayout from "../layouts/DashboardLayout.vue";
import {ref} from "vue";
import {useRouter} from "vue-router";
import {useToastStore} from "../stores/toast.ts";
import {useLoadingStore} from "../stores/loading.ts";

const router = useRouter();
const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const formData = ref({
    name: "",
    description: "",
});

const createStorageSpace = () => {
    if (!formData.value.name.trim()) {
        toastStore.show('error', 'Storage space name is required.');
        return;
    }

    const payload = {
        name: formData.value.name.trim(),
        description: formData.value.description?.trim() || null,
    };

    loadingStore.start();

    axios.post('/api/space-storages', payload)
        .then((response) => {
            const createdSpaceStorage = response.data.data;
            toastStore.show('success', 'Storage space created successfully.');
            router.push(`/storage-spaces/${createdSpaceStorage.id}`);
        })
        .catch((error) => {
            console.error(error);
            const errorMessage = error?.response?.data?.message || 'Could not create storage space.';
            toastStore.show('error', errorMessage);
        })
        .finally(() => {
            loadingStore.stop();
        });
};

const handleCancel = () => {
    router.push('/storage-spaces');
};
</script>

<template>
    <DashboardLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <div class="recipes-hero-card rounded-2xl overflow-hidden relative">
                <div class="p-8 space-y-6">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-6">Create Storage Space</h1>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                            <input
                                v-model="formData.name"
                                type="text"
                                class="top-form-input w-full px-4 py-3 text-2xl font-bold text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                placeholder="e.g., Fridge, Freezer, Pantry shelf"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea
                                v-model="formData.description"
                                rows="3"
                                class="top-form-input w-full px-4 py-3 text-lg text-gray-600 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"
                                placeholder="Enter description (optional)"
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex justify-between gap-3 pt-4 border-t border-gray-200">
                        <button
                            @click="handleCancel"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border-2 border-gray-200 bg-white/90 text-gray-700 font-semibold shadow-sm hover:-translate-y-0.5 hover:border-gray-300 hover:bg-white hover:shadow-md active:translate-y-0 active:scale-[0.98] transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            @click="createStorageSpace"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border-2 border-blue-600 bg-gradient-to-b from-blue-500 to-blue-600 text-white font-semibold shadow-md hover:-translate-y-0.5 hover:shadow-lg hover:from-blue-600 hover:to-blue-700 hover:border-blue-700 active:translate-y-0 active:scale-[0.98] transition-all duration-200"
                        >
                            Create Storage Space
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.recipes-hero-card {
    border: 4px solid color-mix(in srgb, var(--line-soft) 58%, var(--accent-soft) 42%);
    background:
        radial-gradient(85% 75% at 100% 0%, rgba(255, 200, 120, 0.3) 0%, rgba(255, 200, 120, 0) 70%),
        radial-gradient(70% 85% at 0% 100%, rgba(116, 221, 164, 0.12) 0%, rgba(116, 221, 164, 0) 72%),
        linear-gradient(135deg, #ffffff 0%, #fcfffc 45%, #f8fbf9 100%);
    box-shadow: 0 18px 38px rgba(11, 96, 68, 0.2);
}

.top-form-input {
    background: #ffffff;
}
</style>
