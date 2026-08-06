import { ref, computed } from 'vue'
import api from '@/services/api'

export interface PeriodInfo {
  id: number | null
  name: string | null
  status: string | null
  startDate: string | null
  endDate: string | null
}

export interface AvailabilityPayload {
  available: boolean
  reason: 'no_questionnaire' | 'period_not_open' | 'period_ended' | 'already_submitted' | null
  reasonLabel: string | null
  period: PeriodInfo
  estimatedMinutes: number | null
  questionnaire: any | null
  scoringLevels: any[] | null
}

/**
 * Hook untuk memuat status availabilitas kuesioner responden.
 *
 * Memetakan `reason` dari API ke label chip "Status" pada halaman
 * empty-state (kuesioner-kosong), sehingga frontend cukup membaca
 * `data.available` untuk memutuskan alur: mulai evaluasi vs render kosong.
 */
export function useQuestionnaireStatus() {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const status = ref<AvailabilityPayload | null>(null)

  /** Label status untuk chip empty-state, dipetakan dari reason. */
  const statusLabel = ref<'Menunggu buka' | 'Periode berakhir' | 'Sudah selesai' | ''>('')

  /** Estimasi waktu (menit) dari kuesioner/periode, fallback '-'. */
  const estimateLabel = computed(() => {
    if (!status.value) return '-'
    return status.value.estimatedMinutes != null
      ? `${status.value.estimatedMinutes} Menit`
      : '-'
  })

  async function fetchStatus() {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get('/evaluations/active-questionnaire')
      const payload = data.data as AvailabilityPayload
      status.value = payload
      statusLabel.value = mapReasonToLabel(payload.reason)
      return payload
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat status kuesioner'
      console.error('Failed to fetch questionnaire status:', err)
      status.value = null
      throw err
    } finally {
      loading.value = false
    }
  }

  function mapReasonToLabel(reason: AvailabilityPayload['reason']) {
    switch (reason) {
      case 'period_ended':
        return 'Periode berakhir'
      case 'already_submitted':
        return 'Sudah selesai'
      case 'no_questionnaire':
      case 'period_not_open':
        return 'Menunggu buka'
      default:
        return ''
    }
  }

  return {
    loading,
    error,
    status,
    statusLabel,
    estimateLabel,
    fetchStatus,
  }
}