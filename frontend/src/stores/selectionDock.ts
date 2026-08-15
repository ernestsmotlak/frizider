import {defineStore} from "pinia";
import {computed, ref} from "vue";

/**
 * Whether a selection dock is currently standing in for the bottom nav.
 *
 * The dock lives inside whichever card owns the selection, but the nav it
 * replaces lives in DashboardLayout — so the two need one bit of shared state.
 * Only this bit is shared: the count, the actions and what they do all stay
 * local to the card, passed as props and emits.
 *
 * Counted rather than boolean so an unmount that races a mount (navigating
 * between two selectable pages) cannot leave the nav hidden.
 */
export const useSelectionDockStore = defineStore("selectionDock", () => {
    const claims = ref(0);

    const active = computed(() => claims.value > 0);

    const claim = () => {
        claims.value += 1;
    };

    const release = () => {
        claims.value = Math.max(0, claims.value - 1);
    };

    return {active, claim, release};
});
