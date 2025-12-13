import { computed, ref, watch } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import axios from 'axios';

export const usePaginatedTable = (options = {}) => {
    const {
        endpoint,
        initialPerPage = 5,
        searchFields = [],
        filterConfig = {},
        mapResponse,
        onError
    } = options;

    if (!endpoint) {
        throw new Error('Endpoint is required for usePaginatedTable');
    }

    if (!mapResponse || typeof mapResponse !== 'function') {
        throw new Error('mapResponse function is required for usePaginatedTable');
    }

    const data = ref([]);
    const totalRecords = ref(0);
    const perPage = ref(initialPerPage);
    const currentPage = ref(1);
    const isLoading = ref(true);
    const searchQuery = ref('');
    const expandedRows = ref([]);

    const filters = ref({
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        ...Object.entries(filterConfig).reduce((acc, [key, config]) => {
            acc[key] = { 
                value: config.defaultValue || null,
                matchMode: config.matchMode || FilterMatchMode.CONTAINS,
            };
            return acc;
        }, {}),
    });

    let searchDebouceTimer = null;
    let filterDebounceTimer = null;
    let skipFilterWatcher = false;

    const buildActiveFilters = () => {
        return Object.entries(filters.value).reduce((acc, [key, filter]) => {
            if (!filter || filter.value === null || filter.value === undefined) {
                return acc;
            }

            if (typeof filter.value === 'string') {
                const trimmed = filter.value.trim();
                if (trimmed.length === 0) {
                    return acc;
                }
                acc[key] = trimmed;
                return acc;
            }
            
            acc[key] = filter.value;
            return acc;
        }, {}); 
    };

    const hasActiveFilters = computed(() => {
        if (searchQuery.value.trim().length > 0) {
            return true;
        }

        return Object.entries(filters.value).some(filter => {
            if (!filter) {
                return false;
            }

            if (typeof filter.value === 'string') {
                return filter.value.trim().length > 0;
            }
            
            return filter.value !== null && filter.value !== undefined;
        });
    });

    async function fetchData() {
        if (!endpoint) {
            console.warn('Endpoint is not defined for usePaginatedTable');
            return;
        }

        data.value = [];
        isLoading.value = true;
        const searchValue = searchQuery.value.trim();
        const activeFilter = buildActiveFilters();

        try {
            await new Promise(resolve => setTimeout(resolve, 200)); // For data transition to show smoothly

            const params = {
                page: currentPage.value,
                per_page: perPage.value,
            }

            if (searchValue) {
                params.search = searchValue;
            }

            if (Object.keys(activeFilter).length > 0) {
                params.filters = JSON.stringify(activeFilter);
            }

            const response = await axios.get(endpoint, { params });

            const mapped = mapResponse(response);
            data.value = mapped.data;
            totalRecords.value = mapped.total;
            expandedRows.value = [];
        } catch (error) {
            onError(error);
        } finally {
            isLoading.value = false;
        }
    }

    watch(filters, () => {
        if (skipFilterWatcher) {
            skipFilterWatcher = false;
            return;
        }
        clearTimeout(filterDebounceTimer);
        filterDebounceTimer = setTimeout(() => {
            currentPage.value = 1;
            fetchData();
        }, 300);
    }, { deep: true });

    function onSearchInput() {
        clearTimeout(searchDebouceTimer);
        searchDebouceTimer = setTimeout(() => {
            currentPage.value = 1;
            fetchData();
        }, 300);
    }

    function clearFilters() {
        searchQuery.value = '';
        skipFilterWatcher = true;
        Object.keys(filters.value).forEach(filter => {
            if (filter) {
                filter.value = null;
            }
        });
        currentPage.value = 1;
        fetchData();
    }

    function onPageChange(event) {
        currentPage.value = event.page + 1;
        perPage.value = event.rows;
        fetchData();
    }

    function refresh() {
        fetchData();
    }

    return {
        data,
        totalRecords,
        perPage,
        currentPage,
        isLoading,
        searchQuery,
        filters,
        expandedRows,
        hasActiveFilters,

        fetchData,
        onSearchInput,
        clearFilters,
        onPageChange,
        refresh,

        first: computed(() => (currentPage.value - 1) * perPage.value),
    }


}