import { ref, computed } from 'vue'
import api from '@/services/api'

export function useQuestionnaire() {
  // State
  const questionnaires = ref<any[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const searchQuery = ref('')
  const statusFilter = ref('')
  const periodFilter = ref('')

  // Computed
  const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

  // Fetch questionnaires
  async function fetchQuestionnaires(page = 1) {
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
      if (statusFilter.value) {
        params.status = statusFilter.value
      }
      if (periodFilter.value) {
        params.evaluationPeriodId = periodFilter.value
      }
      const { data } = await api.get('/admin/questionnaires', { params })
      const payload = data.data ?? data
      questionnaires.value = payload.contents ?? payload.data ?? payload
      currentPage.value = payload.meta?.page ?? payload.current_page ?? 1
      totalItems.value = payload.meta?.total ?? payload.total ?? 0
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat data kuesioner'
      console.error('Failed to fetch questionnaires:', err)
    } finally {
      loading.value = false
    }
  }

  // Create questionnaire
  async function createQuestionnaire(payload: {
    evaluationPeriodId: number
    title: string
    description?: string
    durationMinutes: number
    status?: string
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/admin/questionnaires', payload)
      await fetchQuestionnaires(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal membuat kuesioner'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update questionnaire
  async function updateQuestionnaire(id: number, payload: {
    evaluationPeriodId: number
    title: string
    description?: string
    durationMinutes: number
    status?: string
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.put(`/admin/questionnaires/${id}`, payload)
      await fetchQuestionnaires(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal mengupdate kuesioner'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete questionnaire
  async function deleteQuestionnaire(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/admin/questionnaires/${id}`)
      await fetchQuestionnaires(currentPage.value)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal menghapus kuesioner'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Publish questionnaire
  async function publishQuestionnaire(id: number) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post(`/admin/questionnaires/${id}/publish`)
      await fetchQuestionnaires(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal mempublish kuesioner'
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
      fetchQuestionnaires(1)
    }, 400)
  }

  // Status filter handler
  function onStatusFilter(status: string) {
    statusFilter.value = status
    fetchQuestionnaires(1)
  }

  // Period filter handler
  function onPeriodFilter(periodId: string) {
    periodFilter.value = periodId
    fetchQuestionnaires(1)
  }

  return {
    // State
    questionnaires,
    loading,
    error,
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    statusFilter,
    periodFilter,
    // Methods
    fetchQuestionnaires,
    createQuestionnaire,
    updateQuestionnaire,
    deleteQuestionnaire,
    publishQuestionnaire,
    onSearch,
    onStatusFilter,
    onPeriodFilter,
  }
}
