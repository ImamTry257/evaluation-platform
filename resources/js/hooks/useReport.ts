import { ref, computed } from 'vue'
import api from '@/services/api'

export function useReport() {
  // State
  const reports = ref<any[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const perPage = ref(10)
  const totalItems = ref(0)
  const searchQuery = ref('')

  const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

  // Stats from API summary
  const stats = ref({
    totalSessions: 0,
    totalRespondents: 0,
    averageScore: 0,
    averagePercentage: 0,
  })

  // Category distribution from API
  const categoryDistribution = ref<Record<string, { label: string; count: number; percentage: number }>>({})

  // Fetch reports
  async function fetchReports(page = 1) {
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
      const { data } = await api.get('/admin/reports', { params })
      const payload = data.data ?? data

      // Map summary
      if (payload.summary) {
        stats.value = {
          totalSessions: payload.summary.totalSessions ?? 0,
          totalRespondents: payload.summary.totalRespondents ?? 0,
          averageScore: payload.summary.averageScore ?? 0,
          averagePercentage: payload.summary.averagePercentage ?? 0,
        }
      }

      // Category distribution
      if (payload.categoryDistribution) {
        categoryDistribution.value = payload.categoryDistribution
      }

      // Paginated submissions
      reports.value = payload.contents ?? []
      currentPage.value = payload.meta?.page ?? 1
      totalItems.value = payload.meta?.total ?? 0
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat data laporan'
      console.error('Failed to fetch reports:', err)
    } finally {
      loading.value = false
    }
  }

  // Export Excel
  async function exportExcel(filters: any = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/admin/reports/export-excel', filters, {
        responseType: 'blob',
      })
      const { data, headers } = response
      const url = window.URL.createObjectURL(new Blob([data]))
      const link = document.createElement('a')
      link.href = url
      // Extract filename from Content-Disposition header, fallback to dynamic name
      const disposition = headers?.['content-disposition']
      let filename = 'laporan-evaluasi.xlsx'
      if (disposition) {
        const match = disposition.match(/filename\*?=(?:UTF-8'')?["']?(.+?)["']?(?:;|$)/i)
        if (match) filename = match[1]
      }
      link.setAttribute('download', filename)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal export Excel'
      console.error('Failed to export Excel:', err)
    } finally {
      loading.value = false
    }
  }

  // Export PDF (per session)
  async function exportPdf(sessionId: number) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/admin/reports/export-pdf', { sessionId }, {
        responseType: 'blob',
      })
      const url = window.URL.createObjectURL(new Blob([data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `laporan-evaluasi-${sessionId}.html`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal export PDF'
      console.error('Failed to export PDF:', err)
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
      fetchReports(1)
    }, 400)
  }

  return {
    // State
    reports,
    loading,
    error,
    currentPage,
    perPage,
    totalItems,
    totalPages,
    searchQuery,
    stats,
    categoryDistribution,
    // Methods
    fetchReports,
    exportExcel,
    exportPdf,
    onSearch,
  }
}
