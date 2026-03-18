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
                <div class="flex flex-col w-full space-y-4">
                    <!-- Mobile header -->
                    <div class="flex flex-col md:hidden w-full space-y-2">
                        <div class="flex w-full justify-end">
                            <Button
                                type="button"
                                icon="pi pi-arrow-left"
                                :label="$t('Back to class records')"
                                severity="primary"
                                @click="goBack"
                            />
                        </div>
                        <div class="flex flex-row items-baseline space-x-2 justify-start">
                            <span class="text-xs uppercase tracking-wide text-gray-500">
                                {{ $t('Class Record') }}:
                            </span>
                            <h1 class="text-sm font-semibold text-gray-800 dark:text-gray-100 overflow-clip text-ellipsis">
                                {{ recordInfo.teacher }} — {{ recordInfo.course }} {{ recordInfo.date }}
                            </h1>
                        </div>
                    </div>

                    <!-- Desktop header -->
                    <div class="hidden md:flex items-center justify-between">
                        <div class="flex flex-col md:flex-row space-y-2 space-x-0 md:space-y-0 md:space-x-6">
                            <div class="flex items-baseline space-x-2">
                                <span class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ $t('Teacher') }}:
                                </span>
                                <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                    {{ recordInfo.teacher }}
                                </h1>
                            </div>
                            <div class="flex items-baseline space-x-2">
                                <span class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ $t('Course') }}:
                                </span>
                                <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                    {{ recordInfo.course }}
                                </h1>
                            </div>
                            <div class="flex items-baseline space-x-2">
                                <span class="text-xs uppercase tracking-wide text-gray-500">
                                    {{ $t('Date') }}:
                                </span>
                                <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                    {{ recordInfo.date }}
                                </h1>
                            </div>
                        </div>
                        <Button
                            type="button"
                            icon="pi pi-arrow-left"
                            :label="$t('Back to class records')"
                            severity="primary"
                            @click="goBack"
                        />
                    </div>

                    <Message v-if="loadError" severity="error">{{ loadError }}</Message>

                    <ProgressSpinner v-if="detailLoading" class="flex justify-center" />

                    <template v-else-if="!loadError">
                        <div class="flex flex-col md:flex-row w-full gap-4">
                            <div class="flex flex-col w-full md:w-1/2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Content') }}
                                </label>
                                <Select
                                    name="content_id"
                                    :options="levelContents"
                                    optionLabel="name"
                                    optionValue="id"
                                    :placeholder="$t('Select content')"
                                    class="w-full"
                                    showClear
                                />
                            </div>
                            <div class="flex flex-col w-full md:w-1/2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $t('Free Content') }}
                                </label>
                                <InputText
                                    name="free_content"
                                    class="w-full"
                                    :placeholder="$t('Custom content name')"
                                />
                            </div>
                        </div>

                        <div class="flex flex-col w-full">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Activity') }} <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                name="activity"
                                class="w-full"
                                :placeholder="$t('Activity description')"
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

                        <div class="flex flex-col w-full">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Links') }}
                            </label>
                            <InputText
                                name="links"
                                class="w-full"
                                :placeholder="$t('http://...')"
                            />
                        </div>

                        <div class="flex flex-col w-full">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Attachment') }}
                            </label>
                            <div
                                v-if="existingAttachment.name"
                                class="flex items-center gap-2 mb-2 text-sm text-gray-600 dark:text-gray-400"
                            >
                                <i class="pi pi-paperclip" />
                                <a
                                    :href="existingAttachment.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-blue-600 dark:text-blue-400 hover:underline"
                                >{{ existingAttachment.name }}</a>
                                <span class="text-gray-400 text-xs">({{ $t('will be replaced if new file selected') }})</span>
                            </div>
                            <input
                                type="file"
                                ref="fileInput"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
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
                    </template>
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
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Message from 'primevue/message';
import Button from 'primevue/button';
import SubmitButton from '@/components/form/SubmitButton.vue';
import ProgressSpinner from 'primevue/progressspinner';
import { useFormValues } from '@/composables/useFormValues';
import { useFormSubmitter } from '@/composables/useFormSubmitter';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler';
import { useToast } from 'primevue/usetoast';

const { t: $t } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const { extractFormData } = useFormValues();
const { handleApiError } = useApiErrorHandler();

const detailId = computed(() => route.params.detailId);

const detailData = ref({
    content_id: null,
    free_content: '',
    activity: '',
    links: '',
});

const recordInfo = ref({ teacher: '', course: '', date: '' });
const levelContents = ref([]);
const existingAttachment = ref({ url: '', name: '' });
const detailLoading = ref(true);
const loadError = ref('');
const formKey = ref(0);
const fileInput = ref(null);

const detailSchema = computed(() => z.object({
    content_id: z.number().nullable().optional(),
    free_content: z.string().max(1000, { message: $t('Free content is too long.') }).optional().or(z.literal('')),
    activity: z.string().min(1, { message: $t('Activity is required.') }).max(1000, { message: $t('Activity is too long.') }),
    links: z.string().max(2000, { message: $t('Links field is too long.') }).optional().or(z.literal('')),
}));

const resolver = computed(() => zodResolver(detailSchema.value));

const initialValues = computed(() => ({
    content_id: detailData.value.content_id,
    free_content: detailData.value.free_content || '',
    activity: detailData.value.activity || '',
    links: detailData.value.links || '',
}));

const { errors, isLoading, setErrors } = useFormSubmitter({
    content_id: null,
    free_content: '',
    activity: '',
    links: '',
    general: '',
});

const fetchDetailData = async () => {
    loadError.value = '';
    detailLoading.value = true;

    try {
        const { data } = await axios.post(`/academics/lessons/class-records/details/${detailId.value}/data`);
        const payload = data?.data || {};

        const detail = payload.detail || {};
        detailData.value = {
            content_id: detail.content_id ?? null,
            free_content: detail.free_content || '',
            activity: detail.activity || '',
            links: detail.links || '',
        };
        existingAttachment.value = {
            url: detail.attachment_url || '',
            name: detail.attachment_name || '',
        };
        levelContents.value = payload.level_contents || [];
        const info = payload.record_info || {};
        recordInfo.value = {
            teacher: info.teacher || '',
            course: info.course || '',
            date: info.date || '',
        };
        formKey.value += 1;
    } catch (error) {
        const apiError = handleApiError(error);
        loadError.value = apiError?.message || $t('Unable to load class record detail.');
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

    const payload = new FormData();
    payload.append('activity', values.activity || '');
    if (values.content_id != null) {
        payload.append('content_id', values.content_id);
    }
    if (values.free_content) {
        payload.append('free_content', values.free_content);
    }
    if (values.links) {
        payload.append('links', values.links);
    }
    if (fileInput.value?.files?.[0]) {
        payload.append('attachment', fileInput.value.files[0]);
    }

    isLoading.value = true;

    try {
        await axios.post(`/academics/lessons/class-records/details/${detailId.value}/edit`, payload, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: $t('Class record detail updated successfully.'),
            life: 4000,
        });
        router.push({ name: 'academics.classes.class-records.list' });
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
    router.push({ name: 'academics.classes.class-records.list' });
};

onMounted(() => {
    fetchDetailData();
});
</script>
