import { ref, computed } from 'vue'
import api from '@/services/api'

export function useSubComponent(componentId?: number) {
  // State
  const subComponents = ref<any[]>([])
  const breadCrumbList = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const searchQuery = ref('')

  // Computed
  const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

  // Fetch sub-components
  async function fetchSubComponents(page = 1) {
    loading.value = true
    error.value = null
    try {
      const params: any = {
        page,
        limit: perPage.value,
      }
      if (componentId) {
        params.componentId = componentId
      }
      if (searchQuery.value) {
        params.search = searchQuery.value
      }
      const { data } = await api.get('/admin/sub-components', { params })
      const payload = data.data ?? data
      subComponents.value = payload.contents ?? payload.data ?? payload
      breadCrumbList.value = payload.breadCrumbList ?? null
      currentPage.value = payload.meta?.page ?? payload.current_page ?? 1
      totalItems.value = payload.meta?.total ?? payload.total ?? 0
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat data sub komponen'
      console.error('Failed to fetch sub-components:', err)
    } finally {
      loading.value = false
    }
  }

  // Create sub-component
  async function createSubComponent(payload: {
    componentId: number
    name: string
    description?: string
    is_active?: number
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/admin/sub-components', payload)
      await fetchSubComponents(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal membuat sub komponen'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update sub-component
  async function updateSubComponent(id: number, payload: {
    componentId: number
    name: string
    description?: string
    is_active?: number
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.put(`/admin/sub-components/${id}`, payload)
      await fetchSubComponents(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal mengupdate sub komponen'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete sub-component
  async function deleteSubComponent(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/admin/sub-components/${id}`)
      await fetchSubComponents(currentPage.value)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal menghapus sub komponen'
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
      fetchSubComponents(1)
    }, 400)
  }

  return {
    // State
    subComponents,
    breadCrumbList,
    loading,
    error,
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    // Methods
    fetchSubComponents,
    createSubComponent,
    updateSubComponent,
    deleteSubComponent,
    onSearch,
  }
}
