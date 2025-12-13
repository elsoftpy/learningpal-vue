<template>
    <PageContainer>
        <template #body>
            <!-- Loading Skeleton -->
            <TableLoadingState 
                :is-loading="table.isLoading.value" 
                :rows="table.perPage.value"
                :skeleton-count="3"
            >
                <!-- Users DataTable -->
                <DataTable
                    :value="table.data.value"
                    :lazy="true"
                    paginator
                    :rows="table.perPage.value"
                    :totalRecords="table.totalRecords.value"
                    :first="table.first.value"
                    v-model:expandedRows="table.expandedRows.value"
                    expansionMode="single"
                    @page="table.onPageChange"
                    v-model:filters="table.filters.value"
                    filterDisplay="row"
                    dataKey="id"
                    :globalFilterFields="['first_name', 'last_name', 'email']"
                    size="small" 
                    tableStyle="min-width: 50rem"
                >
                    <!-- Table Header with Search -->
                    <template #header>
                        <DataTableToolbar
                            v-model:search-query="table.searchQuery.value"
                            :search-placeholder="$t('Search user')"
                            :clear-label="$t('Clear filters')"
                            :has-active-filters="table.hasActiveFilters.value"
                            @search-input="table.onSearchInput"
                            @clear-filters="table.clearFilters"
                        >
                            <template #actions>
                                <Button
                                    v-if="can('create users')"
                                    :label="$t('Add User')"
                                    icon="pi pi-plus"
                                    size="small"
                                    @click="router.push({ name: 'settings.users.create' })"
                                />
                            </template>
                        </DataTableToolbar>
                    </template>
                    <!-- Empty Message -->
                    <template #empty>{{$t('No records found.')}}</template>
                    <!-- Expander -->
                    <Column v-if="can('view profile data')" expander style="width: 1%" />
                    <!-- Avatar -->
                    <Column :header="$t('ID')" style="width: 1%">
                        <template #body="{ data }">     
                            {{ data.id }}
                        </template>
                    </Column>
                    <!-- Name -->
                    <Column 
                        field="full_name"
                        :header="$t('Name')" 
                        :showFilterMenu="false"
                        style="min-width:15%"
                    >
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText 
                                v-model="filterModel.value" 
                                type="text" 
                                @input="filterCallback()" 
                                :placeholder="$t('Search by name')" 
                                class="w-full"
                            />
                        </template>

                        <template #body="{ data }">
                            <div class="flex items-center space-x-2">
                                <img :src="data.avatar_url" class="w-10 h-10 rounded-full object-cover" />
                                <span>{{ data.full_name }}</span>
                            </div>
                        </template>

                    </Column>
                    <!-- Roles -->
                    <Column :header="$t('Roles')" style="min-width: 15%">
                        <template #body="{ data }">
                            <Tag
                                v-for="role in data.display_roles"
                                :key="role"
                                :value="role"
                                severity="info"
                                class="mr-2 mb-1"
                            />
                        </template>
                    </Column>
                    <!-- Status -->
                    <Column :header="$t('Status')" style="min-width: 10%">
                        <template #body="{ data }">
                            <Tag 
                                :value="data.display_status" 
                                :severity="getStatusSeverity(data.status)" 
                            />
                        </template>
                    </Column>
                    <!-- Payment -->
                    <Column :header="$t('Payment')" style="min-width: 5%">
                        <template #body="{ data }">
                            <Button
                                v-if="data.payment_receipt"
                                :label="$t('View')"
                                icon="pi pi-eye"
                                size="small"
                                @click="showReceipt(data.payment_receipt)"
                            />
                            <span v-else class="text-gray-400 text-sm">{{ $t('None') }}</span>
                        </template>
                    </Column>   
                    <!-- Actions Buttons-->
                    <Column v-if="can(['edit users', 'delete users'])" :header="$t('Actions')" style="min-width: 15%">
                        <template #body="{ data }">
                            <RowActionButtons
                                :can-edit="can('edit users')"
                                :can-delete="can('delete users')"
                                :edit-label="$t('Edit')"
                                :delete-label="$t('Delete')"
                                @edit="actions.handleEdit(data.id)"
                                @delete="actions.handleDelete(data.id)"
                            />
                        </template>
                    </Column>

                    <template v-if="can('view profile data')" #expansion="{ data }">
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
                </DataTable>
                <!-- Modal for Payment Receipt -->
                <Dialog 
                    v-model:visible="receiptModal" 
                    modal
                    :closable="false"
                >
                    <template #header >
                        <div class="flex w-full justify-between items-center rounded-lg h-16 p-4 text-white bg-blue-500">
                            <span class="text-xl font-semibold">{{ $t('Payment Receipt') }}</span>
                            <Button
                                icon="pi pi-times"
                                rounded
                                size="small"
                                severity="primary"
                                variant="outlined"
                                class="text-white! border-2! hover:text-gray-800!"
                                @click="receiptModal = false"
                            />
                        </div>
                    </template>
                    <a :href="receiptUrl" target="_blank" rel="noopener noreferrer">
                        <img v-if="isImageUrl(receiptUrl)" :src="receiptUrl" class="w-full rounded">
                        <IconWrapper
                            v-else
                            name="file-pdf"
                            class="text-red-600 text-center mx-auto my-8"
                            size="256"
                        />
                    </a>
                </Dialog>

                <!-- Delete Confirmation Dialog -->
                <DeleteDialog
                    v-model:visible="actions.deleteDialogVisible.value"
                    :message="$t('Are you sure you want to delete this user?')"
                    :onDelete="actions.confirmDelete"
                    :loading="actions.isDeleting.value"
                />
            </TableLoadingState>
        </template>
    </PageContainer>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { usePermissions } from '@/composables/usePermissions.js';
import { usePaginatedTable } from '@/composables/usePaginatedTable';
import { useRowActions } from '@/composables/useRowActions.js';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import TableLoadingState from '@/components/datatable/TableLoadingState.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import IconWrapper from '@/components/common/IconWrapper.vue';
import DataTableToolbar from '@/components/datatable/DataTableToolbar.vue';
import RowActionButtons from '@/components/datatable/RowActionButtons.vue';
import DeleteDialog from '@/components/datatable/DeleteDialog.vue';

const { t: $t } = useI18n();
const { can } = usePermissions();
const toast = useToast();
const router = useRouter();

const table = usePaginatedTable({
    endpoint: '/settings/users',
    initialPerPage: 5,
    searchFields: ['first_name', 'last_name', 'email'],
    filterConfig: {
        full_name: { 
            defaultValue: null, 
            matchMode: 'contains' 
        },
    },
    mapResponse: (response) => ({
        data: response.data.data.users,
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

onMounted(() => {
    table.fetchData();
});

/* Status colors */
const getStatusSeverity = (status) => {
    switch (status) {
        case 'active': return 'success';
        case 'disabled': return 'danger';
        case 'pending': return 'warn';
        default: return 'info';
    }
};

/* Payment receipt modal */
const receiptModal = ref(false);
const receiptUrl = ref(null);

function showReceipt(url) {
    receiptUrl.value = url;
    receiptModal.value = true;
}

function isImageUrl(url) {
    return(url.match(/\.(jpeg|jpg|gif|png|svg)$/) != null);
}
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
