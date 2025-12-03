<template>
    <PageContainer>
        <template #body>
            <div class="flex flex-col w-full space-y-4">
                <div class="space-y-4 md:space-y-0 md:flex md:items-center md:space-x-4">
                    <!-- Avatar -->
                    <div class="flex justify-center w-full md:w-1/12">
                        <img
                            :src="avatar"
                            class="h-16 w-16 rounded-full border-2 border-blue-500 dark:border-blue-100"
                            alt="user_avatar"
                        />
                    </div>
                    <!-- ID Number -->
                    <div class="flex flex-col w-full md:w-1/4">
                        <label for="personal_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('ID Number') }}
                        </label>
                        <InputText
                            id="personal_id"
                            v-model="auth.user.personal_id"
                            :disabled="!creating"
                            :label="$t('ID Number') + ' *'"
                            :placeholder="$t('ID Number')"
                        />
                    </div>
                    <!-- First & Last Name -->
                    <div class="w-full md:flex md:space-x-2 md:w-3/4 space-y-2 md:space-y-0">
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Name') }}
                            </label>
                            <InputText
                                id="first_name"
                                v-model="auth.user.first_name"
                                :label="$t('Name') + ' *'"
                                :placeholder="$t('Name')"
                                class="w-full"
                            />
                        </div>
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Last Name') }}
                            </label>
                            <InputText
                                id="last_name"
                                v-model="auth.user.last_name"
                                :label="$t('Last Name') + ' *'"
                                :placeholder="$t('Last Name')"
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>
                <!-- Address / Phone -->
                <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2">
                    <div class="flex flex-col w-full md:w-3/4">
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Address') }}
                        </label>
                        <InputText
                            id="address"
                            v-model="auth.user.address"
                            :label="$t('Address')"
                            :placeholder="$t('Address')"
                        />
                    </div>
                    <div class="flex flex-col w-full md:w-1/4">
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Phone') }}
                        </label>
                        <InputText
                            id="phone"
                            v-model="auth.user.phone"
                            :label="$t('Phone')"
                            :placeholder="$t('Phone')"
                        />
                    </div>
                </div>
                <!--  Email / Birthdate / Avatar -->
                <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2">
                    <div class="flex flex-col w-full md:w-1/3">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Email') }}
                        </label>
                        <InputText
                            id="email"
                            v-model="auth.user.email"
                            :label="$t('Email') + ' *'"
                            :placeholder="$t('Email')"
                            class="w-full"
                        />
                    </div>
                    <div class="flex flex-col w-full md:w-1/3">
                        <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Birth Date') }}
                        </label>
                        <InputText
                            id="birth_date"
                            v-model="auth.user.birth_date"
                            :label="$t('Birth Date')"
                            :placeholder="$t('Birth Date')"
                            class="w-full"
                        />
                    </div>
                    <div class="flex flex-col w-full md:w-1/3">
                        <label for="avatar" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Profile Picture') }}
                        </label>
                        <InputText
                            id="avatar"
                            v-model="auth.user.avatar"
                            :label="$t('Profile Picture')"
                            :placeholder="$t('Profile Picture')"
                            class="w-full"
                        />
                    </div>
                </div>
                <slot name="model" />
            </div>
        </template>
    </PageContainer>
</template>

<script setup>
import PageContainer from '@/components/layout/pages/PageContainer.vue'
import defaultAvatar from '@/images/default-avatar.png'
import { useAuthStore } from '@/stores/auth'
import { InputText } from 'primevue'
import { useI18n } from 'vue-i18n'

const auth = useAuthStore()
const { t: $t } = useI18n()
const avatar = auth.user?.profile_photo_url || defaultAvatar
</script>