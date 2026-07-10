<template>
  <div class="w-full">
    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="handleResetPassword" class="flex flex-col gap-4">
      <div>
        <label for="email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ $t('Email') }}
        </label>
        <InputText
          id="email"
          v-model="form.email"
          type="email"
          class="w-full"
          :invalid="!!errors.email"
          autocomplete="email"
          fluid
        />
        <small v-if="errors.email" class="text-red-500">{{ errors.email }}</small>
      </div>

      <div>
        <label for="password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ $t('New Password') }}
        </label>
        <Password
          id="password"
          v-model="form.password"
          :feedback="false"
          toggleMask
          fluid
          :invalid="!!errors.password"
          autocomplete="new-password"
        />
        <small v-if="errors.password" class="text-red-500">{{ errors.password }}</small>
      </div>

      <div>
        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ $t('Confirm Password') }}
        </label>
        <Password
          id="password_confirmation"
          v-model="form.password_confirmation"
          :feedback="false"
          toggleMask
          fluid
          :invalid="!!errors.password_confirmation"
          autocomplete="new-password"
        />
        <small v-if="errors.password_confirmation" class="text-red-500">{{ errors.password_confirmation }}</small>
      </div>

      <Button
        type="submit"
        :loading="loading"
        :label="$t('Reset Password')"
        class="w-full"
        rounded
      />
    </form>

    <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
      <router-link :to="{ name: 'login' }" class="font-medium text-blue-600 hover:underline dark:text-blue-400">
        {{ $t('Back to login') }}
      </router-link>
    </p>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../../stores/auth'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'

const route = useRoute()
const router = useRouter()
const { t: $t, locale } = useI18n()
const authStore = useAuthStore()

const form = reactive({
  token: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = reactive({
  email: '',
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const status = ref('')

onMounted(() => {
  form.token = typeof route.query.token === 'string' ? route.query.token : ''
  form.email = typeof route.query.email === 'string' ? route.query.email : ''
  status.value = typeof route.query.status === 'string' ? route.query.status : ''
})

const handleResetPassword = async () => {
  errors.email = ''
  errors.password = ''
  errors.password_confirmation = ''
  loading.value = true

  try {
    const result = await authStore.resetPassword({
      token: form.token,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      locale: locale.value,
    })

    const message = result.message || $t('Your password has been reset.')

    await router.push({
      name: 'login',
      query: { status: message },
    })
  } catch (error) {
    if (error.response?.status === 422) {
      const validationErrors = error.response.data.errors

      if (validationErrors.email) {
        errors.email = validationErrors.email[0]
      }

      if (validationErrors.password) {
        errors.password = validationErrors.password[0]
      }

      if (validationErrors.password_confirmation) {
        errors.password_confirmation = validationErrors.password_confirmation[0]
      }
    } else {
      errors.email = error.response?.data?.message || $t('An error occurred. Please try again.')
    }
  } finally {
    loading.value = false
  }
}
</script>