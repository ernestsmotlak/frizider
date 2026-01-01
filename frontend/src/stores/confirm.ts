import {defineStore} from "pinia";
import {ref} from "vue";

let resolvePromise: ((value: boolean) => void) | null = null;

export const useConfirmStore = defineStore("confirm", () => {
    const isOpen = ref(false);
    const message = ref("");

    function show(msg: string): Promise<boolean> {
        message.value = msg;
        isOpen.value = true;
        
        return new Promise<boolean>((resolve) => {
            resolvePromise = resolve;
        });
    }

    function confirm() {
        isOpen.value = false;
        if (resolvePromise) {
            resolvePromise(true);
            resolvePromise = null;
        }
    }

    function cancel() {
        isOpen.value = false;
        if (resolvePromise) {
            resolvePromise(false);
            resolvePromise = null;
        }
    }

    return {
        isOpen,
        message,
        show,
        confirm,
        cancel,
    };
});
