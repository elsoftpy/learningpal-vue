<template>
    <PageContainer>
        <template #body>
            <Message
                v-if="errors.general"
                severity="error"
                size="small"
                variant="outlined"
                :closable="true"
                class="w-full mb-4"
            >
                {{ errors.general }}
            </Message>
            <div class="flex flex-col w-full space-y-4">
                <div class="space-y-4 md:space-y-0 md:flex md:space-x-4">
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
                            {{ $t('Personal ID') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            id="personal_id"
                            name="personal_id"
                            :disabled="!creating"
                            :placeholder="$t('Personal ID')"
                            @blur="handlePersonalIdBlur"
                        />
                    </div>
                    <!-- First & Last Name -->
                    <div class="w-full md:flex md:space-x-2 md:w-3/4 space-y-2 md:space-y-0">
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('First Name') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="first_name"
                                name="first_name"
                                :placeholder="$t('Name')"
                                class="w-full"
                            />
                            <Message
                                v-if="form.first_name?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ form.first_name.error?.message }}
                            </Message>
                            <Message
                                v-if="errors?.first_name"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ errors?.first_name }}
                            </Message>
                        </div>
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $t('Last Name') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <InputText
                                id="last_name"
                                name="last_name"
                                :placeholder="$t('Last Name')"
                                class="w-full"
                            />
                            <Message
                                v-if="form.last_name?.invalid"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ form.last_name.error?.message }}
                            </Message>
                            <Message
                                v-if="errors?.last_name"
                                severity="error"
                                size="small"
                                variant="simple"
                            >
                                {{ errors?.last_name }}
                            </Message>
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
                        <Message
                            v-if="form.address?.invalid"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ form.address.error?.message }}
                        </Message>
                        <Message
                            v-if="errors?.address"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ errors?.address }}
                        </Message>
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
                        <Message
                            v-if="form.phone?.invalid"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ form.phone.error?.message }}
                        </Message>
                        <Message
                            v-if="errors?.phone"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ errors?.phone }}
                        </Message>
                    </div>
                </div>
                <!--  Email / Birthdate / Avatar -->
                <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2">
                    <div class="flex flex-col w-full"
                        :class="isPersonProfile ? 'md:w-1/3' : 'md:w-2/3'"
                    >
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Email') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            id="email"
                            name="email"
                            :placeholder="$t('Email')"
                            class="w-full"
                        />
                        <Message
                            v-if="form.email?.invalid"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ form.email.error?.message }}
                        </Message>
                        <Message
                            v-if="errors?.email"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ errors?.email }}
                        </Message>
                    </div>
                    <div class="flex flex-col w-full md:w-1/3">
                        <DateInput
                            id="birth_date"
                            name="birth_date"
                            :label="$t('Birth Date')"
                            :placeholder="$t('Birth Date')"
                            @update:unmasked="form.birth_date_unmasked = $event"
                            class="w-full"
                        />
                        <Message
                            v-if="form.birth_date?.invalid"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ form.birth_date.error?.message }}
                        </Message>
                        <Message
                            v-if="errors?.birth_date"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ errors?.birth_date }}
                        </Message>
                    </div>
                    <div v-if="isPersonProfile" class="flex flex-col w-full md:w-1/3">
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
import DateInput from '@/components/form/DateInput.vue'
import Message from 'primevue/message'
import { useProfileCheck } from '@/composables/useProfileCheck'

const { t: $t } = useI18n()
const { checkProfile, isChecking } = useProfileCheck()

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    avatarUrl: {
        type: String,
        default: null,
    },
    creating: {
        type: Boolean,
        default: false,
    },
    isPersonProfile: {
        type: Boolean,
        default: false,
    },

    errors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits([
    'update:avatar',
    'profile-found',
])

const selectedFile = ref(null)

const avatar = computed(() => props.avatarUrl || defaultAvatar)

const onAvatarSelect = (file) => {
    if (!file ) {
        onAvatarClear()
        return
    }
    
    selectedFile.value = file
    emit('update:avatar', file)
}

const onAvatarClear = () => {
    selectedFile.value = null
    emit('update:avatar', null)
}

const handlePersonalIdBlur = async () => {
    const personalId = props.form.personal_id?.value

    if (!personalId || !props.creating) {
        return
    }

    const profileData = await checkProfile(personalId, {
        showToast: true,
        showErrorToast: false,
    })

    if (profileData) {
        emit('profile-found', profileData)
    }
}
</script>