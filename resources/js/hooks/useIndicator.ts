import { ref, computed } from 'vue'
import api from '@/services/api'

export function useIndicator(subComponentId?: number) {
  // State
  const indicators = ref<any[]>([])
  const breadCrumbList = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const searchQuery = ref('')

  // Computed
  const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

  // Fetch indicators
  async function fetchIndicators(page = 1) {
    loading.value = true
    error.value = null
    try {
      const params: any = {
        page,
        limit: perPage.value,
      }
      if (subComponentId) {
        params.subComponentId = subComponentId
      }
      if (searchQuery.value) {
        params.search = searchQuery.value
      }
      const { data } = await api.get('/admin/indicators', { params })
      const payload = data.data ?? data
      indicators.value = payload.contents ?? payload.data ?? payload
      breadCrumbList.value = payload.breadCrumbList ?? null
      currentPage.value = payload.meta?.page ?? payload.current_page ?? 1
      totalItems.value = payload.meta?.total ?? payload.total ?? 0
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat data indikator'
      console.error('Failed to fetch indicators:', err)
    } finally {
      loading.value = false
    }
  }

  // Create indicator
  async function createIndicator(payload: {
    subComponentId: number
    name: string
    description?: string
    is_active?: number
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/admin/indicators', payload)
      await fetchIndicators(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal membuat indikator'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update indicator
  async function updateIndicator(id: number, payload: {
    subComponentId: number
    name: string
    description?: string
    is_active?: number
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.put(`/admin/indicators/${id}`, payload)
      await fetchIndicators(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal mengupdate indikator'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete indicator
  async function deleteIndicator(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/admin/indicators/${id}`)
      await fetchIndicators(currentPage.value)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal menghapus indikator'
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
      fetchIndicators(1)
    }, 400)
  }

  return {
    // State
    indicators,
    breadCrumbList,
    loading,
    error,
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    // Methods
    fetchIndicators,
    createIndicator,
    updateIndicator,
    deleteIndicator,
    onSearch,
  }
}
