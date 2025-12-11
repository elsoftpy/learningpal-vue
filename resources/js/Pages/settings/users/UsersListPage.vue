<template>
    <PageContainer>
        <template #body>
            <div class="flex flex-col w-full">
                <!-- Loading Skeleton -->
                <div v-if="loading" class="w-full">
                    <Skeleton width="100%" height="4rem" class="mb-6" />
                    <Skeleton width="100%" height="2rem" class="mb-4" />
                    <div v-for="n in 5" :key="n" class="flex items-center mb-3">
                        <Skeleton width="40px" height="40px" shape="circle" class="mr-4" />
                        <div class="flex space-x-2 w-full">
                            <Skeleton class="w-1/6" />
                            <Skeleton class="w-1/6" />
                            <Skeleton class="w-1/6" />
                            <Skeleton class="w-1/6" />
                            <Skeleton class="w-1/6" />
                            <Skeleton class="w-1/6" />
                        </div>
                    </div>
                </div>
                <!-- Users DataTable -->
                <DataTable
                    v-else
                    :value="users"
                    :lazy="true"
                    paginator
                    :rows="5"
                    :totalRecords="totalRecords"
                    @page="onPageChange"
                    v-model:filters="filters"
                    filterDisplay="row"
                    dataKey="id"
                    :globalFilterFields="['first_name', 'last_name', 'email']"
                    size="small" 
                    tableStyle="min-width: 50rem"
                >
                    <!-- Table Header with Search -->
                    <template #header>
                        <div class="flex justify-end">
                            <IconField>
                                <InputIcon>
                                    <i class="pi pi-search"></i>
                                </InputIcon>
                                <InputText
                                    v-model="searchQuery"
                                    :placeholder="$t('Search user')"
                                    @input="onSearchInput"
                                />
                            </IconField>
                        </div>
                    </template>
                    <!-- Empty Message -->
                    <template #empty>{{$t('No users found.')}}</template>
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
                    <!-- Email -->
                    <Column :header="$t('Email')" style="min-width: 15%">
                        <template #body="{ data }">
                            {{ data.email }}
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
                    <Column :header="$t('Actions')" style="min-width: 15%">
                        <template #body="{ data }">
                            <div class="space-y-1">
                                <Button
                                    :label="$t('Edit')"
                                    icon="pi pi-pencil"
                                    size="small"
                                    class="mr-2"
                                    :to="`/settings/users/${data.id}/edit`"
                                />
                                <Button
                                    :label="$t('Delete')"
                                    icon="pi pi-trash"
                                    size="small"
                                    class="p-button-danger"
                                    :to="`/settings/users/${data.id}/delete`"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <!-- Modal for Payment Receipt -->
                <Dialog v-model:visible="receiptModal" header="Payment Receipt" modal>
                    <img :src="receiptUrl" class="w-full rounded">
                </Dialog>
            </div>

        </template>
    </PageContainer>
</template>
<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import PageContainer from '@/components/layout/pages/PageContainer.vue';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Skeleton from 'primevue/skeleton';

import { FilterMatchMode } from '@primevue/core/api';

const users = ref([]);
const totalRecords = ref(0);
const loading = ref(true);
const searchQuery = ref('');

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    full_name: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

let searchDebounceTimer = null;
let filterDebounceTimer = null;

const buildActiveFilters = () => {
    return Object.entries(filters.value).reduce((acc, [key, filter]) => {
        if (!filter || filter.value === null || filter.value === undefined) {
            return acc;
        }

        if (typeof filter.value === 'string') {
            const trimmed = filter.value.trim();
            if (trimmed.length === 0) {
                return acc;
            }
            acc[key] = trimmed;
            return acc;
        }

        acc[key] = filter.value;
        return acc;
    }, {});
};

// Watch for column filter changes
watch(filters, () => {
    clearTimeout(filterDebounceTimer);
    filterDebounceTimer = setTimeout(() => {
        fetchUsers(1, 5);
    }, 300);
}, { deep: true });


const onSearchInput = () => {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        fetchUsers(1, 5);
    }, 300);
};

function onPageChange(event) {
    fetchUsers(event.page + 1, event.rows);
}

async function fetchUsers(page, perPage) {
    users.value = [];
    loading.value = true;
    const searchValue = searchQuery.value?.trim();
    const activeFilters = buildActiveFilters();
    
    try {
        await new Promise(resolve => setTimeout(resolve, 200)); // to ease loading transition
        const params = {
            page: page,
            per_page: perPage,
        };

        if (searchValue) {
            params.search = searchValue;
        }

        if (Object.keys(activeFilters).length > 0) {
            params.filters = JSON.stringify(activeFilters);
        }

        const res = await axios.get('/settings/users', {
            params,
        });
        users.value = res.data.data.users;
        totalRecords.value = res.data.data.total;
    } finally {
        loading.value = false;
    }
}

/* Fetch users */
onMounted(async () => {
    fetchUsers(1, 5);
});

/* Status colors */
const getStatusSeverity = (status) => {
    switch (status) {
        case 'active': return 'success';
        case 'disabled': return 'danger';
        case 'pending': return 'warning';
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
</script>
