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
                    <!-- Language Name -->
                    <div class="flex flex-col w-full md:w-1/2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Language Name') }} <span class="text-red-500">*</span>
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
import { createLanguageSchema } from '@/schemas/language';
import { useToast } from 'primevue/usetoast';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler'
import { useFormSubmitter } from '@/composables/useFormSubmitter'
import { useFormValues } from '@/composables/useFormValues';
import { Form } from '@primevue/forms';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import axios from 'axios';
import PageContainer from '@/components/layout/pages/PageContainer.vue';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import SubmitButton from '@/components/form/SubmitButton.vue';

const { t : $t } = useI18n();
const { handleApiError } = useApiErrorHandler();
const { extractFormData } = useFormValues();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const languageSchema = computed(() => createLanguageSchema($t));
const resolver = zodResolver(languageSchema.value);
const languageId = route.params.id || null;
const formKey = ref(0);
const crudAction = route.meta?.crud || 'read';

const languageData = ref(null);

const { errors, isLoading, setErrors, clearErrors } = useFormSubmitter({
    name: '',
});

const initialValues = computed(() => {
    if (crudAction === 'create') {
        
        return {
            name: '',
        };
    }

    return {
        name: languageData.value?.name || '',
    }
});

watch(languageData, (newData) => {
    if (newData) {
        formKey.value++; // Forces form to re-initialize
    }
});

const fetchLanguageData = async () => {
    try {
        const response = await axios.post(`/settings/languages/${languageId}/data`);
        languageData.value = response.data.data.language || response.data.language || {};
    } catch (error) {
        console.error('Error fetching language data:', error);
        languageData.value = null;
        toast.add({ 
            severity: 'error', 
            summary: $t('Error'), 
            detail: $t('Failed to load language data. Please try again.'),
            life: 5000 
        });
    }
};

const handleSubmit = async (formData) => {
    
    const { valid, values } = extractFormData(formData);
    
    if (!valid) {
        isLoading.value = false;
        return;
    }
    
    isLoading.value = true;
    
    try {
        let response;
        let url = crudAction === 'create' ? '/settings/languages' : `/settings/languages/${languageId}/edit`;
        response = await axios.post(url, values);

        toast.add({ 
            severity: 'success', 
            summary: $t('Success'), 
            detail: crudAction === 'create' ? $t('Language created successfully.') : $t('Language updated successfully.'),
            life: 5000 
        });

        router.push({ name: 'settings.languages.list' });

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
    } finally {
        isLoading.value = false;
    }
};

onMounted (async () => {
    if (crudAction === 'edit') {
        await fetchLanguageData();
    }
});
</script>