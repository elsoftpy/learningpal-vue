<template>
    <ResourceTableLayout
        :table="table"
        :columns="columns"
        :title="$t('Study Programs')"
        :search-placeholder="$t('Search study programs')"
        create-permission="create study programs"
        create-route-name="academics.settings.study-programs.create"
        :create-label="$t('Add Study Program')"
        :global-filter-fields="['title', 'status']"
        :delete-dialog="actions.deleteDialogConfig"
    >
        <template v-if="canViewDetailData" #expansion="{ data }">
            <StudyProgramDetailsTable
                :weeks="data.weeks"
                @add-week="handleAddWeek(data)"
                @edit-week="handleEditWeek(data, $event)"
                @delete-week="handleDeleteWeek(data, $event)"
                @add-activity="handleAddActivity(data, $event)"
                @edit-activity="handleEditActivity(data, $event)"
                @delete-activity="handleDeleteActivity(data, $event)"
            />
        </template>
    </ResourceTableLayout>
    <DeleteDialog
        v-if="weekDeleteDialogVisible"
        v-model:visible="weekDeleteDialogVisible"
        :message="weekDeleteDialogConfig.message"
        :onDelete="weekDeleteDialogConfig.onDelete"
        :loading="weekDeleteDialogConfig.loading"
    />
    <DeleteDialog
        v-if="activityDeleteDialogVisible"
        v-model:visible="activityDeleteDialogVisible"
        :message="activityDeleteDialogConfig.message"
        :onDelete="activityDeleteDialogConfig.onDelete"
        :loading="activityDeleteDialogConfig.loading"
    />
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { usePermissions } from '@/composables/usePermissions';
import { useSettingsTable } from '@/composables/useSettingsTable.js';
import { useRowActions } from '@/composables/useRowActions.js';
import { useI18n } from 'vue-i18n';
import { textColumn, statusTagColumn } from '@/components/datatable/columnFactories.js';
import StudyProgramDetailsTable from '@/components/academics/StudyProgramDetailsTable.vue';
import DeleteDialog from '@/components/datatable/DeleteDialog.vue';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import RowActionsColumn from '@/components/datatable/RowActionsColumn.vue';

const { t: $t } = useI18n();
const router = useRouter();
const { can } = usePermissions();
const canViewDetailData = computed(() => can('view study programs'));
const canViewActionsColumn = computed(() =>
    can(['edit study programs', 'delete study programs'])
);

const table = useSettingsTable({
    endpoint: '/academics/settings/study-programs',
    mapResponse: (response) => ({
        data: response.data?.data?.study_programs || [],
        total: response.data?.data?.total || 0,
    }),
});

const columns = computed(() => [
    {
        key: 'expander',
        isExpander: true,
        style: 'width: 1%',
        visible: () => canViewDetailData.value,
    },
    textColumn({
        key: 'id',
        header: $t('ID'),
        style: 'width: 5%; min-width: 2%;',
    }),
    textColumn({
        key: 'title',
        header: $t('Title'),
    }),
    textColumn({
        key: 'language',
        header: $t('Language'),
        formatter: ({ data }) => data?.language_level?.language?.name || '',
    }),
    textColumn({
        key: 'level',
        header: $t('Level'),
        formatter: ({ data }) => data?.language_level?.level || data?.language_level?.description || '',
    }),
    textColumn({
        key: 'weeks_count',
        header: $t('Weeks'),
        formatter: ({ data }) => Array.isArray(data?.weeks) ? data.weeks.length : 0,
    }),
    textColumn({
        key: 'activities_count',
        header: $t('Activities'),
        formatter: ({ data }) => Array.isArray(data?.weeks)
            ? data.weeks.reduce((total, week) => total + (Array.isArray(week?.activities) ? week.activities.length : 0), 0)
            : 0,
    }),
    statusTagColumn({
        key: 'status',
        header: $t('Status'),
    }),
    {
        key: 'actions',
        header: $t('Actions'),
        visible: () => canViewActionsColumn.value,
        bodyComponent: RowActionsColumn,
        bodyProps: {
            editPermission: 'edit study programs',
            deletePermission: 'delete study programs',
            editLabel: $t('Edit'),
            deleteLabel: $t('Delete'),
            onEdit: (row) => actions.handleEdit(row.id),
            onDelete: (row) => actions.handleDelete(row.id),
        },
        style: 'width: 10%; min-width: 10%;',
    },
]);

const actions = useRowActions({
    editRouteName: 'academics.settings.study-programs.edit',
    buildEditRoute: (studyProgramId, context = {}) => ({
        name: 'academics.settings.study-programs.edit',
        params: { id: studyProgramId },
        query: context.query || {},
    }),
    deleteEndpoint: '/academics/settings/study-programs/:id/destroy',
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        successMessage: $t('Study program deleted successfully.'),
        errorMessage: $t('An error occurred while deleting the study program.'),
        confirmMessage: $t('Are you sure you want to delete this study program?'),
    }
});

const weekActions = useRowActions({
    editRouteName: 'academics.settings.study-programs.weeks.edit',
    editRouteParam: 'weekId',
    deleteEndpoint: '/academics/settings/study-programs/weeks/:id/destroy',
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        successMessage: $t('Study program week deleted successfully.'),
        confirmMessage: $t('Are you sure you want to delete this week?'),
    },
});

const activityActions = useRowActions({
    editRouteName: 'academics.settings.study-programs.activities.edit',
    editRouteParam: 'activityId',
    deleteEndpoint: '/academics/settings/study-programs/activities/:id/destroy',
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        successMessage: $t('Study program activity deleted successfully.'),
        confirmMessage: $t('Are you sure you want to delete this activity?'),
    },
});

const weekDeleteDialogVisible = weekActions.deleteDialogVisible;
const weekDeleteDialogConfig = weekActions.deleteDialogConfig;
const activityDeleteDialogVisible = activityActions.deleteDialogVisible;
const activityDeleteDialogConfig = activityActions.deleteDialogConfig;

const handleAddWeek = (studyProgram) => {
    if (!studyProgram?.id || !can('edit study program week')) {
        return;
    }

    router.push({
        name: 'academics.settings.study-programs.weeks.create',
        params: { studyProgramId: studyProgram.id },
    });
};

const handleEditWeek = (_studyProgram, week) => {
    if (!week?.id) {
        return;
    }

    weekActions.handleEdit(week.id);
};

const handleDeleteWeek = (_studyProgram, week) => {
    if (!week?.id) {
        return;
    }

    weekActions.handleDelete(week.id);
};

const handleAddActivity = (studyProgram, week) => {
    if (!studyProgram?.id || !week?.id || !can('edit study program week activity')) {
        return;
    }

    router.push({
        name: 'academics.settings.study-programs.activities.create',
        params: { weekId: week.id },
    });
};

const handleEditActivity = (_studyProgram, payload) => {
    if (!payload?.activity?.id) {
        return;
    }

    activityActions.handleEdit(payload.activity.id);
};

const handleDeleteActivity = (_studyProgram, payload) => {
    if (!payload?.activity?.id) {
        return;
    }

    activityActions.handleDelete(payload.activity.id);
};
</script>
