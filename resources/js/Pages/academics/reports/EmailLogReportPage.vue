<template>
  <ResourceTableLayout
    :table="table"
    :columns="columns"
    :search-placeholder="$t('Search email, recipient, course, teacher, student or URL')"
    :global-filter-fields="['email_destino', 'greeting', 'course_name', 'teacher_name', 'student_name', 'action_type', 'url', 'estado', 'error']"
    table-style="min-width: 88rem"
  >
    <template #before>
      <section class="mb-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div>
          <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $t('Email Logs Report') }}</h2>
          <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">
            {{ $t('Review email delivery records, filter by delivery state or action type, and inspect the full log details from each row.') }}
          </p>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
          <div class="flex min-w-0 flex-col gap-2 lg:col-span-3">
            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $t('From Date') }}</label>
            <InputText v-model="table.filters.value.created_from.value" type="date" class="w-full" />
          </div>

          <div class="flex min-w-0 flex-col gap-2 lg:col-span-3">
            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $t('To Date') }}</label>
            <InputText v-model="table.filters.value.created_to.value" type="date" class="w-full" />
          </div>

          <div class="flex min-w-0 flex-col gap-2 lg:col-span-3">
            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $t('Status') }}</label>
            <Select
              v-model="table.filters.value.estado.value"
              :options="statusOptions"
              optionLabel="label"
              optionValue="value"
              class="w-full"
              showClear
              :placeholder="$t('Select status')"
            />
          </div>

          <div class="flex min-w-0 flex-col gap-2 lg:col-span-3">
            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $t('Action Type') }}</label>
            <Select
              v-model="table.filters.value.action_type.value"
              :options="actionTypeOptions"
              optionLabel="label"
              optionValue="value"
              class="w-full"
              showClear
              :placeholder="$t('Select action type')"
            />
          </div>
        </div>
      </section>
    </template>

    <template #expansion="slotProps">
      <div class="grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-800/60 lg:grid-cols-2">
        <div class="space-y-3">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Recipient') }}</p>
            <p class="mt-1 text-slate-900 dark:text-slate-100">{{ slotProps.data.email_destino || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Greeting') }}</p>
            <p class="mt-1 text-slate-900 dark:text-slate-100">{{ slotProps.data.greeting || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('URL') }}</p>
            <a
              v-if="slotProps.data.url"
              :href="slotProps.data.url"
              target="_blank"
              rel="noopener noreferrer"
              class="mt-1 block break-all text-blue-600 hover:underline dark:text-blue-400"
            >
              {{ slotProps.data.url }}
            </a>
            <p v-else class="mt-1 text-slate-900 dark:text-slate-100">—</p>
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Error') }}</p>
            <p class="mt-1 whitespace-pre-wrap text-slate-900 dark:text-slate-100">{{ slotProps.data.error || '—' }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Course') }}</p>
            <p class="mt-1 text-slate-900 dark:text-slate-100">{{ slotProps.data.course_name || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Teacher') }}</p>
            <p class="mt-1 text-slate-900 dark:text-slate-100">{{ slotProps.data.teacher_name || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Student') }}</p>
            <p class="mt-1 text-slate-900 dark:text-slate-100">{{ slotProps.data.student_name || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Action Type') }}</p>
            <p class="mt-1 text-slate-900 dark:text-slate-100">{{ slotProps.data.action_type || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Session Date') }}</p>
            <p class="mt-1 text-slate-900 dark:text-slate-100">{{ slotProps.data.session_date || '—' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $t('Logged At') }}</p>
            <p class="mt-1 text-slate-900 dark:text-slate-100">{{ slotProps.data.created_at || '—' }}</p>
          </div>
        </div>
      </div>
    </template>
  </ResourceTableLayout>
</template>

<script setup>
import { computed, h } from 'vue';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { useI18n } from 'vue-i18n';
import { useSettingsTable } from '@/composables/useSettingsTable.js';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler.js';
import ResourceTableLayout from '@/components/datatable/ResourceTableLayout.vue';
import { textColumn } from '@/components/datatable/columnFactories.js';

const { t: $t, locale } = useI18n();
const { handleApiError } = useApiErrorHandler();

const statusOptions = [
  { label: $t('Enviado'), value: 'Enviado' },
  { label: $t('Error'), value: 'Error' },
];

const actionTypeOptions = [
  { label: $t('Pending'), value: 'pending' },
  { label: $t('Upload Task'), value: 'upload_task' },
];

const table = useSettingsTable({
  endpoint: '/academics/reports/email-logs',
  searchFields: ['email_destino', 'greeting', 'course_name', 'teacher_name', 'student_name', 'action_type', 'url', 'estado', 'error'],
  filterConfig: {
    created_from: { defaultValue: null, matchMode: 'equals' },
    created_to: { defaultValue: null, matchMode: 'equals' },
    estado: { defaultValue: null, matchMode: 'equals' },
    action_type: { defaultValue: null, matchMode: 'equals' },
    email_destino: { defaultValue: null, matchMode: 'contains' },
    course_name: { defaultValue: null, matchMode: 'contains' },
  },
  initialPerPage: 15,
  initialSortField: 'created_at',
  initialSortOrder: -1,
  mapResponse: (response) => ({
    data: response.data?.data?.logs || [],
    total: response.data?.data?.total || 0,
  }),
  onError: handleApiError,
});

const columns = computed(() => [
  {
    key: 'expander',
    isExpander: true,
    style: 'width: 1%',
  },
  textColumn({
    key: 'id',
    header: $t('ID'),
    sortable: true,
    style: 'width: 5rem',
  }),
  textColumn({
    key: 'created_at',
    header: $t('Logged At'),
    sortable: true,
    style: 'min-width: 12rem',
    formatter: ({ data }) => formatDateTime(data?.created_at),
  }),
  textColumn({
    key: 'estado',
    header: $t('Status'),
    sortable: true,
    style: 'min-width: 8rem',
    body: ({ data }) => h(Tag, {
      value: data?.estado || '',
      severity: statusSeverity(data?.estado),
    }),
  }),
  textColumn({
    key: 'email_destino',
    header: $t('Recipient'),
    sortable: true,
    style: 'min-width: 16rem',
  }),
  textColumn({
    key: 'course_name',
    header: $t('Course'),
    sortable: true,
    style: 'min-width: 14rem',
  }),
  textColumn({
    key: 'teacher_name',
    header: $t('Teacher'),
    sortable: true,
    style: 'min-width: 14rem',
  }),
  textColumn({
    key: 'student_name',
    header: $t('Student'),
    sortable: true,
    style: 'min-width: 14rem',
  }),
  textColumn({
    key: 'action_type',
    header: $t('Action Type'),
    sortable: true,
    style: 'min-width: 10rem',
  }),
  textColumn({
    key: 'url',
    header: $t('URL'),
    style: 'min-width: 18rem',
    body: ({ data }) => data?.url
      ? h('a', {
          href: data.url,
          target: '_blank',
          rel: 'noopener noreferrer',
          class: 'text-blue-600 hover:underline dark:text-blue-400 break-all',
        }, data.url)
      : '—',
  }),
]);

function statusSeverity(status) {
  if (status === 'Enviado') {
    return 'success';
  }

  if (status === 'Error') {
    return 'danger';
  }

  return 'secondary';
}

function formatDateTime(value) {
  if (!value) {
    return '—';
  }

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat(locale.value || undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(date);
}
</script>
