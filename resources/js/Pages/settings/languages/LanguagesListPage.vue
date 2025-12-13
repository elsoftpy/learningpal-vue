<template>
    <PageContainer>
        <template #body>
            <TableLoadingState 
                :is-loading="table.isLoading.value" 
                :rows="table.perPage.value"
                :skeleton-count="3"
            >
                <!-- Language DataTable -->
                <DataTable
                    :value="table.data.value"
                    :lazy="true"
                    paginator
                    :rows="table.perPage.value"
                    :total-records="table.totalRecords.value"
                    :first="table.first.value"
                    @page="table.onPageChange"
                    v-model:filters="table.filters.value"
                    filterDisplay="row"
                    datakey="id"
                    :globalFilterFields="['name']"
                    size="small"
                    table-style="min-width: 50rem"
                >
                    <!-- Table Header with Search -->
                    <template #header>
                        <DataTableToolbar
                            v-model:search-query="table.searchQuery.value"
                            :search-placeholder="$t('Search language')"
                            :clear-label="$t('Clear filters')"
                            :has-active-filters="table.hasActiveFilters.value"
                            @search-input="table.onSearchInput"
                            @clear-filters="table.clearFilters"
                        />
                    </template>
                    <!-- Empty Message -->
                    <template #empty>{{$t('No records found.')}}</template>
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
                        <template #body="{ data }">
                            <div class="flex items-center space-x-2">
                                <span>{{ data.name }}</span>
                            </div>
                        </template>
                    </Column>
                    <!-- Actions Buttons -->
                     <Column v-if="can(['edit languages', 'delete languages'])" :header="$t('Actions')" style="min-width: 15%">
                        <template #body="{ data }">
                            <RowActionButtons
                                :can-edit="can('edit languages')"
                                :can-delete="can('delete languages')"
                                :edit-label="$t('Edit')"
                                :delete-label="$t('Delete')"
                                @edit="actions.handleEdit(data.id)"
                                @delete="actions.handleDelete(data.id)"
                            />
                        </template>
                    </Column>
                </DataTable>
                <!-- Delete Confirmation Dialog -->
                <DeleteDialog
                    v-model:visible="actions.deleteDialogVisible.value"
                    :message="$t('Are you sure you want to delete this language?')"
                    :onDelete="actions.confirmDelete"
                    :loading="actions.isDeleting.value"
                />
            </TableLoadingState>
        </template>
    </PageContainer>    
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import { usePaginatedTable } from '@/composables/usePaginatedTable';
import { useRowActions} from '@/composables/useRowActions.js';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import TableLoadingState from '@/components/datatable/TableLoadingState.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import DataTableToolbar from '@/components/datatable/DataTableToolbar.vue';
import RowActionButtons from '@/components/datatable/RowActionButtons.vue';
import DeleteDialog from '@/components/datatable/DeleteDialog.vue';
import { action } from '@primeuix/themes/aura/image';

const { t : $t } = useI18n();
const { can } = usePermissions();
const toast = useToast();

const table = usePaginatedTable({
    endpoint: '/settings/languages',
    initialPerPage: 5,
    filterConfig: {
        name: { 
            defaultValue: null, 
            matchMode: 'contains' 
        },
    },
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