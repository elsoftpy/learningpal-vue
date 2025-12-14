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
        :delete-dialog="deleteDialogConfig"
    >
        <template v-if="canViewProfileData" #expansion="{ data }">
            <Transition name="table-expand" appear>
                <div class="expand-panel bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                                <th class="pb-2 pr-4">{{ $t('Personal ID') }}</th>
                                <th class="pb-2 pr-4">{{ $t('Email') }}</th>
                                <th class="pb-2 pr-4">{{ $t('Phone') }}</th>
                                <th class="pb-2">{{ $t('Address') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-t border-slate-200 dark:border-slate-700">
                                <td class="py-2 pr-4 font-medium">{{ data.personal_id || '—' }}</td>
                                <td class="py-2 pr-4">{{ data.email || '—' }}</td>
                                <td class="py-2 pr-4">{{ data.phone || '—' }}</td>
                                <td class="py-2">{{ data.address || '—' }}</td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                </div>
            </Transition>
        </template>
    </ResourceTableLayout>
</template>
<script setup>
import { computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions.js';
import { useSettingsTable } from '@/composables/useSettingsTable.js';
import { useRowActions } from '@/composables/useRowActions.js';
import { useI18n } from 'vue-i18n';
import { textColumn, textWithAvatarColumn, tagsArrayColumn, statusTagColumn, resourceViewerColumn } from '@/components/datatable/columnFactories.js';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import RowActionsColumn from '@/components/datatable/RowActionsColumn.vue';

const { t: $t } = useI18n();
const { can } = usePermissions();
const canViewProfileData = computed(() => can('view profile data'));
const canViewActionsColumn = computed(() => can(['edit users', 'delete users']));


const table = useSettingsTable({
    endpoint: '/settings/users',
    searchFields: ['first_name', 'last_name', 'email'],
    filterConfig: {
        full_name: {
            defaultValue: null,
            matchMode: 'contains',
        },
    },
    mapResponse: (response) => ({
        data: response.data.data.users,
        total: response.data.data.total,
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
    }
});

const deleteDialogConfig = computed(() => ({
    visible: actions.deleteDialogVisible,
    message: $t('Are you sure you want to delete this user?'),
    onDelete: actions.confirmDelete,
    loading: actions.isDeleting,
}));

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

<style scoped>
.expand-panel {
    overflow: hidden;
}

.table-expand-enter-active,
.table-expand-leave-active {
    transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
}

.table-expand-enter-from,
.table-expand-leave-to {
    max-height: 0;
    opacity: 0;
}

.table-expand-enter-to,
.table-expand-leave-from {
    max-height: 480px;
    opacity: 1;
}
</style>
