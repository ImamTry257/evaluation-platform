import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('token'))
  const user = ref<any>(null)

  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password?: string, type: string = 'RESPONDENT') {
    const response = await api.post('/auth/login', { email, password, type })
    token.value = response.data.data.token
    user.value = response.data.data.user
    localStorage.setItem('token', token.value!)
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  async function fetchProfile() {
    const response = await api.get('/auth/profile')
    user.value = response.data.data
  }

  return { token, user, isAuthenticated, login, logout, fetchProfile }
})
