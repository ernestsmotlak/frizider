<script setup lang="ts">
import {ref, onUnmounted} from 'vue';
import type {GroceryList} from '../../pages/GroceryList/GroceryListsPage.vue';
import {useToastStore} from '../../stores/toast.ts';
import {useLoadingStore} from '../../stores/loading.ts';
import GroceryListActionModal from '../GroceryListActionModal.vue';

const props = defineProps<{
    groceryList: GroceryList
}>();

const emit = defineEmits<{
    click: [id: number],
    updated: [groceryList: GroceryList]
}>();

const toastStore = useToastStore();
const loadingStore = useLoadingStore();

const isLongPressing = ref(false);
const longPressTimer = ref<number | null>(null);
const hasLongPressed = ref(false);
const initialPosition = ref<{x: number, y: number} | null>(null);
const isActionModalOpen = ref(false);
const LONG_PRESS_DURATION = 500;
const MOVEMENT_THRESHOLD = 10;

const handleClick = () => {
    if (!hasLongPressed.value) {
        emit('click', props.groceryList.id);
    }
    hasLongPressed.value = false;
}

const startLongPress = (x: number, y: number) => {
    hasLongPressed.value = false;
    isLongPressing.value = true;
    initialPosition.value = {x, y};
    longPressTimer.value = window.setTimeout(() => {
        if (isLongPressing.value) {
            hasLongPressed.value = true;
            openActionModal();
        }
    }, LONG_PRESS_DURATION);
}

const cancelLongPress = () => {
    if (longPressTimer.value) {
        window.clearTimeout(longPressTimer.value);
        longPressTimer.value = null;
    }
    isLongPressing.value = false;
    initialPosition.value = null;
}

const checkMovement = (x: number, y: number): boolean => {
    if (!initialPosition.value) return false;
    const deltaX = Math.abs(x - initialPosition.value.x);
    const deltaY = Math.abs(y - initialPosition.value.y);
    return deltaX > MOVEMENT_THRESHOLD || deltaY > MOVEMENT_THRESHOLD;
}

const openActionModal = () => {
    cancelLongPress();
    isActionModalOpen.value = true;
}

const closeActionModal = () => {
    isActionModalOpen.value = false;
}

const handleComplete = () => {
    loadingStore.start();

    const newCompletedAt = props.groceryList.completed_at ? null : new Date().toISOString();

    axios.patch('/api/grocery-lists/' + props.groceryList.id, {
        completed_at: newCompletedAt
    })
        .then((response) => {
            const updatedGroceryList = response.data.data;
            emit('updated', updatedGroceryList);
            toastStore.show('success', updatedGroceryList.completed_at ? 'Shopping list completed.' : 'Shopping list reopened.');
        })
        .catch((error) => {
            console.error(error);
            toastStore.show('error', 'Could not update grocery list.');
        })
        .finally(() => {
            loadingStore.stop();
        });
}

const handleGoShopping = () => {
    // Navigation is handled in the modal component
}

const handleMouseDown = (event: MouseEvent) => {
    if (event.button === 0) {
        startLongPress(event.clientX, event.clientY);
    }
}

const handleMouseMove = (event: MouseEvent) => {
    if (isLongPressing.value && checkMovement(event.clientX, event.clientY)) {
        cancelLongPress();
    }
}

const handleMouseUp = () => {
    cancelLongPress();
}

const handleMouseLeave = () => {
    cancelLongPress();
}

const handleTouchStart = (event: TouchEvent) => {
    const touch = event.touches[0];
    if (touch) {
        startLongPress(touch.clientX, touch.clientY);
    }
}

const handleTouchMove = (event: TouchEvent) => {
    if (isLongPressing.value) {
        const touch = event.touches[0];
        if (touch && checkMovement(touch.clientX, touch.clientY)) {
            cancelLongPress();
        }
    }
}

const handleTouchEnd = () => {
    cancelLongPress();
}

const handleTouchCancel = () => {
    cancelLongPress();
}

onUnmounted(() => {
    cancelLongPress();
});

const truncateNotes = (text: string | null, maxLength: number = 100): string => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength).trim() + '...';
}

</script>

<template>
    <div
        @click="handleClick"
        @mousedown="handleMouseDown"
        @mousemove="handleMouseMove"
        @mouseup="handleMouseUp"
        @mouseleave="handleMouseLeave"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
        @touchcancel="handleTouchCancel"
        :class="[
            'rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 cursor-pointer overflow-hidden border flex flex-row',
            groceryList.completed_at ? 'border-green-100 bg-green-50/70 ring-1 ring-green-200/60' : 'border-gray-100',
            isLongPressing ? 'scale-95 ring-2 ring-blue-400 bg-blue-50/50 shadow-xl' : 'active:scale-[0.98]'
        ]"
    >
        <div
            class="relative w-24 h-full flex-shrink-0 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden self-stretch">
            <div class="w-full h-full flex items-center justify-center">
                <div v-if="groceryList.image_url" class="text-5xl">
                    {{ groceryList.image_url }}
                </div>
                <svg v-else class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
        </div>
        <div class="p-4 flex-1 min-w-0 flex flex-col justify-center">
            <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 break-words">
                    {{ groceryList.name }}
                </h3>
                <span
                    v-if="groceryList.completed_at"
                    class="inline-flex gap-1.5 px-1 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200 flex-shrink-0"
                >
                    <svg class="w-4 h-4 text-green-700" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                    </svg>
                </span>
            </div>
            <p class="text-sm text-gray-600 line-clamp-1 leading-relaxed break-words min-h-[1.25rem]">
                <template v-if="groceryList.notes">{{ truncateNotes(groceryList.notes, 100) }}</template>
<!--                <template v-else>&nbsp;</template>-->
            </p>
        </div>
    </div>

    <GroceryListActionModal
        :is-open="isActionModalOpen"
        :grocery-list="groceryList"
        @close="closeActionModal"
        @complete="handleComplete"
        @go-shopping="handleGoShopping"
    />
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.break-words {
    overflow-wrap: break-word;
    word-break: break-word;
    hyphens: auto;
}
</style>
