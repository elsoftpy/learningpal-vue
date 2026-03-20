<template>
    <DataTable v-bind="$attrs">
        <!-- Header-->
        <template v-if="$slots.header" #header>
            <slot name="header" />
        </template>
        <!-- Empty Message -->
        <template v-if="$slots.empty" #empty>
            <slot name="empty" />
        </template>
        <template v-else #empty>{{$t('No records found.')}}</template>
        <!-- Columns-->
        <Column 
            v-for="col in normalizedColumns"
            :key="col.key || col.field || col.header"
            v-bind="columnProps(col)"
        >
            <!-- Body Slot -->
            <template v-if="col.bodyComponent" #body="slotProps">
                <component :is="col.bodyComponent" v-bind="resolveScopedProps(slotProps, col.bodyProps)" />
            </template>
            <!-- Filter -->
            <template v-if="col.filterComponent" #filter="slotProps">
                <component :is="col.filterComponent" v-bind="resolveScopedProps(slotProps, col.filterProps)" />
            </template>
        </Column>
        <!-- Expansion -->
        <template v-if="$slots.expansion" #expansion="slotProps">
            <slot name="expansion" v-bind="slotProps" />
        </template>
    </DataTable>
</template>
<script setup>
import { computed, defineComponent, h } from 'vue';
import { useI18n } from 'vue-i18n';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const { t: $t } = useI18n();
const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
});

const wrapSlotRenderer = (renderer) => {
    if (!renderer) {
        return null;
    }

    if (typeof renderer === 'function' && !renderer.__wrappedSlot) {
        const Wrapped = defineComponent({
            name: 'DataTableSlotRenderer',
            props: {
                data: { type: Object, default: null },
                column: { type: Object, default: null },
                field: { type: [String, Number], default: null },
                index: { type: Number, default: null },
                filterModel: { type: Object, default: null },
                filterCallback: { type: Function, default: null },
                frozenRow: { type: Boolean, default: false },
                rowTogglerCallback: { type: Function, default: null },
                editorInitCallback: { type: Function, default: null },
                editorSaveCallback: { type: Function, default: null },
                editorCancelCallback: { type: Function, default: null },
                options: { type: Object, default: null },
                rowIndex: { type: Number, default: null },
                columnIndex: { type: Number, default: null },
            },
            setup(slotProps) {
                return () => renderer(slotProps, h);
            },
        });

        Wrapped.__wrappedSlot = true;
        Wrapped.__originalRenderer = renderer;
        return Wrapped;
    }

    return renderer;
};

const normalizedColumns = computed(() =>
    props.columns
        .filter((col) => {
            if (typeof col.visible === 'function') return col.visible();
            if (typeof col.visible === 'boolean') return col.visible;
            return true;
        })
        .map((col) => ({
            ...col,
            bodyComponent: wrapSlotRenderer(col.bodyComponent || col.body),
            filterComponent: wrapSlotRenderer(col.filterComponent || col.filter),
            bodyProps: col.bodyProps,
            filterProps: col.filterProps,
        }))
);

function columnProps(col) {
    return {
        key: col.key,
        header: col.header,
        field: col.field ?? col.key,
        filterField: col.filterField ?? col.field ?? col.key,
        sortField: col.sortField,
        style: col.style,
        expander: col.expander ?? col.isExpander,
        showFilterMenu: col.showFilterMenu,
        sortable: col.sortable,
    };
}

function resolveScopedProps(slotProps, scopedProps) {
    if (!scopedProps) {
        return slotProps;
    }

    const resolved = typeof scopedProps === 'function'
        ? scopedProps(slotProps)
        : scopedProps;

    if (!resolved) {
        return slotProps;
    }

    return {
        ...slotProps,
        ...resolved,
    };
}
</script>