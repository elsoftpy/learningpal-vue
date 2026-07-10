<template>
  <div class="w-full">
    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="handleSendResetLink" class="flex flex-col gap-4">
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

      <Button
        type="submit"
        :loading="loading"
        :label="$t('Send Reset Link')"
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
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useI18n } from 'vue-i18n'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
const { t: $t, locale } = useI18n()
const authStore = useAuthStore()

const form = reactive({
  email: ''
})

const errors = reactive({
  email: ''
})

const loading = ref(false)
const status = ref('')

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search)
  status.value = urlParams.get('status') || ''
})

const handleSendResetLink = async () => {
  errors.email = ''
  loading.value = true

  try {
    const result = await authStore.requestPasswordResetLink({
      email: form.email,
      locale: locale.value,
    })

    status.value = result.message || $t('We have emailed your password reset link.')
  } catch (error) {
    if (error.response?.status === 422) {
      const validationErrors = error.response.data.errors
      if (validationErrors.email) {
        errors.email = validationErrors.email[0]
      }
    } else {
      errors.email = error.response?.data?.message || $t('An error occurred. Please try again.')
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
</style>