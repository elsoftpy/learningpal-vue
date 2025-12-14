<template>
    <Transition name="table-expand" appear>
        <div :class="panelClass">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr :class="headerRowClass">
                            <th
                                v-for="field in resolvedFields"
                                :key="field.label || field.field"
                                :class="field.headerClass || 'pb-2 pr-4 last:pr-0'"
                            >
                                {{ field.label }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr :class="rowClass">
                            <td
                                v-for="field in resolvedFields"
                                :key="field.field"
                                :class="field.cellClass || 'py-2 pr-4 last:pr-0'"
                            >
                                {{ resolveValue(field) }}
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

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    config: {
        type: Object,
        required: true,
    },
});

const resolvedFields = computed(() => props.config.fields ?? []);
const fallbackValue = computed(() => props.config.emptyValue ?? '—');
const panelClass = computed(
    () =>
        props.config.panelClass ||
        'expand-panel bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-5'
);
const headerRowClass = computed(
    () => props.config.headerRowClass || 'text-left text-xs uppercase tracking-wide text-slate-500'
);
const rowClass = computed(() => props.config.rowClass || 'border-t border-slate-200 dark:border-slate-700');

function resolveValue(field) {
    if (typeof field.formatter === 'function') {
        const formatted = field.formatter({ data: props.data, field });
        if (formatted !== undefined && formatted !== null && formatted !== '') {
            return formatted;
        }
    }

    const raw = props.data?.[field.field];
    if (raw === undefined || raw === null || raw === '') {
        return field.emptyValue ?? fallbackValue.value;
    }

    return raw;
}
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
