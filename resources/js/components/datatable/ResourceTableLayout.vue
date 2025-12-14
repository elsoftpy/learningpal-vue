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
                    v-model:expandedRows="table.expandedRows.value"
                    expansionMode="single"
                    :dataKey="dataKey"
                    v-model:filters="table.filters.value"
                    filterDisplay="row"
                    :globalFilterFields="globalFilterFields"
                    :size="size" 
                    :tableStyle="tableStyle"
                    @page="table.onPageChange"
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
                </BasicDataTable>
                <!-- After table slot-->
                <slot />
            </TableLoadingState>
        </template>
    </PageContainer>
</template>
<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import TableLoadingState from '@/components/datatable/TableLoadingState.vue';
import BasicDataTable from '@/components/datatable/BasicDataTable.vue';
import DataTableToolbar from '@/components/datatable/DataTableToolbar.vue';
import CreateButton from '@/components/datatable/CreateButton.vue';

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
});
</script>