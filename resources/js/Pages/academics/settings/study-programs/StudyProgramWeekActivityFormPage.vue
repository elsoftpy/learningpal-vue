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
                            {{ $t('Study Program Week') }}
                        </span>
                        <h1 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            {{ context.weekTitle }}
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
                                {{ $t('Week') }}:
                            </span>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                {{ context.weekTitle }}
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

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div class="flex flex-col xl:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Content Topic') }}
                            </label>
                            <Select
                                v-model="form.level_content_id"
                                :options="levelContents"
                                option-label="content"
                                option-value="id"
                                :placeholder="$t('Select level content')"
                                filter
                                show-clear
                                class="w-full"
                            />
                            <Message v-if="getError('level_content_id')" severity="error" size="small" variant="simple">
                                {{ getError('level_content_id') }}
                            </Message>
                        </div>

                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Type') }} <span class="text-red-500">*</span>
                            </label>
                            <Select
                                v-model="form.type"
                                :options="activityTypeOptions"
                                option-label="name"
                                option-value="value"
                                class="w-full"
                            />
                            <Message v-if="getError('type')" severity="error" size="small" variant="simple">
                                {{ getError('type') }}
                            </Message>
                        </div>

                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Sort Order') }} <span class="text-red-500">*</span>
                            </label>
                            <InputNumber
                                v-model="form.sort_order"
                                :min="1"
                                fluid
                            />
                            <Message v-if="getError('sort_order')" severity="error" size="small" variant="simple">
                                {{ getError('sort_order') }}
                            </Message>
                        </div>

                        <div class="flex flex-col md:col-span-2 xl:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Activity Name') }} <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                v-model="form.activity_name"
                                fluid
                            />
                            <Message v-if="getError('activity_name')" severity="error" size="small" variant="simple">
                                {{ getError('activity_name') }}
                            </Message>
                        </div>

                        <div class="flex flex-col md:col-span-2 xl:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Free Content') }}
                            </label>
                            <Textarea
                                v-model="form.free_content"
                                rows="3"
                                auto-resize
                                fluid
                                :placeholder="$t('Optional custom content for this activity')"
                            />
                            <Message v-if="getError('free_content')" severity="error" size="small" variant="simple">
                                {{ getError('free_content') }}
                            </Message>
                        </div>

                        <div class="flex flex-col md:col-span-2 xl:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Links') }}
                                <span v-if="form.type === 'video'" class="text-red-500">*</span>
                            </label>
                            <Textarea
                                v-model="form.links"
                                rows="3"
                                auto-resize
                                fluid
                                :placeholder="$t('Add one or more links. You can separate multiple links with |')"
                            />
                            <Message v-if="getError('links')" severity="error" size="small" variant="simple">
                                {{ getError('links') }}
                            </Message>
                        </div>

                        <div class="flex flex-col md:col-span-2 xl:col-span-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Study Material') }}
                            </label>
                            <FileUpload
                                id="study-program-activity-study-material"
                                :button-label="$t('Upload study material')"
                                accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpeg,.jpg,.png,.webp"
                                :max-file-size="10240000"
                                empty-icon="pi pi-file"
                                :empty-message="$t('Select a study material file')"
                                status-class="px-2 py-1 rounded-full bg-sky-600 text-xs font-semibold text-white"
                                @update:modelValue="onStudyMaterialSelect"
                            />
                            <Message v-if="getError('study_material')" severity="error" size="small" variant="simple">
                                {{ getError('study_material') }}
                            </Message>
                            <a
                                v-if="existingStudyMaterial.url && !studyMaterialFile"
                                :href="existingStudyMaterial.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-2 text-sm text-blue-600 underline dark:text-blue-400"
                            >
                                {{ existingStudyMaterial.name || $t('View current study material') }}
                            </a>
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
import Textarea from 'primevue/textarea';
import FileUpload from '@/components/form/FileUpload.vue';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler';

const { t: $t } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const { handleApiError } = useApiErrorHandler();

const weekId = computed(() => route.params.weekId);
const activityId = computed(() => route.params.activityId);
const isCreate = computed(() => route.meta?.crud === 'create');
const isLoadingData = ref(true);
const isSubmitting = ref(false);
const loadError = ref('');
const errors = ref({});
const levelContents = ref([]);
const studyMaterialFile = ref(null);
const existingStudyMaterial = reactive({
    url: '',
    name: '',
});

const form = reactive({
    level_content_id: null,
    free_content: '',
    activity_name: '',
    type: 'exercise',
    links: '',
    sort_order: null,
});

const context = reactive({
    studyProgramTitle: '',
    weekTitle: '',
});

const activityTypeOptions = computed(() => ([
    { name: $t('Exercise'), value: 'exercise' },
    { name: $t('Video'), value: 'video' },
    { name: $t('Activity'), value: 'activity' },
    { name: $t('Production'), value: 'production' },
]));

const schema = computed(() => z.object({
    level_content_id: z.number().nullable().optional(),
    free_content: z.string().nullable().optional(),
    activity_name: z.string().min(1, $t('Activity name is required.')),
    type: z.string().min(1, $t('Activity type is required.')),
    links: z.string().nullable().optional(),
    sort_order: z.number().int().min(1, $t('Sort order must be at least 1.')),
}).superRefine((activity, ctx) => {
    const hasLevelContent = activity.level_content_id !== null && activity.level_content_id !== undefined;
    const hasFreeContent = typeof activity.free_content === 'string' && activity.free_content.trim().length > 0;

    if (hasLevelContent && hasFreeContent) {
        ctx.addIssue({
            code: z.ZodIssueCode.custom,
            path: ['free_content'],
            message: $t('Free content must be empty when content topic is selected.'),
        });
    }

    if (!hasLevelContent && !hasFreeContent) {
        ctx.addIssue({
            code: z.ZodIssueCode.custom,
            path: ['free_content'],
            message: $t('Free content is required when no content topic is selected.'),
        });
    }

    const hasLinks = typeof activity.links === 'string' && activity.links.trim().length > 0;

    if (activity.type === 'video' && !hasLinks) {
        ctx.addIssue({
            code: z.ZodIssueCode.custom,
            path: ['links'],
            message: $t('A video activity requires at least one link.'),
        });
    }
}));

const getError = (field) => {
    const value = errors.value?.[field];
    return Array.isArray(value) ? value.join(', ') : (value || '');
};

const goBack = () => {
    router.push({ name: 'academics.settings.study-programs.list' });
};

const onStudyMaterialSelect = (file) => {
    studyMaterialFile.value = file;
};

const mapZodErrors = (issues) => {
    const mapped = {};
    issues.forEach((issue) => {
        mapped[issue.path.join('.') || 'general'] = issue.message;
    });
    return mapped;
};

const fetchActivityData = async () => {
    isLoadingData.value = true;
    loadError.value = '';

    try {
        if (isCreate.value) {
            const response = await axios.post(`/academics/settings/study-programs/activities/study-program-week/${weekId.value}/data`);
            const week = response.data?.data?.week;
            const studyProgram = week?.study_program;

            context.studyProgramTitle = studyProgram?.title ?? '';
            const weekNumber = week?.week_number;
            const weekTitle = week?.title;
            context.weekTitle = weekTitle || `${$t('Week')} ${weekNumber}`;
            levelContents.value = week?.level_contents || [];

            form.level_content_id = null;
            form.free_content = '';
            form.activity_name = '';
            form.type = 'exercise';
            form.links = '';
            form.sort_order = (Array.isArray(week?.activities) ? week.activities.length : 0) + 1;
            existingStudyMaterial.url = '';
            existingStudyMaterial.name = '';

            return;
        }

        const response = await axios.post(`/academics/settings/study-programs/activities/${activityId.value}/data`);
        const activity = response.data?.data?.activity;

        form.level_content_id = activity?.level_content_id ?? null;
        form.free_content = activity?.free_content ?? '';
        form.activity_name = activity?.activity_name ?? '';
        form.type = activity?.type ?? 'exercise';
        form.links = activity?.links ?? '';
        form.sort_order = activity?.sort_order ?? null;
        levelContents.value = activity?.level_contents || [];
        existingStudyMaterial.url = activity?.study_material_url ?? '';
        existingStudyMaterial.name = activity?.study_material_name ?? '';

        const studyProgram = activity?.study_program_week?.study_program;
        context.studyProgramTitle = studyProgram?.title ?? '';
        const weekNumber = activity?.study_program_week?.week_number;
        const weekTitle = activity?.study_program_week?.title;
        context.weekTitle = weekTitle || `${$t('Week')} ${weekNumber}`;
    } catch (error) {
        loadError.value = handleApiError(error)?.message || $t('Failed to load study program activity data.');
    } finally {
        isLoadingData.value = false;
    }
};

const handleSubmit = async () => {
    errors.value = {};

    const payload = {
        level_content_id: form.level_content_id ? Number(form.level_content_id) : null,
        free_content: form.free_content?.trim() || null,
        activity_name: form.activity_name?.trim() || '',
        type: form.type,
        links: form.links?.trim() || null,
        sort_order: Number(form.sort_order),
    };

    const validation = schema.value.safeParse(payload);
    if (!validation.success) {
        errors.value = mapZodErrors(validation.error.issues);
        return;
    }

    isSubmitting.value = true;

    try {
        const url = isCreate.value
            ? `/academics/settings/study-programs/activities/study-program-week/${weekId.value}`
            : `/academics/settings/study-programs/activities/${activityId.value}/edit`;

        const formData = new FormData();
        if (payload.level_content_id !== null) {
            formData.append('level_content_id', payload.level_content_id);
        }
        if (payload.free_content) {
            formData.append('free_content', payload.free_content);
        }
        formData.append('activity_name', payload.activity_name);
        formData.append('type', payload.type);
        if (payload.links) {
            formData.append('links', payload.links);
        }
        formData.append('sort_order', payload.sort_order);
        if (studyMaterialFile.value) {
            formData.append('study_material', studyMaterialFile.value);
        }

        await axios.post(url, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: isCreate.value
                ? $t('Study program activity created successfully.')
                : $t('Study program activity updated successfully.'),
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

onMounted(fetchActivityData);
</script>
