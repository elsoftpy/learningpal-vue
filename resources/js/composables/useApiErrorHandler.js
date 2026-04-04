import { ref } from 'vue'

export const useApiErrorHandler = () => {
  const apiError = ref('')
  const apiErrors = ref({})

  const handleApiError = (error, options = {}) => {
    const {
      defaultMessage = 'An error occurred. Please try again.',
      showConsole = process.env.NODE_ENV !== 'production',
      extractValidationErrors = true
    } = options

    // Clear previous errors
    clearApiErrors()

    if (error?.__authRedirectHandled || error?.__staleSession) {
      return {
        type: 'auth_redirect',
        message: '',
        silent: true
      }
    }

    // Log error in development
    if (showConsole) {
      console.error('API Error:', error)
    }

    // Handle different error types
    if (error.response) {
      // Server responded with error status
      const { status, data } = error.response

      switch (status) {
        case 422:
          // Laravel validation errors
          if (extractValidationErrors && data.errors) {
            apiErrors.value = data.errors
            return {
              type: 'validation',
              errors: data.errors,
              message: data.message || 'Validation failed'
            }
          }
          break

        case 419:
          return {
            type: 'auth_redirect',
            message: '',
            silent: true
          }

        case 401:
          return {
            type: 'auth',
            message: data.message || 'Unauthorized. Please log in.'
          }

        case 403:
          return {
            type: 'permission',
            message: data.message || 'You do not have permission.'
          }

        case 404:
          return {
            type: 'not_found',
            message: data.message || 'Resource not found.'
          }

        case 429:
          return {
            type: 'rate_limit',
            message: data.message || 'Too many requests. Please try again later.'
          }

        case 500:
          return {
            type: 'server',
            message: data.message || 'Server error. Please try again later.'
          }
      }

      // General error message
      apiError.value = data.message || data.error || defaultMessage
      return {
        type: 'general',
        message: data.message || data.error || defaultMessage
      }

    } else if (error.request) {
      // Request made but no response
      apiError.value = 'Network error. Please check your connection.'
      return {
        type: 'network',
        message: 'Network error. Please check your connection.'
      }
    } else {
      // Something else happened
      apiError.value = error.message || defaultMessage
      return {
        type: 'client',
        message: error.message || defaultMessage
      }
    }
  }

  const clearApiErrors = () => {
    apiError.value = ''
    apiErrors.value = {}
  }

  const extractFieldErrors = (serverErrors, fieldNames) => {
    const filtered = {}
    fieldNames.forEach(field => {
      if (serverErrors[field]) {
        filtered[field] = Array.isArray(serverErrors[field]) 
          ? serverErrors[field][0]
          : serverErrors[field]
      }
    })
    return filtered
  }

  return {
    apiError,
    apiErrors,
    handleApiError,
    clearApiErrors,
    extractFieldErrors
  }
}
