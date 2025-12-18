<template>
    <Transition name="table-expand" appear>
        <div class="expand-panel bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg">
            <div class="overflow-hidden rounded-lg">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 dark:bg-gray-800 rounded-lg">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-600 dark:text-slate-100">
                            <th class="pb-2 pr-4 text-right">#</th>
                            <th class="pb-2 pr-4 text-right">{{ $t('Date') }}</th>
                            <th class="pb-2 pr-4 text-right">{{ $t('Start') }}</th>
                            <th class="pb-2 pr-4 text-right">{{ $t('Rescheduled Date') }}</th>
                            <th class="pb-2 pr-4 text-right">{{ $t('Rescheduled Start') }}</th>
                            <th class="pb-2 pr-4 text-right">{{ $t('Reschedule Count') }}</th>
                            <th class="pb-2 pr-4">{{ $t('Topic') }}</th>
                            <th class="pb-2 pr-0">{{ $t('Activity') }}</th>
                            <th class="pb-2 pr-0">{{ $t('Status') }}</th>
                            <th class="pb-2 pr-0">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="session in details"
                            :key="session.id"
                            class="border-t border-slate-200 dark:border-slate-700"
                        >
                            <td class="py-2 pr-4 text-right font-medium text-slate-600 dark:text-slate-200">
                                {{ session.order }}
                            </td>
                            <td class="py-2 pr-4 text-right">
                                {{ session.session_date }}
                            </td>
                            <td class="py-2 pr-4 text-right">
                                {{ session.start_time }}
                            </td>
                            <td class="py-2 pr-4 text-right">
                                {{ session.rescheduled_date }}
                            </td>
                            <td class="py-2 pr-4 text-right">
                                {{ session.rescheduled_start_time }}
                            </td>
                            <td class="py-2 pr-4 text-right">
                                {{ session.reschedule_count || null }}
                            </td>
                            <td class="py-2 pr-4">
                                {{ session.topic }}
                            </td>
                            <td class="py-2 pr-4">
                                {{ session.activity }}
                            </td>
                            <td class="py-2 pr-4">
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
                                        @click="window.open(session.resource_url, '_blank')"
                                    />
                                    <Button 
                                        type="button"
                                        size="small"
                                        :label="$t('Record')"
                                        icon="pi pi-upload"
                                        severity="warn"
                                        @click="window.open(session.resource_url, '_blank')"
                                    />
                                    <Button
                                        type="button"
                                        size="small"
                                        :label="$t('Delete')"
                                        icon="pi pi-trash"
                                        severity="danger"
                                        @click="window.open(session.resource_url, '_blank')"
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
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const { t: $t } = useI18n();

const props = defineProps({
    details: {
        type: Array,
        default: () => [],
    },
});

/* const sortedDetails = computed(() => {
    return [...props.details].sort((a, b) => {
        const orderA = a?.order ?? 0;
        const orderB = b?.order ?? 0;
        return orderA - orderB;
    });
}); */

const statusSeverity = (status) => {
    switch (status) {
        case 'scheduled':
            return 'primary';
        case 'completed':
            return 'success';
        case 'canceled':
            return 'danger';
        case 'pending':
            return 'warn';
        case 'rescheduled':
            return 'info';
        default:
            return 'primary';
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
