import router from "@/router"
import { useAuthStore } from "@/stores/auth"
import { reactive, ref } from "vue"

export function useLoginAdmin(){

    const authStore = useAuthStore()

    const isLoading = ref(false)
    const errorMessage = ref('')
    const fieldErrors = reactive<Record<string, string[]>>({})


    async function login(email: string, password: string) {
        isLoading.value = true
        errorMessage.value = ''

        try {
            await authStore.loginAdmin(email, password)

            if (authStore.user?.role === 'SUPERADMIN' || authStore.user?.role === 'ADMIN') {
            router.push('/admin')
            }
        } catch (error: any) {
            const data = error?.response?.data
            const errData = data?.errors
            if (errData && Object.keys(errData).length) {
            Object.assign(fieldErrors, data.errors)
            } else {
            errorMessage.value = data?.message || 'Terjadi kesalahan. Silakan coba lagi.'
            }
        } finally {
            isLoading.value = false
        }
    }

    async function clearFieldError(field: string) {
        if (fieldErrors[field]) {
            delete fieldErrors[field]
        }
    }
    

    return {
        login,
        clearFieldError,
        isLoading,
        errorMessage,
        fieldErrors
    }
}