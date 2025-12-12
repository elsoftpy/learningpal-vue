<template>
    <PageContainer>
        <template #body>
            <div class="flex flex-col w-full">
                <!-- Loading Skeleton -->
                 <SkeletonBuilder v-if="loading" :per-page="perPage" count="3" />
                 <!-- Language DataTable -->
                  <DataTable
                    v-else 
                    :value="languages"
                    :lazy="true"
                    paginator
                    :rows="perPage"
                    :total-records="totalRecords"
                    :first="(currentPage - 1) * perPage"
                    @page="onPageChange"
                    v-model:filters="filters"
                    filterDisplay="row"
                    datakey="id"
                    :globalFilterFields="['name']"
                    size="small"
                    table-style="min-width: 50rem"
                >
                    <!-- Table Header with Search -->
                    <template #header>
                        <DataTableToolbar
                            v-model:search-query="searchQuery"
                            :search-placeholder="$t('Search language')"
                            :clear-label="$t('Clear filters')"
                            :has-active-filters="hasActiveFilters"
                            @search-input="onSearchInput"
                            @clear-filters="clearFilters"
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
                    :loading="loading"
                />
            </div>
        </template>
    </PageContainer>    
</template>
<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { usePermissions } from '@/composables/usePermissions';
import { FilterMatchMode } from '@primevue/core/api';
import axios from 'axios';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import SkeletonBuilder from '@/components/common/SkeletonBuilder.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import DataTableToolbar from '@/components/datatable/DataTableToolbar.vue';
import RowActionButtons from '@/components/datatable/RowActionButtons.vue';
import DeleteDialog from '@/components/datatable/DeleteDialog.vue';

const { t : $t } = useI18n();
const { can } = usePermissions();
const languages = ref([]);
const totalRecords = ref(0);
const perPage = ref(5);
const currentPage = ref(1);
const loading = ref(true);
const searchQuery = ref('');
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const toast = useToast();

function onPageChange(event) {
    currentPage.value = event.page + 1;
    perPage.value = event.rows;
    fetchLanguages(currentPage.value, perPage.value);
}

/* Search debounce and handler */
let searchDebounceTimer = null;
let filterDebounceTimer = null;
let skipFilterWatcher = false;

const onSearchInput = () => {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        currentPage.value = 1;
        fetchLanguages(currentPage.value, perPage.value);
    }, 300);
};

const hasActiveFilters = computed(() => {
    if (searchQuery.value.trim().length > 0) {
        return true;
    }

    return Object.values(filters.value).some(filter => {
        if (!filter) {
            return false;
        }

        if (typeof filter.value === 'string') {
            return filter.value.trim().length > 0;
        }

        return filter.value !== null && filter.value !== undefined;
    });
});

// Watch for column filter changes
watch(filters, () => {
    if (skipFilterWatcher) {
        skipFilterWatcher = false;
        return;
    }
    clearTimeout(filterDebounceTimer);
    filterDebounceTimer = setTimeout(() => {
        currentPage.value = 1;
        fetchLanguages(currentPage.value, perPage.value);
    }, 300);
}, { deep: true });

function clearFilters() {
    searchQuery.value = '';
    skipFilterWatcher = true;
    Object.values(filters.value).forEach(filter => {
        if (filter) {
            filter.value = null;
        }
    });
    currentPage.value = 1;
    fetchLanguages(currentPage.value, perPage.value);
}

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

        fetchLanguages(currentPage.value, perPage.value);
        
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

/* Fetching */
async function fetchLanguages(page, perPage) {
    languages.value = [];
    loading.value = true;

    
    try {
        const searchValue = searchQuery.value?.trim(); 

        const params = {
            page: page,
            per_page: perPage,
        };

        if (searchValue) {
            params.search = searchValue;
        }

        await new Promise((resolve) => setTimeout(resolve, 200));

        console.log('params:', params);
        const response = await axios.get('/settings/languages', { params, });

        languages.value = response.data.data.languages;
        totalRecords.value = response.data.data.total;
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchLanguages(currentPage.value, perPage.value);
});
</script>