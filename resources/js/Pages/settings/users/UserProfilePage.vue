<template>
    <ProfilePage>
        <template #model>
            <div class="flex-col md:flex md:flex-row space-y-2 md:space-x-2 md:items-center">
                <div class="flex flex-col w-full md:w-1/5">
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $t('Username') }}
                    </label>
                    <InputText
                        id="username"
                        v-model="auth.user.username"
                        :label="$t('Username') + ' *'"
                        :placeholder="$t('Username')"
                    />
                </div>
                
                <div class="flex flex-col w-full md:w-1/5">
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $t('Password') }}
                    </label>
                    <InputText
                        id="password"
                        v-model="auth.user.password"
                        :label="$t('Password') + ' *'"
                        :placeholder="$t('Password')"
                    />
                </div>
                <div class="flex flex-col w-full md:w-1/5">
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $t('Role') }}
                    </label>
                    <InputText
                        id="role"
                        v-model="auth.user.role"
                        :label="$t('Role') + ' *'"
                        :placeholder="$t('Role')"
                    />
                </div>
                <div class="flex flex-col w-full md:w-1/5">
                    <label for="payment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $t('Payment') }}
                    </label>
                    <InputText
                        id="payment"
                        v-model="auth.user.payment"
                        :label="$t('Payment') + ' *'"
                        :placeholder="$t('Payment')"
                    />
                </div>
                <div class="flex w-full pt-4 mb-4 md:mb-0 space-x-2 md:w-1/5">
                    <ToogleSwitch 
                        id="is_active"
                        v-model="auth.user.is_active" 
                        :label="$t('Active')" 
                    />
                    <label for="is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $t('Active') }}
                    </label>
                </div>
            </div>
        </template>
    </ProfilePage>

    
</template>

<script setup>
import { InputText } from 'primevue';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from 'vue-i18n';
import ToogleSwitch from 'primevue/toggleswitch';
import ProfilePage from '../profiles/ProfilePage.vue';

const auth = useAuthStore();
const { t: $t } = useI18n();
</script>