import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function useLogin() {
  const isLoading = ref(false)
  const errorMessage = ref('')
  const fieldErrors = reactive<Record<string, string[]>>({})

  const authStore = useAuthStore()

  function clearFieldError(field: string) {
    if (fieldErrors[field]) {
      delete fieldErrors[field]
    }
  }

  async function login(username: string, password: string): Promise<boolean> {
    isLoading.value = true
    errorMessage.value = ''
    Object.keys(fieldErrors).forEach(k => delete fieldErrors[k])

    try {
      await authStore.login(username, password)
      return true
    } catch (error: any) {
      const data = error?.response?.data
      const errData = data?.errors
      if (errData && Object.keys(errData).length) {
        Object.assign(fieldErrors, data.errors)
      } else {
        errorMessage.value = data?.message || 'Terjadi kesalahan. Silakan coba lagi.'
      }
      return false
    } finally {
      isLoading.value = false
    }
  }

  return {
    isLoading,
    errorMessage,
    fieldErrors,
    login,
    clearFieldError,
  }
}
