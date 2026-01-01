import {useConfirmStore} from "../stores/confirm";

export default {
    install(app: any) {
        app.config.globalProperties.$confirm = function (msg: string): Promise<boolean> {
            const confirmStore = useConfirmStore();
            return confirmStore.show(msg);
        };
    },
};

declare module "@vue/runtime-core" {
    interface ComponentCustomProperties {
        $confirm: (message: string) => Promise<boolean>;
    }
}
