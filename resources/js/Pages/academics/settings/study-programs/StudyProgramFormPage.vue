<template>
    <PageContainer>
        <template #body>
            <div class="flex flex-col w-full space-y-4">
                <Message
                    v-if="errors.general"
                    severity="error"
                    size="small"
                    variant="outlined"
                    :closable="true"
                >
                    {{ errors.general }}
                </Message>

                <div class="flex flex-col lg:flex-row gap-4 w-full">
                    <div class="flex flex-col w-full lg:w-1/4">
                        <label for="language_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Language') }} <span class="text-red-500">*</span>
                        </label>
                        <Select
                            id="language_id"
                            v-model="form.language_id"
                            :options="languages"
                            option-label="name"
                            option-value="id"
                            :placeholder="$t('Select a Language')"
                            filter
                            class="w-full"
                            :disabled="!canEditStudyProgram"
                            @update:model-value="onLanguageChange"
                        />
                        <Message v-if="getError('language_id')" severity="error" size="small" variant="simple">
                            {{ getError('language_id') }}
                        </Message>
                    </div>

                    <div class="flex flex-col w-full lg:w-1/3">
                        <label for="language_level_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Level') }} <span class="text-red-500">*</span>
                        </label>
                        <Select
                            id="language_level_id"
                            v-model="form.language_level_id"
                            :options="languageLevels"
                            option-label="name"
                            option-value="id"
                            :placeholder="$t('Select a Language Level')"
                            filter
                            class="w-full"
                            :disabled="!canEditStudyProgram"
                            @update:model-value="onLanguageLevelChange"
                        />
                        <Message v-if="getError('language_level_id')" severity="error" size="small" variant="simple">
                            {{ getError('language_level_id') }}
                        </Message>
                    </div>

                    <div class="flex flex-col w-full lg:w-1/3">
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Title') }} <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            id="title"
                            v-model="form.title"
                            type="text"
                            fluid
                            :disabled="!canEditStudyProgram"
                        />
                        <Message v-if="getError('title')" severity="error" size="small" variant="simple">
                            {{ getError('title') }}
                        </Message>
                    </div>

                    <div class="flex flex-col w-full lg:w-1/6">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Status') }} <span class="text-red-500">*</span>
                        </label>
                        <Select
                            id="status"
                            v-model="form.status"
                            :options="statusOptions"
                            option-label="name"
                            option-value="value"
                            class="w-full"
                            :disabled="!canEditStudyProgram"
                        />
                        <Message v-if="getError('status')" severity="error" size="small" variant="simple">
                            {{ getError('status') }}
                        </Message>
                    </div>
                </div>

                <div class="flex items-center justify-between w-full pt-2 border-t border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ $t('Weeks') }}
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ $t('Configure the weekly structure and the activities that will be copied into course distance activities.') }}
                        </p>
                    </div>
                    <Button
                        v-if="canAddWeeks"
                        :label="$t('Add Week')"
                        icon="pi pi-plus"
                        severity="info"
                        @click="addWeek"
                    />
                </div>

                <Message v-if="getError('weeks')" severity="error" size="small" variant="simple">
                    {{ getError('weeks') }}
                </Message>

                <div
                    v-for="(week, weekIndex) in form.weeks"
                    :key="week.local_id"
                    class="w-full rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-950"
                >
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col xl:flex-row gap-4 items-start">
                            <div class="flex flex-col w-full xl:w-1/6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Week Number') }} <span class="text-red-500">*</span>
                                </label>
                                <InputNumber
                                    v-model="week.week_number"
                                    :min="1"
                                    fluid
                                    :disabled="!canEditWeeks"
                                />
                                <div class="min-h-6 pt-1">
                                    <Message v-if="getError(`weeks.${weekIndex}.week_number`)" severity="error" size="small" variant="simple">
                                        {{ getError(`weeks.${weekIndex}.week_number`) }}
                                    </Message>
                                </div>
                            </div>

                            <div class="flex flex-col w-full xl:flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Week Title') }} <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    v-model="week.title"
                                    fluid
                                    :disabled="!canEditWeeks"
                                />
                                <div class="min-h-6 pt-1">
                                    <Message v-if="getError(`weeks.${weekIndex}.title`)" severity="error" size="small" variant="simple">
                                        {{ getError(`weeks.${weekIndex}.title`) }}
                                    </Message>
                                </div>
                            </div>

                            <div class="flex flex-col w-full xl:w-1/5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Status') }} <span class="text-red-500">*</span>
                                </label>
                                <Select
                                    v-model="week.status"
                                    :options="statusOptions"
                                    option-label="name"
                                    option-value="value"
                                    class="w-full"
                                    :disabled="!canEditWeeks"
                                />
                                <div class="min-h-6 pt-1">
                                    <Message v-if="getError(`weeks.${weekIndex}.status`)" severity="error" size="small" variant="simple">
                                        {{ getError(`weeks.${weekIndex}.status`) }}
                                    </Message>
                                </div>
                            </div>

                            <div class="flex w-full xl:w-auto xl:self-start pt-0 xl:pt-6">
                                <Button
                                    v-if="canDeleteWeeks"
                                    :label="$t('Remove Week')"
                                    icon="pi pi-trash"
                                    severity="danger"
                                    outlined
                                    @click="removeWeek(weekIndex)"
                                />
                            </div>
                        </div>

                        <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-900/70">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $t('Activities') }}
                                    </h4>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ $t('Each activity becomes a distance activity detail when a course is created.') }}
                                    </p>
                                </div>
                                <Button
                                    v-if="canAddActivities"
                                    :label="$t('Add Activity')"
                                    icon="pi pi-plus"
                                    severity="secondary"
                                    @click="addActivity(weekIndex)"
                                />
                            </div>

                            <Message v-if="getError(`weeks.${weekIndex}.activities`)" severity="error" size="small" variant="simple">
                                {{ getError(`weeks.${weekIndex}.activities`) }}
                            </Message>

                            <div class="space-y-4">
                                <div
                                    v-for="(activity, activityIndex) in week.activities"
                                    :key="activity.local_id"
                                    class="rounded-lg border border-dashed border-slate-300 bg-white p-4 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                                        <div class="flex flex-col xl:col-span-3">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ $t('Content Topic') }}
                                            </label>
                                            <Select
                                                v-model="activity.level_content_id"
                                                :options="levelContents"
                                                option-label="content"
                                                option-value="id"
                                                :placeholder="$t('Select level content')"
                                                filter
                                                show-clear
                                                class="w-full"
                                                :disabled="!canEditActivities"
                                            />
                                            <Message v-if="getError(`weeks.${weekIndex}.activities.${activityIndex}.level_content_id`)" severity="error" size="small" variant="simple">
                                                {{ getError(`weeks.${weekIndex}.activities.${activityIndex}.level_content_id`) }}
                                            </Message>
                                        </div>

                                        <div class="flex flex-col xl:col-span-3">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ $t('Activity Name') }} <span class="text-red-500">*</span>
                                            </label>
                                            <InputText
                                                v-model="activity.activity_name"
                                                fluid
                                                :disabled="!canEditActivities"
                                            />
                                            <Message v-if="getError(`weeks.${weekIndex}.activities.${activityIndex}.activity_name`)" severity="error" size="small" variant="simple">
                                                {{ getError(`weeks.${weekIndex}.activities.${activityIndex}.activity_name`) }}
                                            </Message>
                                        </div>

                                        <div class="flex flex-col xl:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ $t('Type') }} <span class="text-red-500">*</span>
                                            </label>
                                            <Select
                                                v-model="activity.type"
                                                :options="activityTypeOptions"
                                                option-label="name"
                                                option-value="value"
                                                class="w-full"
                                                :disabled="!canEditActivities"
                                            />
                                            <Message v-if="getError(`weeks.${weekIndex}.activities.${activityIndex}.type`)" severity="error" size="small" variant="simple">
                                                {{ getError(`weeks.${weekIndex}.activities.${activityIndex}.type`) }}
                                            </Message>
                                        </div>

                                        <div class="flex flex-col xl:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ $t('Sort Order') }} <span class="text-red-500">*</span>
                                            </label>
                                            <InputNumber
                                                v-model="activity.sort_order"
                                                :min="1"
                                                fluid
                                                :disabled="!canEditActivities"
                                            />
                                            <Message v-if="getError(`weeks.${weekIndex}.activities.${activityIndex}.sort_order`)" severity="error" size="small" variant="simple">
                                                {{ getError(`weeks.${weekIndex}.activities.${activityIndex}.sort_order`) }}
                                            </Message>
                                        </div>

                                        <div class="flex items-end xl:col-span-2">
                                            <Button
                                                v-if="canDeleteActivities"
                                                :label="$t('Remove')"
                                                icon="pi pi-trash"
                                                severity="danger"
                                                text
                                                @click="removeActivity(weekIndex, activityIndex)"
                                            />
                                        </div>

                                        <div class="flex flex-col xl:col-span-12">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                {{ $t('Free Content') }}
                                            </label>
                                            <Textarea
                                                v-model="activity.free_content"
                                                rows="2"
                                                auto-resize
                                                fluid
                                                :placeholder="$t('Optional custom content for this activity')"
                                                :disabled="!canEditActivities"
                                            />
                                            <Message v-if="getError(`weeks.${weekIndex}.activities.${activityIndex}.free_content`)" severity="error" size="small" variant="simple">
                                                {{ getError(`weeks.${weekIndex}.activities.${activityIndex}.free_content`) }}
                                            </Message>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="form.language_level_id && !levelContents.length"
                                class="mt-3 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ $t('No level contents found for this language level. You can still use free content.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex w-full justify-end pt-4">
                    <Button
                        v-if="canSubmitStudyProgram"
                        :label="$t('Save Changes')"
                        icon="pi pi-save"
                        severity="success"
                        :loading="isLoading"
                        @click="handleSubmit"
                    />
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
import { useApiErrorHandler } from '@/composables/useApiErrorHandler';
import { usePermissions } from '@/composables/usePermissions';
import { createStudyProgramSchema } from '@/schemas/studyProgram';
import axios from 'axios';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';

const { t: $t } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const { handleApiError } = useApiErrorHandler();
const { can } = usePermissions();

const studyProgramSchema = computed(() => createStudyProgramSchema($t));
const studyProgramId = route.params.id || null;
const crudAction = route.meta?.crud || 'read';
const isCreate = computed(() => crudAction === 'create');
const isLoading = ref(false);
const languages = ref([]);
const languageLevels = ref([]);
const levelContents = ref([]);
const errors = ref({});
let localId = 0;

const createActivity = (overrides = {}) => ({
    id: null,
    local_id: `activity-${localId++}`,
    level_content_id: null,
    free_content: '',
    activity_name: '',
    type: 'exercise',
    sort_order: 1,
    ...overrides,
});

const createWeek = (overrides = {}) => ({
    id: null,
    local_id: `week-${localId++}`,
    week_number: 1,
    title: '',
    status: 'active',
    activities: [createActivity()],
    ...overrides,
});

const createFormState = () => ({
    language_id: null,
    language_level_id: null,
    title: '',
    status: 'active',
    weeks: [createWeek()],
});

const form = reactive(createFormState());

const statusOptions = computed(() => ([
    { name: $t('Active'), value: 'active' },
    { name: $t('Disabled'), value: 'disabled' },
]));

const canEditStudyProgram = computed(() =>
    isCreate.value ? can('create study programs') : can('edit study programs')
);
const canEditWeeks = computed(() => isCreate.value && canEditStudyProgram.value);
const canDeleteWeeks = computed(() => isCreate.value && canEditStudyProgram.value);
const canEditActivities = computed(() => isCreate.value && canEditStudyProgram.value);
const canDeleteActivities = computed(() => isCreate.value && canEditStudyProgram.value);
const canAddWeeks = computed(() => isCreate.value ? canEditStudyProgram.value : can('edit study program week'));
const canAddActivities = computed(() => isCreate.value ? canEditStudyProgram.value : can('edit study program week activity'));
const canSubmitStudyProgram = computed(() =>
    canEditStudyProgram.value
);

const activityTypeOptions = computed(() => ([
    { name: $t('Exercise'), value: 'exercise' },
    { name: $t('Video'), value: 'video' },
    { name: $t('Activity'), value: 'activity' },
    { name: $t('Production'), value: 'production' },
]));

const getError = (path) => {
    const value = errors.value?.[path];

    if (Array.isArray(value)) {
        return value.join(', ');
    }

    return value || '';
};

const clearErrors = () => {
    errors.value = {};
};

const addWeek = () => {
    if (!canAddWeeks.value) {
        return;
    }

    if (!isCreate.value) {
        router.push({
            name: 'academics.settings.study-programs.weeks.create',
            params: { studyProgramId },
        });
        return;
    }

    form.weeks.push(createWeek({
        week_number: form.weeks.length + 1,
        title: `${$t('Week')} ${form.weeks.length + 1}`,
    }));
};

const removeWeek = (weekIndex) => {
    if (!canDeleteWeeks.value) {
        return;
    }

    if (form.weeks.length === 1) {
        return;
    }

    form.weeks.splice(weekIndex, 1);
};

const addActivity = (weekIndex) => {
    if (!canAddActivities.value) {
        return;
    }

    const week = form.weeks[weekIndex];

    if (!isCreate.value) {
        if (!week?.id) {
            return;
        }

        router.push({
            name: 'academics.settings.study-programs.activities.create',
            params: { weekId: week.id },
        });
        return;
    }

    const highestSortOrder = week.activities.reduce((maxSortOrder, activity, index) => {
        const currentSortOrder = Number(activity.sort_order || index + 1);
        return Math.max(maxSortOrder, currentSortOrder);
    }, 0);

    week.activities.push(createActivity({
        sort_order: highestSortOrder + 1,
    }));
};

const removeActivity = (weekIndex, activityIndex) => {
    if (!canDeleteActivities.value) {
        return;
    }

    const week = form.weeks[weekIndex];

    if (week.activities.length === 1) {
        return;
    }

    week.activities.splice(activityIndex, 1);
};

const resetForm = (values) => {
    form.language_id = values.language_id;
    form.language_level_id = values.language_level_id;
    form.title = values.title;
    form.status = values.status;
    form.weeks = values.weeks.map((week) => createWeek({
        id: week.id ?? null,
        week_number: week.week_number,
        title: week.title,
        status: week.status,
        activities: week.activities.map((activity, index) => createActivity({
            id: activity.id ?? null,
            level_content_id: activity.level_content_id ?? null,
            free_content: activity.free_content ?? '',
            activity_name: activity.activity_name ?? '',
            type: activity.type ?? 'exercise',
            sort_order: activity.sort_order ?? index + 1,
        })),
    }));
};

const buildPayload = () => {
    const payload = {
        language_level_id: Number(form.language_level_id),
        title: form.title?.trim() || '',
        status: form.status,
    };

    if (isCreate.value) {
        payload.weeks = form.weeks.map((week) => ({
            week_number: Number(week.week_number),
            title: week.title?.trim() || '',
            status: week.status,
            activities: week.activities.map((activity, index) => ({
                level_content_id: activity.level_content_id ? Number(activity.level_content_id) : null,
                free_content: activity.free_content?.trim() || null,
                activity_name: activity.activity_name?.trim() || '',
                type: activity.type,
                sort_order: Number(activity.sort_order || index + 1),
            })),
        }));
    }

    return payload;
};

const buildValidationPayload = () => ({
    language_id: Number(form.language_id || 0),
    ...buildPayload(),
    weeks: form.weeks.map((week) => ({
        week_number: Number(week.week_number),
        title: week.title?.trim() || '',
        status: week.status,
        activities: week.activities.map((activity, index) => ({
            level_content_id: activity.level_content_id ? Number(activity.level_content_id) : null,
            free_content: activity.free_content?.trim() || null,
            activity_name: activity.activity_name?.trim() || '',
            type: activity.type,
            sort_order: Number(activity.sort_order || index + 1),
        })),
    })),
});

const mapZodErrors = (issues) => {
    const mappedErrors = {};

    issues.forEach((issue) => {
        const path = issue.path.join('.');
        mappedErrors[path || 'general'] = issue.message;
    });

    return mappedErrors;
};

const fetchLanguages = async () => {
    try {
        const response = await axios.post('/lists/languages');
        languages.value = response.data || [];
    } catch (error) {
        languages.value = [];
        console.error('Error fetching languages:', error);
    }
};

const fetchLanguageLevels = async (languageId) => {
    if (!languageId) {
        languageLevels.value = [];
        return;
    }

    try {
        const response = await axios.post('/lists/language-levels', {
            language_id: languageId,
        });
        languageLevels.value = response.data || [];
    } catch (error) {
        languageLevels.value = [];
        console.error('Error fetching language levels:', error);
    }
};

const fetchLevelContents = async (languageLevelId) => {
    if (!languageLevelId) {
        levelContents.value = [];
        return;
    }

    try {
        const response = await axios.get('/academics/settings/level-contents', {
            params: {
                per_page: 500,
                filters: JSON.stringify([
                    {
                        field: 'language_level_id',
                        operator: '=',
                        value: languageLevelId,
                    },
                ]),
            },
        });

        levelContents.value = response.data?.data?.level_contents || [];
    } catch (error) {
        levelContents.value = [];
        console.error('Error fetching level contents:', error);
    }
};

const fetchStudyProgramData = async () => {
    if (isCreate.value) {
        return;
    }

    try {
        const response = await axios.post(`/academics/settings/study-programs/${studyProgramId}/data`);
        const studyProgram = response.data?.data?.study_program || {};
        const languageId = studyProgram?.language_level?.language?.id || null;

        resetForm({
            language_id: languageId,
            language_level_id: studyProgram.language_level_id || null,
            title: studyProgram.title || '',
            status: studyProgram.status || 'active',
            weeks: studyProgram.weeks || [createWeek()],
        });

        await fetchLanguageLevels(languageId);
        await fetchLevelContents(studyProgram.language_level_id);
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: $t('Failed to fetch study program data.'),
            life: 5000,
        });
    }
};

const onLanguageChange = async (languageId) => {
    form.language_id = languageId;
    form.language_level_id = null;
    levelContents.value = [];
    await fetchLanguageLevels(languageId);
};

const onLanguageLevelChange = async (languageLevelId) => {
    form.language_level_id = languageLevelId;
    await fetchLevelContents(languageLevelId);

    if (isCreate.value && !form.title) {
        const selectedLanguage = languages.value.find((language) => language.id === form.language_id);
        const selectedLevel = languageLevels.value.find((level) => level.id === languageLevelId);

        if (selectedLanguage && selectedLevel) {
            form.title = `${selectedLanguage.name} ${selectedLevel.name} ${$t('Study Program')}`;
        }
    }
};

const handleSubmit = async () => {
    clearErrors();

    const validation = studyProgramSchema.value.safeParse(buildValidationPayload());

    if (!validation.success) {
        errors.value = mapZodErrors(validation.error.issues);
        return;
    }

    isLoading.value = true;

    try {
        const payload = buildPayload();
        const url = isCreate.value
            ? '/academics/settings/study-programs'
            : `/academics/settings/study-programs/${studyProgramId}/edit`;

        await axios.post(url, payload);

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: isCreate.value
                ? $t('Study program created successfully.')
                : $t('Study program updated successfully.'),
            life: 5000,
        });

        router.push({ name: 'academics.settings.study-programs.list' });
    } catch (error) {
        const apiError = handleApiError(error);

        if (apiError.type === 'validation') {
            errors.value = apiError.errors || {};
        } else {
            errors.value = {
                general: apiError.message || $t('An unexpected error occurred. Please try again.'),
            };
        }
    } finally {
        isLoading.value = false;
    }
};

onMounted(async () => {
    await fetchLanguages();
    await fetchStudyProgramData();
});
</script>
