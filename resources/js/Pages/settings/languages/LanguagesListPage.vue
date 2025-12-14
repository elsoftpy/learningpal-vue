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
        :delete-dialog="actions.deleteDialogConfig"
    >
    </ResourceTableLayout>

</template>
<script setup>
import { computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import { useSettingsTable } from '@/composables/useSettingsTable.js';
import { useRowActions} from '@/composables/useRowActions.js';
import { useI18n } from 'vue-i18n';
import { textColumn } from '@/components/datatable/columnFactories.js';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import RowActionsColumn from '@/components/datatable/RowActionsColumn.vue';

const { t : $t } = useI18n();
const { can } = usePermissions();
const canViewActionsColumn = computed(() => 
    can(['edit languages', 'delete languages'])
);

const table = useSettingsTable({
    endpoint: '/settings/languages',
    mapResponse: (response) => ({
        data: response.data.data.languages,
        total: response.data.data.total,
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
        header: $t('Name'),
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
        style: 'width: 10%; min-width: 10%;'
    }
]);

const actions = useRowActions({
    editRouteName: 'settings.languages.edit',
    deleteEndpoint: '/settings/languages/:id/destroy',
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        successMessage: $t('Language deleted successfully.'),
        errorMessage: $t('An error occurred while deleting the language.'),
        confirmMessage: $t('Are you sure you want to delete this language?'),
    }
});

</script>