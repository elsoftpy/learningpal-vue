<template>
    <Form
        v-slot="$form"
        :resolver="resolver"
        :initial-values="initialValues"
        @submit="handleSubmit"
        :validateOnBlur="true"
    >
        <ProfilePage 
            :form="$form" 
            :creating="creating" 
            :isPersonProfile="true"
            :errors="errors"
        >
            <template #model>
                <div class="flex-col md:flex md:flex-row space-y-2 md:space-x-2">
                    <div class="flex flex-col w-full md:w-1/6">
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Username') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            id="name"
                            name="name"
                            :placeholder="$t('Username')"
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
                            {{ errors?.name }}
                        </Message>
                    </div>
                    
                    <div class="flex flex-col w-full md:w-1/6">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Password') }}
                        </label>
                        <Password
                            id="password"
                            name="password"
                            :placeholder="$t('Password')"
                        />
                        <Message
                            v-if="$form.password?.invalid"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ $form.password.error?.message }}
                        </Message>
                        <Message
                            v-if="errors?.password"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ errors?.password }}
                        </Message>
                    </div>

                    <div class="flex flex-col w-full md:w-2/6">
                        <label for="roles" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Roles') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <MultiSelect 
                            id="roles"
                            name="roles"
                            :options="rolesOptions"
                            option-label="name"
                            option-value="id"
                            :placeholder="$t('Select roles')"
                            filter
                            :filter-placeholder="$t('Search roles...')"
                            :show-clear="false"
                            :loading="rolesLoading"
                            @filter="onRolesFilter"
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
                            v-if="$form.roles?.invalid"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ $form.roles.error?.message }}
                        </Message>
                        <Message
                            v-if="errors?.roles"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ Array.isArray(errors?.roles) ? errors?.roles.join(', ') : errors?.roles }}
                        </Message>
                    </div>

                    <div class="flex flex-col w-full md:w-1/6">
                        <label for="payment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Payment') }}
                        </label>
                        <InputText
                            id="payment"
                            name="payment"
                            :placeholder="$t('Payment')"
                        />
                    </div>

                    <div class="flex flex-col w-full md:w-1/6">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Status') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <Select
                            id="status"
                            name="status"
                            :options="statusList"
                            option-label="name"
                            option-value="value"
                            :placeholder="$t('Select Status')"
                            :label="$t('Status')"
                            class="w-full"
                        />
                    </div>
                </div>
                <!-- Submit button-->
                <div class="flex justify-end mt-4">
                    <Button
                        type="submit"
                        :loading="loading"
                        :label="$t('Save Changes')"
                        icon="pi pi-save"
                        severity="success"
                    />
                </div>
            </template>
        </ProfilePage>
    </Form>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { createUserSchema } from '@/schemas/user';
import { useApiErrorHandler } from '@/composable/useApiErrorHandler'
import { useAuthForm } from '@/composable/useAuthForm'
import { Form } from '@primevue/forms';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import { useToast } from 'primevue/usetoast';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import ProfilePage from '../profiles/ProfilePage.vue';
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';
import Select from 'primevue/select';
import MultiSelect from 'primevue/multiselect';
import Message from 'primevue/message';
import axios from 'axios';

const { locale, t: $t } = useI18n();
const { handleApiError } = useApiErrorHandler();
const userSchema = computed(() => createUserSchema($t, locale.value));
const resolver = zodResolver(userSchema.value);
const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const toast = useToast();


const statusList = ref([]);
const rolesOptions = ref([]);
const rolesLoading = ref(false);
let rolesDebounceTimer = null;

const crudAction = route.meta?.crud || 'read';
const creating = crudAction === 'create';

const userId = route.meta.crud === 'edit.auth-user' ? auth.user.id : route.params.id;

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
            avatar: '',
            name: '',
            password: '',
            roles: [],
            payment: '',
            status: '',
        };
    }

    if (crudAction === 'edit.auth-user') {
        const userRoles = auth.user?.roles || [];
        const roleIds = Array.isArray(userRoles) 
            ? userRoles.map(role => typeof role === 'object' ? role.id : role) 
            : [];

        return {
            personal_id: auth.user?.personal_id || '',
            first_name: auth.user?.first_name || '',
            last_name: auth.user?.last_name || '',
            address: auth.user?.address || '',
            phone: auth.user?.phone || '',
            email: auth.user?.email || '',
            birth_date: auth.user?.birth_date || '',
            avatar: auth.user?.avatar || '',
            name: auth.user?.name || '',
            password: auth.user?.password || '',
            roles: roleIds,
            payment: auth.user?.payment || '',
            status: auth.user?.status || '',
        };
    }
    return {};
});


const { errors, loading, setErrors, clearErrors } = useAuthForm({
    personal_id: '',
    first_name: '', 
    last_name: '',
    address: '',
    phone: '',
    email: '',
    birth_date: '',
    avatar: '',
    name: '',
    password: '',
    roles: '',
    payment: '',
    status: '',
});

const fetchRoles = async (query = '') => {
    rolesLoading.value = true;
    try {
        const params = query ? { search: query } : {};
        const response = await axios.post('/lists/roles', { params });
        rolesOptions.value = response.data.data || response.data || [];

    } catch (error) {
        console.error('Error fetching roles:', error);
        rolesOptions.value = [];
    } finally {
        rolesLoading.value = false;
    }
};

const onRolesFilter = (event) => {
    clearTimeout(rolesDebounceTimer);
    rolesDebounceTimer = setTimeout(() => {
        fetchRoles(event.value);
    }, 300);
};

const handleSubmit =  async (formState) => {
    errors.value = {};

    const { valid } = formState;

    console.log('Form states:', formState.states);
    console.log('Roles state:', formState.states.roles);

    const values = Object.keys(formState.states).reduce((acc, key) => {
        acc[key] = formState.states[key].value;
        return acc;
    }, {});

    console.log('Extracted values:', values);
    console.log('Roles value:', values.roles);
    
    // Add type field to values to pass reusabeable validation rules
    values.type = 'person'; // user is always a person

    if (!valid) {
        return;
    }

    loading.value = true;

    try {
        const { data } = await axios.post(
            creating ? '/settings/users' : `/settings/users/profile/${userId}/edit`,
            values
        );

        if (crudAction === 'edit.auth-user') {
            data.data?.user ? auth.setUser(data.data.user) : await auth.checkAuth();
        }

        toast.add({ 
            severity: 'success', 
            summary: $t('Success'), 
            detail: $t('User saved successfully.'),
            life: 3000 
        });

        router.push({ name: 'settings.users.list' });

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
        
        toast.add({ 
            severity: 'error', 
            summary: $t('Error'), 
            detail: $t('An unexpected error occurred. Please try again.'),
            life: 5000 
        });
        
    } finally { loading.value = false; } 
        
};

onMounted(async () => {
    try {
        statusList.value = await axios.post('/lists/status').then(response => {
            return response.data;
        });

        await fetchRoles();
    } catch (error) {
        console.error('Error during component mount:', error);
    }
});
</script>