// composables/useAuthForm.js - Specialized for auth forms
import { useFormErrors } from './useFormErrors'
import { useApiErrorHandler } from './useApiErrorHandler'
import { ref } from 'vue'

export const useAuthForm = (formFields = {}) => {
  const { errors, setErrors, clearErrors } = useFormErrors(formFields)
  const { handleApiError, extractFieldErrors } = useApiErrorHandler()
  
  const loading = ref(false)
  const success = ref(false)
  
  const submit = async (action, data, options = {}) => {
    const {
      onSuccess = () => {},
      onError = () => {},
      redirectTo = null,
      mapErrors = {}
    } = options
    
    clearErrors()
    loading.value = true
    success.value = false
    
    try {
      const result = await action(data)
      success.value = true
      
      // Call success callback
      await onSuccess(result)
      
      return { success: true, data: result }
      
    } catch (error) {
      const errorInfo = handleApiError(error)
      
      if (errorInfo.type === 'validation') {
        // Map server field names to form field names if needed
        const mappedErrors = mapErrorFields(errorInfo.errors, mapErrors)
        setErrors(mappedErrors)
      } else {
        // Set general error (usually on password field for auth forms)
        errors.password = errorInfo.message
      }
      
      // Call error callback
      await onError(errorInfo)
      
      return { success: false, error: errorInfo }
      
    } finally {
      loading.value = false
    }
  }
  
  /**
   * Map server error field names to form field names
   */
  const mapErrorFields = (serverErrors, mapping) => {
    if (!mapping || Object.keys(mapping).length === 0) {
      return serverErrors
    }
    
    const mapped = {}
    Object.keys(serverErrors).forEach(serverField => {
      const formField = mapping[serverField] || serverField
      if (formFields.hasOwnProperty(formField)) {
        mapped[formField] = serverErrors[serverField]
      }
    })
    
    return mapped
  }
  
  return {
    errors,
    loading,
    success,
    submit,
    clearErrors,
    setErrors
  }
}