<template>
    <ResourceTableLayout
        :table="table"
        :columns="columns"
        :search-placeholder="$t('Search language levels...')"
        :clear-filter-label="$t('Clear filters')"
        create-permission="create language levels"
        create-route-name="academics.settings.language-levels.create"
        :create-label="$t('Add Language Level')"
        :global-filter-fields="['description', 'level', 'language_name']"
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
import { statusTagColumn } from '../../../../components/datatable/columnFactories';

const { t : $t } = useI18n();
const { can } = usePermissions();
const canViewActionsColumn = computed(() => 
    can(['edit language levels', 'delete language levels'])
);

const table = useSettingsTable({
    endpoint: '/academics/settings/language-levels',
    initialSortField: 'level',
    initialSortOrder: 1,
    mapResponse: (response) => ({
        data: response.data?.data?.language_levels || [],
        total: response.data?.data?.total || 0,
    }),
});

const columns = computed(() => [
    textColumn({
        key: 'id',
        header: $t('ID'),
        sortable: true,
        style: 'width: 5%; min-width: 2%;',
    }),
    textColumn({
        key: 'description',
        header: $t('Description'),
        sortable: true,
    }),
    textColumn({
        key: 'level',
        header: $t('Level'),
        sortable: true,
    }),
    textColumn({
        key: 'language_name',
        header: $t('Name'),
        sortable: true,
    }),
    statusTagColumn({
        key: 'status',
        header: $t('Status'),
        sortable: true,
    }),
    {
        key: 'actions',
        header: $t('Actions'),
        visible: () => canViewActionsColumn.value,
        bodyComponent: RowActionsColumn,
        bodyProps: {
            editPermission: 'edit language levels',
            deletePermission: 'delete language levels',
            editLabel: $t('Edit'),
            deleteLabel: $t('Delete'),
            onEdit: (data) => actions.handleEdit(data.id),
            onDelete: (data) => actions.handleDelete(data.id),
        },
    },
]);

const actions = useRowActions({
    editRouteName: 'academics.settings.language-levels.edit',
    deleteEndpoint: '/academics/settings/language-levels/:id/destroy',
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        successMessage: $t('Language level deleted successfully.'),
        errorMessage: $t('An error occurred while deleting the language level.'),
        confirmMessage: $t('Are you sure you want to delete this language level?'),
    }
});

</script>