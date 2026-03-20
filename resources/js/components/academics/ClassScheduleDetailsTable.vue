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
                                />
                            </td>
                            <td class="py-2 pr-4">
                                <div class="flex space-x-2">
                                    <Button 
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
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const { t: $t } = useI18n();
const router = useRouter();

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
