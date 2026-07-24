import { ref, computed } from 'vue'
import apiV2 from '@/services/apiV2'
import type { TreeInstrument } from '@/v2/questions/types/question'

/** Fetch cascade tree data for filter dropdowns */
const treeCache = ref<TreeInstrument[] | null>(null)

export function useQuestionTree() {
  const loading = ref(false)
  const error = ref<string | null>(null)

  const instruments = computed(() => treeCache.value ?? [])

  async function fetchTree(): Promise<TreeInstrument[]> {
    if (treeCache.value) return treeCache.value

    loading.value = true
    error.value = null
    try {
      const res = await apiV2.get('/admin/questions/tree')
      treeCache.value = res.data.data ?? []
      return treeCache.value
    } catch (e: any) {
      error.value = e?.response?.data?.message || 'Gagal memuat data tree'
      return []
    } finally {
      loading.value = false
    }
  }

  function clearCache() {
    treeCache.value = null
  }

  return { instruments, loading, error, fetchTree, clearCache }
}
