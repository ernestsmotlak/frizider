import {defineStore} from "pinia";
import {ref, computed} from "vue";

export const useAuthStore = defineStore("auth", () => {
    const user = ref<object | null>(null);
    const initialized = ref(false);
    let fetchPromise: Promise<void> | null = null;

    const isAuthenticated = computed(() => user.value !== null);

    async function fetchUser() {
        if (fetchPromise) {
            return fetchPromise;
        }

        fetchPromise = (async () => {
            try {
                const response = await axios.get("/api/me");
                user.value = response.data.data;
            } catch {
                user.value = null;
            } finally {
                initialized.value = true;
                fetchPromise = null;
            }
        })();

        return fetchPromise;
    }

    function logout() {
        user.value = null;
        initialized.value = true;
    }

    return {
        user,
        initialized,
        isAuthenticated,
        fetchUser,
        logout,
    };
});

