import {defineStore} from "pinia";
import {ref} from "vue";

export type ToastType = "success" | "error" | "info" | "warning";

export interface Toast {
    id: string;
    type: ToastType;
    text: string;
}

export const useToastStore = defineStore("toast", () => {
    const toasts = ref<Toast[]>([]);

    function show(type: ToastType, text: string) {
        const id = Math.random().toString(36).substring(7) + Date.now();
        const toast: Toast = {id, type, text};
        toasts.value.push(toast);

        setTimeout(() => {
            remove(id);
        }, 20000);
    }

    function remove(id: string) {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    }

    return {
        toasts,
        show,
        remove,
    };
});

