<template>
    <!-- Users DataTable -->
    <ResourceTableLayout
        :table="table"
        :columns="columns"
        :search-placeholder="$t('Search user')"
        create-permission="create users"
        create-route-name="settings.users.create"
        :create-label="$t('Add User')"
        filter-display="row"
        :global-filter-fields="['first_name', 'last_name', 'email']"
        :delete-dialog="actions.deleteDialogConfig"
        :row-expansion="profileExpansion"
    >
    </ResourceTableLayout>
</template>
<script setup>
import { computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions.js';
import { useSettingsTable } from '@/composables/useSettingsTable.js';
import { useRowActions } from '@/composables/useRowActions.js';
import { useI18n } from 'vue-i18n';
import { textColumn, textWithAvatarColumn, tagsArrayColumn, statusTagColumn, resourceViewerColumn, dateColumn } from '@/components/datatable/columnFactories.js';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import RowActionsColumn from '@/components/datatable/RowActionsColumn.vue';

const { t: $t, locale } = useI18n();
const { can } = usePermissions();
const canViewProfileData = computed(() => can('view profile data'));
const canViewActionsColumn = computed(() => can(['edit users', 'delete users']));

const profileExpansion = computed(() => {
    if (!canViewProfileData.value) {
        return null;
    }

    return {
        fields: [
            { label: $t('Personal ID'), field: 'personal_id', cellClass: 'py-2 pr-4 font-medium' },
            { label: $t('Email'), field: 'email' },
            { label: $t('Phone'), field: 'phone' },
            { label: $t('Address'), field: 'address', cellClass: 'py-2' },
            { label: $t('Birth Date'), field: 'birth_date', cellClass: 'py-2', formatter: ({ data }) => {
                if (!data.birth_date) {
                    return '';
                }
                const [year, month, day] = data.birth_date.split('-').map(Number);
                
                if (!year || !month || !day) {
                    return data.birth_date;
                }

                const date = new Date(year, month - 1, day);

                return date.toLocaleDateString(locale.value);
            } },
        ],
        emptyValue: '—',
    };
});


const table = useSettingsTable({
    endpoint: '/settings/users',
    searchFields: ['first_name', 'last_name', 'email'],
    filterConfig: {
        full_name: {
            defaultValue: null,
            matchMode: 'contains',
        },
        birth_date: {
            defaultValue: null,
            matchMode: 'equals',
        },
    },
    mapResponse: (response) => ({
        data: response.data?.data?.users || [],
        total: response.data?.data?.total || 0,
    }),
});

const actions = useRowActions({
    editRouteName: 'settings.users.data.edit',
    deleteEndpoint: '/settings/users/profile/:id/destroy',
    onDeleteSuccess: () => {
        table.refresh();
    },
    messages: {
        successMessage: $t('User deleted successfully.'),
        errorMessage: $t('An error occurred while deleting the user.'),
        confirmMessage: $t('Are you sure you want to delete this user?'),
    }
});

const columns = computed(() => [
    { 
        key: 'expander',
        isExpander: true,
        style: 'width: 1%',
        visible: () => canViewProfileData.value,
    },
    textColumn({
        key: 'id',
        header: $t('ID'),
        style: 'width: 1%',
    }),
    textWithAvatarColumn({
        key: "full_name",
        header: $t('Name'),
        fieldName: 'full_name',
        placeholder: $t('Search by name'),
        style: 'min-width:15%',
    }),
    tagsArrayColumn({
        key: 'roles',
        header: $t('Roles'),
        itemsField: 'display_roles',
        style: 'min-width: 15%',
        emptyLabel: $t('None'),
    }),
    statusTagColumn({
        header: $t('Status'),
        style: 'min-width: 10%',
    }),
    resourceViewerColumn({
        header: $t('Payment'),
        style: 'min-width: 5%',
        viewLabel: $t('View'),
        emptyLabel: $t('None'),
        modalTitle: $t('Payment Receipt'),
        resourceField: 'payment_receipt',
    }),
    {
        key: 'actions',
        header: $t('Actions'),
        style: 'min-width: 15%',
        visible: () => canViewActionsColumn.value,
        bodyComponent: RowActionsColumn,
        bodyProps: {
            editPermission: 'edit users',
            deletePermission: 'delete users',
            editLabel: $t('Edit'),
            deleteLabel: $t('Delete'),
            onEdit: (row) => actions.handleEdit(row.id),
            onDelete: (row) => actions.handleDelete(row.id),
        },
    },
]);

</script>
