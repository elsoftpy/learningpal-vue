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
                            name="avatar-input[]"
                            mode="advanced"
                            accept="image/*"
                            :auto="false"
                            :choose-label="$t('Select Avatar')"
                            :custom-upload="true"
                            :show-upload-button="false"
                            :show-cancel-button="false"
                            :multiple="false"
                            @select="onAvatarSelect"
                            class="w-full mt-2"
                        >
                            <template #empty>
                                <div class="flex flex-col items-center justify-center py-4">
                                    <i class="pi pi-image text-4xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-500">
                                        {{ $t('Drag and drop image here') }}
                                    </p>
                                </div>
                            </template>
                            
                            <template #content="{ files, uploadedFiles, removeUploadedFileCallback, removeFileCallback }">
                                <div v-if="files.length > 0" class="p-4">
                                    <div 
                                        v-for="(file, index) in files" :key="file.name" 
                                        class="flex flex-col items-center justify-between p-3 border rounded-lg"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <img 
                                                v-if="file.objectURL" 
                                                :src="file.objectURL" 
                                                :alt="file.name"
                                                class="h-12 w-12 rounded object-cover"
                                            />
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ file.name }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ formatFileSize(file.size) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex w-full items-center justify-between space-x-2 p-1">
                                            <span class="px-2 py-1 rounded-full bg-amber-500 text-xs font-semibold text-white">
                                                {{ $t('Pending') }}
                                            </span>
                                            <Button
                                                icon="pi pi-times"
                                                @click="removeFileCallback(index)"
                                                text
                                                rounded
                                                severity="danger"
                                                size="small"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </FileUpload>
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
import FileUpload from 'primevue/fileupload'
import Button from 'primevue/button'

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

const avatarPreview = computed(() => {
    if (selectedFile.value?.objectURL) {
        return selectedFile.value.objectURL
    }
    return props.form.avatar || defaultAvatar
})

const onAvatarSelect = ({ files }) => {
    const [file] = files || []
    if (!file) return

    selectedFile.value = file
    emit('update:avatar', file)
}

const onAvatarClear = () => {
    selectedFile.value = null
    emit('update:avatar', null)
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const avatar = props.form.avatar || defaultAvatar
</script>