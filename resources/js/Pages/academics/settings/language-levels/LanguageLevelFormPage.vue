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
                    <div class="flex w-full space-x-2">
                        <!-- Language Level Description -->
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Language Level') }} <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="description"
                                name="description"
                                type="text"
                                fluid
                            />
                            <!-- Client-side error  -->
                            <Message
                                v-if="$form.description?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.description.error?.message }}
                            </Message>
                            <!-- Server-side error -->
                            <Message
                                v-if="errors?.description"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ errors?.description }}
                            </Message>
                        </div>
                        <!-- Language Level level -->
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Level') }} 
                            </label>
                            <InputText
                                id="level"
                                name="level"
                                type="text"
                                fluid
                            />
                            <!-- Client-side error  -->
                            <Message
                                v-if="$form.level?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ $form.level.error?.message }}
                            </Message>
                            <!-- Server-side error -->
                            <Message
                                v-if="errors?.level"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ errors?.level }}
                            </Message>
                        </div>
                        <!-- Language Level Language id-->
                        <div class="flex flex-col w-full md:w-1/2">
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
                        <!-- Status -->
                        <div class="flex flex-col w-full md:w-1/2">
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
import { createLanguageLevelSchema } from '@/schemas/languageLevel';
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
const languageLevelSchema = computed(() => createLanguageLevelSchema($t));
const resolver = zodResolver(languageLevelSchema.value);
const languageLevelId = route.params.id || null;
const formKey = ref(0);
const crudAction = route.meta?.crud || 'read';
const languageLevelData = ref(null); 
const languages = ref([]);
const statusList = ref([]);
const initialValues = computed(() => {
    if (crudAction === 'create') {
        return {
            description: '',
            level: '',
            language_id: null,
            status: 'active',
            active: true,
        };
    }

    let active = languageLevelData.value?.status === 'active' ? true : false;
    return {
        description: languageLevelData.value?.description || '',
        level: languageLevelData.value?.level || '',
        language_id: languageLevelData.value?.language_id || null,
        status: languageLevelData.value?.status || 'active',
        active: active,
    };
});

watch(languageLevelData, (newData) => {
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

const fetchLanguageLevelData = async () => {
    try {
        const response = await axios.post(`/academics/settings/language-levels/${languageLevelId}/data`);
        languageLevelData.value = response.data.data.language_level || response.data.language_level || {};
    } catch (error) {
        console.error('Error fetching language level data:', error);
        languageLevelData.value = null;
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: $t('Failed to fetch language level data.'),
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

    console.log('crudAction', crudAction);
    console.log('values', values);
    values.status = values?.active ? 'active' : 'disabled';
    delete values?.active;
    console.log('values before submit', values);
    try {
        let response;
        let url = crudAction === 'create' 
            ? '/academics/settings/language-levels' 
            : `/academics/settings/language-levels/${languageLevelId}/edit`;
        response = await axios.post(url, values);

        toast.add({
            severity: 'success',
            summary: $t('Success'),
            detail: crudAction === 'create' ? $t('Language level created successfully.') : $t('Language level updated successfully.'),
            life: 5000,
        });

        router.push({ name: 'academics.settings.language-levels.list' });
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
    await fetchStatusList();
    if (crudAction === 'edit') {
        await fetchLanguageLevelData();
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

const fetchStatusList = async () => {
    try {
        const response = await axios.post('/lists/status');
        
        let availableOptions = response.data.filter(option => option.value === 'active' || option.value === 'disabled');
        statusList.value = availableOptions || [];
    } catch (error) {
        console.error('Error fetching status list:', error);
        statusList.value = [];
        toast.add({
            severity: 'error',
            summary: $t('Error'),
            detail: $t('Failed to load status list. Please try again.'),
            life: 5000,
        });
    }
};
</script>