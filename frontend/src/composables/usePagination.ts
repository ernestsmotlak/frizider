import {ref, onMounted, onUnmounted} from "vue";
import {useToastStore} from "../stores/toast.ts";
import {useLoadingStore} from "../stores/loading.ts";

type PaginationPayload = Record<string, unknown> | (() => Record<string, unknown>);

interface PaginationOptions {
    endpoint: string;
    errorMessage?: string;
    scrollThreshold?: number;
    debounceMs?: number;
    payload?: PaginationPayload;
}

export function usePagination<T>(options: PaginationOptions) {
    const toastStore = useToastStore();
    const loadingStore = useLoadingStore();

    const items = ref<T[]>([]);
    const currentPage = ref(0);
    const hasMore = ref(true);
    const isLoading = ref(false);
    const allRows = ref(0);
    let scrollTimeout: number | null = null;

    const getPayload = (): Record<string, unknown> => {
        if (!options.payload) {
            return {};
        }
        return typeof options.payload === "function" ? options.payload() : options.payload;
    };

    const fetchPage = (page: number = 1) => {
        if (isLoading.value) return;
        if (page !== 1 && !hasMore.value) return;
        if (page > 1 && page <= currentPage.value) return;

        isLoading.value = true;
        if (page === 1) loadingStore.start();

        axios.post(options.endpoint, {...getPayload(), page})
            .then((response: any) => {
                const paginator = response.data.data;
                allRows.value = response.data.allRecipes ?? 0;
                if (page === 1) {
                    items.value = paginator.data;
                } else {
                    items.value.push(...paginator.data);
                }

                currentPage.value = page;
                hasMore.value = paginator.next_page_url !== null;
            })
            .catch(() => {
                toastStore.show('error', options.errorMessage || 'Could not fetch data.');
            })
            .finally(() => {
                isLoading.value = false;
                if (page === 1) loadingStore.stop();
            });
    }

    const handleScroll = () => {
        if (scrollTimeout) clearTimeout(scrollTimeout);

        scrollTimeout = window.setTimeout(() => {
            if (!hasMore.value || isLoading.value) return;

            const scrolledToBottom = window.innerHeight + window.scrollY >=
                document.documentElement.scrollHeight - (options.scrollThreshold || 300);

            if (scrolledToBottom) {
                fetchPage(currentPage.value + 1);
            }
        }, options.debounceMs || 200);
    }

    onMounted(() => {
        fetchPage(1);
        window.addEventListener('scroll', handleScroll, {passive: true});
    });

    onUnmounted(() => {
        window.removeEventListener('scroll', handleScroll);
        if (scrollTimeout) clearTimeout(scrollTimeout);
    });

    const refresh = () => {
        hasMore.value = true;
        fetchPage(1);
    };

    return {
        items,
        currentPage,
        hasMore,
        isLoading,
        fetchPage,
        allRows,
        refresh,
    };
}








