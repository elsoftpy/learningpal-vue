<template>
    <PageContainer>
        <template #body>
            <div class="flex flex-col w-full space-y-4">
                <div class="flex flex-col md:hidden w-full space-y-2">
                    <div class="flex w-full justify-end">
                        <Button
                            type="button"
                            icon="pi pi-arrow-left"
                            :label="$t('Back to study programs')"
                            severity="primary"
                            @click="goBack"
                        />
                    </div>
                    <div class="flex flex-col space-y-1">
                        <span class="text-xs uppercase tracking-wide text-gray-500">
                            {{ $t('Study Program') }}
                        </span>
                        <h1 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            {{ context.studyProgramTitle }}
                        </h1>
                    </div>
                </div>

                <div class="hidden md:flex items-center justify-between">
                    <div class="flex flex-col md:flex-row md:items-baseline md:space-x-6">
                        <div class="flex items-baseline space-x-2">
                            <span class="text-xs uppercase tracking-wide text-gray-500">
                                {{ $t('Study Program') }}:
                            </span>
                            <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                {{ context.studyProgramTitle }}
                            </h1>
                        </div>
                        <div class="flex items-baseline space-x-2">
                            <span class="text-xs uppercase tracking-wide text-gray-500">
                                {{ $t('Level') }}:
                            </span>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                {{ context.languageLevel }}
                            </h2>
                        </div>
                    </div>
                    <Button
                        type="button"
                        icon="pi pi-arrow-left"
                        :label="$t('Back to study programs')"
                        severity="primary"
                        @click="goBack"
                    />
                </div>

                <Message
                    v-if="loadError"
                    severity="error"
                    size="small"
                    variant="simple"
                >
                    {{ loadError }}
                </Message>

                <div v-if="isLoadingData" class="flex justify-center py-16">
                    <ProgressSpinner style="width: 40px; height: 40px" stroke-width="4" />
                </div>

                <div v-else class="space-y-4">
                    <Message
                        v-if="errors.general"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ errors.general }}
                    </Message>

                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex flex-col w-full md:w-1/4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Week Number') }} <span class="text-red-500">*</span>
                            </label>
                            <InputNumber
                                v-model="form.week_number"
                                :min="1"
                                fluid
                            />
                            <Message v-if="getError('week_number')" severity="error" size="small" variant="simple">
                                {{ getError('week_number') }}
                            </Message>
                        </div>

                        <div class="flex flex-col w-full md:flex-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Week Title') }} <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                v-model="form.title"
                                fluid
                            />
                            <Message v-if="getError('title')" severity="error" size="small" variant="simple">
                                {{ getError('title') }}
                            </Message>
                        </div>

                        <div class="flex flex-col w-full md:w-1/4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Status') }} <span class="text-red-500">*</span>
                            </label>
                            <Select
                                v-model="form.status"
                                :options="statusOptions"
                                option-label="name"
                                option-value="value"
                                class="w-full"
                            />
                            <Message v-if="getError('status')" severity="error" size="small" variant="simple">
                                {{ getError('status') }}
                            </Message>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <Button
                            :label="$t('Save Changes')"
                            icon="pi pi-save"
                            severity="success"
                            :loading="isSubmitting"
                            @click="handleSubmit"
                        />
                    </div>
                </div>
            </div>
        </template>
    </PageContainer>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { z } from 'zod';
import axios from 'axios';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import ProgressSpinner from 'primevue/progressspinner';
import Select from 'primevue/select';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler';

const { t: $t } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const { handleApiError } = useApiErrorHandler();

const studyProgramId = computed(() => route.params.studyProgramId);
const weekId = computed(() => route.params.weekId);
const isCreate = computed(() => route.meta?.crud === 'create');
const isLoadingData = ref(true);
const isSubmitting = ref(false);
const loadError = ref('');
const errors = ref({});

const form = reactive({
    week_number: null,
    title: '',
    status: 'active',
});

const context = reactive({
    studyProgramTitle: '',
    languageLevel: '',
});

const statusOptions = computed(() => ([
    { name: $t('Active'), value: 'active' },
    { name: $t('Disabled'), value: 'disabled' },
]));

const schema = computed(() => z.object({
    week_number: z.number().int().min(1, $t('Week number must be at least 1.')),
    title: z.string().min(1, $t('Week title is required.')),
    status: z.string().min(1, $t('Status is required.')),
}));

const getError = (field) => {
    const value = errors.value?.[field];
    return Array.isArray(value) ? value.join(', ') : (value || '');
};

const goBack = () => {
    router.push({ name: 'academics.settings.study-programs.list' });
};

const mapZodErrors = (issues) => {
    const mapped = {};
    issues.forEach((issue) => {
        mapped[issue.path.join('.') || 'general'] = issue.message;
    });
    return mapped;
};

const fetchWeekData = async () => {
    isLoadingData.value = true;
    loadError.value = '';

    try {
        if (isCreate.value) {
            const response = await axios.post(`/academics/settings/study-programs/weeks/study-program/${studyProgramId.value}/data`);
            const studyProgram = response.data?.data?.study_program;
            const weeks = Array.isArray(studyProgram?.weeks) ? studyProgram.weeks : [];
            const highestWeekNumber = weeks.reduce(
                (highest, week) => Math.max(highest, Number(week?.week_number || 0)),
                0,
            );

            form.week_number = highestWeekNumber + 1;
            form.title = `${$t('Week')} ${form.week_number}`;
            form.status = 'active';

            context.studyProgramTitle = studyProgram?.title ?? '';
            const level = studyProgram?.language_level;
            context.languageLevel = level
                ? `${level.language?.name ?? ''} ${level.level ?? level.description ?? ''}`.trim()
                : '';

            return;
        }

        const response = await axios.post(`/academics/settings/study-programs/weeks/${weekId.value}/data`);
        const week = response.data?.data?.week;

        form.week_number = week?.week_number ?? null;
        form.title = week?.title ?? '';
        form.status = week?.status ?? 'active';

        context.studyProgramTitle = week?.study_program?.title ?? '';
        const level = week?.study_program?.language_level;
        context.languageLevel = level
            ? `${level.language?.name ?? ''} ${level.level ?? level.description ?? ''}`.trim()
            : '';
    } catch (error) {
        loadError.value = handleApiError(error)?.message || $t('Failed to load study program week data.');
    } finally {
        isLoadingData.value = false;
    }
};

const handleSubmit = async () => {
    errors.value = {};

    const payload = {
        week_number: Number(form.week_number),
        title: form.title?.trim() || '',
        status: form.status,
    };

    const validation = schema.value.safeParse(payload);
    if (!validation.success) {
        errors.value = mapZodErrors(validation.error.issues);
        return;
    }

    isSubmitting.value = true;

    try {
        const url = isCreate.value
            ? `/academics/settings/study-programs/weeks/study-program/${studyProgramId.value}`
            : `/academics/settings/study-programs/weeks/${weekId.value}/edit`;

        await axios.post(url, payload);

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: isCreate.value
                ? $t('Study program week created successfully.')
                : $t('Study program week updated successfully.'),
            life: 3000,
        });

        goBack();
    } catch (error) {
        const apiError = handleApiError(error);

        if (apiError.type === 'validation') {
            errors.value = apiError.errors || {};
        } else {
            errors.value = { general: apiError.message || $t('An unexpected error occurred. Please try again.') };
        }
    } finally {
        isSubmitting.value = false;
    }
};

onMounted(fetchWeekData);
</script>
