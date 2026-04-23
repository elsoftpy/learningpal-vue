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
            :can-edit-selected-profile="canEditSelectedProfile"
            :selected-profile-option="selectedProfileOption"
            @update:avatar="onAvatarUpdate"
            @profile-found="handleProfileFound"
            @profile-cleared="handleProfileCleared"
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
                            input-class="w-full"
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

                    <div v-if="can('change roles')" class="flex flex-col w-full md:w-2/6">
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

                    <div v-if="can('change user status')" class="flex flex-col w-full md:w-1/6">
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
                                v-if="!hasPayment && isStudent"
                                id="payment"
                                :label="$t('Payment')"
                                :button-label="$t('Payment')"
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,application/pdf"
                                :max-file-size="4096000"
                                :helper-text="$t('Allowed formats: JPG, JPEG, PNG, GIF, WEBP, PDF.')"
                                :disabled="profileFieldsLocked"
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
                    <SubmitButton :isLoading="isLoading"/>
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

                <Dialog
                    v-model:visible="showNewUser"
                    modal
                    :closable="false"
                    :style="{ width: '25rem' }"
                >
                    <template #header >
                        <div class="flex w-full justify-between items-center rounded-lg h-16 p-4 text-white bg-blue-500">
                            <span class="text-xl font-semibold">{{ $t('Welcome!') }}</span>
                            <Button
                                icon="pi pi-times"
                                rounded
                                size="small"
                                severity="primary"
                                variant="outlined"
                                class="text-white! border-2! hover:text-gray-800!"
                                @click="showNewUser = false"
                            />
                        </div>
                    </template>
                    <div class="flex flex-col gap-3">
                        <!-- Icon / Visual cue -->
                        <div class="flex justify-center">
                            <IconWrapper
                                name="check-circle"
                                class="text-green-500"
                                size="48"
                            />
                        </div>

                        <!-- Title -->
                        <div class="flex justify-center text-lg font-semibold text-gray-900">
                            {{ $t("Great news, you're in!") }}
                        </div>

                        <!-- Description -->
                        <p class="text-gray-600 max-w-md">
                            {{ $t('Your account is almost ready.') }}
                        </p>
                        <p class="text-gray-600 max-w-md">
                            {{ $t('To activate it, please upload your payment receipt.') }}
                        </p>

                        <p class="text-gray-600 max-w-md">
                            {{ $t("While your account is under review, you can update your information.") }}
                            {{ $t('We\'ll notify you as soon as your account becomes active') }}
                        </p>

                        <!-- Footer / Thanks -->
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $t('Thanks!') }}
                        </p>

                        <!-- Action -->
                        <div class="flex justify-end">
                            <Button
                                type="button"
                                icon="pi pi-check"
                                severity="success"
                                :label="$t('OK')"
                                :aria-label="$t('OK')"
                                @click="showNewUser = false"
                            />
                        </div>
                    </div>
                </Dialog>

                <Dialog
                    v-model:visible="showPendingValidationNotice"
                    modal
                    :closable="false"
                    :style="{ width: '25rem' }"
                >
                    <template #header>
                        <div class="flex w-full justify-between items-center rounded-lg h-16 p-4 text-white bg-blue-500">
                            <span class="text-xl font-semibold">{{ $t('Information') }}</span>
                            <Button
                                icon="pi pi-times"
                                rounded
                                size="small"
                                severity="primary"
                                variant="outlined"
                                class="text-white! border-2! hover:text-gray-800!"
                                @click="showPendingValidationNotice = false"
                            />
                        </div>
                    </template>
                    <div class="flex flex-col gap-4">
                        <p class="text-gray-600 max-w-md">
                            {{ $t("Once we validate your payment you'll be good to go") }}
                        </p>
                        <div class="flex justify-end">
                            <Button
                                type="button"
                                icon="pi pi-check"
                                severity="info"
                                :label="$t('OK')"
                                :aria-label="$t('OK')"
                                @click="showPendingValidationNotice = false"
                            />
                        </div>
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
import { useFormValues } from '@/composables/useFormValues';
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
import IconWrapper from '@/components/common/IconWrapper.vue';
import axios from 'axios';
import SubmitButton from '../../../components/form/SubmitButton.vue';

const { locale, t: $t } = useI18n();
const { can } = usePermissions();
const { handleApiError } = useApiErrorHandler();
const { extractFormData } = useFormValues();
const userSchema = computed(() => createUserSchema($t, locale.value));
const resolver = zodResolver(userSchema.value);
const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const toast = useToast();
const canEditSelectedProfile = computed(() => can(['edit profiles', 'edit users']));
const profileFieldsLocked = computed(() => creating && !!userData.value?.profile_id && !canEditSelectedProfile.value);


const statusList = ref([]);
const rolesOptions = ref([]);
const selectedAvatar = ref(null);
const rolesLoading = ref(false);
const visible = ref(false);
const userData = ref(null);
const isStudent = ref(false);
const formKey = ref(0);
let rolesDebounceTimer = null;

const crudAction = route.meta?.crud || 'read';
const creating = crudAction === 'create';

const userId = route.meta.crud === 'edit.auth-user' ? auth.user.id : route.params.id;
const from = route.query.from|| null;

const emit = defineEmits(['update:paymentReceipt']);
const selectedPaymentFile = ref(null);
const selectedProfileOption = ref(null);

const showNewUser = ref(false);
const showPendingValidationNotice = ref(false);

const currentAvatarUrl = computed(() => {
    console.log('Computing currentAvatarUrl for crudAction:', auth.user?.avatar_url);
    if (crudAction === 'edit.auth-user') {
        return auth.user?.avatar_url || null;
    }

    return userData.value?.avatar_url || null;
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

watch(
    () => route.query.pending_notice_at,
    (pendingNoticeAt) => {
        if (!pendingNoticeAt || route.query.pending_notice !== 'payment_validation') {
            return;
        }

        showPendingValidationNotice.value = true;

        const query = { ...route.query };
        delete query.pending_notice;
        delete query.pending_notice_at;

        router.replace({ query });
    }
);

const initialValues = computed(() => {
    if (crudAction === 'create') {
        const roleNames = getRoleNames(userData.value?.roles || []);
        const data = userData.value || {};
        isStudent.value = roleNames.includes('student');
        
        return {
            profile_id: data.profile_id || null,
            personal_id: data.personal_id || '',
            first_name: data.first_name || '',
            last_name: data.last_name || '',
            address: data.address || '',
            phone: data.phone || '',
            email: data.email || '',
            email_alt: data.email_alt || '',
            birth_date: data.birth_date || '',
            name: data.name || '',
            password: data.password || '',
            roles: roleNames,
            status: data.status || '',
        };
    }

    if (crudAction === 'edit.auth-user') {
        const roleNames = getRoleNames(auth.user?.roles || []);
        isStudent.value = roleNames.includes('student');
        
        return {
            profile_id: auth.user?.profile_id || null,
            personal_id: auth.user?.personal_id || '',
            first_name: auth.user?.first_name || '',
            last_name: auth.user?.last_name || '',
            address: auth.user?.address || '',
            phone: auth.user?.phone || '',
            email: auth.user?.email || '',
            email_alt: auth.user?.email_alt || '',
            birth_date: auth.user?.birth_date || '',
            name: auth.user?.name || '',
            password: auth.user?.password || '',
            roles: roleNames,
            status: auth.user?.status || '',
        };
    }

    if (userData.value) {
        const roleNames = getRoleNames(userData.value?.roles || []);
        isStudent.value = roleNames.includes('student');
        
        return {
            profile_id: userData.value.profile_id || null,
            personal_id: userData.value.personal_id || '',
            first_name: userData.value.first_name || '',
            last_name: userData.value.last_name || '',
            address: userData.value.address || '',
            phone: userData.value.phone || '',
            email: userData.value.email || '',
            email_alt: userData.value.email_alt || '',
            birth_date: userData.value.birth_date || '',
            name: userData.value.name || '',
            password: userData.value.password || '',
            roles: roleNames,
            status: userData.value.status || '',
        };
    }

    return {
            profile_id: null,
            personal_id: '',
            first_name: '',
            last_name: '',
            address: '',
            phone: '',
            email: '',
            email_alt: '',
            birth_date: '',
            name: '',
            password: '',
            roles: [],
            status: '',
        };
});


const { errors, isLoading, setErrors, clearErrors } = useFormSubmitter({
    profile_id: '',
    personal_id: '',
    first_name: '', 
    last_name: '',
    address: '',
    phone: '',
    email: '',
    email_alt: '',
    birth_date: '',
    avatar: '',
    name: '',
    password: '',
    roles: '',
    payment_receipt: '',
    status: '',
    general: '',
});

const handleProfileFound = async (profileData) => {
    if (!profileData) return;

    selectedProfileOption.value = {
        id: profileData.id,
        label: [profileData.full_name, profileData.personal_id || profileData.ruc].filter(Boolean).join(' - '),
        profile: profileData,
    };

    userData.value = {
        ...(userData.value || {}),
        profile_id: profileData.id,
        personal_id: profileData.personal_id,
        first_name: profileData.first_name,
        last_name: profileData.last_name,
        address: profileData.address,
        phone: profileData.phone,
        email: profileData.email,
        email_alt: profileData.email_alt,
        birth_date: profileData.birth_date,
        avatar_url: profileData.avatar_url,
        payment_receipt: profileData.payment_receipt,
    };
}

const handleProfileCleared = () => {
    selectedProfileOption.value = null;
    userData.value = {
        ...(userData.value || {}),
        profile_id: null,
        personal_id: '',
        first_name: '',
        last_name: '',
        address: '',
        phone: '',
        email: '',
        email_alt: '',
        birth_date: '',
        avatar_url: null,
        payment_receipt: null,
    };
}

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

const handleSubmit =  async (formData) => {
    errors.value = {};

    const { valid, values } = extractFormData(formData);
    
    // Add type field to values to pass reusabeable validation rules
    values.type = 'person'; // user is always a person
    values.profile_id = userData.value?.profile_id || null;

    if (!valid) {
        return;
    }

    isLoading.value = true;

    try {
        const formData = new FormData();

        if (!can('change roles')) {
            delete values.roles;
        }

        if (!can('change user status')) {
            delete values.status;
        }

        Object.keys(values).forEach(key => {
            if (values[key] === null || values[key] === undefined || values[key] === '') {
                return;
            }

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

        if (crudAction === 'edit.auth-user') {
            router.push({
                name: 'settings.users.profile',
                params: { id: auth.user?.id ?? userId },
            });
        } else {
            router.push({ name: 'settings.users.list' });
        }

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

        if (can('change roles')) {
            await fetchRoles();
        }

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

        if (from === 'register') {
            showNewUser.value = true;
        }
    } catch (error) {
        console.error('Error during component mount:', error);
    }
});
</script>
