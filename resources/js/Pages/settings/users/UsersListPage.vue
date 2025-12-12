<template>
    <PageContainer>
        <template #body>
            <div class="flex flex-col w-full">
                <!-- Loading Skeleton -->
                <SkeletonBuilder v-if="loading" :perPage="perPage" count="5" />
                <!-- Users DataTable -->
                <DataTable
                    v-else
                    :value="users"
                    :lazy="true"
                    paginator
                    :rows="perPage"
                    :totalRecords="totalRecords"
                    :first="(currentPage - 1) * perPage"
                    v-model:expandedRows="expandedRows"
                    expansionMode="single"
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
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <div class="flex flex-1">
                                <Button
                                    v-if="can('create users')"
                                    :label="$t('Add User')"
                                    icon="pi pi-plus"
                                    size="small"
                                    @click="router.push({ name: 'settings.users.create' })"
                                />
                            </div>
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
                                        :placeholder="$t('Search user')"
                                        @input="onSearchInput"
                                    />
                                </IconField>
                            </div>
                        </div>
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
                            <div class="space-y-1">
                                <Button
                                    v-if="can('edit users')"
                                    @click="navigateToEdit(data.id)"
                                    :label="$t('Edit')"
                                    icon="pi pi-pencil"
                                    size="small"
                                    class="mr-2"
                                />
                                <Button
                                    v-if="can('delete users')"
                                    @click="showDeleteDialog(data.id)"
                                    :label="$t('Delete')"
                                    icon="pi pi-trash"
                                    size="small"
                                    class="p-button-danger"
                                />
                            </div>
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
                <Dialog
                    v-model:visible="deleteDialog"
                    modal
                    :closable="false"
                >
                    <template #header >
                        <div class="flex w-full justify-between items-center rounded-lg h-16 p-4 text-white bg-red-500">
                            <span class="text-xl font-semibold">{{ $t('Delete Confirmation') }}</span>
                            <Button
                                icon="pi pi-times"
                                rounded
                                size="small"
                                severity="danger"
                                variant="outlined"
                                class="text-white! border-2! hover:text-gray-800!"
                                @click="deleteDialog = false"
                            />
                        </div>
                    </template>
                    <span class="flex p-4 items-center font-semibold mb-4 text-center">
                        {{ $t('Are you sure you want to delete this user?') }}
                    </span>
                    <div class="flex justify-end gap-2">
                        <Button 
                            type="button" 
                            :label="$t('Cancel')" 
                            severity="secondary" 
                            @click="deleteDialog = false">
                        </Button>
                        <Button 
                            type="button" 
                            :label="$t('Delete')"
                            severity="danger" 
                            @click="deleteUser">
                        </Button>
                    </div>
                </Dialog>
            </div>

        </template>
    </PageContainer>
</template>
<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { usePermissions } from '@/composables/usePermissions.js';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import IconWrapper from '@/components/common/IconWrapper.vue';
import InputIcon from 'primevue/inputicon';
import SkeletonBuilder from '@/components/common/SkeletonBuilder.vue';


const { t: $t } = useI18n();
const { can } = usePermissions();
const users = ref([]);
const totalRecords = ref(0);
const perPage = ref(5);
const currentPage = ref(1);
const loading = ref(true);
const expandedRows = ref([]);
const searchQuery = ref('');
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    full_name: { value: null, matchMode: FilterMatchMode.CONTAINS },
});
const toast = useToast();
const router = useRouter();

/* Filter and Search Debounce */
let searchDebounceTimer = null;
let filterDebounceTimer = null;
let skipFilterWatcher = false;

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


const onSearchInput = () => {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        currentPage.value = 1;
        fetchUsers(currentPage.value, perPage.value);
    }, 300);
};

function clearFilters() {
    searchQuery.value = '';
    skipFilterWatcher = true;
    Object.values(filters.value).forEach(filter => {
        if (filter) {
            filter.value = null;
        }
    });
    currentPage.value = 1;
    fetchUsers(currentPage.value, perPage.value);
}

/* Pagination change */
function onPageChange(event) {
    currentPage.value = event.page + 1;
    perPage.value = event.rows;
    fetchUsers(currentPage.value, perPage.value);
}

/* Fetch users */
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
        expandedRows.value = [];
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchUsers(currentPage.value, perPage.value);
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

/* Delete user functionality */
const deleteDialog = ref(false);
const userIdToDelete = ref(null);


function showDeleteDialog(userId) {
    userIdToDelete.value = userId;
    deleteDialog.value = true;
}

/* Actions */
function navigateToEdit(userId) {
   router.push({name: 'settings.users.data.edit', params: { userId: userId }});
}

async function deleteUser() {
    try {
        await axios.post(`/settings/users/profile/${userIdToDelete.value}/destroy`);
        deleteDialog.value = false;
        userIdToDelete.value = null;
        fetchUsers(currentPage.value, perPage.value);
        toast.add({ 
            severity: 'success', 
            summary: $t('Success'), 
            detail: $t('User deleted successfully.'),
            life: 3000 
        });
    } catch (error) {
        toast.add({ 
            severity: 'error', 
            summary: $t('Error'), 
            detail: $t('An error occurred while deleting the user.'),
            life: 3000 
        });

        deleteDialog.value = false;
        userIdToDelete.value = null;
    }
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
