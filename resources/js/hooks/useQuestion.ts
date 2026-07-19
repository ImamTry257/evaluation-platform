import { ref, computed } from 'vue'
import api from '@/services/api'

export function useQuestion(indicatorId?: number) {
  // State
  const questions = ref<any[]>([])
  const breadCrumbList = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const searchQuery = ref('')

  // Computed
  const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

  // Fetch questions
  async function fetchQuestions(page = 1) {
    loading.value = true
    error.value = null
    try {
      const params: any = {
        page,
        limit: perPage.value,
      }
      if (indicatorId) {
        params.indicatorId = indicatorId
      }
      if (searchQuery.value) {
        params.search = searchQuery.value
      }
      const { data } = await api.get('/admin/questions', { params })
      const payload = data.data ?? data
      questions.value = payload.contents ?? payload.data ?? payload
      breadCrumbList.value = payload.breadCrumbList ?? null
      currentPage.value = payload.meta?.page ?? payload.current_page ?? 1
      totalItems.value = payload.meta?.total ?? payload.total ?? 0
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat data pertanyaan'
      console.error('Failed to fetch questions:', err)
    } finally {
      loading.value = false
    }
  }

  // Fetch breadCrumbList only (lightweight, for add/edit/view pages)
  async function fetchBreadCrumbList(targetIndicatorId: number) {
    try {
      const { data } = await api.get('/admin/questions', {
        params: { indicatorId: targetIndicatorId, limit: 1 },
      })
      const payload = data.data ?? data
      breadCrumbList.value = payload.breadCrumbList ?? null
    } catch (err: any) {
      console.error('Failed to fetch breadCrumbList:', err)
    }
  }

  // Get single question
  async function getQuestion(id: number) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/admin/questions/${id}`)
      return data.data ?? data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat pertanyaan'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Create question
  async function createQuestion(payload: {
    indicatorId: number
    questionText: string
    weight: number
    isActive?: number
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/admin/questions', payload)
      await fetchQuestions(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal membuat pertanyaan'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update question
  async function updateQuestion(id: number, payload: {
    indicatorId: number
    questionText: string
    weight: number
    isActive?: number
  }) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.put(`/admin/questions/${id}`, payload)
      await fetchQuestions(currentPage.value)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal mengupdate pertanyaan'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete question
  async function deleteQuestion(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/admin/questions/${id}`)
      await fetchQuestions(currentPage.value)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal menghapus pertanyaan'
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
      fetchQuestions(1)
    }, 400)
  }

  return {
    // State
    questions,
    breadCrumbList,
    loading,
    error,
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    // Methods
    fetchQuestions,
    fetchBreadCrumbList,
    getQuestion,
    createQuestion,
    updateQuestion,
    deleteQuestion,
    onSearch,
  }
}
