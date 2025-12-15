<template>
    <Form
        :key="formKey"
        v-slot="$form"
        :resolver="resolver"
        :initialValues="initialValues"
        @submit="handleSubmit"
        :validateOnBlur="true"
    >
        <PageContainer>
            <template #body>
                <div class="flex flex-col w-full space-y-4">
                    <div class="flex flex-col md:flex-row w-full space-y-4 md:space-x-2">
                        <!-- Course name -->
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Course Name') }} <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="name"
                                name="name"
                                type="text"
                                fluid
                            />
                            <!-- Client-side error  -->
                            <Message
                                v-if="$form.name?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.name.error?.message }}
                            </Message>
                            <!-- Server-side error -->
                            <Message
                                v-if="errors?.name"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ errors?.name }}
                            </Message>
                        </div>
                        <!-- Language Level Language id-->
                        <div class="flex flex-col w-full md:w-1/3">
                            <label for="language_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Language') }} <span class="text-red-500">*</span>
                            </label>
                            <Select
                                id="language_id"
                                name="language_id"
                                :options="languages"
                                option-label="name"
                                option-value="id"
                                :placeholder="$t('Select a Language')"
                                filter
                                class="w-full"
                                @update:model-value="fetchLanguageLevels"
                            />
                            <!-- Client-side error  -->
                            <Message
                                v-if="$form.language_id?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.language_id.error?.message }}
                            </Message>
                            <!-- Server-side error -->
                            <Message
                                v-if="errors?.language_id"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ errors?.language_id }}
                            </Message>
                        </div>
                        <!-- Language Level Language id-->
                        <div class="flex flex-col w-full md:w-1/3">
                            <label for="language_level_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Level') }} <span class="text-red-500">*</span>
                            </label>
                            <Select
                                id="language_level_id"
                                name="language_level_id"
                                :options="languageLevels"
                                option-label="name"
                                option-value="id"
                                :placeholder="$t('Select a Language Level')"
                                filter
                                class="w-full"
                            />
                            <!-- Client-side error  -->
                            <Message
                                v-if="$form.language_level_id?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.language_level_id.error?.message }}
                            </Message>
                            <!-- Server-side error -->
                            <Message
                                v-if="errors?.language_level_id"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ errors?.language_level_id }}
                            </Message>
                        </div>
                        
                    </div>
                    <div class="flex flex-col md:flex-row w-full space-y-4 md:space-x-2">
                        <!-- Chat room -->
                        <div class="flex flex-col w-full md:w-1/3">
                            <label for="chat_room_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Chat Room Link') }} 
                            </label>
                            <InputText
                                id="chat_room_link"
                                name="chat_room_link"
                                type="text"
                                fluid
                            />
                            <!-- Client-side error  -->
                            <Message
                                v-if="$form.chat_room_link?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.chat_room_link.error?.message }}
                            </Message>
                            <!-- Server-side error -->
                            <Message
                                v-if="errors?.chat_room_link"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ errors?.chat_room_link }}
                            </Message>
                        </div>
                        <!-- Status -->
                        <div class="flex flex-col w-full md:w-1/4">
                            <label for="active" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Status') }} <span class="text-red-500">*</span>
                            </label>
                            <ToggleSwitch
                                id="active"
                                name="active"
                            >
                                <template #handle="{ checked }">
                                    <i :class="['text-xs! pi', { 'pi-check-circle': checked, 'pi-times-circle': !checked }]" />
                                </template>
                            </ToggleSwitch>
                            <!-- Client-side error  -->
                            <Message
                                v-if="$form.status?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.status.error?.message }}
                            </Message>
                            <!-- Server-side error -->
                            <Message
                                v-if="errors?.status"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ errors?.status }}
                            </Message>
                        </div>
                    </div>
                    
                </div>
                <!-- Submit button-->
                <div class="flex w-full justify-end mt-4">
                    <SubmitButton :isLoading="isLoading"/>
                </div>  
            </template>
        </PageContainer>
    </Form>
</template>
<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { createCourseSchema } from '@/schemas/course';
import { useToast } from 'primevue/usetoast';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler'
import { useFormSubmitter } from '@/composables/useFormSubmitter'
import { useFormValues } from '@/composables/useFormValues';
import { Form } from '@primevue/forms';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import axios from 'axios';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Message from 'primevue/message';
import ToggleSwitch from 'primevue/toggleswitch';
import SubmitButton from '@/components/form/SubmitButton.vue';

const { t : $t } = useI18n();
const { handleApiError } = useApiErrorHandler();
const { extractFormData } = useFormValues();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const courseSchema = computed(() => createCourseSchema($t));
const resolver = zodResolver(courseSchema.value);
const courseId = route.params.id || null;
const formKey = ref(0);
const crudAction = route.meta?.crud || 'read';
const courseData = ref(null); 
const languages = ref([]);
const languageLevels = ref([]);
const initialValues = computed(() => {
    if (crudAction === 'create') {
        return {
            name: '',
            language_id: null,
            language_level_id: null,
            chat_room_link: '',
            status: 'active',
            active: true,
        };
    }

    let active = courseData.value?.status === 'active' ? true : false;
    return {
        name: courseData.value?.name || '',
        language_id: courseData.value?.language_id || null,
        language_level_id: courseData.value?.language_level_id || null,
        chat_room_link: courseData.value?.chat_room_link || '',
        status: courseData.value?.status || 'active',
        active: active,
    };
});

watch(courseData, (newData) => {
    if (newData) {
        formKey.value++; // Forces form to re-initialize
    }
});

const { errors, isLoading, setErrors, clearErrors } = useFormSubmitter({
    description: '',
    level: '',
    language_id: null,
    status: '',
    active: true,
});

const fetchCourseData = async () => {
    try {
        const response = await axios.post(`/academics/settings/courses/${courseId}/data`);
        courseData.value = response.data.data.course || response.data.course || {};
    } catch (error) {
        console.error('Error fetching course data:', error);
        courseData.value = null;
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: $t('Failed to fetch course data.'),
            life: 5000,
        });
    }
};

const handleSubmit = async (formData) => {
    const { valid, values } = extractFormData(formData);

    if (!valid) {
        return;
    }

    isLoading.value = true;

    values.status = values?.active ? 'active' : 'disabled';
    delete values?.active;
    try {
        let response;
        let url = crudAction === 'create' 
            ? '/academics/settings/courses' 
            : `/academics/settings/courses/${courseId}/edit`;
        response = await axios.post(url, values);

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: crudAction === 'create' ? $t('Course created successfully.') : $t('Course updated successfully.'),
            life: 5000,
        });

        router.push({ name: 'academics.settings.courses.list' });
    } catch (error) {
        const apiError = handleApiError(error);
        if (apiError.isValidationError) {
            setErrors(apiError.validationErrors);
        } else {
            toast.add({
                severity: 'error',
                summary: $t('Error'),
                detail: apiError.message || $t('An unexpected error occurred. Please try again.'),
                life: 5000,
            });
        }
    } finally {
        isLoading.value = false;
    }
};

onMounted(async () => {
    await fetchLanguages();
    // await fetchLanguageLevels();
    if (crudAction === 'edit') {
        await fetchCourseData();
        await fetchLanguageLevels(courseData.value?.language_id);
    }
});
const fetchLanguages = async () => {
    try {
        const response = await axios.post('/lists/languages');
        languages.value = response.data || [];
    } catch (error) {
        console.error('Error fetching languages:', error);
        languages.value = [];
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: $t('Failed to load languages. Please try again.'),
            life: 5000,
        });
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
        console.error('Error fetching language levels:', error);
        languageLevels.value = [];
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: $t('Failed to load language levels. Please try again.'),
            life: 5000,
        });
    }
};
</script>