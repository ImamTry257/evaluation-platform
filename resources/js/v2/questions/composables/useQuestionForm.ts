import { ref, computed, watch } from 'vue'
import apiV2 from '@/services/apiV2'
import { useQuestionTree } from './useQuestionTree'

export function useQuestionForm() {
  const { instruments, fetchTree } = useQuestionTree()

  // Form state
  const selectedInstrument = ref<number | null>(null)
  const selectedComponent = ref<number | null>(null)
  const selectedSub = ref<number | null>(null)
  const selectedIndicator = ref<number | null>(null)
  const questionText = ref('')
  const weight = ref(1.0)
  const isActive = ref(true)
  const submitting = ref(false)
  const error = ref<string | null>(null)
  const showConfirm = ref(false)

  // Computed cascade options
  const componentOptions = computed(() => {
    if (!selectedInstrument.value) return []
    const inst = instruments.value.find((i) => i.id == selectedInstrument.value)
    return inst?.components.map((c) => ({ value: c.id, label: c.name })) ?? []
  })

  const subOptions = computed(() => {
    if (!selectedInstrument.value || !selectedComponent.value) return []
    const inst = instruments.value.find((i) => i.id == selectedInstrument.value)
    const comp = inst?.components.find((c) => c.id == selectedComponent.value)
    return comp?.subComponents.map((s) => ({ value: s.id, label: s.name })) ?? []
  })

  const indicatorOptions = computed(() => {
    if (!selectedInstrument.value || !selectedComponent.value || !selectedSub.value) return []
    const inst = instruments.value.find((i) => i.id == selectedInstrument.value)
    const comp = inst?.components.find((c) => c.id == selectedComponent.value)
    const sub = comp?.subComponents.find((s) => s.id == selectedSub.value)
    return sub?.indicators.map((ind) => ({ value: ind.id, label: ind.name })) ?? []
  })

  // Cascade reset
  watch(selectedInstrument, () => { selectedComponent.value = null; selectedSub.value = null; selectedIndicator.value = null })
  watch(selectedComponent, () => { selectedSub.value = null; selectedIndicator.value = null })
  watch(selectedSub, () => { selectedIndicator.value = null })

  // Summary for confirm modal
  const summaryLabels = computed(() => ({
    instrument: instruments.value.find((i) => i.id == selectedInstrument.value)?.title ?? '-',
    component: componentOptions.value.find((c) => c.value == selectedComponent.value)?.label ?? '-',
    sub: subOptions.value.find((s) => s.value == selectedSub.value)?.label ?? '-',
    indicator: indicatorOptions.value.find((i) => i.value == selectedIndicator.value)?.label ?? '-',
  }))

  function canSubmit(): boolean {
    return !!selectedIndicator.value && !!questionText.value.trim()
  }

  function openConfirm() { showConfirm.value = true }
  function closeConfirm() { showConfirm.value = false }

  async function submit(): Promise<void> {
    if (!canSubmit() || !selectedIndicator.value) return
    submitting.value = true
    error.value = null
    try {
      await apiV2.post('/admin/questions', {
        indicatorId: selectedIndicator.value,
        questionText: questionText.value.trim(),
        weight: weight.value,
        isActive: isActive.value,
      })
      closeConfirm()
      return // caller should handle redirect
    } catch (e: any) {
      error.value = e?.response?.data?.message || 'Gagal menyimpan pernyataan'
    } finally {
      submitting.value = false
    }
  }

  function resetForm() {
    selectedInstrument.value = null
    selectedComponent.value = null
    selectedSub.value = null
    selectedIndicator.value = null
    questionText.value = ''
    weight.value = 1.0
    isActive.value = true
    error.value = null
    showConfirm.value = false
  }

  // Load tree data
  fetchTree()

  return {
    selectedInstrument, selectedComponent, selectedSub, selectedIndicator,
    questionText, weight, isActive,
    componentOptions, subOptions, indicatorOptions,
    submitting, error, showConfirm, summaryLabels,
    instruments,
    canSubmit, openConfirm, closeConfirm, submit, resetForm,
  }
}
