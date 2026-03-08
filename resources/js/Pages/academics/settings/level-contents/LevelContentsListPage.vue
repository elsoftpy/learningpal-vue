<template>
    <ResourceTableLayout
        :table="table"
        :columns="columns"
        :search-placeholder="$t('Search level contents...')"
        :clear-filter-label="$t('Clear filters')"
        create-permission="create level contents"
        create-route-name="academics.settings.level-contents.create"
        :create-label="$t('Add Level Content')"
        :global-filter-fields="['description', 'language_level_description', 'language_name']"
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

const { t : $t } = useI18n();
const { can } = usePermissions();
const canViewActionsColumn = computed(() => 
    can(['edit level contents', 'delete level contents'])
);

function formatLanguageLevel(languageLevel) {
    if (!languageLevel) {
        return '';
    }

    const description = languageLevel?.description ?? '';
    const level = languageLevel?.level ?? '';

    if (description && level) {
        return `${description} (${level})`;
    }

    return description || level;
}

const table = useSettingsTable({
    endpoint: '/academics/settings/level-contents',
    mapResponse: (response) => {
        const rows = response.data?.data?.level_contents || [];
        return {
            data: rows.map((item) => ({
                ...item,
                language_name: item?.language_level?.language?.name ?? '',
                language_level_description: formatLanguageLevel(item?.language_level),
            })),
            total: response.data?.data?.total || 0,
        };
    },
});

const columns = computed(() => [
    textColumn({
        key: 'id',
        header: $t('ID'),
        style: 'width: 5%; min-width: 2%;',
    }),
    textColumn({
        key: 'description',
        header: $t('Description'),
    }),
    textColumn({
        key: 'language_name',
        header: $t('Language'),
    }),
    textColumn({
        key: 'language_level_description',
        header: $t('Language Level'),
    }),
    textColumn({
        key: 'content',
        header: $t('Content'),
    }),
    
    {
        key: 'actions',
        header: $t('Actions'),
        visible: () => canViewActionsColumn.value,
        bodyComponent: RowActionsColumn,
        bodyProps: {
            editPermission: 'edit level contents',
            deletePermission: 'delete level contents',
            editLabel: $t('Edit'),
            deleteLabel: $t('Delete'),
            onEdit: (data) => actions.handleEdit(data.id),
            onDelete: (data) => actions.handleDelete(data.id),
        },
    },
]);

const actions = useRowActions({
    editRouteName: 'academics.settings.level-contents.edit',
    deleteEndpoint: '/academics/settings/level-contents/:id/destroy',
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        successMessage: $t('Level content deleted successfully.'),
        errorMessage: $t('An error occurred while deleting the level content.'),
        confirmMessage: $t('Are you sure you want to delete this level content?'),
    }
});

</script>