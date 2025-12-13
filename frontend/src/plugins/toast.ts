import {useToastStore} from "../stores/toast";

export default {
    install(app: any) {
        app.config.globalProperties.$toast = function (
            type: "success" | "error" | "info" | "warning",
            text: string
        ) {
            const toastStore = useToastStore();
            toastStore.show(type, text);
        };
    },
};

declare module "@vue/runtime-core" {
    interface ComponentCustomProperties {
        $toast: (
            type: "success" | "error" | "info" | "warning",
            text: string
        ) => void;
    }
}

