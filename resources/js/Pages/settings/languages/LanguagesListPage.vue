<template>
    <PageContainer>
        <template #body>
            <div class="flex flex-col w-full">
                <!-- Loading Skeleton -->
                 <SkeletonBuilder v-if="table.isLoading.value" :per-page="table.perPage.value" count="3" />
                 <!-- Language DataTable -->
                  <DataTable
                    v-else 
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
                                @edit="navigateToEdit(data.id)"
                                @delete="showDeleteDialog(data.id)"
                            />
                        </template>
                    </Column>
                </DataTable>
                <!-- Delete Confirmation Dialog -->
                <DeleteDialog
                    v-model:visible="deleteDialogVisible"
                    :message="$t('Are you sure you want to delete this language?')"
                    :onDelete="deleteLanguage"
                    :loading="table.isLoading.value"
                />
            </div>
        </template>
    </PageContainer>    
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { usePermissions } from '@/composables/usePermissions';
import { usePaginatedTable } from '@/composables/usePaginatedTable';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import SkeletonBuilder from '@/components/common/SkeletonBuilder.vue';
import DataTableToolbar from '@/components/datatable/DataTableToolbar.vue';
import RowActionButtons from '@/components/datatable/RowActionButtons.vue';
import DeleteDialog from '@/components/datatable/DeleteDialog.vue';

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

onMounted(() => {
    table.fetchData();
});

/* Delete Language */
const deleteDialogVisible = ref(false);
const languageIdToDelete = ref(null);

function showDeleteDialog(languageId) {
    languageIdToDelete.value = languageId;
    deleteDialogVisible.value = true;
}

async function deleteLanguage() {
    try {
        await axios.post(`/settings/languages/${languageIdToDelete.value}/destroy`);
        deleteDialogVisible.value = false;
        languageIdToDelete.value = null;

        table.fetchData();
        
        toast.add({ 
            severity: 'success', 
            summary: $t('Success'), 
            detail: $t('Language deleted successfully.'),
            life: 3000 
        });
    } catch (error) {
        toast.add({ 
            severity: 'error', 
            summary: $t('Error'), 
            detail: $t('An error occurred while deleting the user.'),
            life: 3000 
        });

        deleteDialogVisible.value = false;
        languageIdToDelete.value = null;
    }
}
</script>