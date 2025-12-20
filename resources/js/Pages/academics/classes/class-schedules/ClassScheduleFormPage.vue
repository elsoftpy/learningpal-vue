<template>
    <Form
        :key="formKey"
        v-slot="$form"
        :resolver="resolver"
        :initial-values="initialValues"
        @submit="handleSubmit"
        :validateOnBlur="true"
    >
        <PageContainer>
            <template #body>
                <div class="flex flex-col w-full space-y-4">
                    <div class="flex flex-col md:flex-row w-full space-y-4 md:space-x-2">
                        <div class="flex flex-col w-full md:w-1/3">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Schedule Name') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="name"
                                name="name"
                                type="text"
                                class="w-full"
                            />
                            <Message
                                v-if="$form.name?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.name.error?.message }}
                            </Message>
                            <Message
                                v-if="errors?.name"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ Array.isArray(errors?.name) ? errors?.name.join(', ') : errors?.name }}
                            </Message>
                        </div>

                        <div class="flex flex-col w-full md:w-1/3">
                            <label for="courses" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Courses') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <Select 
                                id="course"
                                name="course"
                                :options="coursesOptions"
                                option-label="name"
                                option-value="id"
                                :placeholder="$t('Select course')"
                                filter
                                :filter-placeholder="$t('Search courses')"
                                :show-clear="false"
                                :loading="coursesLoading"
                                @filter="onCoursesFilter"
                                class="w-full"
                                display="chip"
                            >
                                <template #empty>
                                    <div class="p-3 text-center text-gray-500">
                                        {{ $t('No results found.') }}
                                    </div>
                                </template>

                                <template #loader>
                                    <div class="flex items-center justify-center p-3">
                                        <ProgressSpinner style="width: 20px; height: 20px" stroke-width="4" />
                                        <span class="ml text-sm text-gray-600">{{ $t('Loading...') }}</span>
                                    </div>
                                </template>
                            </Select>
                            <Message
                                v-if="$form.course ?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.course.error?.message }}
                            </Message>
                            <Message
                                v-if="errors?.courses"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ Array.isArray(errors?.course) ? errors?.course.join(', ') : errors?.course }}
                            </Message>
                        </div>
                        <div class="flex flex-col w-full md:w-1/3">
                            <MonthInput
                                id="start_month"
                                name="start_month"
                                :label="$t('Start Month')"
                                :placeholder="$t('mm/yyyy')"
                                :mandatory="true"
                            />
                            <Message
                                v-if="$form.start_month?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.start_month.error?.message }}
                            </Message>
                            <Message
                                v-if="errors?.start_month"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ Array.isArray(errors?.start_month) ? errors?.start_month.join(', ') : errors?.start_month }}
                            </Message>
                        </div>
                    </div>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-200">
                                {{ $t('Session Details') }}
                            </h3>
                            <span class="text-xs text-gray-500">{{ $t('Add each session before saving the schedule.') }}</span>
                        </div>

                        <div class="flex flex-col lg:flex-row w-full gap-4">
                            <div class="flex flex-col w-full lg:w-1/5">
                                <DateInput
                                    id="detail-session-date"
                                    name="detail_session_date"
                                    :label="`${$t('Session Date')} *`"
                                    :placeholder="$t('dd/mm/yyyy')"
                                    v-model="detailForm.session_date"
                                />
                            </div>

                            <div class="flex flex-col w-full lg:w-1/5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Start Time') }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    v-model="detailForm.start_time"
                                    v-maska="timeMaskOptions"
                                    :placeholder="$t('HH:MM')"
                                    class="w-full"
                                />
                            </div>

                            <div class="flex flex-col w-full lg:w-1/5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('End Time') }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    v-model="detailForm.end_time"
                                    v-maska="timeMaskOptions"
                                    :placeholder="$t('HH:MM')"
                                    class="w-full"
                                />
                            </div>

                            <div class="flex flex-col w-full lg:w-1/5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Topic') }}
                                </label>
                                <InputText
                                    v-model="detailForm.topic"
                                    :placeholder="$t('Topic')"
                                    class="w-full"
                                />
                            </div>

                            <div class="flex flex-col w-full lg:w-1/5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Activity') }}
                                </label>
                                <InputText
                                    v-model="detailForm.activity"
                                    :placeholder="$t('Activity')"
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <Message
                            v-if="detailFormError"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ detailFormError }}
                        </Message>

                        <div class="flex justify-end">
                            <Button
                                type="button"
                                :label="$t('Add schedule')"
                                icon="pi pi-plus"
                                class="p-button-primary"
                                @click="addScheduleDetail"
                            />
                        </div>

                        <div v-if="scheduleDetails.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                                    <tr>
                                        <th class="px-3 py-2 text-left">{{ $t('Session Date') }}</th>
                                        <th class="px-3 py-2 text-left">{{ $t('Start Time') }}</th>
                                        <th class="px-3 py-2 text-left">{{ $t('End Time') }}</th>
                                        <th class="px-3 py-2 text-left">{{ $t('Topic') }}</th>
                                        <th class="px-3 py-2 text-left">{{ $t('Activity') }}</th>
                                        <th class="px-3 py-2 text-right">{{ $t('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="detail in scheduleDetails" :key="detail._key" class="bg-white dark:bg-slate-900">
                                        <td class="px-3 py-2">{{ detail.session_date }}</td>
                                        <td class="px-3 py-2">{{ detail.start_time }}</td>
                                        <td class="px-3 py-2">{{ detail.end_time }}</td>
                                        <td class="px-3 py-2">{{ detail.topic || $t('N/A') }}</td>
                                        <td class="px-3 py-2">{{ detail.activity || $t('N/A') }}</td>
                                        <td class="px-3 py-2 text-right">
                                            <Button
                                                type="button"
                                                icon="pi pi-trash"
                                                severity="danger"
                                                text
                                                @click="removeDetail(detail._key)"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-xs text-gray-500">{{ $t('No sessions added yet.') }}</div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <SubmitButton :isLoading="isLoading" />
                    </div>
                </div>      
            </template>
        </PageContainer>
    </Form>
</template>

<script setup>
import { ref, computed, watch, onMounted, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { createClassScheduleSchema } from '@/schemas/classSchedule';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler'
import { useFormValues } from '@/composables/useFormValues';
import { useFormSubmitter } from '@/composables/useFormSubmitter'
import { Form } from '@primevue/forms';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import { useToast } from 'primevue/usetoast';
import ProgressSpinner from 'primevue/progressspinner';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import MonthInput from '@/components/form/MonthInput.vue';
import DateInput from '@/components/form/DateInput.vue';
import Message from 'primevue/message';
import Button from 'primevue/button';
import axios from 'axios';
import SubmitButton from '@/components/form/SubmitButton.vue';


const { locale, t: $t } = useI18n();
const { handleApiError } = useApiErrorHandler();
const { extractFormData } = useFormValues();
const classScheduleSchema = computed(() => createClassScheduleSchema($t, locale.value));
const resolver = zodResolver(classScheduleSchema.value);
const route = useRoute();
const router = useRouter();
const toast = useToast();

const coursesOptions = ref([]);
const coursesLoading = ref(false);
const classScheduleData = ref(null);
const formKey = ref(0);
let coursesDebounceTimer = null;
const crudAction = route.meta?.crud || 'read';
const creating = crudAction === 'create';

const classScheduleId = route.params.id;

const generateDetailKey = () => (typeof crypto !== 'undefined' && crypto.randomUUID
    ? crypto.randomUUID()
    : `detail-${Date.now()}-${Math.random().toString(36).slice(2, 9)}`);

const normalizeDetail = (detail = {}) => ({
    id: detail.id ?? null,
    session_date: detail.session_date ?? '',
    start_time: detail.start_time ?? '',
    end_time: detail.end_time ?? '',
    topic: detail.topic ?? '',
    activity: detail.activity ?? '',
    order: detail.order ?? null,
    _key: detail._key ?? (detail.id ? `detail-${detail.id}` : generateDetailKey()),
});

const loadScheduleDetails = (details = []) => {
    if (!Array.isArray(details)) {
        scheduleDetails.value = [];
        return;
    }

    scheduleDetails.value = details.map((detail) => normalizeDetail(detail));
};

const resetDetailForm = () => {
    Object.assign(detailForm, createEmptyDetailForm());
};

const isValidTime = (value) => /^\d{2}:\d{2}$/.test(value || '');

const addScheduleDetail = () => {
    detailFormError.value = '';

    if (!detailForm.session_date) {
        detailFormError.value = $t('Session date is required.');
        return;
    }

    if (!isValidTime(detailForm.start_time)) {
        detailFormError.value = $t('Start time must be in HH:MM format.');
        return;
    }

    if (!isValidTime(detailForm.end_time)) {
        detailFormError.value = $t('End time must be in HH:MM format.');
        return;
    }

    const newDetail = normalizeDetail({
        ...detailForm,
        order: scheduleDetails.value.length + 1,
    });

    scheduleDetails.value = [...scheduleDetails.value, newDetail];
    resetDetailForm();
};

const removeDetail = (detailKey) => {
    scheduleDetails.value = scheduleDetails.value.filter((detail) => detail._key !== detailKey);
};

const buildDetailsPayload = () => scheduleDetails.value.map((detail, index) => ({
    id: detail.id,
    session_date: detail.session_date,
    start_time: detail.start_time,
    end_time: detail.end_time,
    topic: detail.topic,
    activity: detail.activity,
    order: detail.order ?? index + 1,
}));

const timeMaskOptions = { mask: '##:##', eager: true };
const scheduleDetails = ref([]);
const detailFormError = ref('');
const createEmptyDetailForm = () => ({
    session_date: '',
    start_time: '',
    end_time: '',
    topic: '',
    activity: '',
});
const detailForm = reactive(createEmptyDetailForm());


watch(classScheduleData, (newData) => {
    if (newData) {
        formKey.value++; // Forces form to re-initialize
        loadScheduleDetails(newData.details || []);
    } else {
        scheduleDetails.value = [];
    }
});

const initialValues = computed(() => {
    if (crudAction === 'create') {
        return {
            name: '',
            course: null,
            start_month: '',
        };
    }

    const data = classScheduleData.value || {};
    let isActive = classScheduleData.value?.status === 'active' ? true : false;
    return {
        name: data.name || '',
        course: data.course_id || null,
        start_month: data.start_month || '',
    };
});


const { errors, isLoading, setErrors, clearErrors } = useFormSubmitter({
    name: '',
    course: '',
    start_month: '',
    general: '',
});

const fetchCourses = async (query = '') => {
    coursesLoading.value = true;
    try {
        const params = query ? { search: query } : {};
        const response = await axios.post('/lists/courses', { params });
        coursesOptions.value = response.data.data || response.data || [];

    } catch (error) {
        console.error('Error fetching courses:', error);
        coursesOptions.value = [];
    } finally {
        coursesLoading.value = false;
    }
};

const fetchClassScheduleData = async () => {
    try {
        const response = await axios.post(`/academics/lessons/class-schedules/${classScheduleId}/data`);
        classScheduleData.value = response.data.data.class_schedule || response.data.class_schedule || {};
        loadScheduleDetails(classScheduleData.value?.details || []);
    } catch (error) {
        console.error('Error fetching class schedule data:', error);
        classScheduleData.value = null;
        scheduleDetails.value = [];
        toast.add({ 
            severity: 'error', 
            summary: $t('Error'), 
            detail: $t('Failed to load class schedule data. Please try again.'),
            life: 5000 
        });
    }
};

const getCourseNames = (courses) => {

    return Array.isArray(courses) 
        ? courses.map(course => typeof course === 'object' ? course.name : course) 
        : [];
}

const onCoursesFilter = (event) => {
    clearTimeout(coursesDebounceTimer);
    coursesDebounceTimer = setTimeout(() => {
        fetchCourses(event.value);
    }, 300);
};

const handleSubmit =  async (formData) => {
    errors.value = {};

    const { valid, values } = extractFormData(formData);
    
    if (!valid) {
        return;
    }

    values.details = buildDetailsPayload();

    isLoading.value = true;

    try {
        let url = crudAction === 'create' 
            ? '/academics/settings/class-schedules' 
            : `/academics/settings/class-schedules/${classScheduleId}/edit`;
            
        const { data } = await axios.post(url, values);

        toast.add({ 
            severity: 'success', 
            summary: $t('Success'), 
            detail: $t('User saved successfully.'),
            life: 3000 
        });

        router.push({ name: 'academics.settings.students.list' });

    } catch (error) {
        const apiError = handleApiError(error)
        
        if (apiError?.type === 'validation' && apiError.errors) {
            setErrors(apiError.errors)

            toast.add({ 
                severity: 'error', 
                summary: $t('Validation Error'), 
                detail: $t('Please correct the errors in the form.'),
                life: 5000 
            });

            return;
        }

        setErrors({ general: apiError?.message || $t('An unexpected error occurred. Please try again.') });
        
        toast.add({ 
            severity: 'error', 
            summary: $t('Error'), 
            detail: $t('An unexpected error occurred. Please try again.'),
            life: 5000 
        });
        
    } finally { isLoading.value = false; } 
        
};

onMounted(async () => {
    try {
        await fetchCourses();
        
        if (!creating) {    
            await fetchClassScheduleData();
        }
        
    } catch (error) {
        console.error('Error during component mount:', error);
    }
});
</script>