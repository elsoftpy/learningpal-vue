<template>
    <ResourceTableLayout
        :table="table"
        :columns="columns"
        :search-placeholder="$t('Search class schedule')"
        create-permission="create class schedules"
        create-route-name="academics.classes.class-schedules.create"
        :create-label="$t('Add Class Schedule')"
        :global-filter-fields="['name']"
        :delete-dialog="actions.deleteDialogConfig"
    >
        <template v-if="canViewDetailData" #expansion="{ data }">
            <ClassScheduleDetailsTable
                v-if="Array.isArray(data.details) && data.details.length"
                :details="data.details"
                @edit-detail="handleDetailEdit(data.id, $event)"
                @delete-detail="handleDetailDelete($event)"
            />
            <div
                v-else
                class="expand-panel bg-slate-50 dark:bg-slate-800 border border-dashed border-slate-200 dark:border-slate-700 rounded-lg p-5 text-sm text-slate-500"
            >
                {{ $t('No session details available.') }}
            </div>
        </template>
    </ResourceTableLayout>
    <DeleteDialog
        v-if="detailDeleteDialogVisible"
        v-model:visible="detailDeleteDialogVisible"
        :message="detailDeleteDialogConfig.message"
        :onDelete="detailDeleteDialogConfig.onDelete"
        :loading="detailDeleteDialogConfig.loading"
    />
</template>
<script setup>
import { computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions.js';
import { useSettingsTable } from '@/composables/useSettingsTable.js';
import { useRowActions } from '@/composables/useRowActions.js';
import { useI18n } from 'vue-i18n';
import { textColumn } from '@/components/datatable/columnFactories.js';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import RowActionsColumn from '@/components/datatable/RowActionsColumn.vue';
import ClassScheduleDetailsTable from '@/components/academics/ClassScheduleDetailsTable.vue';
import DeleteDialog from '@/components/datatable/DeleteDialog.vue';

const { t: $t } = useI18n();
const { can } = usePermissions();
const canViewDetailData = computed(() => can('view class schedule details'));
const canViewActionsColumn = computed(() => can(['edit class schedules', 'delete class schedules']));

const table = useSettingsTable({
    endpoint: '/academics/lessons/class-schedules',
    searchFields: ['name'],
    filterConfig: {},
    mapResponse: (response) => ({
        data: response.data?.data?.class_schedules || [],
        total: response.data?.data?.total || 0,
    }),
});

const actions = useRowActions({
    editRouteName: 'academics.classes.class-schedules.edit',
    deleteEndpoint: '/academics/lessons/class-schedules/:id/destroy',
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        successMessage: $t('Class schedule deleted successfully.'),
        errorMessage: $t('An error occurred while deleting the class schedule.'),
        confirmMessage: $t('Are you sure you want to delete this class schedule?'),
    }
});

const detailActions = useRowActions({
    editRouteName: 'academics.classes.class-schedules.details.edit',
    deleteEndpoint: '/academics/lessons/class-schedules/details/:id/destroy',
    buildEditRoute: (detailId, { scheduleId }) => {
        if (!scheduleId || !detailId) {
            return null;
        }

        return {
            name: 'academics.classes.class-schedules.details.edit',
            params: {
                scheduleId,
                detailId,
            },
        };
    },
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        successMessage: $t('Session deleted successfully.'),
        errorMessage: $t('An error occurred while deleting the session.'),
        confirmMessage: $t('Are you sure you want to delete this session?'),
    }
});

const detailDeleteDialogVisible = detailActions.deleteDialogVisible;
const detailDeleteDialogConfig = detailActions.deleteDialogConfig;

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
        style: 'width: 1%',
    }),
    textColumn({
        key: "name",
        header: $t('Name'),
        fieldName: 'name',
        style: 'min-width:15%',
    }),
    textColumn({
        key: 'display_schedule_month',
        header: $t('Schedule Month'),
        fieldName: 'display_schedule_month',
    }),
    textColumn({
        key: 'course',
        header: $t('Course'),
        fieldName: 'course',
    }),
    {
        key: 'actions',
        header: $t('Actions'),
        style: 'min-width: 15%',
        visible: () => canViewActionsColumn.value,
        bodyComponent: RowActionsColumn,
        bodyProps: {
            editPermission: 'edit class schedules',
            deletePermission: 'delete class schedules',
            editLabel: $t('Edit'),
            deleteLabel: $t('Delete'),
            onEdit: (row) => actions.handleEdit(row.id),
            onDelete: (row) => actions.handleDelete(row.id),
        },
    },
]);

const handleDetailEdit = (scheduleId, detail) => {
    if (!scheduleId || !detail?.id) {
        return;
    }

    detailActions.handleEdit(detail.id, { scheduleId });
};

const handleDetailDelete = (detail) => {
    if (!detail?.id) {
        return;
    }

    detailActions.handleDelete(detail.id);
};
</script>