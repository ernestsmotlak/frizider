<script setup lang="ts">
import {computed, onBeforeUnmount, onMounted} from "vue";
import ActionIcon, {type ActionIconName} from "./ActionIcon.vue";

/**
 * One thing the user can do. Actions are data, not markup — which is the whole
 * point of this component: a new operation is a row in an array, not another
 * button competing for width in a footer.
 */
export interface SheetAction {
    id: string;
    label: string;
    description?: string;
    icon?: ActionIconName | string;
    /** Featured actions get a tile in the 2-up grid as well as a list row. */
    featured?: boolean;
    disabled?: boolean;
    /** Shown in place of the description when disabled — say why, not just that. */
    disabledReason?: string;
    danger?: boolean;
    /**
     * Renders the row as a file picker instead of a button.
     *
     * A phone only opens the camera for a tap that lands on the input itself.
     * Going through `select` and having the parent call `input.click()` puts a
     * hop between the gesture and the picker, and iOS answers that by doing
     * nothing at all — silently, and only on the device it matters on.
     */
    filePicker?: { accept: string; capture?: string };
}

const props = withDefaults(defineProps<{
    isOpen: boolean;
    title?: string;
    subtitle?: string;
    actions: SheetAction[];
}>(), {
    title: 'Actions',
});

const emit = defineEmits<{
    close: [];
    select: [action: SheetAction];
    pick: [action: SheetAction, file: File];
}>();

/** A picker row is a label wrapping its input; a disabled one is a dead button. */
const asPicker = (action: SheetAction) => action.filePicker !== undefined && !action.disabled;

const handleFile = (action: SheetAction, event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    // The same photo twice in a row must still fire a change event.
    input.value = "";

    if (file !== null) emit('pick', action, file);
};

const featuredActions = computed(() => props.actions.filter((action) => action.featured));

const availableCount = computed(() => props.actions.filter((action) => !action.disabled).length);

const select = (action: SheetAction) => {
    if (action.disabled) return;
    // A picker row does its own work through the input it wraps. Emitting here
    // as well would open the sheet's action twice for one tap.
    if (asPicker(action)) return;

    emit('select', action);
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && props.isOpen) {
        emit('close');
    }
};

onMounted(() => document.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => document.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <Teleport to="body">
        <Transition name="action-picker-backdrop">
            <div
                v-if="isOpen"
                class="action-picker-backdrop fixed inset-0 z-40"
                @click="emit('close')"
            ></div>
        </Transition>

        <Transition name="action-picker-panel">
            <div
                v-if="isOpen"
                class="fixed left-0 right-0 z-50 px-4"
                style="bottom: calc(5.5rem + env(safe-area-inset-bottom)); padding-bottom: max(0.5rem, env(safe-area-inset-bottom));"
                role="dialog"
                aria-modal="true"
                :aria-label="title"
            >
                <div class="mx-auto max-w-md">
                    <div class="action-panel rounded-2xl p-3">
                        <div class="action-panel__header px-1 pb-2">
                            <div class="action-panel__title-row">
                                <h3 class="action-panel__title">{{ title }}</h3>
                                <span class="action-panel__count text-xs font-semibold">
                                    {{ subtitle ?? `${availableCount} available` }}
                                </span>
                            </div>
                        </div>

                        <div class="action-panel__body">
                            <div v-if="featuredActions.length" class="grid grid-cols-2 gap-3">
                                <component
                                    :is="asPicker(action) ? 'label' : 'button'"
                                    v-for="action in featuredActions"
                                    :key="action.id"
                                    :type="asPicker(action) ? undefined : 'button'"
                                    :disabled="asPicker(action) ? undefined : action.disabled"
                                    @click="select(action)"
                                    class="action-choice group flex flex-col items-center justify-center gap-2 rounded-xl px-3 py-4 transition-all duration-200 hover:-translate-y-1 active:translate-y-0 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:hover:translate-y-0"
                                    :class="asPicker(action) ? 'cursor-pointer' : ''"
                                    :aria-label="`Open ${action.label}`"
                                >
                                    <div class="action-choice__icon flex items-center justify-center w-14 h-14 rounded-2xl transition-all duration-200 group-hover:scale-110">
                                        <ActionIcon :name="action.icon" class="w-8 h-8"/>
                                    </div>
                                    <div class="action-choice__title text-sm font-semibold">{{ action.label }}</div>
                                    <div class="action-choice__subtitle text-xs">
                                        {{ action.disabled ? (action.disabledReason ?? 'Not available') : action.description }}
                                    </div>
                                    <input
                                        v-if="asPicker(action)"
                                        type="file"
                                        class="hidden"
                                        :accept="action.filePicker!.accept"
                                        :capture="action.filePicker!.capture"
                                        @change="handleFile(action, $event)"
                                    />
                                </component>
                            </div>

                            <div v-if="actions.length" class="action-list mt-3">
                                <div class="action-list__heading px-1 pb-1 text-xs font-semibold uppercase tracking-[0.08em]">
                                    All actions
                                </div>
                                <component
                                    :is="asPicker(action) ? 'label' : 'button'"
                                    v-for="action in actions"
                                    :key="`${action.id}-list`"
                                    :type="asPicker(action) ? undefined : 'button'"
                                    class="action-list__item"
                                    :class="[
                                        action.danger ? 'action-list__item--danger' : '',
                                        asPicker(action) ? 'cursor-pointer' : '',
                                    ]"
                                    :disabled="asPicker(action) ? undefined : action.disabled"
                                    @click="select(action)"
                                >
                                    <span class="action-list__label">{{ action.label }}</span>
                                    <span class="action-list__description">
                                        {{ action.disabled ? (action.disabledReason ?? 'Not available') : action.description }}
                                    </span>
                                    <input
                                        v-if="asPicker(action)"
                                        type="file"
                                        class="hidden"
                                        :accept="action.filePicker!.accept"
                                        :capture="action.filePicker!.capture"
                                        @change="handleFile(action, $event)"
                                    />
                                </component>
                            </div>

                            <p v-if="!actions.length" class="action-panel__empty text-center text-sm">
                                No actions found yet.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
