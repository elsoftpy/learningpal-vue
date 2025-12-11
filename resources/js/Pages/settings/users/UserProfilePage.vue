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
            :avatar-url="currentAvatarUrl"
            :creating="creating" 
            :isPersonProfile="true"
            :errors="errors"
            @update:avatar="onAvatarUpdate"
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
                            option-label="label"
                            option-value="name"
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

                    <div class="flex flex-col w-full md:w-1/6">
                        
                            <FileUpload
                                v-if="!hasPayment"
                                id="payment"
                                :label="$t('Payment')"
                                :button-label="$t('Payment')"
                                accept="image/*,application/pdf"
                                :max-file-size="4096000"
                                preview-class="h-16 w-16 rounded-full object-cover"
                                @update:modelValue="onPaymentReceiptSelect"

                            />
                        
                            <Button
                                v-if="hasPayment"
                                type="button"
                                class="mt-6 ml-2"
                                icon="pi pi-receipt"
                                severity="primary"
                                :label="$t('Payment')"
                                :aria-label="$t('Payment')"
                                @click="visible = true"
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

                <Dialog
                    v-model:visible="visible"
                    modal 
                    :header="$t('Payment Receipt')" 
                    :style="{ width: '25rem' }"
                >
                    <div class="flex flex-col items-center justify-center p-4">
                        <img 
                            v-if="paymentReceiptUrl" 
                            :src="paymentReceiptUrl" 
                            :alt="$t('Payment Receipt')"
                            class="max-w-full h-auto rounded-lg shadow-md"
                        />
                        <p v-else class="text-gray-500">
                            {{ $t('No payment receipt available.') }}
                        </p>
                    </div>
                </Dialog>
            </template>
        </ProfilePage>
    </Form>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { createUserSchema } from '@/schemas/user';
import { useApiErrorHandler } from '@/composables/useApiErrorHandler'
import { useFormSubmitter } from '@/composables/useFormSubmitter'
import { usePermissions } from '@/composables/usePermissions';
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
import Dialog from 'primevue/dialog';
import FileUpload from '@/components/form/FileUpload.vue';
import axios from 'axios';

const { locale, t: $t } = useI18n();
const { can } = usePermissions();
const { handleApiError } = useApiErrorHandler();
const userSchema = computed(() => createUserSchema($t, locale.value));
const resolver = zodResolver(userSchema.value);
const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const toast = useToast();


const statusList = ref([]);
const rolesOptions = ref([]);
const selectedAvatar = ref(null);
const rolesLoading = ref(false);
const visible = ref(false);
const userData = ref(null);
const formKey = ref(0);
let rolesDebounceTimer = null;

const crudAction = route.meta?.crud || 'read';
const creating = crudAction === 'create';

const userId = route.meta.crud === 'edit.auth-user' ? auth.user.id : route.params.userId;

const emit = defineEmits(['update:paymentReceipt']);
const selectedPaymentFile = ref(null);

const currentAvatarUrl = computed(() => {
    console.log('Computing currentAvatarUrl for crudAction:', auth.user?.avatar_url);
    if (crudAction === 'edit.auth-user') {
        return auth.user?.avatar_url || null;
    }
    return null;
});

const paymentReceiptUrl = computed(() => {
    console.log('User has payment receipt:', auth.user?.payment_receipt);
    if (crudAction === 'edit.auth-user') {
        return auth.user?.payment_receipt;
    }
    return null;
});

const hasPayment = computed(() => !!paymentReceiptUrl.value);

watch(userData, (newData) => {
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
            name: '',
            password: '',
            roles: [],
            status: '',
        };
    }

    if (crudAction === 'edit.auth-user') {
        const roleNames = getRoleNames(auth.user?.roles || []);
        return {
            personal_id: auth.user?.personal_id || '',
            first_name: auth.user?.first_name || '',
            last_name: auth.user?.last_name || '',
            address: auth.user?.address || '',
            phone: auth.user?.phone || '',
            email: auth.user?.email || '',
            birth_date: auth.user?.birth_date || '',
            name: auth.user?.name || '',
            password: auth.user?.password || '',
            roles: roleNames,
            status: auth.user?.status || '',
        };
    }

    const roleNames = getRoleNames(userData.value?.roles || []);
    const data = userData.value || {};

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
        roles: roleNames,
        status: data.status || '',
    };
});


const { errors, loading, setErrors, clearErrors } = useFormSubmitter({
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
    payment_receipt: '',
    status: '',
    general: '',
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

const fetchUserData = async () => {
    try {
        const response = await axios.post(`/settings/users/profile/${userId}/data`);
        userData.value = response.data.data.user || response.data.user || {};
    } catch (error) {
        console.error('Error fetching user data:', error);
        userData.value = null;
        toast.add({ 
            severity: 'error', 
            summary: $t('Error'), 
            detail: $t('Failed to load user data. Please try again.'),
            life: 5000 
        });
    }
};

const getRoleNames = (roles) => {

    return Array.isArray(roles) 
        ? roles.map(role => typeof role === 'object' ? role.name : role) 
        : [];
}

const onRolesFilter = (event) => {
    clearTimeout(rolesDebounceTimer);
    rolesDebounceTimer = setTimeout(() => {
        fetchRoles(event.value);
    }, 300);
};

const onAvatarUpdate = (file) => {
    console.log('Avatar updated:', file);
    selectedAvatar.value = file;
}

const onPaymentReceiptSelect = (file) => {
    if (!file ) {
        onPaymentReceiptClear()
        return
    }
    
    selectedPaymentFile.value = file
    emit('update:paymentReceipt', file)
}

const onPaymentReceiptClear = () => {
    selectedPaymentFile.value = null
    emit('update:paymentReceipt', null)
}

const handleSubmit =  async (formState) => {
    errors.value = {};

    const { valid } = formState;

    const values = Object.keys(formState.states).reduce((acc, key) => {
        acc[key] = formState.states[key].value;
        return acc;
    }, {});
    
    // Add type field to values to pass reusabeable validation rules
    values.type = 'person'; // user is always a person

    if (!valid) {
        return;
    }

    loading.value = true;

    try {
        const formData = new FormData();

        Object.keys(values).forEach(key => {
            if (Array.isArray(values[key])) {
                values[key].forEach((item, index) => {
                    formData.append(`${key}[${index}]`, item);
                });
            } else {
                formData.append(key, values[key]);
            }
        });

        console.log('Appending avatar to form data:', selectedAvatar.value);
        if (selectedAvatar.value) {
            formData.append('avatar', selectedAvatar.value);
        }

        console.log('Appending payment receipt to form data:', selectedPaymentFile.value);
        if (selectedPaymentFile.value) {
            formData.append('payment_receipt', selectedPaymentFile.value);
        }

        const { data } = await axios.post(
            creating ? '/settings/users' : `/settings/users/profile/${userId}/edit`,
            formData,
            { 
                headers: { 'Content-Type': 'multipart/form-data' } 
            }
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

        setErrors({ general: apiError?.message || $t('An unexpected error occurred. Please try again.') });
        
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

        if (crudAction === 'edit' && userId) {
            if (!can('edit users')) {
                toast.add({ 
                    severity: 'error', 
                    summary: $t('Unauthorized'), 
                    detail: $t('You do not have permission to edit users.'),
                    life: 5000 
                });
                router.push({ name: 'settings.users.list' });
            
                return;
            }

            await fetchUserData();
        }
    } catch (error) {
        console.error('Error during component mount:', error);
    }
});
</script>