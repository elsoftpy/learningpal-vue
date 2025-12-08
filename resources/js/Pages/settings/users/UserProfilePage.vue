<template>
    <Form
        v-slot="$form"
        :resolver="resolver"
        :initial-values="initialValues"
        @submit="handleSubmit"
        :validateOnBlur="true"
    >
        <!-- <pre>{{ $form }}</pre> -->
        <ProfilePage :form="$form" :creating="creating" :isPersonProfile="true">
            <template #model>
                <div class="flex-col md:flex md:flex-row space-y-2 md:space-x-2">
                    <div class="flex flex-col w-full md:w-1/5">
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
                        <ServerSideSelect
                            id="roles"
                            name="roles"
                            :label="$t('Role')"
                            :placeholder="$t('Select a role')"
                            api-endpoint="/lists/roles"
                            option-label="name"
                            option-value="id"
                            search-param="search"
                            :filter-placeholder="$t('Search roles...')"
                            :required="true"
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

                    <div class="flex flex-col w-full md:w-1/5">
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
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { createUserSchema } from '@/schemas/user';
import { Form } from '@primevue/forms';
import { zodResolver } from '@primevue/forms/resolvers/zod';
import InputText from 'primevue/inputtext';
import ProfilePage from '../profiles/ProfilePage.vue';
import Button from 'primevue/button';
import ServerSideSelect from '@/components/form/ServerSideSelect.vue';
import Select from 'primevue/select';
import Message from 'primevue/message';
import axios from 'axios';

const { locale, t: $t } = useI18n();
const userSchema = computed(() => createUserSchema($t, locale.value));
const resolver = zodResolver(userSchema.value);
const auth = useAuthStore();
const route = useRoute();
const loading = ref(false);
const statusList = ref([]);

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
            status: '',
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
            status: auth.user?.status || '',
        };
    }
    return {};
});

const handleSubmit = ({valid, values}) => {
    if (!valid) {
        return;
    }

    // Handle form submission logic here
    console.log('Form submitted with values:', values);
};

onMounted(async () => {
    try {
        statusList.value = await axios.post('/lists/status').then(response => {
            return response.data;
        });
    } catch (error) {
        console.error('Error during component mount:', error);
    }
});
</script>