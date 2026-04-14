<template>
    <ResourceTableLayout
        :table="table"
        :columns="columns"
        :title="$t('Distance Activities')"
        :search-placeholder="$t('Search distance activities...')"
        :global-filter-fields="['title', 'course_name', 'teacher_name', 'status']"
    />
</template>

<script setup>
import { computed, h } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useSettingsTable } from '@/composables/useSettingsTable';
import { usePermissions } from '@/composables/usePermissions';
import { textColumn } from '@/components/datatable/columnFactories';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const { t: $t } = useI18n();
const router = useRouter();
const { can } = usePermissions();

const table = useSettingsTable({
    endpoint: '/academics/lessons/distance-activities',
    initialSortField: 'week_number',
    initialSortOrder: 1,
    mapResponse: (response) => ({
        data: response.data?.data?.distance_activities || [],
        total: response.data?.data?.total || 0,
    }),
});

const openDetail = (id) => {
    router.push({
        name: 'academics.classes.distance-activities.detail',
        params: { id },
    });
};

const statusSeverity = (status) => {
    if (status === 'completed') {
        return 'success';
    }

    return 'warn';
};

const columns = computed(() => [
    ...(can('view id columns') ? [textColumn({
        key: 'id',
        header: $t('ID'),
        sortable: true,
        style: 'width: 6rem;',
    })] : []),
    textColumn({
        key: 'teacher_name',
        header: $t('Teacher'),
        sortable: true,
    }),
    textColumn({
        key: 'course_name',
        header: $t('Course'),
        sortable: true,
    }),
    textColumn({
        key: 'title',
        header: $t('Distance Activity'),
        sortable: true,
    }),
    textColumn({
        key: 'comments',
        header: $t('Comments'),
        emptyValue: '—',
    }),
    textColumn({
        key: 'progress',
        header: $t('Progress'),
        formatter: ({ data }) => data?.progress || '0/0',
    }),
    {
        key: 'status',
        header: $t('Status'),
        body: ({ data }) => h(Tag, {
            value: data?.display_status || $t('Pending'),
            severity: statusSeverity(data?.status),
        }),
    },
    {
        key: 'actions',
        header: $t('Actions'),
        style: 'width: 8rem;',
        body: ({ data }) => h(Button, {
            type: 'button',
            size: 'small',
            icon: 'pi pi-eye',
            label: $t('View'),
            onClick: () => openDetail(data.id),
        }),
    },
]);
</script>
