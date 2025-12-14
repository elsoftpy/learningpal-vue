<template>
    <!-- Language DataTable -->
    <ResourceTableLayout
        :table="table"
        :columns="columns"
        :search-placeholder="$t('Search language')"
        :clear-filter-label="$t('Clear filters')"
        create-permission="create languages"
        create-route-name="settings.languages.create"
        :create-label="$t('Add Language')"
        :global-filter-fields="['name']"
    >
    </ResourceTableLayout>
    <!-- Delete Confirmation Dialog -->
    <DeleteDialog
        v-model:visible="actions.deleteDialogVisible.value"
        :message="$t('Are you sure you want to delete this language?')"
        :onDelete="actions.confirmDelete"
        :loading="actions.isDeleting.value"
    />

</template>
<script setup>
import { onMounted, computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import { usePaginatedTable } from '@/composables/usePaginatedTable';
import { useRowActions} from '@/composables/useRowActions.js';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import { textColumn } from '@/components/datatable/columnFactories.js';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import RowActionsColumn from '@/components/datatable/RowActionsColumn.vue';
import DeleteDialog from '@/components/datatable/DeleteDialog.vue';

const { t : $t } = useI18n();
const { can } = usePermissions();
const toast = useToast();
const canViewActionsColumn = computed(() => 
    can(['edit languages', 'delete languages'])
);

const table = usePaginatedTable({
    endpoint: '/settings/languages',
    initialPerPage: 5,
    mapResponse: (response) => ({
        data: response.data.data.languages,
        total: response.data.data.total,
    }),
    onError: (error) => {
        toast.add({ 
            severity: 'error', 
            summary: $t('Error'), 
            detail: error.message,
            life: 3000 
        });
    },
});

const columns = computed(() => [
    textColumn({
        key: 'id',
        header: $t('ID'),
        style: 'width: 6rem',
    }),
    textColumn({
        key: 'name',
        header: $t('Name'),
        style: 'min-width: 15%',
    }),
    {
        key: 'actions',
        header: $t('Actions'),
        visible: () => canViewActionsColumn.value,
        bodyComponent: RowActionsColumn,
        bodyProps: {
            editPermission: 'edit languages',
            deletePermission: 'delete languages',
            editLabel: $t('Edit'),
            deleteLabel: $t('Delete'),
            onEdit: (row) => actions.handleEdit(row.id),
            onDelete: (row) => actions.handleDelete(row.id),
        },
    }
]);

const actions = useRowActions({
    editRouteName: 'settings.languages.edit',
    deleteEndpoint: '/settings/languages/:id/destroy',
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        deleteSuccess: $t('Language deleted successfully.'),
        deleteError: $t('An error occurred while deleting the language.'),
    }
});

onMounted(() => {
    table.fetchData();
});
</script>