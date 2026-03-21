<template>
  <PageContainer>
    <template #body>
      <div class="space-y-4">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
          <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $t('Monthly Classes Report') }}</h2>
              <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
                {{ $t('Review monthly class details in a responsive view and export matching Excel or PDF files.') }}
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <Button
                :label="$t('Export Excel')"
                icon="pi pi-file-excel"
                severity="success"
                outlined
                :disabled="!hasResult"
                :loading="isExportingExcel"
                @click="exportReport('excel')"
              />
              <Button
                :label="$t('Export PDF')"
                icon="pi pi-file-pdf"
                severity="danger"
                outlined
                :disabled="!hasResult"
                :loading="isExportingPdf"
                @click="exportReport('pdf')"
              />
            </div>
          </div>

          <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="flex flex-col gap-2 lg:col-span-4">
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                {{ $t('Course') }} <span class="text-red-500">*</span>
              </label>
              <Select
                v-model="filters.course_id"
                :options="courseOptions"
                optionLabel="label"
                optionValue="value"
                filter
                showClear
                class="w-full"
                :loading="isLoadingCourses"
                :placeholder="$t('Select course')"
              />
            </div>

            <div class="flex flex-col gap-2 lg:col-span-3">
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                {{ $t('Month') }} <span class="text-red-500">*</span>
              </label>
              <Select
                v-model="filters.month"
                :options="monthOptions"
                optionLabel="label"
                optionValue="value"
                class="w-full"
                :loading="isLoadingMonths"
                :disabled="!filters.course_id"
                :placeholder="$t('Select month')"
              />
            </div>

            <div class="flex flex-col gap-2 lg:col-span-3">
              <label class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $t('Student') }}</label>
              <Select
                v-model="filters.student_id"
                :options="studentOptionsWithAll"
                optionLabel="label"
                optionValue="value"
                filter
                showClear
                class="w-full"
                :loading="isLoadingStudents"
                :disabled="!filters.course_id || !filters.month"
                :placeholder="$t('Select student')"
              />
            </div>

            <div class="flex items-end gap-2 lg:col-span-2 lg:justify-end">
              <Button
                :label="$t('Run')"
                icon="pi pi-search"
                :loading="isLoading"
                @click="runReport"
              />
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

          <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
            {{ $t('Student is optional. Leave it empty to generate one report per student with attendance.') }}
          </p>

          <div
            v-if="errorMessage"
            class="mt-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-700 dark:bg-red-950/30 dark:text-red-300"
          >
            {{ errorMessage }}
          </div>
        </section>

        <section
          v-if="hasResult"
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900"
        >
          <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Course') }}</p>
              <p class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ result.course?.display_name || '-' }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Month') }}</p>
              <p class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ result.month?.label || '-' }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Report Count') }}</p>
              <p class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ result.report_count || 0 }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Student') }}</p>
              <p class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ selectedStudentLabel }}</p>
            </div>
          </div>

          <div class="mt-5 space-y-4">
            <article
              v-for="report in result.reports"
              :key="report.student_id"
              class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700"
            >
              <div class="bg-slate-100 px-4 py-3 dark:bg-slate-800">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                  <div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ report.student_name }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ report.level }} • {{ report.month_label }}</p>
                  </div>

                  <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700">
                      {{ $t('Hours per class') }}: {{ formatDecimal(report.hours_per_class) }}
                    </span>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700">
                      {{ $t('Classes in month') }}: {{ report.classes_in_month }}
                    </span>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700">
                      {{ $t('Previous carryover') }}: {{ report.previous_carryover }}
                    </span>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700">
                      {{ $t('Hours in favor') }}: {{ formatDecimal(report.hours_in_favor) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-4 p-4 xl:grid-cols-[minmax(0,2.2fr)_minmax(320px,1fr)]">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-blue-600 text-white">
                      <tr>
                        <th class="px-3 py-2 text-left font-semibold">{{ $t('Teacher') }}</th>
                        <th class="px-3 py-2 text-left font-semibold">{{ $t('Course') }}</th>
                        <th class="px-3 py-2 text-left font-semibold">{{ $t('Date') }}</th>
                        <th class="px-3 py-2 text-right font-semibold">{{ $t('Hours') }}</th>
                        <th class="px-3 py-2 text-right font-semibold">{{ $t('Attendance') }}</th>
                        <th class="px-3 py-2 text-left font-semibold">{{ $t('Progress') }}</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                      <tr v-if="!report.sessions?.length">
                        <td colspan="6" class="px-3 py-6 text-center text-slate-500 dark:text-slate-400">
                          {{ $t('No session records') }}
                        </td>
                      </tr>
                      <tr v-for="(session, sessionIndex) in report.sessions" :key="`${report.student_id}-${sessionIndex}`">
                        <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ session.teacher || '-' }}</td>
                        <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ session.course || '-' }}</td>
                        <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ session.display_date || '-' }}</td>
                        <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ formatDecimal(session.hours) }}</td>
                        <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">{{ session.attendance || '-' }}</td>
                        <td class="px-3 py-2 text-slate-700 dark:text-slate-200">{{ session.progress || '-' }}</td>
                      </tr>
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-800/60">
                      <tr>
                        <td colspan="3" class="px-3 py-2"></td>
                        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ formatDecimal(report.totals?.hours) }}</td>
                        <td class="px-3 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">{{ formatDecimal(report.totals?.attendance) }}</td>
                        <td class="px-3 py-2 text-left font-semibold text-slate-900 dark:text-slate-100">{{ formatPercentage(report.totals?.attendance_percentage) }}</td>
                      </tr>
                      <tr>
                        <td colspan="3" class="px-3 py-2"></td>
                        <td class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Hours') }}</td>
                        <td class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Attendance') }}</td>
                        <td class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Attendance %') }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>

                <aside class="overflow-hidden rounded-2xl border border-blue-200 bg-blue-600 text-white dark:border-blue-500/30">
                  <div class="border-b border-white/15 px-4 py-3">
                    <h4 class="text-sm font-semibold uppercase tracking-wide">RESUMEN DE CLASES</h4>
                  </div>

                  <div class="border-b border-white/15 px-4 py-3">
                    <div class="grid grid-cols-[minmax(0,1fr)_80px_80px] gap-2 text-sm">
                      <div class="font-semibold">{{ $t('Attendance') }}</div>
                      <div class="text-right font-semibold">Total</div>
                      <div class="text-right font-semibold">%</div>
                      <div>{{ $t('Attendance') }}</div>
                      <div class="text-right">{{ formatDecimal(report.totals?.attendance) }}</div>
                      <div class="text-right">{{ formatPercentage(report.totals?.attendance_percentage) }}</div>
                    </div>
                  </div>

                  <div class="px-4 py-3">
                    <div class="text-sm font-semibold uppercase tracking-wide">FEEDBACK</div>
                    <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-blue-50">
                      {{ report.feedback || '-' }}
                    </p>
                  </div>
                </aside>
              </div>
            </article>
          </div>
        </section>
      </div>
    </template>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import Select from 'primevue/select';
import { useI18n } from 'vue-i18n';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler';

const { t: $t } = useI18n();
const { handleApiError, clearApiErrors } = useApiErrorHandler();

const isLoading = ref(false);
const isLoadingCourses = ref(false);
const isLoadingMonths = ref(false);
const isLoadingStudents = ref(false);
const isExportingExcel = ref(false);
const isExportingPdf = ref(false);
const errorMessage = ref('');

const courses = ref([]);
const months = ref([]);
const students = ref([]);

const filters = reactive({
  course_id: null,
  month: null,
  student_id: null,
});

const result = reactive({
  course: null,
  month: null,
  reports: [],
  report_count: 0,
});

const courseOptions = computed(() =>
  (courses.value || []).map((course) => ({
    label: course.display_name,
    value: course.id,
  }))
);

const monthOptions = computed(() =>
  (months.value || []).map((month) => ({
    label: month.label,
    value: month.value,
  }))
);

const studentOptionsWithAll = computed(() => {
  const mappedStudents = (students.value || []).map((student) => ({
    label: student.name,
    value: student.id,
  }));

  return [
    { label: $t('All students with attendance'), value: null },
    ...mappedStudents,
  ];
});

const hasResult = computed(() => Array.isArray(result.reports) && result.reports.length > 0);
const selectedStudentLabel = computed(() => {
  if (!filters.student_id) {
    return $t('All students with attendance');
  }

  return studentOptionsWithAll.value.find((student) => student.value === filters.student_id)?.label || '-';
});

onMounted(async () => {
  await loadCourses();
});

watch(() => filters.course_id, async (nextCourseId, previousCourseId) => {
  if (nextCourseId === previousCourseId) {
    return;
  }

  filters.month = null;
  filters.student_id = null;
  months.value = [];
  students.value = [];
  clearResult();

  if (nextCourseId) {
    await loadMonths();
  }
});

watch(() => filters.month, async (nextMonth, previousMonth) => {
  if (nextMonth === previousMonth) {
    return;
  }

  filters.student_id = null;
  students.value = [];
  clearResult();

  if (filters.course_id && nextMonth) {
    await loadStudents();
  }
});

async function loadCourses() {
  isLoadingCourses.value = true;

  try {
    const response = await axios.get('/academics/settings/courses', {
      params: {
        page: 1,
        per_page: 1000,
        sort_field: 'name',
        sort_order: 'asc',
      },
    });

    courses.value = (response?.data?.data?.courses || []).map((course) => ({
      id: course.id,
      display_name: `${course.name} - ${course.language_level} - ${course.language_name}`,
    }));
  } catch (error) {
    const apiError = handleApiError(error);
    errorMessage.value = apiError?.message || $t('Unable to load courses.');
  } finally {
    isLoadingCourses.value = false;
  }
}

async function loadMonths() {
  isLoadingMonths.value = true;

  try {
    const response = await axios.post('/academics/reports/monthly-classes/options/months', {
      course_id: filters.course_id,
    });

    months.value = response?.data?.data?.months || [];
  } catch (error) {
    const apiError = handleApiError(error);
    errorMessage.value = apiError?.message || $t('Unable to load months.');
  } finally {
    isLoadingMonths.value = false;
  }
}

async function loadStudents() {
  isLoadingStudents.value = true;

  try {
    const response = await axios.post('/academics/reports/monthly-classes/options/students', {
      course_id: filters.course_id,
      month: filters.month,
    });

    students.value = response?.data?.data?.students || [];
  } catch (error) {
    const apiError = handleApiError(error);
    errorMessage.value = apiError?.message || $t('Unable to load students.');
  } finally {
    isLoadingStudents.value = false;
  }
}

async function runReport() {
  clearApiErrors();
  errorMessage.value = '';
  isLoading.value = true;

  try {
    const response = await axios.post('/academics/reports/monthly-classes', buildPayload());
    const data = response?.data?.data || {};

    result.course = data.course || null;
    result.month = data.month || null;
    result.reports = data.reports || [];
    result.report_count = data.report_count || 0;
  } catch (error) {
    clearResult();

    const apiError = handleApiError(error);
    errorMessage.value = apiError?.message || $t('Failed to load report.');
  } finally {
    isLoading.value = false;
  }
}

function clearFilters() {
  clearApiErrors();
  errorMessage.value = '';
  filters.course_id = null;
  filters.month = null;
  filters.student_id = null;
  months.value = [];
  students.value = [];
  clearResult();
}

function clearResult() {
  result.course = null;
  result.month = null;
  result.reports = [];
  result.report_count = 0;
}

function buildPayload() {
  return {
    course_id: filters.course_id,
    month: filters.month,
    student_id: filters.student_id,
  };
}

async function exportReport(type) {
  const isExcel = type === 'excel';
  const endpoint = isExcel
    ? '/academics/reports/monthly-classes/export/excel'
    : '/academics/reports/monthly-classes/export/pdf';

  if (isExcel) {
    isExportingExcel.value = true;
  } else {
    isExportingPdf.value = true;
  }

  errorMessage.value = '';

  try {
    const response = await axios.post(endpoint, buildPayload(), {
      responseType: 'blob',
    });

    const contentDisposition = response.headers['content-disposition'] || '';
    const fallback = isExcel ? 'monthly_classes.xlsx' : 'monthly_classes.pdf';
    const fileName = extractFileName(contentDisposition, fallback);
    const blob = new Blob([response.data], {
      type: response.headers['content-type'] || (isExcel
        ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        : 'application/pdf'),
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

function formatDecimal(value) {
  return Number(value || 0).toFixed(2);
}

function formatPercentage(value) {
  return `${Number(value || 0).toFixed(2)}%`;
}
</script>