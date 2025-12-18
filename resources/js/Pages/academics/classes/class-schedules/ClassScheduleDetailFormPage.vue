<template>
    <Form
        :key="formKey"
        v-slot="$form"
        :resolver="resolver"
        :initial-values="initialValues"
        :validateOnBlur="true"
        @submit="handleSubmit"
    >
        <PageContainer>
            <template #body>
                <div class="max-w-3xl mx-auto w-full space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">
                                {{ $t('Class Schedule Detail') }}
                            </p>
                            <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                {{ scheduleInfoDisplay }}
                            </h1>
                            <p class="text-sm text-gray-500" v-if="detailContext.order">
                                {{ $t('Session {order}', { order: detailContext.order }) }}
                            </p>
                        </div>
                        <Button
                            type="button"
                            icon="pi pi-arrow-left"
                            :label="$t('Back to schedule')"
                            severity="secondary"
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

                    <div v-if="detailLoading" class="flex justify-center py-16">
                        <ProgressSpinner style="width: 40px; height: 40px" stroke-width="4" />
                    </div>

                    <div v-else-if="!loadError" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <DateInput
                                id="session_date"
                                name="session_date"
                                :label="`${$t('Session Date')} *`"
                                :placeholder="$t('dd/mm/yyyy')"
                            />
                            <Message
                                v-if="$form.session_date?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.session_date?.error?.message }}
                            </Message>
                            <div class="flex flex-col">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Start Time') }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    name="start_time"
                                    v-maska="timeMaskOptions"
                                    :placeholder="$t('HH:MM')"
                                    class="w-full"
                                />
                                <Message
                                    v-if="$form.start_time?.invalid"
                                    severity="error"
                                    size="small"
                                    variant="simple"
                                >
                                    {{ $form.start_time?.error?.message }}
                                </Message>
                            </div>
                            <div class="flex flex-col">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('End Time') }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    name="end_time"
                                    v-maska="timeMaskOptions"
                                    :placeholder="$t('HH:MM')"
                                    class="w-full"
                                />
                                <Message
                                    v-if="$form.end_time?.invalid"
                                    severity="error"
                                    size="small"
                                    variant="simple"
                                >
                                    {{ $form.end_time?.error?.message }}
                                </Message>
                            </div>
                            <div class="flex flex-col">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Topic') }}
                                </label>
                                <InputText
                                    name="topic"
                                    :placeholder="$t('Topic')"
                                    class="w-full"
                                />
                                <Message
                                    v-if="$form.topic?.invalid"
                                    severity="error"
                                    size="small"
                                    variant="simple"
                                >
                                    {{ $form.topic?.error?.message }}
                                </Message>
                            </div>
                            <div class="flex flex-col">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Activity') }}
                                </label>
                                <InputText
                                    name="activity"
                                    :placeholder="$t('Activity')"
                                    class="w-full"
                                />
                                <Message
                                    v-if="$form.activity?.invalid"
                                    severity="error"
                                    size="small"
                                    variant="simple"
                                >
                                    {{ $form.activity?.error?.message }}
                                </Message>
                            </div>
                            <DateInput
                                id="rescheduled_date"
                                name="rescheduled_date"
                                :label="$t('Rescheduled Date')"
                                :placeholder="$t('dd/mm/yyyy')"
                            />
                            <Message
                                v-if="$form.rescheduled_date?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.rescheduled_date?.error?.message }}
                            </Message>
                            <div class="flex flex-col">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Rescheduled Start Time') }}
                                </label>
                                <InputText
                                    name="rescheduled_start_time"
                                    v-maska="timeMaskOptions"
                                    :placeholder="$t('HH:MM')"
                                    class="w-full"
                                />
                                <Message
                                    v-if="$form.rescheduled_start_time?.invalid"
                                    severity="error"
                                    size="small"
                                    variant="simple"
                                >
                                    {{ $form.rescheduled_start_time?.error?.message }}
                                </Message>
                            </div>
                            <div class="flex flex-col">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Rescheduled End Time') }}
                                </label>
                                <InputText
                                    name="rescheduled_end_time"
                                    v-maska="timeMaskOptions"
                                    :placeholder="$t('HH:MM')"
                                    class="w-full"
                                />
                                <Message
                                    v-if="$form.rescheduled_end_time?.invalid"
                                    severity="error"
                                    size="small"
                                    variant="simple"
                                >
                                    {{ $form.rescheduled_end_time?.error?.message }}
                                </Message>
                            </div>
                        </div>

                        <Message
                            v-if="errors?.general"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ Array.isArray(errors?.general) ? errors.general.join(', ') : errors.general }}
                        </Message>

                        <div class="flex justify-end">
                            <SubmitButton :isLoading="isLoading" />
                        </div>
                    </div>
                </div>
            </template>
        </PageContainer>
    </Form>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { Form } from '@primevue/forms';
import { z } from 'zod';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import axios from 'axios';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import DateInput from '@/components/form/DateInput.vue';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Button from 'primevue/button';
import SubmitButton from '@/components/form/SubmitButton.vue';
import ProgressSpinner from 'primevue/progressspinner';
import { useFormValues } from '@/composables/useFormValues';
import { useFormSubmitter } from '@/composables/useFormSubmitter';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler';
import { useToast } from 'primevue/usetoast';

const { t: $t, locale } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const { extractFormData } = useFormValues();
const { handleApiError } = useApiErrorHandler();

const scheduleId = computed(() => route.params.scheduleId);
const detailId = computed(() => route.params.detailId);

const createEmptyDetail = () => ({
    session_date: '',
    start_time: '',
    end_time: '',
    topic: '',
    activity: '',
    rescheduled_date: '',
    rescheduled_start_time: '',
    rescheduled_end_time: '',
});

const detailData = ref(createEmptyDetail());
const scheduleInfo = ref({
    name: '',
    course: '',
    month: '',
});
const detailContext = ref({ order: null });
const detailLoading = ref(true);
const loadError = ref('');
const formKey = ref(0);
const timeMaskOptions = { mask: '##:##', eager: true };

const createDetailSchema = (t) => z.object({
    session_date: z.string()
        .min(1, { message: t('Session date is required.') })
        .refine((val) => {
            const regex = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/;
            return regex.test(val);
        }, { message: t('Invalid date format. Use DD/MM/YYYY.') }),
    start_time: z.string()
        .min(1, { message: t('Start time is required.') })
        .regex(/^([01]\d|2[0-3]):[0-5]\d$/, { message: t('Use 24h HH:MM format.') }),
    end_time: z.string()
        .min(1, { message: t('End time is required.') })
        .regex(/^([01]\d|2[0-3]):[0-5]\d$/, { message: t('Use 24h HH:MM format.') }),
    topic: z.string().max(255, { message: t('Topic is too long.') }).optional().or(z.literal('')),
    activity: z.string().max(255, { message: t('Activity is too long.') }).optional().or(z.literal('')),
    rescheduled_date: z.string().optional().refine((val) => {
        if (!val) {
            return true;
        }
        const regex = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/;
        return regex.test(val);
    }, { message: t('Invalid date format. Use DD/MM/YYYY.') }),
    rescheduled_start_time: z.string().optional().refine((val) => {
        if (!val) {
            return true;
        }
        return /^([01]\d|2[0-3]):[0-5]\d$/.test(val);
    }, { message: t('Use 24h HH:MM format.') }),
    rescheduled_end_time: z.string().optional().refine((val) => {
        if (!val) {
            return true;
        }
        return /^([01]\d|2[0-3]):[0-5]\d$/.test(val);
    }, { message: t('Use 24h HH:MM format.') }),
});

const detailSchema = computed(() => createDetailSchema($t, locale.value));
const resolver = zodResolver(detailSchema.value);

const initialValues = computed(() => ({
    session_date: detailData.value.session_date,
    start_time: detailData.value.start_time,
    end_time: detailData.value.end_time,
    topic: detailData.value.topic,
    activity: detailData.value.activity,
    rescheduled_date: detailData.value.rescheduled_date,
    rescheduled_start_time: detailData.value.rescheduled_start_time,
    rescheduled_end_time: detailData.value.rescheduled_end_time,
}));

const { errors, isLoading, setErrors } = useFormSubmitter({
    session_date: '',
    start_time: '',
    end_time: '',
    topic: '',
    activity: '',
    rescheduled_date: '',
    rescheduled_start_time: '',
    rescheduled_end_time: '',
    general: '',
});

const scheduleInfoDisplay = computed(() => {
    const segments = [scheduleInfo.value.name, scheduleInfo.value.course, scheduleInfo.value.month]
        .filter((segment) => Boolean(segment));
    return segments.length ? segments.join(' • ') : $t('Loading...');
});

const setDetailData = (detail) => {
    detailData.value = {
        session_date: detail?.session_date || '',
        start_time: detail?.start_time || '',
        end_time: detail?.end_time || '',
        topic: detail?.topic || '',
        activity: detail?.activity || '',
        rescheduled_date: detail?.rescheduled_date || '',
        rescheduled_start_time: detail?.rescheduled_start_time || '',
        rescheduled_end_time: detail?.rescheduled_end_time || '',
    };
    formKey.value += 1;
};

const fetchScheduleDetail = async () => {
    loadError.value = '';
    detailLoading.value = true;

    try {
        const { data } = await axios.post(`/academics/lessons/class-schedules/${scheduleId.value}/data`);
        const schedule = data?.data?.classSchedule
            || data?.data?.class_schedule
            || data?.classSchedule
            || data?.class_schedule
            || data?.data
            || {};

        if (!schedule) {
            throw new Error('SCHEDULE_NOT_FOUND');
        }

        scheduleInfo.value = {
            name: schedule?.name || '',
            course: schedule?.course || '',
            month: schedule?.display_schedule_month || schedule?.schedule_month || '',
        };

        const details = schedule?.details || [];
        const detail = details.find((item) => String(item.id) === String(detailId.value));

        if (!detail) {
            throw new Error('DETAIL_NOT_FOUND');
        }

        detailContext.value.order = detail?.order ?? null;
        setDetailData(detail);
    } catch (error) {
        if (error?.message === 'DETAIL_NOT_FOUND') {
            loadError.value = $t('The requested schedule detail could not be found.');
        } else {
            const apiError = handleApiError(error);
            loadError.value = apiError?.message || $t('Unable to load schedule detail.');
        }
    } finally {
        detailLoading.value = false;
    }
};

const handleSubmit = async (formData) => {
    errors.value = {};
    const { valid, values } = extractFormData(formData);

    if (!valid) {
        return;
    }

    values.class_schedule_id = scheduleId.value;

    isLoading.value = true;

    try {
        await axios.post(`/academics/lessons/class-schedules/details/${detailId.value}/edit`, values);
        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: $t('Session detail updated successfully.'),
            life: 4000,
        });
        router.push({
            name: 'academics.classes.class-schedules.edit',
            params: { id: scheduleId.value },
        });
    } catch (error) {
        const apiError = handleApiError(error);
        if (apiError?.type === 'validation' && apiError.errors) {
            setErrors(apiError.errors);
        } else {
            setErrors({ general: apiError?.message || $t('An unexpected error occurred.') });
        }
    } finally {
        isLoading.value = false;
    }
};

const goBack = () => {
    router.push({
        name: 'academics.classes.class-schedules.edit',
        params: { id: scheduleId.value },
    });
};

onMounted(() => {
    fetchScheduleDetail();
});
</script>
