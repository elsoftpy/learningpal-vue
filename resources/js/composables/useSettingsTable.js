import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { usePaginatedTable } from '@/composables/usePaginatedTable.js';

const DEFAULT_PER_PAGE = 5;

export function useSettingsTable(options = {}) {
    const {
        endpoint,
        mapResponse,
        searchFields = [],
        filterConfig = {},
        initialPerPage = DEFAULT_PER_PAGE,
        onError,
        autoFetch = true,
        ...rest
    } = options;

    if (!endpoint) {
        throw new Error('useSettingsTable requires an endpoint');
    }

    if (typeof mapResponse !== 'function') {
        throw new Error('useSettingsTable requires a mapResponse function');
    }

    const toast = useToast();
    const { t: $t } = useI18n();

    const defaultOnError = (error) => {
        const status = error?.response?.status;

        // Auth/session failures are handled globally by axios interceptor/router redirect.
        if (error?.__authRedirectHandled || status === 401 || status === 419) {
            return;
        }

        const message = error?.message || $t('An unexpected error occurred.');
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: message,
            life: 4000,
        });
    };

    const table = usePaginatedTable({
        endpoint,
        mapResponse,
        searchFields,
        filterConfig,
        initialPerPage,
        onError: typeof onError === 'function' ? onError : defaultOnError,
        ...rest,
    });

    if (autoFetch) {
        onMounted(() => {
            table.fetchData();
        });
    }

    return table;
}