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
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex space-x-2">
                                <Button
                                    v-if="hasActiveFilters"
                                    size="small"
                                    severity="secondary"
                                    icon="pi pi-filter-slash"
                                    :label="$t('Clear filters')"
                                    @click="clearFilters"
                                />
                                <IconField>
                                    <InputIcon>
                                        <i class="pi pi-search"></i>
                                    </InputIcon>
                                    <InputText
                                        v-model="searchQuery"
                                        :placeholder="$t('Search language')"
                                        @input="onSearchInput"
                                    />
                                </IconField>
                            </div>
                        </div>
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
                            <div class="space-y-1">
                                <Button
                                    v-if="can('edit languages')"
                                    @click="navigateToEdit(data.id)"
                                    :label="$t('Edit')"
                                    icon="pi pi-pencil"
                                    size="small"
                                    class="mr-2"
                                />
                                <Button
                                    v-if="can('delete languages')"
                                    @click="showDeleteDialog(data.id)"
                                    :label="$t('Delete')"
                                    icon="pi pi-trash"
                                    size="small"
                                    class="p-button-danger"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </template>
    </PageContainer>    
</template>
<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import { FilterMatchMode } from '@primevue/core/api';
import axios from 'axios';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import SkeletonBuilder from '@/components/common/SkeletonBuilder.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';

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

function onPageChange(event) {
    currentPage.value = event.page + 1;
    perPage.value = event.rows;
    fetchLanguages(currentPage.value, perPage.value);
}

/* Search debounce and handler */
let searchDebounceTimer;
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
        fetchUsers(currentPage.value, perPage.value);
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