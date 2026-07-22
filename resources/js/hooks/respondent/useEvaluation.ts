import { ref } from 'vue'
import api from '@/services/api'
import { useRouter } from 'vue-router'

export function useEvaluation() {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const questionnaire = ref<any[]>([])

  // Fetch active (published) questionnaire for respondent
  async function fetchActiveQuestionnaire() {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get('/evaluations/active-questionnaire')
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat kuesioner'
      console.error('Failed to fetch active questionnaire:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // Router instance for navigation
  const router = useRouter()

  // Start evaluation session
  async function startEvaluation(questionnaireId: number, currentComponent?: number) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/evaluations/start', { questionnaireId })
      // Handle both resumed (from localStorage) and new evaluations
      if (data.data.isResumed && currentComponent) {
        // Resume from the saved component instead of always going to component 1
        router.push(`/respondent/evaluation/${data.data.session.evaluation.id}/component/${currentComponent}`)
      } else {
        router.push(`/respondent/evaluation/${data.data.session.evaluation.id}/component/1`)
      }
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memulai evaluasi'
      console.error('Failed to start evaluation:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // Save single answer (auto-save on radio click)
  async function saveAnswer(sessionId: number, questionId: number, score: number) {
    try {
      const { data } = await api.post(`/evaluations/${sessionId}/answers`, {
        questionId,
        score,
      })
      return data.data
    } catch (err: any) {
      console.error('Failed to save answer:', err)
      throw err
    }
  }

  // Auto-save remaining time + optional answers
  async function autoSave(sessionId: number, remainingSeconds: number, answers?: { questionId: number; score: number }[]) {
    try {
      const payload: any = { remainingSeconds }
      if (answers && answers.length > 0) {
        payload.answers = answers
      }
      const { data } = await api.post(`/evaluations/${sessionId}/autosave`, payload)
      return data.data
    } catch (err: any) {
      console.error('Auto-save failed:', err)
      throw err
    }
  }

  // Submit evaluation
  async function submitEvaluation(sessionId: number) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post(`/evaluations/${sessionId}/submit`)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal mengirim evaluasi'
      console.error('Failed to submit evaluation:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // Fetch evaluation results
  async function fetchResults(sessionId: number) {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/evaluations/${sessionId}/results`)
      return data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal memuat hasil evaluasi'
      console.error('Failed to fetch results:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    fetchActiveQuestionnaire,
    startEvaluation,
    saveAnswer,
    autoSave,
    submitEvaluation,
    fetchResults,
  }
}