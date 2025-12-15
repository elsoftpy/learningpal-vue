<template>
    <!-- Courses DataTable-->
    <ResourceTableLayout
        :table="table"
        :columns="columns"
        :title="$t('Courses')"
        :search-placeholder="$t('Search courses')"
        create-permission="create courses"
        create-route-name="academics.settings.courses.create"
        :create-label="$t('Add Course')"
        :global-filter-fields="['name', 'language_name', 'language_level_description']"
        :delete-dialog="actions.deleteDialogConfig"
    >
    </ResourceTableLayout>
</template>
<script setup>
import { computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import { useSettingsTable } from '@/composables/useSettingsTable.js';
import { useRowActions } from '@/composables/useRowActions.js';
import { useI18n } from 'vue-i18n';
import { textColumn } from '@/components/datatable/columnFactories.js';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import RowActionsColumn from '@/components/datatable/RowActionsColumn.vue';
import { statusTagColumn } from '@/components/datatable/columnFactories';

const { t : $t } = useI18n();
const { can } = usePermissions();
const canViewActionsColumn = computed(() => 
    can(['edit courses', 'delete courses'])
);

const table = useSettingsTable({
    endpoint: '/academics/settings/courses',
    mapResponse: (response) => ({
        data: response.data?.data?.courses || [],
        total: response.data?.data?.total || 0,
    }),
});

const columns = computed(() => [
    textColumn({
        key: 'id',
        header: $t('ID'),
        style: 'width: 5%; min-width: 2%;',
    }),
    textColumn({
        key: 'name',
        header: $t('Course Name'),
    }),
    textColumn({
        key: 'language_name',
        header: $t('Language'),
    }),
    textColumn({
        key: 'language_level_description',
        header: $t('Language Level'),
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
            editPermission: 'edit courses',
            deletePermission: 'delete courses',
            editLabel: $t('Edit'),
            deleteLabel: $t('Delete'),
            onEdit: (row) => actions.handleEdit(row.id),
            onDelete: (row) => actions.handleDelete(row.id),
        },
        style: 'width: 10%; min-width: 10%;'
    }
]);

const actions = useRowActions({
    editRouteName: 'academics.settings.courses.edit',
    deleteEndpoint: '/academics/settings/courses/:id/destroy',
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        successMessage: $t('Course deleted successfully.'),
        errorMessage: $t('An error occurred while deleting the course.'),
        confirmMessage: $t('Are you sure you want to delete this course?'),
    }
});
</script>