<template>
  <GuestLayout :title="$t('Register')">
    <form @submit.prevent="handleRegister">
      <div class="mt-4 space-y-2">
        <!-- ID Number -->
        <div>
          <label for="documento_identidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $t('ID Number') }} *
          </label>
          <InputText
            id="documento_identidad"
            v-model="form.documento_identidad"
            :placeholder="$t('ID number')"
            :invalid="!!errors.documento_identidad"
            autofocus
            fluid
          />
          <small v-if="errors.documento_identidad" class="text-red-500">
            {{ errors.documento_identidad }}
          </small>
        </div>

        <!-- Name -->
        <div>
          <label for="nombres" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $t('Name') }} *
          </label>
          <InputText
            id="nombres"
            v-model="form.nombres"
            :placeholder="$t('Name')"
            :invalid="!!errors.nombres"
            fluid
          />
          <small v-if="errors.nombres" class="text-red-500">{{ errors.nombres }}</small>
        </div>

        <!-- Last Name -->
        <div>
          <label for="apellidos" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $t('Last Name') }} *
          </label>
          <InputText
            id="apellidos"
            v-model="form.apellidos"
            :placeholder="$t('Last Name')"
            :invalid="!!errors.apellidos"
            fluid
          />
          <small v-if="errors.apellidos" class="text-red-500">{{ errors.apellidos }}</small>
        </div>

        <!-- Phone -->
        <div>
          <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $t('Phone') }}
          </label>
          <InputText
            id="telefono"
            v-model="form.telefono"
            :placeholder="$t('Phone')"
            :invalid="!!errors.telefono"
            fluid
          />
          <small v-if="errors.telefono" class="text-red-500">{{ errors.telefono }}</small>
        </div>

        <!-- Username -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $t('Username') }} *
          </label>
          <InputText
            id="name"
            v-model="form.name"
            :placeholder="$t('Username')"
            :invalid="!!errors.name"
            fluid
          />
          <small v-if="errors.name" class="text-red-500">{{ errors.name }}</small>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $t('Email') }} *
          </label>
          <InputText
            id="email"
            v-model="form.email"
            type="email"
            :placeholder="$t('Email')"
            :invalid="!!errors.email"
            fluid
          />
          <small v-if="errors.email" class="text-red-500">{{ errors.email }}</small>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $t('Password') }} *
          </label>
          <Password
            id="password"
            v-model="form.password"
            :feedback="true"
            :invalid="!!errors.password"
            toggleMask
            fluid
          />
          <small v-if="errors.password" class="text-red-500">{{ errors.password }}</small>
        </div>

        <!-- Password Confirmation -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $t('Confirm Password') }} *
          </label>
          <Password
            id="password_confirmation"
            v-model="form.password_confirmation"
            :feedback="false"
            :invalid="!!errors.password_confirmation"
            toggleMask
            fluid
          />
          <small v-if="errors.password_confirmation" class="text-red-500">
            {{ errors.password_confirmation }}
          </small>
        </div>
      </div>

      <!-- Submit Section -->
      <div class="flex items-center justify-end mt-4">
        <router-link
          to="/login"
          class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
        >
          {{ $t('Already registered?') }}
        </router-link>

        <Button
          type="submit"
          :loading="loading"
          :label="$t('Send')"
          class="ms-4"
        />
      </div>
    </form>
  </GuestLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import GuestLayout from '@/layouts/GuestLayout.vue'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import axios from 'axios'

const router = useRouter()

const form = reactive({
  documento_identidad: '',
  nombres: '',
  apellidos: '',
  telefono: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const errors = reactive({
  documento_identidad: '',
  nombres: '',
  apellidos: '',
  telefono: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)

const handleRegister = async () => {
  // Clear previous errors
  Object.keys(errors).forEach(key => errors[key] = '')
  loading.value = true

  try {
    // Get CSRF token first
    await axios.get('/sanctum/csrf-cookie')

    // Attempt registration
    await axios.post('/register', form)

    // Redirect to registered route on success (not dashboard per your original code)
    router.push({ name: 'registered' })
  } catch (error) {
    if (error.response?.status === 422) {
      // Validation errors
      const validationErrors = error.response.data.errors
      Object.keys(validationErrors).forEach(key => {
        if (errors.hasOwnProperty(key)) {
          errors[key] = validationErrors[key][0]
        }
      })
    } else {
      // General error
      errors.email = error.response?.data?.message || 'An error occurred. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

// Translation helper (replace with your i18n solution)
const $t = (key) => {
  const translations = {
    'Register': 'Register',
    'ID Number': 'ID Number',
    'ID number': 'ID number',
    'Name': 'Name',
    'Last Name': 'Last Name',
    'Phone': 'Phone',
    'Username': 'Username',
    'Email': 'Email',
    'Password': 'Password',
    'Confirm Password': 'Confirm Password',
    'Already registered?': 'Already registered?',
    'Send': 'Send'
  }
  return translations[key] || key
}
</script>

<style scoped>
/* Ensure PrimeVue components take full width */
:deep(.p-password) {
  width: 100%;
}

:deep(.p-password input) {
  width: 100%;
}
</style>