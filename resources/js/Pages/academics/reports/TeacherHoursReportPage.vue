<template>
  <PageContainer>
    <template #body>
      <div class="space-y-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $t('Teacher Hours Report') }}</h2>

          <div class="mt-4 grid grid-cols-1 items-end gap-3 xl:grid-cols-12">
            <div class="flex flex-col gap-2 xl:col-span-3">
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                {{ $t('Teacher') }} <span class="text-red-500">*</span>
              </label>
              <Select
                v-model="filters.teacher_id"
                :options="teacherOptions"
                optionLabel="label"
                optionValue="value"
                filter
                showClear
                class="w-full"
                :placeholder="$t('Select teacher')"
              />
            </div>

            <div class="flex flex-col gap-2 xl:col-span-1">
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $t('Filter Type') }}</label>
              <Select
                v-model="filters.filter_type"
                :options="filterTypeOptions"
                optionLabel="label"
                optionValue="value"
                class="w-full"
              />
            </div>

            <div class="flex flex-col gap-2 xl:col-span-2">
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $t('From Date') }}</label>
              <InputText
                v-model="filters.from_date"
                type="date"
                class="w-full"
                :disabled="filters.filter_type !== 'range'"
              />
            </div>

            <div class="flex flex-col gap-2 xl:col-span-2">
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $t('To Date') }}</label>
              <InputText
                v-model="filters.to_date"
                type="date"
                class="w-full"
                :disabled="filters.filter_type !== 'range'"
              />
            </div>

            <div class="flex flex-col gap-2 xl:col-span-2">
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $t('Month') }}</label>
              <InputText
                v-model="filters.month"
                type="month"
                class="w-full"
                :disabled="filters.filter_type !== 'month'"
              />
            </div>

            <div class="flex items-center gap-2 xl:col-span-1 xl:justify-end">
              <Button
                :label="$t('Run')"
                icon="pi pi-search"
                :loading="isLoading"
                @click="runReport(1)"
              />
            </div>

            <div class="flex items-center gap-2 xl:col-span-1 xl:justify-end">
              <Button
                :label="$t('Clear')"
                icon="pi pi-times"
                severity="secondary"
                outlined
                :disabled="isLoading"
                @click="clearFilters"
              />
            </div>
          </div>

          <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
            <span>{{ $t('Use Date Range for explicit dates.') }}</span>
            <span>{{ $t('Use Month to auto-calculate start and end dates.') }}</span>
          </div>

          <div v-if="errorMessage" class="mt-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-700 dark:bg-red-950/30 dark:text-red-300">
            {{ errorMessage }}
          </div>
        </div>

        <div v-if="hasResult" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
          <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-slate-700 dark:text-slate-300">
              <span class="font-semibold">{{ $t('Teacher') }}:</span>
              {{ result.teacher?.full_name || '-' }}
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm text-slate-700 dark:text-slate-300">
              <div>
                <span class="font-semibold">{{ $t('Total Hours') }}:</span>
                {{ formatHours(result.total_hours) }}
              </div>
              <div>
                <span class="font-semibold">{{ $t('Selected Rows') }}:</span>
                {{ selectedCount }}
              </div>
              <Button
                v-if="selectedCount > 0"
                :label="$t('Clear Selection')"
                icon="pi pi-times"
                severity="secondary"
                text
                size="small"
                @click="clearSelection"
              />
              <Button
                :label="$t('Export Excel')"
                icon="pi pi-file-excel"
                severity="success"
                outlined
                size="small"
                :loading="isExportingExcel"
                @click="exportReport('excel')"
              />
              <Button
                :label="$t('Export PDF')"
                icon="pi pi-file-pdf"
                severity="danger"
                outlined
                size="small"
                :loading="isExportingPdf"
                @click="exportReport('pdf')"
              />
            </div>
          </div>

          <BasicDataTable
            :value="result.rows"
            :columns="columns"
            :selection="selectedRows"
            :lazy="true"
            :paginator="true"
            :rows="pagination.per_page"
            :totalRecords="pagination.total"
            :first="(pagination.page - 1) * pagination.per_page"
            :sortField="sort.field"
            :sortOrder="sort.order"
            dataKey="id"
            @page="onPage"
            @sort="onSort"
            @update:selection="onSelectionChange"
          />
        </div>
      </div>
    </template>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { useI18n } from 'vue-i18n';
import { textColumn } from '@/components/datatable/columnFactories';
import BasicDataTable from '@/components/datatable/BasicDataTable.vue';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler';

const { t: $t } = useI18n();
const { handleApiError, clearApiErrors } = useApiErrorHandler();

const isLoading = ref(false);
const isExportingExcel = ref(false);
const isExportingPdf = ref(false);
const teachers = ref([]);
const errorMessage = ref('');
const selectedRows = ref([]);
const selectedRowIds = ref([]);

const sort = reactive({
  field: 'course',
  order: 1,
});

const filters = reactive({
  teacher_id: null,
  filter_type: 'range',
  from_date: '',
  to_date: '',
  month: '',
});

const result = reactive({
  teacher: null,
  rows: [],
  total_hours: 0,
});

const pagination = reactive({
  page: 1,
  per_page: 15,
  total: 0,
  last_page: 1,
});

const hasResult = computed(() => Array.isArray(result.rows) && result.rows.length > 0);
const selectedCount = computed(() => selectedRowIds.value.length);

const filterTypeOptions = computed(() => [
  { label: $t('Date Range'), value: 'range' },
  { label: $t('Month'), value: 'month' },
]);

const teacherOptions = computed(() =>
  (teachers.value || []).map((teacher) => ({
    label: teacher.full_name,
    value: teacher.id,
  }))
);

const columns = computed(() => [
  {
    key: 'select',
    selectionMode: 'multiple',
    style: 'width: 3rem',
  },
  textColumn({ key: 'teacher', header: $t('Teacher') }),
  textColumn({ key: 'course', header: $t('Course'), sortable: true, sortField: 'course' }),
  textColumn({ key: 'date', header: $t('Date'), sortable: true, sortField: 'date' }),
  textColumn({
    key: 'hours',
    header: $t('Hours'),
    sortable: true,
    sortField: 'hours',
    body: ({ data }) => formatHours(data?.hours),
  }),
]);

onMounted(async () => {
  await loadTeachers();
  setDefaultDates();
});

async function loadTeachers() {
  try {
    const response = await axios.get('/academics/settings/teachers', {
      params: {
        page: 1,
        per_page: 1000,
        sort_field: 'full_name',
        sort_order: 'asc',
      },
    });

    teachers.value = response?.data?.data?.teachers || [];
  } catch (error) {
    const apiError = handleApiError(error);
    errorMessage.value = apiError?.message || $t('Unable to load teachers.');
  }
}

function setDefaultDates() {
  const now = new Date();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const year = now.getFullYear();

  filters.from_date = `${year}-${month}-01`;
  filters.to_date = endOfMonth(`${year}-${month}`);
  filters.month = `${year}-${month}`;
}

function clearFilters() {
  clearApiErrors();
  errorMessage.value = '';
  filters.teacher_id = null;
  filters.filter_type = 'range';
  selectedRows.value = [];
  selectedRowIds.value = [];
  sort.field = 'course';
  sort.order = 1;
  result.teacher = null;
  result.rows = [];
  result.total_hours = 0;
  pagination.page = 1;
  pagination.total = 0;
  pagination.last_page = 1;
  setDefaultDates();
}

function buildPayload(page = 1, includeSelection = false) {
  const payload = {
    teacher_id: filters.teacher_id,
    page,
    per_page: pagination.per_page,
    sort_field: sort.field,
    sort_order: sort.order === 1 ? 'asc' : 'desc',
  };

  if (filters.filter_type === 'range') {
    payload.from_date = filters.from_date;
    payload.to_date = filters.to_date;
  } else {
    payload.month_start_date = startOfMonth(filters.month);
    payload.month_end_date = endOfMonth(filters.month);
  }

  if (includeSelection && selectedRowIds.value.length > 0) {
    payload.selected_row_ids = selectedRowIds.value;
  }

  return payload;
}

async function runReport(page = 1) {
  clearApiErrors();
  errorMessage.value = '';
  isLoading.value = true;

  try {
    const response = await axios.post('/academics/reports/teacher-hours', buildPayload(page));
    const data = response?.data?.data || {};

    result.teacher = data.teacher || null;
    result.rows = data.rows || [];
    result.total_hours = data.total_hours || 0;
    selectedRows.value = result.rows.filter((row) => selectedRowIds.value.includes(row.id));

    const responsePagination = data.pagination || {};
    pagination.page = responsePagination.page || 1;
    pagination.per_page = responsePagination.per_page || pagination.per_page;
    pagination.last_page = responsePagination.last_page || 1;
    pagination.total = data.total || 0;
  } catch (error) {
    result.teacher = null;
    result.rows = [];
    result.total_hours = 0;
    selectedRows.value = [];
    pagination.page = 1;
    pagination.total = 0;
    pagination.last_page = 1;

    const apiError = handleApiError(error);
    errorMessage.value = apiError?.message || $t('Failed to load report.');
  } finally {
    isLoading.value = false;
  }
}

function onPage(event) {
  const nextPage = (event?.page || 0) + 1;
  pagination.per_page = event?.rows || pagination.per_page;
  runReport(nextPage);
}

function onSort(event) {
  sort.field = event?.sortField || 'course';
  sort.order = typeof event?.sortOrder === 'number' ? event.sortOrder : 1;
  runReport(1);
}

function onSelectionChange(value) {
  const selectedOnPage = Array.isArray(value) ? value : [];
  selectedRows.value = selectedOnPage;

  const pageIds = (result.rows || []).map((row) => row.id);
  const preservedIds = selectedRowIds.value.filter((id) => !pageIds.includes(id));
  const selectedIdsFromPage = selectedOnPage.map((row) => row.id);

  selectedRowIds.value = [...new Set([...preservedIds, ...selectedIdsFromPage])];
}

function clearSelection() {
  selectedRows.value = [];
  selectedRowIds.value = [];
}

async function exportReport(type) {
  const isExcel = type === 'excel';
  const endpoint = isExcel
    ? '/academics/reports/teacher-hours/export/excel'
    : '/academics/reports/teacher-hours/export/pdf';

  if (isExcel) {
    isExportingExcel.value = true;
  } else {
    isExportingPdf.value = true;
  }

  errorMessage.value = '';

  try {
    const response = await axios.post(endpoint, buildPayload(1, true), {
      responseType: 'blob',
    });

    const contentDisposition = response.headers['content-disposition'] || '';
    const fileName = extractFileName(contentDisposition, isExcel ? 'teacher_hours.xlsx' : 'teacher_hours.pdf');

    const blob = new Blob([response.data], {
      type: response.headers['content-type'] || (isExcel ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/pdf'),
    });

    const url = window.URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = fileName;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    const parsedMessage = await parseBlobErrorMessage(error);
    if (parsedMessage) {
      errorMessage.value = parsedMessage;
    } else {
      const apiError = handleApiError(error, {
        extractValidationErrors: false,
      });
      errorMessage.value = apiError?.message || $t('Failed to export report.');
    }
  } finally {
    if (isExcel) {
      isExportingExcel.value = false;
    } else {
      isExportingPdf.value = false;
    }
  }
}

async function parseBlobErrorMessage(error) {
  const blob = error?.response?.data;
  if (!blob || typeof blob.text !== 'function') {
    return null;
  }

  try {
    const text = await blob.text();
    const json = JSON.parse(text);
    return json?.message || null;
  } catch {
    return null;
  }
}

function extractFileName(contentDisposition, fallback) {
  if (!contentDisposition) {
    return fallback;
  }

  const utfMatch = contentDisposition.match(/filename\*=UTF-8''([^;]+)/i);
  if (utfMatch?.[1]) {
    return decodeURIComponent(utfMatch[1]);
  }

  const asciiMatch = contentDisposition.match(/filename="?([^";]+)"?/i);
  if (asciiMatch?.[1]) {
    return asciiMatch[1];
  }

  return fallback;
}

function startOfMonth(monthValue) {
  if (!monthValue) {
    return '';
  }

  return `${monthValue}-01`;
}

function endOfMonth(monthValue) {
  if (!monthValue) {
    return '';
  }

  const [yearString, monthString] = monthValue.split('-');
  const year = Number(yearString);
  const month = Number(monthString);

  if (!year || !month) {
    return '';
  }

  const lastDayDate = new Date(year, month, 0);
  const lastDay = String(lastDayDate.getDate()).padStart(2, '0');

  return `${yearString}-${monthString}-${lastDay}`;
}

function formatHours(value) {
  const numeric = Number(value || 0);
  return numeric.toFixed(2);
}
</script>
