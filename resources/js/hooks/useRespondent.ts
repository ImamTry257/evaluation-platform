import { ref, computed } from 'vue'
import api from '@/services/api'

export function useRespondent() {
  // State
  const respondents = ref<any[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const searchQuery = ref('')

  // Stats
  const stats = ref({
    total: 0,
    active: 0,
    inactive: 0,
  })

  // Computed
  const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

  // Fetch respondents
  async function fetchRespondents(page = 1) {
    loading.value = true
    error.value = null
    try {
      const params: any = {
        page,
        limit: perPage.value,
      }
      if (searchQuery.value) {
        params.search = searchQuery.value
      }
      const { data } = await api.get('/admin/respondents', { params })
      const payload = data.data ?? data
      respondents.value = payload.contents ?? payload.data ?? payload
      currentPage.value = payload.meta?.page ?? payload.current_page ?? 1
      totalItems.value = payload.meta?.total ?? payload.total ?? 0
      updateStats()
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat data responden'
      console.error('Failed to fetch respondents:', err)
    } finally {
      loading.value = false
    }
  }

  // Update stats
  function updateStats() {
    stats.value.total = totalItems.value
    stats.value.active = respondents.value.filter((r) => r.isActive).length
    stats.value.inactive = respondents.value.filter((r) => !r.isActive).length
  }

  // Create respondent
  async function createRespondent(payload: {
    name: string
    username: string
    email: string
    password: string
    isActive?: boolean
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/admin/respondents', payload)
      await fetchRespondents(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal membuat responden'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update respondent
  async function updateRespondent(id: number, payload: {
    name: string
    username: string
    email: string
    password?: string
    isActive?: boolean
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.put(`/admin/respondents/${id}`, payload)
      await fetchRespondents(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal mengupdate responden'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete respondent
  async function deleteRespondent(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/admin/respondents/${id}`)
      await fetchRespondents(currentPage.value)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal menghapus responden'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Search handler (debounced)
  let searchTimeout: ReturnType<typeof setTimeout>
  function onSearch(query: string) {
    searchQuery.value = query
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
      fetchRespondents(1)
    }, 400)
  }

  return {
    // State
    respondents,
    loading,
    error,
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    stats,
    // Methods
    fetchRespondents,
    createRespondent,
    updateRespondent,
    deleteRespondent,
    onSearch,
  }
}
