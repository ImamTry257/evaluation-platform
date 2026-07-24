import axios from 'axios'

const apiV2 = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL
    ? `${import.meta.env.VITE_API_BASE_URL}/v2`
    : '/api/v2',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Attach token — reuse logic from existing auth store
apiV2.interceptors.request.use((config) => {
  const token = localStorage.getItem('tokenAdmin')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Handle 401
apiV2.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('tokenAdmin')
      const { default: router } = await import('@/router')
      router.push('/login/admin')
    }
    return Promise.reject(error)
  },
)

export default apiV2
