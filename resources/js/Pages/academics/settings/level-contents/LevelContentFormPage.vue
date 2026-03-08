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
                        <!-- Content -->
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Content') }} 
                            </label>
                            <InputText
                                id="content"
                                name="content"
                                type="text"
                                fluid
                            />
                            <!-- Client-side error  -->
                            <Message
                                v-if="$form.content?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.content.error?.message }}
                            </Message>
                            <!-- Server-side error -->
                            <Message
                                v-if="errors?.content"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ errors?.content }}
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
import { createLevelContentSchema } from '@/schemas/levelContent.js';
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
import SubmitButton from '@/components/form/SubmitButton.vue';

const { t : $t } = useI18n();
const { handleApiError } = useApiErrorHandler();
const { extractFormData } = useFormValues();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const levelContentSchema = computed(() => createLevelContentSchema($t));
const resolver = zodResolver(levelContentSchema.value);
const levelContentId = route.params.id || null;
const formKey = ref(0);
const crudAction = route.meta?.crud || 'read';
const levelContentData = ref(null);
const languages = ref([]); 
const languageLevels = ref([]);
const initialValues = computed(() => {
    if (crudAction === 'create') {
        return {
            language_id: null,
            language_level_id: null,
            content: '',
        };
    }

    return {
        language_id: levelContentData.value?.language_level?.language?.id || null,
        language_level_id: levelContentData.value?.language_level_id || null,
        content: levelContentData.value?.content || '',
    };
});

watch(levelContentData, (newData) => {
    if (newData) {
        formKey.value++; // Forces form to re-initialize
    }
});

const { errors, isLoading, setErrors, clearErrors } = useFormSubmitter({
    language_id: null,
    language_level_id: null,
    content: '',
}); 


const fetchLevelContentData = async () => {
    try {
        const response = await axios.post(`/academics/settings/level-contents/${levelContentId}/data`);
        levelContentData.value = response.data.data.level_content || response.data.level_content || {};
    } catch (error) {
        console.error('Error fetching level content data:', error);
        levelContentData.value = null;
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: $t('Failed to fetch level content data.'),
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

    try {
        let response;
        let url = crudAction === 'create' 
            ? '/academics/settings/level-contents' 
            : `/academics/settings/level-contents/${levelContentId}/edit`;
        response = await axios.post(url, values);

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: crudAction === 'create' ? $t('Level content created successfully.') : $t('Level content updated successfully.'),
            life: 5000,
        });

        router.push({ name: 'academics.settings.level-contents.list' });
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
    if (crudAction === 'edit') {
        await fetchLevelContentData();
        await fetchLanguageLevels(levelContentData.value?.language_level?.language?.id);
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