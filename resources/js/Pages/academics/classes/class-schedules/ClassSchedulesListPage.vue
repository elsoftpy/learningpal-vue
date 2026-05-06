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
    <FeedbackDialog
        v-if="selectedSchedule"
        v-model:visible="feedbackDialogVisible"
        :feedback="feedbackValue"
        :loading="feedbackDialogLoading"
        :readonly="feedbackDialogReadonly"
        :show-save="feedbackDialogShowSave"
        :save-disabled="feedbackDialogSaveDisabled"
        :show-delete="feedbackDialogShowDelete"
        :delete-disabled="feedbackDeleteDisabled"
        :dismiss-label="feedbackDialogDismissLabel"
        :onSave="saveFeedback"
        :onDelete="deleteFeedback"
    />
</template>
<script setup>
import { computed, h, ref, watch } from 'vue';
import { usePermissions } from '@/composables/usePermissions.js';
import { useSettingsTable } from '@/composables/useSettingsTable.js';
import { useRowActions } from '@/composables/useRowActions.js';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { textColumn } from '@/components/datatable/columnFactories.js';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import RowActionsColumn from '@/components/datatable/RowActionsColumn.vue';
import ClassScheduleDetailsTable from '@/components/academics/ClassScheduleDetailsTable.vue';
import DeleteDialog from '@/components/datatable/DeleteDialog.vue';
import FeedbackDialog from '@/components/datatable/FeedbackDialog.vue';
import Button from 'primevue/button';
import axios from 'axios';

const { t: $t } = useI18n();
const { can } = usePermissions();
const { handleApiError } = useApiErrorHandler();
const toast = useToast();
const canViewDetailData = computed(() => can('view class schedule details'));
const canViewFeedback = computed(() => can('view schedule feedback'));
const canCreateFeedback = computed(() => can('create schedule feedback'));
const canEditFeedback = computed(() => can('edit schedule feedback'));
const canDeleteFeedback = computed(() => can('delete schedule feedback'));
const canViewActionsColumn = computed(() => can(['edit class schedules', 'delete class schedules']));
const feedbackDialogVisible = ref(false);
const feedbackDialogLoading = ref(false);
const selectedSchedule = ref(null);
const feedbackValue = ref('');

const table = useSettingsTable({
    endpoint: '/academics/lessons/class-schedules',
    searchFields: ['name'],
    filterConfig: {},
    initialSortField: 'schedule_month',
    initialSortOrder: -1,
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

const selectedScheduleHasFeedback = computed(() => Boolean(selectedSchedule.value?.feedback?.trim()));
const feedbackDialogReadonly = computed(() => {
    if (selectedScheduleHasFeedback.value) {
        return !canEditFeedback.value;
    }

    return !canCreateFeedback.value;
});
const feedbackDialogShowSave = computed(() => {
    if (selectedScheduleHasFeedback.value) {
        return canEditFeedback.value;
    }

    return canCreateFeedback.value;
});
const feedbackDialogSaveDisabled = computed(() => feedbackDialogLoading.value || !feedbackDialogShowSave.value);
const feedbackDialogShowDelete = computed(() => selectedScheduleHasFeedback.value && canDeleteFeedback.value);
const feedbackDeleteDisabled = computed(() => feedbackDialogLoading.value || !feedbackDialogShowDelete.value);
const feedbackDialogDismissLabel = computed(() => {
    const hasOnlyDismissAction = !feedbackDialogShowSave.value && !feedbackDialogShowDelete.value;
    return hasOnlyDismissAction ? $t('Dismiss') : $t('Cancel');
});

const columns = computed(() => [
    {
        key: 'expander',
        isExpander: true,
        style: 'width: 1%',
        visible: () => canViewDetailData.value,
    },
    ...(can('view id columns') ? [textColumn({
        key: 'id',
        header: $t('ID'),
        sortable: true,
        style: 'width: 1%',
    })] : []),
    textColumn({
        key: 'name',
        header: $t('Name'),
        fieldName: 'name',
        sortable: true,
        style: 'min-width:15%',
    }),
    textColumn({
        key: 'display_schedule_month',
        header: $t('Schedule Month'),
        sortField: 'schedule_month',
        sortable: true,
    }),
    textColumn({
        key: 'course',
        header: $t('Course'),
        fieldName: 'course',
    }),
    {
        key: 'feedback',
        header: $t('Feedback'),
        style: 'min-width: 12rem',
        visible: () => canViewFeedback.value,
        body: ({ data }) => isFeedbackButtonDisabled(data) ? null : h(Button, {
            size: 'small',
            severity: 'info',
            outlined: true,
            icon: resolveFeedbackButtonIcon(data),
            label: resolveFeedbackButtonLabel(data),
            onClick: () => openFeedbackDialog(data),
        }),
    },
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

const rowHasFeedback = (schedule) => Boolean(schedule?.feedback?.trim());

const isFeedbackButtonDisabled = (schedule) => !rowHasFeedback(schedule) && !canCreateFeedback.value;

const resolveFeedbackButtonLabel = (schedule) => {
    if (rowHasFeedback(schedule)) {
        return canEditFeedback.value ? `${$t('Edit')} Feedback` : `${$t('View')} Feedback`;
    }

    return `${$t('Add')} Feedback`;
};

const resolveFeedbackButtonIcon = (schedule) => {
    if (rowHasFeedback(schedule)) {
        return canEditFeedback.value ? 'pi pi-pencil' : 'pi pi-eye';
    }

    return 'pi pi-comment';
};

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

const openFeedbackDialog = (schedule) => {
    if (!schedule?.id || !canViewFeedback.value || isFeedbackButtonDisabled(schedule)) {
        return;
    }

    selectedSchedule.value = schedule;
    feedbackValue.value = schedule.feedback ?? '';
    feedbackDialogVisible.value = true;
};

watch(feedbackDialogVisible, (visible) => {
    if (visible) {
        return;
    }

    selectedSchedule.value = null;
    feedbackValue.value = '';
});

const saveFeedback = async (value) => {
    if (!selectedSchedule.value?.id || feedbackDialogSaveDisabled.value) {
        return;
    }

    feedbackDialogLoading.value = true;

    try {
        await axios.post(`/academics/lessons/class-schedules/${selectedSchedule.value.id}/feedback`, {
            feedback: value,
        });

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: $t('Feedback saved successfully.'),
            life: 3000,
        });

        await table.refresh();
    } catch (error) {
        const apiError = handleApiError(error);

        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: apiError?.message || $t('An unexpected error occurred. Please try again.'),
            life: 5000,
        });

        throw error;
    } finally {
        feedbackDialogLoading.value = false;
    }
};

const deleteFeedback = async () => {
    if (!selectedSchedule.value?.id || feedbackDeleteDisabled.value) {
        return;
    }

    feedbackDialogLoading.value = true;

    try {
        await axios.post(`/academics/lessons/class-schedules/${selectedSchedule.value.id}/feedback`, {
            feedback: null,
        });

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: $t('Feedback deleted successfully.'),
            life: 3000,
        });

        await table.refresh();
    } catch (error) {
        const apiError = handleApiError(error);

        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: apiError?.message || $t('An unexpected error occurred. Please try again.'),
            life: 5000,
        });

        throw error;
    } finally {
        feedbackDialogLoading.value = false;
    }
};
</script>
