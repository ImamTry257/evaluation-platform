import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  // localStorage keys: tokenRespondent, tokenAdmin, userRespondent, userAdmin
  const token = ref<string | null>(null)
  const user = ref<any>(null)

  // Initialize from localStorage
  function initAuth() {
    // Check admin token first
    const adminToken = localStorage.getItem('tokenAdmin')
    const adminUser = localStorage.getItem('userAdmin')
    if (adminToken && adminUser) {
      token.value = adminToken
      user.value = JSON.parse(adminUser)
      return
    }

    // Check respondent token
    const respondentToken = localStorage.getItem('tokenRespondent')
    const respondentUser = localStorage.getItem('userRespondent')
    if (respondentToken && respondentUser) {
      token.value = respondentToken
      user.value = JSON.parse(respondentUser)
      return
    }

    // Legacy fallback: single 'token' key
    const legacyToken = localStorage.getItem('token')
    if (legacyToken) {
      token.value = legacyToken
    }
  }

  // Run on store init
  initAuth()

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'ADMIN')
  const isSuperAdmin = computed(() => user.value?.role === 'SUPERADMIN')
  const isRespondent = computed(() => user.value?.role === 'RESPONDENT')
  const isAdminOrSuperAdmin = computed(() => isAdmin.value || isSuperAdmin.value)

  function saveAuth(tokenVal: string, userVal: any) {
    token.value = tokenVal
    user.value = userVal

    const role = userVal?.role
    if (role === 'ADMIN' || role === 'SUPERADMIN') {
      localStorage.setItem('tokenAdmin', tokenVal)
      localStorage.setItem('userAdmin', JSON.stringify(userVal))
      // Clear respondent token to avoid conflict
      localStorage.removeItem('tokenRespondent')
      localStorage.removeItem('userRespondent')
    } else {
      localStorage.setItem('tokenRespondent', tokenVal)
      localStorage.setItem('userRespondent', JSON.stringify(userVal))
      // Clear admin token to avoid conflict
      localStorage.removeItem('tokenAdmin')
      localStorage.removeItem('userAdmin')
    }

    // Clean legacy key
    localStorage.removeItem('token')
  }

  async function clearAuth() {
    token.value = null
    user.value = null
    localStorage.removeItem('tokenAdmin')
    localStorage.removeItem('userAdmin')
    localStorage.removeItem('tokenRespondent')
    localStorage.removeItem('userRespondent')
    localStorage.removeItem('token')
  }

  async function login(username: string, password: string) {
    const response = await api.post('/auth/login', { username, password })
    console.log(response);
    saveAuth(response.data.data.token, response.data.data.user)
  }

  async function loginAdmin(email: string, password: string) {
    const response = await api.post('/auth/login-admin', { email, password })
    saveAuth(response.data.data.token, response.data.data.user)
  }

  async function register(data: { name: string; username: string; email: string; password: string; password_confirmation: string }) {
    await api.post('/auth/register', data)
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch (e) {
      // Ignore error - token will be cleared locally anyway
    }
    clearAuth()
  }

  async function fetchProfile() {
    const response = await api.get('/auth/profile')
    user.value = response.data.data
    // Update persisted user data
    const role = user.value?.role
    if (role === 'ADMIN' || role === 'SUPERADMIN') {
      localStorage.setItem('userAdmin', JSON.stringify(user.value))
    } else {
      localStorage.setItem('userRespondent', JSON.stringify(user.value))
    }
  }

  async function validateToken() {
    try {
      const response = await api.get('/auth/validate')
      // Verify token is still valid and get fresh user data
      const freshUser = response.data.data
      user.value = freshUser
      // Update persisted user data
      const role = freshUser?.role
      if (role === 'ADMIN' || role === 'SUPERADMIN') {
        localStorage.setItem('userAdmin', JSON.stringify(freshUser))
      } else {
        localStorage.setItem('userRespondent', JSON.stringify(freshUser))
      }
      return true
    } catch (error) {
      console.log('Token validation failed:', error)
      clearAuth()
      return false
    }
  }

  return {
    token,
    user,
    isAuthenticated,
    isAdmin,
    isSuperAdmin,
    isRespondent,
    isAdminOrSuperAdmin,
    login,
    loginAdmin,
    register,
    logout,
    fetchProfile,
    validateToken,
    clearAuth
  }
})
