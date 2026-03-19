<template>
    <div class="expand-panel rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
        <div
            v-if="canEditWeek"
            class="mb-4 flex justify-end"
        >
            <Button
                type="button"
                size="small"
                :label="$t('Add Week')"
                icon="pi pi-plus"
                severity="info"
                @click="emit('add-week')"
            />
        </div>

        <div
            v-if="!normalizedWeeks.length"
            class="rounded-md border border-dashed border-slate-200 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400"
        >
            {{ $t('No weeks available for this study program.') }}
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="week in normalizedWeeks"
                :key="week.id || week.week_number"
                class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-950"
            >
                <div class="flex items-start justify-between gap-3 px-4 py-3">
                    <button
                        type="button"
                        class="flex min-w-0 flex-1 items-center justify-between gap-3 text-left"
                        @click="toggleWeek(week.key)"
                    >
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                {{ week.title || `${$t('Week')} ${week.week_number}` }}
                            </div>
                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ $t('Week') }} {{ week.week_number }} · {{ week.activities.length }} {{ week.activities.length === 1 ? $t('activity') : $t('activities') }}
                            </div>
                        </div>
                        <i
                            :class="[
                                'pi text-sm text-slate-500 dark:text-slate-400',
                                isWeekExpanded(week.key) ? 'pi-chevron-up' : 'pi-chevron-down',
                            ]"
                        />
                    </button>

                    <div
                        v-if="canEditWeek || canDeleteWeek || canEditWeekActivity"
                        class="flex flex-wrap justify-end gap-2"
                    >
                        <Button
                            v-if="canEditWeek"
                            type="button"
                            size="small"
                            :label="$t('Edit')"
                            icon="pi pi-pencil"
                            @click="emit('edit-week', week)"
                        />
                        <Button
                            v-if="canEditWeekActivity"
                            type="button"
                            size="small"
                            :label="$t('Add Activity')"
                            icon="pi pi-plus"
                            severity="secondary"
                            @click="emit('add-activity', week)"
                        />
                        <Button
                            v-if="canDeleteWeek"
                            type="button"
                            size="small"
                            :label="$t('Delete')"
                            icon="pi pi-trash"
                            severity="danger"
                            @click="emit('delete-week', week)"
                        />
                    </div>
                </div>

                <div
                    v-if="isWeekExpanded(week.key)"
                    class="border-t border-slate-200 px-4 py-3 dark:border-slate-700"
                >
                    <div
                        v-if="week.activities.length"
                        class="overflow-x-auto"
                    >
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    <th class="pb-2 pr-4">{{ $t('Order') }}</th>
                                    <th class="pb-2 pr-4">{{ $t('Activity') }}</th>
                                    <th class="pb-2 pr-4">{{ $t('Type') }}</th>
                                    <th class="pb-2 pr-4">{{ $t('Content Topic') }}</th>
                                    <th class="pb-2">{{ $t('Free Content') }}</th>
                                    <th
                                        v-if="canEditWeekActivity || canDeleteWeekActivity"
                                        class="pb-2 pl-4 text-right"
                                    >
                                        {{ $t('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="activity in week.activities"
                                    :key="activity.id || `${week.key}-${activity.sort_order}-${activity.activity_name}`"
                                    class="border-t border-slate-100 align-top dark:border-slate-800"
                                >
                                    <td class="py-3 pr-4 text-slate-700 dark:text-slate-200">
                                        {{ activity.sort_order }}
                                    </td>
                                    <td class="py-3 pr-4 font-medium text-slate-900 dark:text-slate-100">
                                        {{ activity.activity_name }}
                                    </td>
                                    <td class="py-3 pr-4 text-slate-700 dark:text-slate-200">
                                        {{ activity.display_type || activity.type || '—' }}
                                    </td>
                                    <td class="py-3 pr-4 text-slate-700 dark:text-slate-200">
                                        {{ activity.level_content || '—' }}
                                    </td>
                                    <td class="py-3 text-slate-700 dark:text-slate-200">
                                        {{ activity.free_content || '—' }}
                                    </td>
                                    <td
                                        v-if="canEditWeekActivity || canDeleteWeekActivity"
                                        class="py-3 pl-4"
                                    >
                                        <div class="flex justify-end gap-2">
                                            <Button
                                                v-if="canEditWeekActivity"
                                                type="button"
                                                size="small"
                                                :label="$t('Edit')"
                                                icon="pi pi-pencil"
                                                @click="emit('edit-activity', { week, activity })"
                                            />
                                            <Button
                                                v-if="canDeleteWeekActivity"
                                                type="button"
                                                size="small"
                                                :label="$t('Delete')"
                                                icon="pi pi-trash"
                                                severity="danger"
                                                @click="emit('delete-activity', { week, activity })"
                                            />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-else
                        class="rounded-md border border-dashed border-slate-200 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400"
                    >
                        {{ $t('No activities available for this week.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePermissions } from '@/composables/usePermissions';
import Button from 'primevue/button';

const props = defineProps({
    weeks: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits([
    'add-week',
    'edit-week',
    'delete-week',
    'add-activity',
    'edit-activity',
    'delete-activity',
]);

const { t: $t } = useI18n();
const { can } = usePermissions();
const expandedWeeks = ref({});

const canEditWeek = computed(() => can('edit study program week'));
const canDeleteWeek = computed(() => can('delete study program week'));
const canEditWeekActivity = computed(() => can('edit study program week activity'));
const canDeleteWeekActivity = computed(() => can('delete study program week activity'));

const normalizedWeeks = computed(() =>
    (props.weeks || [])
        .map((week, index) => ({
            ...week,
            key: week.id || `week-${week.week_number || index + 1}`,
            activities: Array.isArray(week.activities)
                ? [...week.activities].sort((left, right) => (left.sort_order || 0) - (right.sort_order || 0))
                : [],
        }))
        .sort((left, right) => (left.week_number || 0) - (right.week_number || 0))
);

const toggleWeek = (weekKey) => {
    expandedWeeks.value = {
        ...expandedWeeks.value,
        [weekKey]: !expandedWeeks.value[weekKey],
    };
};

const isWeekExpanded = (weekKey) => Boolean(expandedWeeks.value[weekKey]);
</script>
