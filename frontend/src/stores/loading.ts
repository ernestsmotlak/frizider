import {defineStore} from "pinia";
import {ref} from "vue";

export const useLoadingStore = defineStore("loading", () => {
    const loading = ref(false);

    function setLoading(value: boolean) {
        loading.value = value;
    }

    function start() {
        loading.value = true;
    }

    function stop() {
        loading.value = false;
    }

    return {
        loading,
        setLoading,
        start,
        stop,
    };
});









