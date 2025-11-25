<template>
  <GuestLayout :title="$t('Login')">
    <!-- Session Status -->
    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="handleLogin">
      <!-- Username Input -->
      <div class="mt-4">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          {{ $t('Username') }}
        </label>
        <InputText
          id="name"
          v-model="form.name"
          type="text"
          :invalid="!!errors.name"
          fluid
        />
        <small v-if="errors.name" class="text-red-500">{{ errors.name }}</small>
      </div>

      <!-- Password Input -->
      <div class="mt-4">
        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          {{ $t('Password') }}
        </label>
        <Password
          id="password"
          v-model="form.password"
          :feedback="false"
          :invalid="!!errors.password"
          toggleMask
          fluid
        />
        <small v-if="errors.password" class="text-red-500">{{ errors.password }}</small>
      </div>

      <!-- Remember Me Toggle -->
      <div class="flex text-sm">
        <div class="mt-6 flex items-center">
          <ToggleSwitch
            v-model="form.remember"
            inputId="remember"
          />
          <label for="remember" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $t('Remember me') }}
          </label>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="mt-4">
        <Button
          type="submit"
          :loading="loading"
          :label="$t('Log in')"
          class="w-full"
          rounded
        />
      </div>
    </form>

    <Divider class="my-8" />

    <!-- Forgot Password Link -->
    <p v-if="hasPasswordReset" class="mt-4">
      <router-link
        to="/forgot-password"
        class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
      >
        {{ $t('Forgot your password?') }}
      </router-link>
    </p>
  </GuestLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import GuestLayout from '@/layouts/GuestLayout.vue'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import ToggleSwitch from 'primevue/toggleswitch'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import axios from 'axios'

const router = useRouter()

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
    await axios.get('/sanctum/csrf-cookie')

    // Attempt login
    await axios.post('/login', {
      name: form.name,
      password: form.password,
      remember: form.remember
    })

    // Redirect to dashboard on success
    router.push({ name: 'dashboard' })
  } catch (error) {
    if (error.response?.status === 422) {
      // Validation errors
      const validationErrors = error.response.data.errors
      if (validationErrors.name) errors.name = validationErrors.name[0]
      if (validationErrors.password) errors.password = validationErrors.password[0]
    } else {
      // General error
      errors.password = error.response?.data?.message || 'An error occurred. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

// Translation helper (replace with your i18n solution)
const $t = (key) => {
  const translations = {
    'Login': 'Login',
    'Username': 'Username',
    'Password': 'Password',
    'Remember me': 'Remember me',
    'Log in': 'Log in',
    'Forgot your password?': 'Forgot your password?'
  }
  return translations[key] || key
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