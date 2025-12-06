<template>
    <PageContainer>
        <template #body>
            <div class="flex flex-col w-full space-y-4">
                <div class="space-y-4 md:space-y-0 md:flex md:items-center md:space-x-4">
                    <!-- Avatar -->
                    <div v-if="isPersonProfile" class="flex justify-center w-full md:max-w-16">
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
                            name="personal_id"
                            :disabled="!creating"
                            :placeholder="$t('ID Number')"
                        />
                    </div>
                    <!-- First & Last Name -->
                    <div class="w-full md:flex md:space-x-2 md:w-3/4 space-y-2 md:space-y-0">
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('First Name') }}
                            </label>
                            <InputText
                                id="first_name"
                                name="first_name"
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
                                name="last_name"
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
                            name="address"
                            :placeholder="$t('Address')"
                        />
                    </div>
                    <div class="flex flex-col w-full md:w-1/4">
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Phone') }}
                        </label>
                        <InputText
                            id="phone"
                            name="phone"
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
                            name="email"
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
                            name="birth_date"
                            :placeholder="$t('Birth Date')"
                            class="w-full"
                        />
                    </div>
                    <div class="flex flex-col w-full md:w-1/3">
                        <FileUpload
                            id="avatar-input"
                            :label="$t('Profile Picture')"
                            :button-label="$t('Select Avatar')"
                            accept="image/*"
                            :max-file-size="2000000"
                            preview-class="h-16 w-16 rounded-full object-cover"
                            @update:modelValue="onAvatarSelect"
                        />
                    </div>
                </div>
                <slot name="model" />
            </div>
        </template>
    </PageContainer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import defaultAvatar from '@/images/default-avatar.png'
import PageContainer from '@/components/layout/pages/PageContainer.vue'
import InputText from 'primevue/inputtext'
import FileUpload from '@/components/form/FileUpload.vue'


const { t: $t } = useI18n()

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    creating: {
        type: Boolean,
        default: false,
    },
    isPersonProfile: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:avatar'])

const selectedFile = ref(null)

/* const avatarPreview = computed(() => {
    if (selectedFile.value?.objectURL) {
        return selectedFile.value.objectURL
    }
    return props.form.avatar || defaultAvatar
})
 */
const onAvatarSelect = (value) => {
    if (!value) {
        selectedFile.value = null
        emit('update:avatar', null)
        return
    }

    const files = value.files || []
    const [file] = files

    if (!file) {
        selectedFile.value = null
        emit('update:avatar', null)
        return
    }

    selectedFile.value = file
    emit('update:avatar', file)
}

const onAvatarClear = () => {
    selectedFile.value = null
    emit('update:avatar', null)
}

/* const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
} */

const avatar = props.form.avatar || defaultAvatar
</script>