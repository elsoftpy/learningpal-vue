<template>
    <ResourceTableLayout
        :table="table"
        :columns="columns"
        :search-placeholder="$t('Search class records...')"
        create-permission="create class records"
        create-route-name="academics.classes.class-records.create"
        :create-label="$t('Add Class Record')"
        :global-filter-fields="['name']"
        :delete-dialog="actions.deleteDialogConfig"
    >
        <template v-if="canViewDetailData" #expansion="{ data }">
            <ClassRecordDetailsTable
                v-if="Array.isArray(data.details) && data.details.length"
                :details="data.details"
                :class-record-id="data.id"
                :student-production-media="data.student_production_media || []"
                @edit-detail="handleDetailEdit(data.id, $event)"
                @delete-detail="handleDetailDelete($event)"
                @student-production-saved="table.refresh()"
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

    <Dialog
        :visible="commentsDialogVisible"
        modal
        :closable="false"
        @update:visible="commentsDialogVisible = $event"
    >
        <template #header>
            <div class="flex w-full justify-between items-center rounded-lg h-16 p-4 text-white bg-blue-500">
                <span class="text-xl font-semibold">{{ $t('Class Record Comment') }}</span>
                <Button
                    icon="pi pi-times"
                    rounded
                    size="small"
                    severity="info"
                    variant="outlined"
                    class="text-white! border-2! hover:text-gray-800!"
                    @click="commentsDialogVisible = false"
                />
            </div>
        </template>
        <span class="flex p-4 items-center font-semibold mb-4 whitespace-pre-wrap wrap-break-word">
            {{ selectedComment || $t('No comment available.') }}
        </span>
        <div class="flex justify-end gap-2">
            <Button
                type="button"
                :label="$t('Close')"
                severity="secondary"
                @click="commentsDialogVisible = false"
            />
        </div>
    </Dialog>
</template>
<script setup>
import { computed, h, ref } from 'vue';
import { usePermissions } from '@/composables/usePermissions.js';
import { useSettingsTable } from '@/composables/useSettingsTable.js';
import { useRowActions } from '@/composables/useRowActions.js';
import { useI18n } from 'vue-i18n';
import { textColumn } from '@/components/datatable/columnFactories.js';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import RowActionsColumn from '@/components/datatable/RowActionsColumn.vue';
import ClassRecordDetailsTable from '@/components/academics/ClassRecordDetailsTable.vue';
import DeleteDialog from '@/components/datatable/DeleteDialog.vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const { t: $t } = useI18n();
const { can } = usePermissions();
const canViewDetailData = computed(() => can('view class records'));
const canViewActionsColumn = computed(() => can(['edit class records', 'delete class records']));

const table = useSettingsTable({
    endpoint: '/academics/lessons/class-records',
    searchFields: ['name'],
    filterConfig: {},
    mapResponse: (response) => ({
        data: response.data?.data?.class_records || [],
        total: response.data?.data?.total || 0,
    }),
});

const actions = useRowActions({
    editRouteName: 'academics.classes.class-records.edit',
    deleteEndpoint: '/academics/lessons/class-records/:id/destroy',
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        successMessage: $t('Class record deleted successfully.'),
        errorMessage: $t('An error occurred while deleting the class record.'),
        confirmMessage: $t('Are you sure you want to delete this class record?'),
    }
});

const detailActions = useRowActions({
    editRouteName: 'academics.classes.class-records.details.edit',
    deleteEndpoint: '/academics/lessons/class-records/details/:id/destroy',
    buildEditRoute: (detailId, { scheduleId }) => {
        if (!scheduleId || !detailId) {
            return null;
        }

        return {
            name: 'academics.classes.class-records.details.edit',
            params: {
                recordId: scheduleId,
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
const commentsDialogVisible = ref(false);
const selectedComment = ref('');

const getAttendanceSeverity = (row) => {
    const rawValue = String(row?.attendance_label).trim().toLowerCase();
    console.log('Attendance value:', rawValue);
    if (rawValue === $t('present').toLowerCase()) {
        return 'success';
    }

    if (rawValue === $t('absent').toLowerCase()) {
        return 'danger';
    }

    if (rawValue === $t('late').toLowerCase()) {
        return 'warn';
    }

    return 'secondary';
};

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
        key: "teacher",
        header: $t('Teacher'),
        fieldName: 'teacher',
        style: 'min-width:15%',
    }),
    textColumn({
        key: 'user',
        header: $t('User'),
        fieldName: 'user',
    }),
    textColumn({
        key: 'course',
        header: $t('Course'),
        fieldName: 'course',
    }),
    textColumn({
        key: 'date',
        header: $t('Date'),
        fieldName: 'date',
    }),
    textColumn({
        key: 'start_time',
        header: $t('Start'),
        fieldName: 'start_time',
        emptyValue: '-',
    }),
    textColumn({
        key: 'end_time',
        header: $t('End'),
        fieldName: 'end_time',
        emptyValue: '-',
    }),
    textColumn({
        key: 'attendance_label',
        header: $t('Attendance'),
        fieldName: 'attendance_label',
        emptyValue: '-',
        body: ({ data }) => {
            const label = data?.attendance_label || data?.attendance || '';

            if (!label) {
                return '-';
            }

            return h(Tag, {
                value: label,
                severity: getAttendanceSeverity(data),
            });
        },
    }),
    {
        key: 'comment',
        header: $t('Comment'),
        style: 'min-width: 10%',
        body: ({ data }) => h(Button, {
            type: 'button',
            size: 'small',
            icon: 'pi pi-comment',
            severity: 'secondary',
            label: $t('View'),
            onClick: () => openCommentsDialog(data?.comments),
        }),
    },
    {
        key: 'actions',
        header: $t('Actions'),
        style: 'min-width: 15%',
        visible: () => canViewActionsColumn.value,
        bodyComponent: RowActionsColumn,
        bodyProps: {
            editPermission: 'edit class records',
            deletePermission: 'delete class records',
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

const openCommentsDialog = (comment) => {
    selectedComment.value = comment || '';
    commentsDialogVisible.value = true;
};
</script>