import { reactive } from 'vue'

export const useFormErrors = (initialErrors = {}) => {

  const errors = reactive({ ...initialErrors })

  const setErrors = (serverErrors, clearExisting = true) => {
    if (clearExisting) {
      clearErrors()
    }
    
    if (!serverErrors || typeof serverErrors !== 'object') {
      console.warn('setErrors expects an object, received:', serverErrors)
      return
    }

    Object.keys(serverErrors).forEach(field => {
      if (errors.hasOwnProperty(field)) {
        const message = Array.isArray(serverErrors[field]) 
          ? serverErrors[field][0]
          : serverErrors[field]
        
        errors[field] = message
      }
    })
  }

  const setError = (field, message) => {
    if (errors.hasOwnProperty(field)) {
      errors[field] = message
    }
  }

  const clearErrors = (fields = null) => {
    if (!fields) {
      // Clear all errors
      Object.keys(errors).forEach(key => {
        errors[key] = ''
      })
    } else if (Array.isArray(fields)) {
      // Clear specific array of fields
      fields.forEach(field => {
        if (errors.hasOwnProperty(field)) {
          errors[field] = ''
        }
      })
    } else if (typeof fields === 'string') {
      // Clear single field
      if (errors.hasOwnProperty(fields)) {
        errors[fields] = ''
      }
    }
  }

  const hasErrors = () => {

    return Object.values(errors).some(error => error !== '')

  }

  const errorCount = () => {

    return Object.values(errors).filter(error => error !== '').length

  }

  const resetErrors = () => {

    Object.keys(errors).forEach(key => {
      errors[key] = initialErrors[key] || ''
    })

  }

  return {
    errors,
    setErrors,
    setError,
    clearErrors,
    hasErrors,
    errorCount,
    resetErrors
  }
}