<template>
    <ResourceTableLayout
        :table="table"
        :columns="columns"
        :title="$t('Distance Activities')"
        :search-placeholder="$t('Search distance activities...')"
        :global-filter-fields="globalFilterFields"
    >
        <template #before-filter>
            <Select
                v-if="languageLevelOptions.length"
                v-model="table.filters.value.language_level_id.value"
                :options="languageLevelOptions"
                option-label="label"
                option-value="value"
                :placeholder="$t('Language Level')"
                show-clear
                class="w-full md:w-48"
                size="small"
            />
            <Select
                v-if="studentOptions.length"
                v-model="table.filters.value.student_id.value"
                :options="studentOptions"
                option-label="label"
                option-value="value"
                :placeholder="$t('Student')"
                show-clear
                class="w-full md:w-48"
                size="small"
            />
        </template>
    </ResourceTableLayout>
</template>

<script setup>
import { computed, h, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useSettingsTable } from '@/composables/useSettingsTable';
import { usePermissions } from '@/composables/usePermissions';
import { textColumn } from '@/components/datatable/columnFactories';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import axios from 'axios';

const { t: $t } = useI18n();
const router = useRouter();
const { can } = usePermissions();
const canViewTeacherCourseColumns = computed(() => can('view distance activity teacher and course columns'));

const languageLevelOptions = ref([]);
const studentOptions = ref([]);

onMounted(async () => {
    try {
        const response = await axios.get('/academics/lessons/distance-activities/filter-options');
        languageLevelOptions.value = response.data?.data?.language_levels ?? [];
        studentOptions.value = response.data?.data?.students ?? [];
    } catch {
        // Filter options unavailable — filters remain hidden.
    }
});

const table = useSettingsTable({
    endpoint: '/academics/lessons/distance-activities',
    initialSortField: 'week_number',
    initialSortOrder: 1,
    filterConfig: {
        language_level_id: { defaultValue: null, matchMode: 'equals' },
        student_id: { defaultValue: null, matchMode: 'equals' },
    },
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

    if (status === 'started') {
        return 'warn';
    }

    return 'danger';
};

const globalFilterFields = computed(() => [
    'title',
    ...(canViewTeacherCourseColumns.value ? ['course_name', 'teacher_name'] : []),
    'status',
]);

const columns = computed(() => [
    ...(can('view id columns') ? [textColumn({
        key: 'id',
        header: $t('ID'),
        sortable: true,
        style: 'width: 6rem;',
    })] : []),
    ...(canViewTeacherCourseColumns.value ? [
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
    ] : []),
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
