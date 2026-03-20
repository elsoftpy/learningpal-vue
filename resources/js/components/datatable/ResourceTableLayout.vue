<template>
    <PageContainer>
        <template #body>
            <!-- Loading Skeleton -->
            <TableLoadingState 
                :is-loading="table.isLoading.value" 
                :rows="table.perPage.value"
                :skeleton-count="3"
            >
                <!-- DataTable -->
                <BasicDataTable
                    :value="table.data.value"
                    :columns="columns"
                    :lazy="lazy"
                    :paginator="paginator"
                    :rows="table.perPage.value"
                    :totalRecords="table.totalRecords.value"
                    :first="table.first.value"
                    :sortField="table.sortField?.value"
                    :sortOrder="table.sortOrder?.value"
                    v-model:expandedRows="table.expandedRows.value"
                    expansionMode="single"
                    :dataKey="dataKey"
                    v-model:filters="table.filters.value"
                    :filterDisplay="filterDisplay"
                    :globalFilterFields="globalFilterFields"
                    :size="size" 
                    :tableStyle="tableStyle"
                    @page="table.onPageChange"
                    @sort="table.onSort"
                >
                    <!-- Header slot -->
                    <template #header>
                        <slot name="header">
                            <DataTableToolbar
                                v-model:search-query="table.searchQuery.value"
                                :search-placeholder="searchPlaceholder"
                                :clear-filter-label="resolvedClearFilterLabel"
                                :has-active-filters="table.hasActiveFilters.value"
                                @search-input="table.onSearchInput"
                                @clear-filters="table.clearFilters"
                            >
                                <template #actions>
                                    <slot name="actions">
                                        <CreateButton
                                            v-if="createRouteName"
                                            :permission="createPermission"
                                            :route-name="createRouteName"
                                            :label="createLabel"
                                        />
                                    </slot>
                                </template>
                            </DataTableToolbar>
                        </slot>
                    </template>
                    <!-- Expansion slot -->
                    <template v-if="$slots.expansion" #expansion="slotProps">
                        <slot name="expansion" v-bind="slotProps" />
                    </template>
                    <template v-else-if="resolvedRowExpansion" #expansion="slotProps">
                        <RowExpansionPanel :data="slotProps.data" :config="resolvedRowExpansion" />
                    </template>
                </BasicDataTable>
                <!-- After table slot-->
                <slot />

                <!-- Shared delete dialog -->
                <DeleteDialog
                    v-if="hasDeleteDialog"
                    v-model:visible="deleteDialogVisible"
                    :message="resolvedDeleteDialog.message"
                    :onDelete="resolvedDeleteDialog.onDelete"
                    :loading="deleteDialogLoading"
                />
            </TableLoadingState>
        </template>
    </PageContainer>
</template>
<script setup>
import { computed, unref, isRef } from 'vue';
import { useI18n } from 'vue-i18n';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import TableLoadingState from '@/components/datatable/TableLoadingState.vue';
import BasicDataTable from '@/components/datatable/BasicDataTable.vue';
import DataTableToolbar from '@/components/datatable/DataTableToolbar.vue';
import CreateButton from '@/components/datatable/CreateButton.vue';
import DeleteDialog from '@/components/datatable/DeleteDialog.vue';
import RowExpansionPanel from '@/components/datatable/RowExpansionPanel.vue';

const { t: $t } = useI18n();

const resolvedClearFilterLabel = computed(() => {
    return props.clearFilterLabel || $t('Clear filters');
});

const props = defineProps({
    table: {
        type: Object,
        required: true,
    },
    columns: {
        type: Array,
        required: true,
    },
    lazy: {
        type: Boolean,
        default: true,
    },
    paginator: {
        type: Boolean,
        default: true,
    },
    dataKey: {
        type: String,
        default: 'id',
    },
    globalFilterFields: {
        type: Array,
        default: () => [],
    },
    size: {
        type: String,
        default: 'normal', // 'small', 'normal', 'large'
    },
    tableStyle: {
        type: String,
        default: 'min-width: 50rem',
    },
    searchPlaceholder: {
        type: String,
        default: '',
    },
    clearFilterLabel: {
        type: String,
        default: null,
    },
    createRouteName: {
        type: String,
        default: null,
    },
    createPermission: {
        type: String,
        default: null,
    },
    createLabel: {
        type: String,
        default: '',
    },
    filterDisplay: {
        type: String,
        default: null, // 'row', 'menu', false
    },
    deleteDialog: {
        type: Object,
        default: null,
    },
    rowExpansion: {
        type: Object,
        default: null,
    },
});

const resolvedDeleteDialog = computed(() => unref(props.deleteDialog));

const hasDeleteDialog = computed(() => Boolean(resolvedDeleteDialog.value));

const deleteDialogVisible = computed({
    get() {
        const dialog = resolvedDeleteDialog.value;
        if (!dialog) return false;
        return unref(dialog.visible) ?? false;
    },
    set(value) {
        const dialog = resolvedDeleteDialog.value;
        if (!dialog) return;
        const target = dialog.visible;
        if (isRef(target)) {
            target.value = value;
        }
    },
});

const deleteDialogLoading = computed(() => {
    const dialog = resolvedDeleteDialog.value;
    if (!dialog) return false;
    return unref(dialog.loading) ?? false;
});

const resolvedRowExpansion = computed(() => {
    if (!props.rowExpansion) {
        return null;
    }

    const visible = props.rowExpansion.visible;
    const isVisible = typeof visible === 'function' ? visible() : visible !== false;
    if (!isVisible) {
        return null;
    }

    const fields = typeof props.rowExpansion.fields === 'function'
        ? props.rowExpansion.fields()
        : props.rowExpansion.fields;

    if (!Array.isArray(fields) || !fields.length) {
        return null;
    }

    return {
        ...props.rowExpansion,
        fields,
    };
});
</script>