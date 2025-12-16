<template>
    <Form
        :key="formKey"
        v-slot="$form"
        :resolver="resolver"
        :initial-values="initialValues"
        @submit="handleSubmit"
        :validateOnBlur="true"
    >
        <ProfilePage 
            :form="$form" 
            :creating="creating" 
            :isPersonProfile="false"
            :errors="errors"
        >
            <template #model>
                <div class="flex-col md:flex md:flex-row space-y-2 md:space-x-2">
                    <div class="flex flex-col w-full md:w-5/6">
                        <label for="courses" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Courses') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <MultiSelect 
                            id="courses"
                            name="courses"
                            :options="coursesOptions"
                            option-label="name"
                            option-value="id"
                            :placeholder="$t('Select courses')"
                            filter
                            :filter-placeholder="$t('Search courses')"
                            :show-clear="false"
                            :loading="coursesLoading"
                            @filter="onCoursesFilter"
                            class="w-full"
                            display="chip"
                            :max-selected-label="3"
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
                        </MultiSelect>
                        <Message
                            v-if="$form.courses?.invalid"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ $form.courses.error?.message }}
                        </Message>
                        <Message
                            v-if="errors?.courses"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ Array.isArray(errors?.courses) ? errors?.courses.join(', ') : errors?.courses }}
                        </Message>
                    </div>

                    <!-- Status -->
                    <div class="flex flex-col w-full md:w-1/4">
                        <label for="active" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Status') }} <span class="text-red-500">*</span>
                        </label>
                        <ToggleSwitch
                            id="isActive"
                            name="isActive"
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
                <!-- Submit button-->
                <div class="flex justify-end mt-4">
                    <SubmitButton :isLoading="isLoading"/>
                </div>
            </template>
        </ProfilePage>
    </Form>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { createStudentSchema } from '@/schemas/student';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler'
import { useFormValues } from '@/composables/useFormValues';
import { useFormSubmitter } from '@/composables/useFormSubmitter'
import { Form } from '@primevue/forms';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import { useToast } from 'primevue/usetoast';
import ProfilePage from '@/Pages/settings/profiles/ProfilePage.vue';
import ProgressSpinner from 'primevue/progressspinner';
import ToggleSwitch from 'primevue/toggleswitch';
import MultiSelect from 'primevue/multiselect';
import Message from 'primevue/message';
import axios from 'axios';
import SubmitButton from '@/components/form/SubmitButton.vue';

const { locale, t: $t } = useI18n();
const { handleApiError } = useApiErrorHandler();
const { extractFormData } = useFormValues();
const studentSchema = computed(() => createStudentSchema($t, locale.value));
const resolver = zodResolver(studentSchema.value);
const route = useRoute();
const router = useRouter();
const toast = useToast();


const statusList = ref([]);
const coursesOptions = ref([]);
const coursesLoading = ref(false);
const studentData = ref(null);
const formKey = ref(0);
let coursesDebounceTimer = null;
const crudAction = route.meta?.crud || 'read';
const creating = crudAction === 'create';

const studentId = route.params.id;


watch(studentData, (newData) => {
    if (newData) {
        formKey.value++; // Forces form to re-initialize
    }
});

const initialValues = computed(() => {
    if (crudAction === 'create') {
        return {
            personal_id: '',
            first_name: '',
            last_name: '',
            address: '',
            phone: '',
            email: '',
            birth_date: '',
            courses: [],
            display_courses: [],
            status: '',
            isActive: true,
        };
    }

    const data = studentData.value || {};
    let isActive = studentData.value?.status === 'active' ? true : false;
    return {
        personal_id: data.personal_id || '',
        first_name: data.first_name || '',
        last_name: data.last_name || '',
        address: data.address || '',
        phone: data.phone || '',
        email: data.email || '',
        birth_date: data.birth_date || '',
        name: data.name || '',
        password: data.password || '',
        courses: data.courses || [],
        status: data.status || '',
        isActive: isActive || false,
    };
});


const { errors, isLoading, setErrors, clearErrors } = useFormSubmitter({
    personal_id: '',
    first_name: '', 
    last_name: '',
    address: '',
    phone: '',
    email: '',
    birth_date: '',
    courses: '',
    payment_receipt: '',
    isActive: '',
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

const fetchStudentData = async () => {
    try {
        const response = await axios.post(`/academics/settings/students/${studentId}/data`);
        studentData.value = response.data.data.student || response.data.student || {};
    } catch (error) {
        console.error('Error fetching student data:', error);
        studentData.value = null;
        toast.add({ 
            severity: 'error', 
            summary: $t('Error'), 
            detail: $t('Failed to load student data. Please try again.'),
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
    
    // Add type field to values to pass reusabeable validation rules
    values.type = 'person'; // user is always a person
    values.status = values.isActive ? 'active' : 'disabled';
    delete values.isActive;

    if (!valid) {
        return;
    }

    isLoading.value = true;

    try {
        let url = crudAction === 'create' 
            ? '/academics/settings/students' 
            : `/academics/settings/students/${studentId}/edit`;
            
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
        statusList.value = await axios.post('/lists/status').then(response => {
            return response.data;
        });

        await fetchCourses();
        
        if (!creating) {    
            await fetchStudentData();
        }
        
    } catch (error) {
        console.error('Error during component mount:', error);
    }
});
</script>