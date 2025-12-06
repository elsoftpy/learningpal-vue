<template>
    <Form
        v-slot="$form"
        @submit="handleLogin"
        :initial-values="initialValues"
        :resolver="resolver"
    >
        <ProfilePage :form="$form" :creating="creating" :isPersonProfile="true">
            <template #model>
                <div class="flex-col md:flex md:flex-row space-y-2 md:space-x-2 md:items-center">
                    <div class="flex flex-col w-full md:w-1/5">
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Username') }}
                        </label>
                        <InputText
                            id="name"
                            name="name"
                            :placeholder="$t('Username')"
                        />
                    </div>
                    
                    <div class="flex flex-col w-full md:w-1/5">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Password') }}
                        </label>
                        <InputText
                            id="password"
                            name="password"
                            :placeholder="$t('Password')"
                        />
                    </div>
                    <div class="flex flex-col w-full md:w-1/5">
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Role') }}
                        </label>
                        <InputText
                            id="role"
                            name="role"
                            :placeholder="$t('Role')"
                        />
                    </div>
                    <div class="flex flex-col w-full md:w-1/5">
                        <label for="payment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Payment') }}
                        </label>
                        <InputText
                            id="payment"
                            name="payment"
                            :placeholder="$t('Payment')"
                        />
                    </div>
                    <div class="flex w-full pt-4 mb-4 md:mb-0 space-x-2 md:w-1/5">
                        <ToogleSwitch 
                            id="is_active"
                            name="is_active"
                            :label="$t('Active')" 
                        />
                        <label for="is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Active') }}
                        </label>
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
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { createUserSchema } from '@/schemas/user';
import { Form } from '@primevue/forms';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import InputText from 'primevue/inputtext';
import ProfilePage from '../profiles/ProfilePage.vue';
import ToogleSwitch from 'primevue/toggleswitch';
import Button from 'primevue/button';

const { t: $t } = useI18n();
const resolver = zodResolver(createUserSchema($t));
const auth = useAuthStore();
const route = useRoute();

const crudAction = route.meta?.crud || 'read';
const creating = crudAction === 'create';

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
            role: '',
            payment: '',
            is_active: true,
        };
    }

    if (crudAction === 'edit.auth-user') {
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
            role: auth.user?.role || '',
            payment: auth.user?.payment || '',
            is_active: auth.user?.is_active || false,
        };
    }
    return {};
});

const handleLogin = ({valid, values}) => {
    if (!valid) {
        return;
    }

    // Handle form submission logic here
    console.log('Form submitted with values:', values);
};
</script>