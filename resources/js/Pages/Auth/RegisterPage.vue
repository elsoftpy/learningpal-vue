<template>
    <div class="w-full">
      <form @submit.prevent="handleRegister">
        <div class="mt-4 space-y-2">
          <!-- ID Number -->
          <div>
            <label for="documento_identidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('ID Number') }} <span class="text-red-500">*</span>
            </label>
            <InputText
              id="documento_identidad"
              v-model="form.documento_identidad"
              :placeholder="$t('ID Number')"
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
              {{ $t('Name') }} <span class="text-red-500">*</span>
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
              {{ $t('Last Name') }} <span class="text-red-500">*</span>
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
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('Email') }} <span class="text-red-500">*</span> 
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
              {{ $t('Password') }} <span class="text-red-500">*</span>
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
              {{ $t('Confirm Password') }} <span class="text-red-500">*</span>
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
            :to="{ name: 'login' }"
            class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
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
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import { useI18n } from 'vue-i18n'
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
    const authStore = useAuthStore();

    const result = await authStore.register({
      documento_identidad: form.documento_identidad,
      nombres: form.nombres,
      apellidos: form.apellidos,
      telefono: form.telefono,
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation
    });

    if (result.success) {
      router.push({ name: 'registered' })
    }else {
      // Handle unexpected failure
      console.error('Registration failed:', result.message);
    }
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
const { t : $t } = useI18n()
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