<template>
    <Transition name="table-expand" appear>
        <div class="expand-panel bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
            <div class="overflow-hidden rounded-lg">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 dark:bg-gray-800 rounded-lg">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-600 dark:text-slate-100">
                            <th class="py-2 px-2 text-left">{{ $t('Date') }}</th>
                            <th class="py-2 px-2 text-left">{{ $t('Start') }}</th>
                            <th class="py-2 px-2 text-left">{{ $t('Rescheduled') }}</th>
                            <th class="py-2 px-2 text-left">{{ $t('Start') }}</th>
                            <th class="py-2 px-2 text-right">{{ $t('Count') }}</th>
                            <th class="py-2 px-2">{{ $t('Status') }}</th>
                            <th class="py-2 px-2">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="session in details"
                            :key="session.id"
                            class="border-t border-slate-200 dark:border-slate-700"
                        >
                            <td class="py-2 px-2 text-left">
                                {{ session.session_date }}
                            </td>
                            <td class="py-2 px-2 text-left">
                                {{ session.start_time }}
                            </td>
                            <td class="py-2 px-2 text-left">
                                {{ session.rescheduled_date }}
                            </td>
                            <td class="py-2 px-2 text-left">
                                {{ session.rescheduled_start_time }}
                            </td>
                            <td class="py-2 px-2 text-right">
                                {{ session.reschedule_count || null }}
                            </td>
                            <td class="py-2 px-2">
                               <Tag
                                    :value="session.display_status"
                                    :severity="statusSeverity(session.status)"
                                    class="cursor-pointer"
                                    @click="openHistory(session)"
                                />
                            </td>
                            <td class="py-2 pr-4">
                                <div class="flex space-x-2">
                                    <Button 
                                        v-if="canEditDetail"
                                        type="button"
                                        size="small"
                                        :label="$t('Edit')"
                                        icon="pi pi-pencil"
                                        @click="handleEdit(session)"
                                    />
                                    <Button 
                                        type="button"
                                        size="small"
                                        :label="$t('Record')"
                                        icon="pi pi-upload"
                                        :severity="recordSeverity(session)"
                                        @click="handleRecord(session)"
                                    />
                                    <Button
                                        v-if="canDeleteDetail"
                                        type="button"
                                        size="small"
                                        :label="$t('Delete')"
                                        icon="pi pi-trash"
                                        severity="danger"
                                        @click="handleDelete(session)"
                                    />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </Transition>

    <Dialog
        v-model:visible="historyDialogVisible"
        :header="$t('Status History')"
        :style="{ width: '28rem' }"
        modal
    >
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
                    <p class="mt-1 flex items-center gap-1 text-xs text-slate-700 dark:text-slate-200">
                        <Tag v-if="entry.old_status" :value="$t(entry.old_status)" :severity="statusSeverity(entry.old_status)" class="text-xs" />
                        <span v-if="entry.old_status" class="text-slate-400">→</span>
                        <Tag :value="$t(entry.new_status)" :severity="statusSeverity(entry.new_status)" class="text-xs" />
                    </p>
                </div>
            </li>
        </ol>
    </Dialog>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { usePermissions } from '@/composables/usePermissions.js';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import api from '@/axios';

const { t: $t } = useI18n();
const router = useRouter();
const toast = useToast();
const { can } = usePermissions();

const canEditDetail = computed(() => can(['edit class schedule details', 'reschedule class']));
const canDeleteDetail = computed(() => can('delete class schedule details'));

const historyDialogVisible = ref(false);
const historyLoading = ref(false);
const historyEntries = ref([]);

const props = defineProps({
    details: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['edit-detail', 'delete-detail']);

const statusSeverity = (status) => {
    switch (status) {
        case 'scheduled':
            return 'primary';
        case 'completed':
            return 'success';
        case 'pending':
            return 'warn';
        case 'ongoing':
            return 'secondary';
        case 'reprogramed':
            return 'info';
        case 'canceled':
            return 'danger';
        default:
            return 'primary';
    }
};

const handleEdit = (session) => {
    emit('edit-detail', session);
};

const handleDelete = (session) => {
    emit('delete-detail', session);
};

const isSessionCompleted = (session) => session?.status === 'completed';

const recordSeverity = (session) => (isSessionCompleted(session) ? 'success' : 'warn');

const handleRecord = (session) => {
    if (isSessionCompleted(session) && session?.class_record_id) {
        router.push({
            name: 'academics.classes.class-records.show',
            params: {
                id: session.class_record_id,
            },
        });
        return;
    }

    router.push({
        name: 'academics.classes.class-records.create',
        params: {
            classScheduleDetailId: session.id,
        },
    });
};

const avatarInitials = (name = '') => {
    const parts = name.trim().split(/\s+/);
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase();
    }

    return name.slice(0, 2).toUpperCase() || '?';
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

const openHistory = async (session) => {
    historyDialogVisible.value = true;
    historyEntries.value = [];
    historyLoading.value = true;
    try {
        const { data } = await api.post(`/academics/lessons/class-schedules/details/${session.id}/status-history`);
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
</script>

<style scoped>
.expand-panel {
    overflow: hidden;
}

.table-expand-enter-active,
.table-expand-leave-active {
    transition:
        max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1),
        opacity 0.3s ease;
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
