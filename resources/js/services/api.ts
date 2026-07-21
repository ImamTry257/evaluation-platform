import axios from 'axios'
import router from '@/router'
import { useAuthStore } from '@/stores/auth'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor - attach token
api.interceptors.request.use((config) => {
  const authStore = useAuthStore()

  let token = null
  if (authStore.user?.role === 'ADMIN' || authStore.user?.role === 'SUPERADMIN') {
    token = localStorage.getItem('tokenAdmin')
  } else if (authStore.user?.role === 'RESPONDENT') {
    token = localStorage.getItem('tokenRespondent')
  } else {
    // Fallback: try both
    token = localStorage.getItem('tokenAdmin') || localStorage.getItem('tokenRespondent')
  }
  
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Response interceptor - handle errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

export default api
