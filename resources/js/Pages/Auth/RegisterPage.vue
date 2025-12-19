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
              v-model="form.personal_id"
              :placeholder="$t('ID Number')"
              :invalid="!!errors.personal_id"
              autofocus
              fluid
            />
            <small v-if="errors.personal_id" class="text-red-500">
              {{ errors.personal_id }}
            </small>
          </div>

          <!-- Name -->
          <div>
            <label for="nombres" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('Name') }} <span class="text-red-500">*</span>
            </label>
            <InputText
              id="nombres"
              v-model="form.first_name"
              :placeholder="$t('Name')"
              :invalid="!!errors.first_name"
              fluid
            />
            <small v-if="errors.first_name" class="text-red-500">{{ errors.first_name }}</small>
          </div>

          <!-- Last Name -->
          <div>
            <label for="apellidos" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('Last Name') }} <span class="text-red-500">*</span>
            </label>
            <InputText
              id="apellidos"
              v-model="form.last_name"
              :placeholder="$t('Last Name')"
              :invalid="!!errors.last_name"
              fluid
            />
            <small v-if="errors.last_name" class="text-red-500">{{ errors.last_name }}</small>
          </div>

          <!-- Phone -->
          <div>
            <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('Phone') }}
            </label>
            <InputText
              id="telefono"
              v-model="form.phone"
              :placeholder="$t('Phone')"
              :invalid="!!errors.phone"
              fluid
            />
            <small v-if="errors.phone" class="text-red-500">{{ errors.phone }}</small>
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
  personal_id: '',
  first_name: '',
  last_name: '',
  phone: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const errors = reactive({
  personal_id: '',
  first_name: '',
  last_name: '',
  phone: '',
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
      type: 'person',
      personal_id: form.personal_id,
      first_name: form.first_name,
      last_name: form.last_name,
      phone: form.phone,
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation
    });

    if (result.success) {
      console.log('Registration successful:', result);
      router.push({ 
        name: 'settings.users.profile', 
        params: { id: result.user.id },
        query: { from: 'register' }
      });
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
      console.log(error);
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