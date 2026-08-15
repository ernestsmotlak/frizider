<script setup lang="ts">
import {computed, onBeforeUnmount, onMounted} from "vue";
import ActionIcon from "./ActionIcon.vue";
import {useSelectionDockStore} from "../stores/selectionDock.ts";

/**
 * The bar that stands in for the bottom nav while the user is selecting.
 *
 * It deliberately takes the nav's place rather than stacking above it: select
 * mode is modal, so the destinations are not reachable anyway, and two floating
 * bars at the bottom of a phone screen is one too many — the AI pill already
 * sits just above the nav.
 */
const props = withDefaults(defineProps<{
    count: number;
    total: number;
    /** Singular noun for what is being selected, e.g. "item". */
    noun?: string;
    /** Label on the primary control. */
    actionLabel?: string;
}>(), {
    noun: 'item',
    actionLabel: 'Actions',
});

const emit = defineEmits<{
    cancel: [];
    'select-all': [];
    'open-actions': [];
}>();

const dock = useSelectionDockStore();

onMounted(() => dock.claim());
onBeforeUnmount(() => dock.release());

const allSelected = computed(() => props.total > 0 && props.count === props.total);

const countLabel = computed(() =>
    props.count === 0
        ? `Select ${props.noun}s`
        : `${props.count} selected`
);
</script>

<template>
    <Teleport to="body">
        <Transition name="dock-swap" appear>
            <div
                class="dock-shell z-30"
                style="padding-bottom: max(0.45rem, env(safe-area-inset-bottom));"
            >
                <div class="mx-auto max-w-md px-3">
                    <div class="dock-track selection-dock__track">
                        <button
                            type="button"
                            class="selection-dock__ghost"
                            @click="emit('cancel')"
                            aria-label="Leave select mode"
                        >
                            <ActionIcon name="close" class="w-5 h-5"/>
                        </button>

                        <div class="flex flex-col min-w-0">
                            <span class="selection-dock__count truncate">{{ countLabel }}</span>
                            <button
                                v-if="total > 0"
                                type="button"
                                class="selection-dock__hint text-left"
                                @click="emit('select-all')"
                            >
                                {{ allSelected ? 'Clear selection' : `Select all ${total}` }}
                            </button>
                        </div>

                        <button
                            type="button"
                            class="selection-dock__primary ml-auto"
                            :disabled="count === 0"
                            @click="emit('open-actions')"
                        >
                            <ActionIcon name="dots" class="w-4 h-4"/>
                            {{ actionLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
