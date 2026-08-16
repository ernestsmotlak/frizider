<script setup lang="ts">
import {computed, onBeforeUnmount, onMounted, ref, watch} from "vue";
import {useRoute, useRouter} from "vue-router";
import DashboardLayout from "../../layouts/DashboardLayout.vue";
import ActionSheet, {type SheetAction} from "../../components/ActionSheet.vue";
import ScanItemRow, {type ScanItem} from "../../components/Pantry/ScanItemRow.vue";
import {useAiGenerationStore} from "../../stores/aiGeneration.ts";
import {useSelectionDockStore} from "../../stores/selectionDock.ts";
import {useToastStore} from "../../stores/toast.ts";

/**
 * What the scan found, before any of it is real.
 *
 * The page is the review step: nothing reaches the pantry until the user taps
 * add, and what gets sent is the edited list, not what the model returned. The
 * server result is a suggestion this page is free to disagree with.
 *
 * It is a page rather than a modal because the job finishes while the user is
 * elsewhere — the pill needs a URL to send them to, and that URL has to survive
 * a refresh.
 */
interface ScanSpace {
    id: number;
    name: string;
}

type ScanStatus = "pending" | "processing" | "completed" | "failed";

const POLL_INTERVAL_MS = 2500;

const route = useRoute();
const router = useRouter();
const aiStore = useAiGenerationStore();
const dock = useSelectionDockStore();
const toastStore = useToastStore();

const generationId = Number(route.params.id);

const loading = ref(true);
const loadError = ref("");
const status = ref<ScanStatus>("pending");
const scanError = ref<string | null>(null);
const confirmed = ref(false);
const spaces = ref<ScanSpace[]>([]);
const items = ref<ScanItem[]>([]);
const adding = ref(false);

/** The item whose space is being picked, or null while the sheet is closed. */
const movingKey = ref<string | null>(null);

let pollTimer: ReturnType<typeof setTimeout> | null = null;
let claimedDock = false;
let keySeed = 0;

const isRunning = computed(() => status.value === "pending" || status.value === "processing");

// A fetch that never lands leaves the status at its initial "pending", so
// without this the error state would poll itself forever behind the message.
const keepWatching = computed(() => isRunning.value && loadError.value === "");
const named = computed(() => items.value.filter((item) => item.name.trim() !== ""));
const unassignedCount = computed(() => named.value.filter((item) => item.spaceId === null).length);

const showList = computed(() => status.value === "completed" && !confirmed.value && items.value.length > 0);

/**
 * Items under the space they are going to. Grouping is what makes a wrong
 * assignment visible — "why is butter under Pantry shelf?" jumps out of a
 * grouped list and hides in a flat one. Unassigned goes last, where it reads as
 * a short to-do rather than a mistake buried in the middle.
 */
const groups = computed(() => {
    const rows = spaces.value
        .map((space) => ({
            id: space.id as number | null,
            name: space.name,
            items: items.value.filter((item) => item.spaceId === space.id),
        }))
        .filter((group) => group.items.length > 0);

    const loose = items.value.filter((item) => item.spaceId === null);

    if (loose.length > 0) {
        rows.push({id: null, name: "Needs a space", items: loose});
    }

    return rows;
});

const spaceActions = computed<SheetAction[]>(() => {
    const current = items.value.find((item) => item.key === movingKey.value) ?? null;

    return [
        ...spaces.value.map((space) => ({
            id: String(space.id),
            label: space.name,
            description: current?.spaceId === space.id ? "Already here" : "Move here",
            disabled: current?.spaceId === space.id,
            disabledReason: "Already here",
        })),
        {
            id: "none",
            label: "No space",
            description: "Leave it unassigned",
            disabled: current?.spaceId === null,
            disabledReason: "Already unassigned",
        },
    ];
});

const toItem = (row: any): ScanItem => ({
    key: `scan-${keySeed++}`,
    name: String(row.name ?? ""),
    spaceId: row.space_id ?? null,
    notes: row.notes ?? null,
});

const applyResponse = (data: any) => {
    status.value = data.status;
    scanError.value = data.error ?? null;
    confirmed.value = data.confirmed_at !== null && data.confirmed_at !== undefined;
    spaces.value = data.spaces ?? [];

    // Only ever adopt the server's list once. A poll landing mid-edit must not
    // overwrite a name the user is halfway through typing.
    if (items.value.length === 0) {
        items.value = (data.items ?? []).map(toItem);
    }

    // Arriving by URL counts as being told — otherwise the pill would still be
    // announcing a scan the user is currently looking at.
    if (!isRunning.value) aiStore.acknowledge(generationId);
};

const fetchScan = () => {
    return axios.get(`/api/pantry/ai/generations/${generationId}`)
        .then((response) => {
            applyResponse(response.data);
        })
        .catch((error) => {
            console.error(error);
            loadError.value = error?.response?.data?.message || "Could not load this scan.";
        })
        .finally(() => {
            loading.value = false;
        });
};

const schedulePoll = () => {
    if (pollTimer !== null) clearTimeout(pollTimer);

    pollTimer = setTimeout(() => {
        pollTimer = null;
        fetchScan().then(() => {
            if (keepWatching.value) schedulePoll();
        });
    }, POLL_INTERVAL_MS);
};

const rename = (key: string, value: string) => {
    const item = items.value.find((candidate) => candidate.key === key);

    if (item) item.name = value;
};

const remove = (key: string) => {
    items.value = items.value.filter((item) => item.key !== key);
};

const openMove = (key: string) => {
    movingKey.value = key;
};

const pickSpace = (action: SheetAction) => {
    const item = items.value.find((candidate) => candidate.key === movingKey.value);

    if (item) {
        item.spaceId = action.id === "none" ? null : Number(action.id);
    }

    movingKey.value = null;
};

/**
 * The edited list, not what came back from the model. Blank names are dropped
 * rather than rejected — clearing a name is a reasonable way to say "not this
 * one", and the X is right there for anyone who means it more firmly.
 */
const confirm = () => {
    if (adding.value || named.value.length === 0) return;

    adding.value = true;

    axios.post(`/api/pantry/ai/generations/${generationId}/confirm`, {
        items: named.value.map((item) => ({
            name: item.name.trim(),
            space_id: item.spaceId,
            notes: item.notes,
        })),
    })
        .then((response) => {
            const added = response.data.added ?? named.value.length;
            confirmed.value = true;
            toastStore.show("success", `Added ${added} item${added === 1 ? "" : "s"} to your pantry.`);
            router.push("/storage-spaces");
        })
        .catch((error) => {
            console.error(error);
            toastStore.show("error", error?.response?.data?.message || "Could not add these items.");
        })
        .finally(() => {
            adding.value = false;
        });
};

/**
 * Leaving without adding anything. The server is told so the photo goes now
 * rather than waiting for the sweep — best effort, because a failed cleanup
 * call is no reason to trap someone on this page.
 */
const discard = () => {
    if (showList.value && !confirmed.value) {
        axios.delete(`/api/pantry/ai/generations/${generationId}`).catch(() => {
        });
    }

    router.push("/storage-spaces");
};

// The dock stands in for the bottom nav rather than stacking above it, so it
// only claims that place while there is actually something to add.
watch(showList, (visible) => {
    if (visible && !claimedDock) {
        dock.claim();
        claimedDock = true;
    } else if (!visible && claimedDock) {
        dock.release();
        claimedDock = false;
    }
});

onMounted(() => {
    if (!Number.isFinite(generationId)) {
        loadError.value = "That scan does not exist.";
        loading.value = false;
        return;
    }

    fetchScan().then(() => {
        if (keepWatching.value) schedulePoll();
    });
});

onBeforeUnmount(() => {
    if (pollTimer !== null) clearTimeout(pollTimer);
    if (claimedDock) dock.release();
});
</script>

<template>
    <DashboardLayout>
        <div class="pt-7 px-5" :style="showList ? 'padding-bottom: 1rem;' : ''">
            <div class="bg-white app-surface-gradient rounded-2xl shadow-xl p-6 border-2 border-slate-300">
                <div class="min-w-0">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">Review scan</h2>
                    <p v-if="showList" class="text-xs text-gray-500">
                        {{ named.length }} item{{ named.length === 1 ? '' : 's' }} found · nothing is added yet
                    </p>
                    <p v-else-if="isRunning" class="text-xs text-gray-500">Reading the photo</p>
                </div>

                <hr class="border-gray-200 my-4"/>

                <!-- Loading -->
                <div v-if="loading" class="py-10 text-center">
                    <svg class="w-8 h-8 mx-auto text-violet-600 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>

                <!-- Could not load -->
                <div v-else-if="loadError" class="space-y-4">
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-700">{{ loadError }}</p>
                    </div>
                    <button
                        @click="discard"
                        class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors"
                    >
                        Back to storage spaces
                    </button>
                </div>

                <!-- Still working. The job outlives this page, so leaving is safe. -->
                <div v-else-if="isRunning" class="py-10 text-center space-y-3">
                    <svg class="w-10 h-10 mx-auto text-violet-600 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-gray-900 font-semibold">Reading the photo…</p>
                    <p class="text-sm text-gray-500">This usually takes about 10 seconds. You can leave — it will be here when it is done.</p>
                </div>

                <!-- Failed -->
                <div v-else-if="status === 'failed'" class="space-y-4">
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-700">{{ scanError || "The scan failed." }}</p>
                    </div>
                    <p class="text-xs text-gray-400">The credit for this scan was refunded automatically.</p>
                    <button
                        @click="discard"
                        class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors"
                    >
                        Back to storage spaces
                    </button>
                </div>

                <!-- Already added, on this device or another one -->
                <div v-else-if="confirmed" class="space-y-4">
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                        <p class="text-sm text-emerald-800">These items have already been added to your pantry.</p>
                    </div>
                    <button
                        @click="discard"
                        class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors"
                    >
                        Back to storage spaces
                    </button>
                </div>

                <!-- Nothing recognisable, or everything removed by hand -->
                <div v-else-if="items.length === 0" class="py-8 text-center space-y-3">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h4l2-2h6l2 2h4v12H3z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"></path>
                    </svg>
                    <p class="text-gray-500 font-medium">Nothing to add</p>
                    <p class="text-sm text-gray-400">Try again with more light, or closer to the shelf so the labels are readable.</p>
                    <button
                        @click="discard"
                        class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors"
                    >
                        Back to storage spaces
                    </button>
                </div>

                <!-- The list -->
                <div v-else class="space-y-5">
                    <div v-for="group in groups" :key="group.id ?? 'none'">
                        <div class="flex items-baseline justify-between px-1 pb-1">
                            <h3
                                class="text-xs font-semibold uppercase tracking-wide"
                                :class="group.id === null ? 'text-amber-700' : 'text-gray-500'"
                            >
                                {{ group.name }}
                            </h3>
                            <span class="text-xs text-gray-400">{{ group.items.length }}</span>
                        </div>

                        <div
                            class="rounded-xl border px-2"
                            :class="group.id === null ? 'border-amber-200 bg-amber-50/60' : 'border-gray-200 bg-white'"
                        >
                            <ScanItemRow
                                v-for="item in group.items"
                                :key="item.key"
                                :item="item"
                                :needs-space="group.id === null"
                                @rename="rename(item.key, $event)"
                                @move="openMove(item.key)"
                                @remove="remove(item.key)"
                            />
                        </div>
                    </div>

                    <p v-if="unassignedCount > 0" class="text-xs text-amber-700 px-1">
                        {{ unassignedCount }} item{{ unassignedCount === 1 ? '' : 's' }} will be added without a storage space. You can pick one now or later.
                    </p>

                    <p class="text-xs text-gray-400 px-1">
                        Quantities and expiry dates are not read from a photo — add them from the pantry when you need them.
                    </p>
                </div>
            </div>
        </div>

        <ActionSheet
            :is-open="movingKey !== null"
            title="Move to"
            subtitle="Pick a space"
            :actions="spaceActions"
            @close="movingKey = null"
            @select="pickSpace"
        />

        <!-- The dock takes the nav's place rather than stacking on it — same
             reasoning as the selection dock, and the same styling. -->
        <Teleport to="body">
            <Transition name="dock-swap" appear>
                <div
                    v-if="showList"
                    class="dock-shell z-30"
                    style="padding-bottom: max(0.45rem, env(safe-area-inset-bottom));"
                >
                    <div class="mx-auto max-w-md px-3">
                        <div class="dock-track selection-dock__track">
                            <button
                                type="button"
                                class="selection-dock__ghost"
                                @click="discard"
                                aria-label="Discard this scan"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>

                            <div class="flex flex-col min-w-0">
                                <span class="selection-dock__count truncate">
                                    {{ named.length }} item{{ named.length === 1 ? '' : 's' }}
                                </span>
                                <span class="selection-dock__hint no-underline">
                                    {{ unassignedCount > 0 ? `${unassignedCount} without a space` : 'All sorted' }}
                                </span>
                            </div>

                            <button
                                type="button"
                                class="selection-dock__primary ml-auto"
                                :disabled="named.length === 0 || adding"
                                @click="confirm"
                            >
                                <svg
                                    v-if="adding"
                                    class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                                </svg>
                                {{ adding ? 'Adding…' : 'Add to pantry' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </DashboardLayout>
</template>
