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
                <div v-if="creating" class="flex flex-col w-full">
                    <label for="existing_profile" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $t('Existing Profile') }}
                    </label>
                    <Select
                        id="existing_profile"
                        :model-value="selectedProfileOption?.id ?? null"
                        :options="profileOptions"
                        option-label="label"
                        option-value="id"
                        :loading="profilesLoading"
                        :placeholder="$t('Search by name, personal ID, or RUC')"
                        filter
                        :show-clear="true"
                        :filter-placeholder="$t('Type to search profiles')"
                        @filter="onProfileFilter"
                        @change="onProfileChange"
                        class="w-full"
                    >
                        <template #empty>
                            <div class="p-3 text-center text-gray-500">
                                {{ $t('No matching profile found. You can create a new one below.') }}
                            </div>
                        </template>

                        <template #loader>
                            <div class="flex items-center justify-center p-3">
                                <ProgressSpinner style="width: 20px; height: 20px" stroke-width="4" />
                                <span class="ml-2 text-sm text-gray-600">{{ $t('Loading...') }}</span>
                            </div>
                        </template>
                    </Select>
                    <small class="mt-1 text-xs text-gray-500">
                        {{ $t('Select an existing profile to reuse it, or leave this empty and complete the fields below to create a new profile.') }}
                    </small>
                    <small v-if="profileFieldsDisabled" class="mt-1 text-xs text-amber-600">
                        {{ $t('This profile is read-only because you do not have permission to edit existing profile data.') }}
                    </small>
                </div>
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
                            :disabled="personalIdDisabled"
                            :placeholder="$t('Personal ID')"
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
                                :disabled="profileFieldsDisabled"
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
                                :disabled="profileFieldsDisabled"
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
                            :disabled="profileFieldsDisabled"
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
                            :disabled="profileFieldsDisabled"
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
                    <div class="flex flex-col w-full md:w-1/4">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Email') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            id="email"
                            name="email"
                            :placeholder="$t('Email')"
                            :disabled="profileFieldsDisabled"
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
                    <div class="flex flex-col w-full md:w-1/4">
                        <label for="email_alt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ $t('Alternative Email') }}
                        </label>
                        <InputText
                            id="email_alt"
                            name="email_alt"
                            :placeholder="$t('Alternative Email')"
                            :disabled="profileFieldsDisabled"
                            class="w-full"
                        />
                        <Message
                            v-if="form.email_alt?.invalid"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ form.email_alt.error?.message }}
                        </Message>
                        <Message
                            v-if="errors?.email_alt"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            {{ errors?.email_alt }}
                        </Message>
                    </div>
                    <div class="flex flex-col w-full md:w-1/4">
                        <DateInput
                            id="birth_date"
                            name="birth_date"
                            :label="$t('Birth Date')"
                            :placeholder="$t('Birth Date')"
                            :disabled="profileFieldsDisabled"
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
                    <div v-if="isPersonProfile" class="flex flex-col w-full md:w-1/4">
                        <FileUpload
                            id="avatar-input"
                            :label="$t('Profile Picture')"
                            :button-label="$t('Select Avatar')"
                            accept="image/*"
                            :max-file-size="2000000"
                            :disabled="profileFieldsDisabled"
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
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import defaultAvatar from '@/images/default-avatar.png'
import PageContainer from '@/components/layout/pages/PageContainer.vue'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import ProgressSpinner from 'primevue/progressspinner'
import FileUpload from '@/components/form/FileUpload.vue'
import DateInput from '@/components/form/DateInput.vue'
import Message from 'primevue/message'
import axios from 'axios'

const { t: $t } = useI18n()

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
    canEditSelectedProfile: {
        type: Boolean,
        default: false,
    },
    profileType: {
        type: String,
        default: 'person',
    },
    selectedProfileOption: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits([
    'update:avatar',
    'profile-found',
    'profile-cleared',
])

const selectedFile = ref(null)
const profileOptions = ref([])
const profilesLoading = ref(false)
let profilesDebounceTimer = null

const avatar = computed(() => props.avatarUrl || defaultAvatar)
const profileFieldsDisabled = computed(() => props.creating && props.selectedProfileOption?.id && !props.canEditSelectedProfile)
const personalIdDisabled = computed(() => !props.creating || profileFieldsDisabled.value)

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

const fetchProfiles = async (query = '') => {
    profilesLoading.value = true

    try {
        const response = await axios.post('/lists/profiles', {
            params: {
                search: query,
                type: props.profileType,
            },
        })

        const fetchedOptions = response.data.data || response.data || []

        if (props.selectedProfileOption?.id && !fetchedOptions.some((profile) => profile.id === props.selectedProfileOption.id)) {
            fetchedOptions.unshift(props.selectedProfileOption)
        }

        profileOptions.value = fetchedOptions
    } catch (error) {
        console.error('Error fetching profiles:', error)
        profileOptions.value = []
    } finally {
        profilesLoading.value = false
    }
}

const onProfileFilter = (event) => {
    clearTimeout(profilesDebounceTimer)
    profilesDebounceTimer = setTimeout(() => {
        fetchProfiles(event.value || '')
    }, 300)
}

const onProfileChange = (event) => {
    const profileId = event.value

    if (!profileId) {
        emit('profile-cleared')
        return
    }

    const option = profileOptions.value.find((profile) => profile.id === profileId)

    if (option?.profile) {
        emit('profile-found', option.profile)
    }
}

onMounted(() => {
    if (props.creating) {
        fetchProfiles()
    }
})
</script>
