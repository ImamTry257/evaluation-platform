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

  // Stats
  const stats = ref({
    totalSessions: 0,
    totalRespondents: 0,
    averageScore: 0,
    categoryDistribution: {} as Record<string, number>,
  })

  // Computed
  const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

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
      reports.value = payload.contents ?? payload.data ?? payload.sessions ?? payload
      currentPage.value = payload.meta?.page ?? payload.current_page ?? 1
      totalItems.value = payload.meta?.total ?? payload.total ?? 0
      
      // Update stats if available
      if (payload.stats) {
        stats.value = payload.stats
      }
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
      const { data } = await api.post('/admin/reports/export-excel', filters, {
        responseType: 'blob',
      })
      // Create download link
      const url = window.URL.createObjectURL(new Blob([data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', 'report.xlsx')
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

  // Export PDF
  async function exportPdf(filters: any = {}) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/admin/reports/export-pdf', filters, {
        responseType: 'blob',
      })
      // Create download link
      const url = window.URL.createObjectURL(new Blob([data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', 'report.pdf')
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
    // Methods
    fetchReports,
    exportExcel,
    exportPdf,
    onSearch,
  }
}
