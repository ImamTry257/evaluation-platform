import { ref, computed } from 'vue'
import api from '@/services/api'
import { useRoute } from 'vue-router'

export function useComponent(questionnaireId?: number) {
  // State
  const components = ref<any[]>([])
  const breadCrumbList = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const searchQuery = ref('')

  // Computed
  const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

  // Fetch components
  async function fetchComponents(page = 1) {
    loading.value = true
    error.value = null
    try {
      const params: any = {
        page,
        limit: perPage.value,
      }
      if (questionnaireId) {
        params.questionnaireId = questionnaireId
      }
      if (searchQuery.value) {
        params.search = searchQuery.value
      }
      const { data } = await api.get('/admin/components', { params })
      const payload = data.data ?? data
      components.value = payload.contents ?? payload.data ?? payload
      breadCrumbList.value = payload.breadCrumbList ?? null
      currentPage.value = payload.meta?.page ?? payload.current_page ?? 1
      totalItems.value = payload.meta?.total ?? payload.total ?? 0
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat data komponen'
      console.error('Failed to fetch components:', err)
    } finally {
      loading.value = false
    }
  }

  // Create component
  async function createComponent(payload: {
    questionnaireId: number
    name: string
    description?: string
    orderNumber?: number
    isActive?: number
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/admin/components', payload)
      await fetchComponents(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal membuat komponen'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update component
  async function updateComponent(id: number, payload: {
    questionnaireId: number
    name: string
    description?: string
    orderNumber?: number
    isActive?: number
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.put(`/admin/components/${id}`, payload)
      await fetchComponents(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal mengupdate komponen'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete component (soft delete)
  async function deleteComponent(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/admin/components/${id}`)
      await fetchComponents(currentPage.value)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal menghapus komponen'
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
      fetchComponents(1)
    }, 400)
  }

  return {
    // State
    components,
    breadCrumbList,
    loading,
    error,
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    // Methods
    fetchComponents,
    createComponent,
    updateComponent,
    deleteComponent,
    onSearch,
  }
}
