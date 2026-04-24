<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-md bg-white p-4 shadow-md dark:bg-slate-900">
            <h2 class="mb-4 text-lg font-semibold text-slate-800 dark:text-slate-100">
                {{ $t('My Class Schedules') }}
            </h2>

            <DataTable
                v-model:filters="filters"
                :value="sessions"
                :loading="loading"
                :global-filter-fields="['display_course', 'display_status']"
                paginator
                :rows="10"
                :rows-per-page-options="[5, 10, 25]"
                sort-field="date"
                :sort-order="-1"
                filter-display="row"
                responsive-layout="scroll"
                class="p-datatable-sm"
            >
                <template #header>
                    <div class="flex justify-end">
                        <InputText
                            v-model="filters['global'].value"
                            :placeholder="$t('Search…')"
                            class="text-sm"
                        />
                    </div>
                </template>

                <Column field="date" :header="$t('Date')" sortable style="width: 9rem;">
                    <template #body="{ data: row }">
                        {{ formatDate(row.rescheduled_date ?? row.date) }}
                    </template>
                </Column>

                <Column field="display_course" :header="$t('Course')" sortable style="width: 12rem;" />

                <Column :header="$t('Time')" style="width: 10rem;">
                    <template #body="{ data: row }">
                        {{ formatTime(row.rescheduled_start_time ?? row.start_time) }}
                        <template v-if="row.rescheduled_start_time ?? row.end_time">
                            – {{ formatTime(row.rescheduled_end_time ?? row.end_time) }}
                        </template>
                    </template>
                </Column>

                <Column field="status" :header="$t('Status')" sortable style="width: 9rem;">
                    <template #body="{ data: row }">
                        <Tag
                            :value="row.display_status"
                            :severity="statusSeverity(row.status)"
                            class="cursor-pointer"
                            @click="openHistory(row)"
                        />
                    </template>
                </Column>

                <Column :header="$t('Actions')" style="width: 20rem;">
                    <template #body="{ data: row }">
                        <div v-if="isActionable(row)" class="flex gap-2">
                            <Button
                                type="button"
                                size="small"
                                :label="$t('Leave Pending')"
                                severity="warn"
                                :loading="actionLoading === `${row.id}:pending`"
                                :disabled="!!actionLoading"
                                @click="performAction(row, 'pending')"
                            />
                            <Button
                                type="button"
                                size="small"
                                :label="$t('Request Class Record Upload')"
                                severity="help"
                                :loading="actionLoading === `${row.id}:upload_task`"
                                :disabled="!!actionLoading"
                                @click="performAction(row, 'upload_task')"
                            />
                        </div>
                        <span v-else class="text-xs text-slate-400 dark:text-slate-500">—</span>
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog
            v-model:visible="historyDialogVisible"
            :style="{ width: '28rem' }"
            modal
            :closable="false"
        >
            <template #header>
                <div class="flex w-full justify-between items-center rounded-lg h-16 p-4 text-white bg-blue-500">
                    <span class="text-xl font-semibold">{{ $t('Status History') }}</span>
                    <Button
                        icon="pi pi-times"
                        rounded
                        size="small"
                        severity="info"
                        variant="outlined"
                        class="text-white! border-2! hover:text-gray-800!"
                        @click="historyDialogVisible = false"
                    />
                </div>
            </template>
            <div v-if="historyLoading" class="py-4 text-center text-sm text-slate-500">
                {{ $t('Loading…') }}
            </div>
            <div v-else-if="!historyEntries.length" class="py-4 text-center text-sm text-slate-500">
                {{ $t('No status history available.') }}
            </div>
            <ol v-else class="flex flex-col gap-3">
                <li
                    v-for="entry in historyEntries"
                    :key="entry.id"
                    class="flex items-start gap-3 rounded-lg border border-slate-100 p-3 dark:border-slate-700"
                >
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700 dark:bg-blue-900 dark:text-blue-200">
                        {{ avatarInitials(entry.changed_by_name) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                            {{ entry.changed_by_name }}
                            <span class="ml-1 text-xs font-normal text-slate-400">({{ $t(entry.changed_by_type) }})</span>
                        </p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            {{ formatTimestamp(entry.created_at) }}
                        </p>
                        <p class="mt-1 text-xs text-slate-700 dark:text-slate-200">
                            <Tag v-if="entry.old_status" :value="$t(entry.old_status)" :severity="statusSeverity(entry.old_status)" class="mr-1 text-xs" />
                            <span v-if="entry.old_status" class="mr-1 text-slate-400">→</span>
                            <Tag :value="$t(entry.new_status)" :severity="statusSeverity(entry.new_status)" class="text-xs" />
                        </p>
                    </div>
                </li>
            </ol>
        </Dialog>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { FilterMatchMode } from '@primevue/core/api';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import api from '@/axios';

defineOptions({ layout: AppLayout });

const { t: $t, locale } = useI18n();
const toast = useToast();

const sessions = ref([]);
const loading = ref(false);
const actionLoading = ref(null);
const historyDialogVisible = ref(false);
const historyLoading = ref(false);
const historyEntries = ref([]);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const actionableStatuses = ['scheduled', 'reprogramed'];

const isActionable = (row) => actionableStatuses.includes(row.status);

const statusSeverity = (status) => {
    switch (status) {
        case 'scheduled': return 'primary';
        case 'completed': return 'success';
        case 'pending': return 'warn';
        case 'ongoing': return 'secondary';
        case 'reprogramed': return 'info';
        case 'canceled': return 'danger';
        default: return 'primary';
    }
};

const formatTime = (timeStr) => {
    if (!timeStr) return '—';
    const parts = timeStr.split(':');
    return parts.length >= 2 ? `${parts[0]}:${parts[1]}` : timeStr;
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    try {
        // Parse ISO date string (YYYY-MM-DD) to dd/mm/yyyy
        const [year, month, day] = dateStr.split('-');
        return `${day}/${month}/${year}`;
    } catch {
        return dateStr;
    }
};

const formatTimestamp = (iso) => {
    if (!iso) return '';
    try {
        return new Intl.DateTimeFormat(undefined, {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit',
        }).format(new Date(iso));
    } catch {
        return iso;
    }
};

const avatarInitials = (name = '') => {
    const parts = name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase();
    }

    return name.slice(0, 2).toUpperCase() || '?';
};

const fetchSessions = async () => {
    loading.value = true;
    try {
        const { data } = await api.post('/academics/lessons/class-schedules/my-sessions/list');
        sessions.value = Array.isArray(data.sessions) ? data.sessions : [];
    } catch (error) {
        const status = error?.response?.status;
        if (status !== 401 && status !== 419) {
            toast.add({ severity: 'error', summary: $t('Error'), detail: $t('Unable to load sessions.'), life: 4000 });
        }
    } finally {
        loading.value = false;
    }
};

const performAction = async (row, actionType) => {
    actionLoading.value = `${row.id}:${actionType}`;
    try {
        await api.post(`/academics/lessons/class-schedules/details/${row.id}/student-action`, { action_type: actionType });
        toast.add({ severity: 'success', summary: $t('Success'), detail: $t('Action registered successfully.'), life: 3000 });
        await fetchSessions();
    } catch (error) {
        const message = error?.response?.data?.message ?? $t('Unable to perform action.');
        toast.add({ severity: 'error', summary: $t('Error'), detail: message, life: 4000 });
    } finally {
        actionLoading.value = null;
    }
};

const openHistory = async (row) => {
    historyDialogVisible.value = true;
    historyEntries.value = [];
    historyLoading.value = true;
    try {
        const { data } = await api.post(`/academics/lessons/class-schedules/details/${row.id}/status-history`);
        historyEntries.value = Array.isArray(data.history) ? data.history : [];
    } catch (error) {
        const status = error?.response?.status;
        if (status !== 401 && status !== 419) {
            toast.add({ severity: 'error', summary: $t('Error'), detail: $t('Unable to load history.'), life: 4000 });
        }
    } finally {
        historyLoading.value = false;
    }
};

onMounted(fetchSessions);
</script>
