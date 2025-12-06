<template>
  <div class="w-full">
    <!-- PrimeVue Form Component -->
    <Form
      v-slot="$form"
      :resolver="resolver"
      :initial-values="form"
      @submit="handleLogin"
      :validateOnBlur="true"
      class="flex flex-col gap-4"
    >

      <!-- Username Input -->
      <div class="mt-4">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          {{ $t('Username') }} <span class="text-red-500">*</span>
        </label>
        <InputText
          id="name"
          name="name"
          type="text"
          fluid
        />
        <!-- Client-side error  -->
         <Message
          v-if="$form.name?.invalid"
          severity="error"
          size="small"
          variant="simple"
        >
          {{ $form.name.error?.message }}
        </Message>
        <!-- Server-side error -->
        <Message
          v-if="errors?.name"
          severity="error"
          size="small"
          variant="simple"
        >
          {{ errors?.name }}
        </Message>
      </div>

      <!-- Password Input -->
      <div class="mt-4">
        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          {{ $t('Password') }} <span class="text-red-500">*</span>
        </label>
        <Password
          id="password"
          name="password"
          :feedback="false"
          toggleMask
          fluid
        />
        <!-- Client-side error -->
        <Message
          v-if="$form.password?.invalid"
          severity="error"
          size="small"
          variant="simple"
        >
          {{ $form.password.error?.message }}
        </Message>
        <!-- Server-side error -->
        <Message
          v-if="errors.password"
          severity="error"
          size="small"
          variant="simple"
        >
          {{ errors?.password }}
        </Message>
      </div>

      <!-- Remember me -->
      <div class="flex text-sm">
        <div class="mt-6 flex items-center">
          <ToggleSwitch name="remember" inputId="remember" />
          <label for="remember" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $t('Remember me') }}
          </label>
        </div>
      </div>

      <!-- Submit button-->
      <div class="mt-4">
        <Button
          type="submit"
          :loading="loading"
          :label="$t('Log in')"
          class="w-full"
          rounded
        />
      </div>
    </Form>

    <!-- Divider and Forgot Password / Register links-->
    <Divider class="my-8" />

    <div class="flex w-full justify-between mt-4">
      <div class="flex w-full justify-between mt-4">
        
        <!-- Forgot Password Link -->
        <p v-if="hasPasswordReset">
          <router-link
            :to="{ name: 'forgot-password' }"
            class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
          >
            {{ $t('Forgot your password?') }}
          </router-link>
        </p>

        <!-- Register Link -->
        <p>
          <router-link
            :to="{ name: 'register' }"
            class="ml-4 text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
          >
            {{ $t('Register') }}
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useApiErrorHandler } from '@/composable/useApiErrorHandler'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../../stores/auth'
import { useAuthForm } from '@/composable/useAuthForm'
import { createLoginSchema } from '@/schemas/login'
import { Form } from '@primevue/forms'
import { zodResolver } from '@primevue/forms/resolvers/zod'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Message from 'primevue/message'
import ToggleSwitch from 'primevue/toggleswitch'
import Button from 'primevue/button'
import Divider from 'primevue/divider'

const { t: $t } = useI18n()
const { handleApiError } = useApiErrorHandler()
const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const loginSchema = computed(() => createLoginSchema($t))
const resolver = zodResolver(loginSchema.value)
const status = ref('')
const hasPasswordReset = ref(true)


const form = reactive({
  name: '',
  password: '',
  remember: false
})

const { errors, loading, setErrors, clearErrors } = useAuthForm({
  name: '',
  password: '',
})


const handleLogin = async ({valid, values}) => {
  clearErrors()
  loading.value = true

  try {

    if (!valid) {
      loading.value = false
      return
    }

    const result = await authStore.login(values) // Use values
    const redirectTo = route.query.redirect || '/dashboard'

    router.push(redirectTo)
  } catch (error) {
    const apiError = handleApiError(error)
    
    if (apiError?.type === 'validation' && apiError.errors) {
      setErrors(apiError.errors)
      return
    }

    errors.password = apiError?.message || $t('An unexpected error occurred. Please try again.')
  } finally {
    loading.value = false
  }
}
</script>