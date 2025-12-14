import { ref } from 'vue'
import { useFormErrors } from './useFormErrors'
import { useApiErrorHandler } from './useApiErrorHandler'

export const useFormSubmitter = (formFields = {}) => {
  const { errors, setErrors, clearErrors } = useFormErrors(formFields)
  const { handleApiError } = useApiErrorHandler()

  const isLoading = ref(false)
  const success = ref(false)

  const submit = async (action, data, options = {}) => {
    const {
      onSuccess = () => {},
      onError = () => {},
      redirectTo = null, // retained for backward compatibility
      mapErrors = {}
    } = options

    clearErrors()
    isLoading.value = true
    success.value = false

    try {
      const result = await action(data)
      success.value = true

      await onSuccess(result)
      return { success: true, data: result }
    } catch (error) {
      const errorInfo = handleApiError(error)

      if (errorInfo.type === 'validation') {
        const mappedErrors = mapErrorFields(errorInfo.errors, mapErrors)
        setErrors(mappedErrors)
      } else if (Object.prototype.hasOwnProperty.call(errors, 'general')) {
        errors.general = errorInfo.message
      } else {
        const fallbackField = Object.keys(formFields)[0]
        if (fallbackField && Object.prototype.hasOwnProperty.call(errors, fallbackField)) {
          errors[fallbackField] = errorInfo.message
        }
      }

      await onError(errorInfo)
      return { success: false, error: errorInfo }
    } finally {
      isLoading.value = false
    }
  }

  const mapErrorFields = (serverErrors, mapping) => {
    if (!mapping || Object.keys(mapping).length === 0) {
      return serverErrors
    }

    const mapped = {}
    Object.keys(serverErrors).forEach(serverField => {
      const formField = mapping[serverField] || serverField
      if (Object.prototype.hasOwnProperty.call(formFields, formField)) {
        mapped[formField] = serverErrors[serverField]
      }
    })

    return mapped
  }

  return {
    errors,
    isLoading,
    success,
    submit,
    clearErrors,
    setErrors
  }
}