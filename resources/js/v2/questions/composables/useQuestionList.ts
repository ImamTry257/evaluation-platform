import { ref, reactive, computed } from 'vue'
import apiV2 from '@/services/apiV2'
import type { Question, QuestionFilterParams, PaginationMeta } from '@/v2/questions/types/question'

export function useQuestionList() {
  const questions = ref<Question[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const filters = reactive<QuestionFilterParams>({
    search: '',
    instrumentId: undefined,
    componentId: undefined,
    subComponentId: undefined,
    indicatorId: undefined,
    page: 1,
    limit: 10,
    sortBy: 'order_number',
    sortOrder: 'asc',
  })

  const meta = reactive<PaginationMeta>({
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 0,
  })

  const hasMore = computed(() => meta.page < meta.totalPages)

  async function fetchQuestions(): Promise<void> {
    loading.value = true
    error.value = null

    const params: Record<string, any> = {
      page: filters.page,
      limit: filters.limit,
      sortBy: filters.sortBy,
      sortOrder: filters.sortOrder,
    }

    if (filters.search) params.search = filters.search
    if (filters.instrumentId) params.instrumentId = filters.instrumentId
    if (filters.componentId) params.componentId = filters.componentId
    if (filters.subComponentId) params.subComponentId = filters.subComponentId
    if (filters.indicatorId) params.indicatorId = filters.indicatorId

    try {
      const res = await apiV2.get('/admin/questions', { params })
      const data = res.data.data
      questions.value = data.contents ?? []
      meta.page = data.meta.page
      meta.limit = data.meta.limit
      meta.total = data.meta.total
      meta.totalPages = data.meta.totalPages
    } catch (e: any) {
      error.value = e?.response?.data?.message || 'Gagal memuat data pernyataan'
      questions.value = []
    } finally {
      loading.value = false
    }
  }

  function setPage(page: number): void {
    if (page < 1 || page > meta.totalPages) return
    filters.page = page
    fetchQuestions()
  }

  function setSearch(q: string): void {
    filters.search = q
    filters.page = 1
    fetchQuestions()
  }

  function applyFilter(): void {
    filters.page = 1
    fetchQuestions()
  }

  function resetFilter(): void {
    filters.search = ''
    filters.instrumentId = undefined
    filters.componentId = undefined
    filters.subComponentId = undefined
    filters.indicatorId = undefined
    filters.page = 1
    fetchQuestions()
  }

  return {
    questions,
    loading,
    error,
    filters,
    meta,
    hasMore,
    fetchQuestions,
    setPage,
    setSearch,
    applyFilter,
    resetFilter,
  }
}
