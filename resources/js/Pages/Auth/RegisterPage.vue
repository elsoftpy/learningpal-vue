<template>
    <div class="w-full">
      <Form
        :key="formKey"
        v-slot="$form"
        :resolver="resolver"
        :initialValues="initialValues"
        :validateOnBlur="true"
        @submit="handleRegister"
        class="max-w-md mx-auto bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md"
      >
        <Message
          v-if="errors.general"
          severity="error"
          size="small"
          variant="outlined"
        >
          {{ errors.general }}
        </Message>
        <div class="mt-4 space-y-2">
          <!-- ID Number -->
          <div>
            <label for="documento_identidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('ID Number') }} <span class="text-red-500">*</span>
            </label>
            <InputText
              id="personal_id"
              name="personal_id"
              :placeholder="$t('ID Number')"
              autofocus
              fluid
            />
            <Message 
              v-if="$form.personal_id?.invalid"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ $form.personal_id.error?.message }}
            </Message>
            <Message 
              v-if="errors.personal_id"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ errors.personal_id }}
            </Message>
          </div>

          <!-- Name -->
          <div>
            <label for="nombres" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('Name') }} <span class="text-red-500">*</span>
            </label>
            <InputText
              id="first_name"
              name="first_name"
              :placeholder="$t('Name')"
              fluid
            />
            <Message 
              v-if="$form.first_name?.invalid"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ $form.first_name.error?.message }}
            </Message>
            <Message 
              v-if="errors.first_name"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ errors.first_name }}
            </Message>
          </div>

          <!-- Last Name -->
          <div>
            <label for="apellidos" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('Last Name') }} <span class="text-red-500">*</span>
            </label>
            <InputText
              id="last_name"
              name="last_name"
              :placeholder="$t('Last Name')"
              fluid
            />
            <Message 
              v-if="$form.last_name?.invalid"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ $form.last_name.error?.message }}
            </Message>
            <Message 
              v-if="errors.last_name"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ errors.last_name }}
            </Message>
          </div>

          <!-- Phone -->
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('Phone') }}
            </label>
            <InputText
              id="phone"
              name="phone"
              :placeholder="$t('Phone')"
              fluid
            />
            <Message 
              v-if="$form.phone?.invalid"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ $form.phone.error?.message }}
            </Message>
            <Message 
              v-if="errors.phone"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ errors.phone }}
            </Message>
          </div>
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('Email') }} <span class="text-red-500">*</span> 
            </label>
            <InputText
              id="email"
              name="email"
              type="email"
              :placeholder="$t('Email')"
              fluid
            />
            <Message 
              v-if="$form.email?.invalid"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ $form.email.error?.message }}
            </Message>
            <Message 
              v-if="errors.email"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ errors.email }}
            </Message>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('Password') }} <span class="text-red-500">*</span>
            </label>
            <Password
              id="password"
              name="password"
              :feedback="true"
              toggleMask
              fluid
            />
            <Message 
              v-if="$form.password?.invalid"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ $form.password.error?.message }}
            </Message>
            <Message 
              v-if="errors.password"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ errors.password }}
            </Message>
          </div>

          <!-- Password Confirmation -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              {{ $t('Confirm Password') }} <span class="text-red-500">*</span>
            </label>
            <Password
              id="password_confirmation"
              name="password_confirmation"
              :feedback="false"
              toggleMask
              fluid
            />
            <Message 
              v-if="$form.password_confirmation?.invalid"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ $form.password_confirmation.error?.message }}
            </Message>
            <Message 
              v-if="errors.password_confirmation"
              severity="error"
              size="small"
              variant="simple"
            >
              {{ errors.password_confirmation }}
            </Message>
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
            :loading="isLoading"
            :label="$t('Send')"
            class="ms-4"
          />
        </div>
      </Form>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from 'vue-i18n'
import { useApiErrorHandler } from '@/composables/useApiErrorHandler'
import { useFormSubmitter } from '@/composables/useFormSubmitter'
import { useFormValues } from '@/composables/useFormValues';
import { createRegisterSchema } from '@/schemas/register'
import { zodResolver } from '@primevue/forms/resolvers/zod'
import { useToast } from 'primevue/usetoast'
import { Form } from '@primevue/forms'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Password from 'primevue/password'
import Button from 'primevue/button'
import { set } from 'zod'

const { t: $t } = useI18n()
const { handleApiError } = useApiErrorHandler();
const { extractFormData } = useFormValues();

const router = useRouter()
const registerSchema = computed(() => createRegisterSchema($t))
const resolver = computed(() => zodResolver(registerSchema.value))
const toast = useToast();

const formKey = ref(0)

const initialValues = {
  personal_id: '',
  first_name: '',
  last_name: '',
  phone: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
}

const { errors, isLoading, setErrors, clearErrors } = useFormSubmitter({
  personal_id: '',
  first_name: '',
  last_name: '',
  phone: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  general: ''
})

const handleRegister = async (formData) => {
  // Clear previous errors
  clearErrors()
  const { valid, values } = extractFormData(formData)
  
  if (!valid) {
    return
  }

  isLoading.value = true

  try {
    const authStore = useAuthStore();

    const result = await authStore.register({
      type: 'person',
      personal_id: values.personal_id,
      first_name: values.first_name,
      last_name: values.last_name,
      phone: values.phone,
      name: values.name,
      email: values.email,
      password: values.password,
      password_confirmation: values.password_confirmation
    });

    console.log('Registration successful:', result);
    
    router.push({ 
      name: 'settings.users.profile', 
      params: { id: result.user.id },
      query: { from: 'register' }
    });
    
  } catch (error) {
      const apiError = handleApiError(error);
      if (apiError.isValidationError) {
          setErrors(apiError.validationErrors);
      } else {
          setErrors({ general: apiError.message || $t('An unexpected error occurred. Please try again.') });
          toast.add({
              severity: 'error',
              summary: $t('Error'),
              detail: apiError.message || $t('An unexpected error occurred. Please try again.'),
              life: 5000,
          });
      }
  } finally {
    isLoading.value = false
  }
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