import { ref, computed } from 'vue'
import api from '@/services/api'

export function usePeriode() {
  // State
  const periods = ref<any[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const searchQuery = ref('')
  const statusFilter = ref('')

  // Computed
  const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

  // Fetch periods
  async function fetchPeriods(page = 1) {
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
      if (statusFilter.value !== '') {
        params.isActive = statusFilter.value
      }
      const { data } = await api.get('/admin/periods', { params })
      const payload = data.data ?? data
      periods.value = payload.contents ?? payload.data ?? payload
      currentPage.value = payload.meta?.page ?? payload.current_page ?? 1
      totalItems.value = payload.meta?.total ?? payload.total ?? 0
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat data periode'
      console.error('Failed to fetch periods:', err)
    } finally {
      loading.value = false
    }
  }

  // Create period
  async function createPeriod(payload: {
    name: string
    description?: string
    startDate: string
    endDate: string
    isActive?: boolean
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/admin/periods', payload)
      await fetchPeriods(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal membuat periode'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update period
  async function updatePeriod(id: number, payload: {
    name: string
    description?: string
    startDate: string
    endDate: string
    isActive?: boolean
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.put(`/admin/periods/${id}`, payload)
      await fetchPeriods(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal mengupdate periode'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete period
  async function deletePeriod(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/admin/periods/${id}`)
      await fetchPeriods(currentPage.value)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal menghapus periode'
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
      fetchPeriods(1)
    }, 400)
  }

  // Status filter handler
  function onStatusFilter(status: string) {
    statusFilter.value = status
    fetchPeriods(1)
  }

  return {
    // State
    periods,
    loading,
    error,
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    statusFilter,
    // Methods
    fetchPeriods,
    createPeriod,
    updatePeriod,
    deletePeriod,
    onSearch,
    onStatusFilter,
  }
}
