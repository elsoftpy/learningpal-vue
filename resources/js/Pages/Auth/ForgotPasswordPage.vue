<template>
    <div class="w-full">
        <!-- Session Status -->
      <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
        {{ status }}
      </div>

      <form @submit.prevent="handleLogin">
        <!-- Username Input -->
        <div class="mt-4">
          <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $t('Email') }}
          </label>
          <InputText
            id="email"
            v-model="form.email"
            type="email"
            :invalid="!!errors.email"
            fluid
          />
          <small v-if="errors.email" class="text-red-500">{{ errors.email }}</small>
        </div>

        <!-- Submit Button -->
        <div class="mt-4">
          <Button
            type="submit"
            :loading="loading"
            :label="$t('Send Reset Link')"
            class="w-full"
            rounded
          />
        </div>
      </form>
    </div>    
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useI18n } from 'vue-i18n'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import ToggleSwitch from 'primevue/toggleswitch'
import Button from 'primevue/button'
import Divider from 'primevue/divider'

const router = useRouter()
const route = useRoute()
const { t: $t } = useI18n()

const form = reactive({
  name: '',
  password: '',
  remember: false
})

const errors = reactive({
  name: '',
  password: ''
})

const loading = ref(false)
const status = ref('')
const hasPasswordReset = ref(true)

onMounted(() => {
  // Get session status if exists
  const urlParams = new URLSearchParams(window.location.search)
  status.value = urlParams.get('status') || ''
})

const handleLogin = async () => {
  // Clear previous errors
  errors.name = ''
  errors.password = ''
  loading.value = true

  try {
    // Get CSRF token first
    const authStore = useAuthStore();

    const result = await authStore.login({
      name: form.name,
      password: form.password,
      remember: form.remember
    })

    if (result.success) {
      // Check for redirect query parameter
      const redirectTo = route.query.redirect || '/dashboard'
      router.push(redirectTo)
    } else {
      console.error(result.error);
      errors.password = result.error || 'An error occurred. Please try again.'
    }
  } catch (error) {
    if (error.response?.status === 422) {
      // Validation errors
      const validationErrors = error.response.data.errors
      if (validationErrors.name) errors.name = validationErrors.name[0]
      if (validationErrors.password) errors.password = validationErrors.password[0]
    } else {
      console.error(error);
      // General error
      errors.password = error.response?.data?.message || 'An error occurred. Please try again.'
    }
  } finally {
    loading.value = false
  }
}


</script>

<style scoped>
/* Ensure PrimeVue Password component takes full width */
:deep(.p-password) {
  width: 100%;
}

:deep(.p-password input) {
  width: 100%;
}
</style>