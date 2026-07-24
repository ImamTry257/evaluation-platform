<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import SearchableSelect from './SearchableSelect.vue'
import { useQuestionTree } from '@/v2/questions/composables/useQuestionTree'

const props = withDefaults(defineProps<{
  initialData?: {
    instrumentId?: number | null
    componentId?: number | null
    subComponentId?: number | null
    indicatorId?: number | null
    questionText?: string
    weight?: number
    isActive?: boolean
  }
  instrumentDisabled?: boolean
  mode?: 'add' | 'edit'
  submitting?: boolean
}>(), {
  initialData: () => ({}),
  instrumentDisabled: false,
  mode: 'add',
  submitting: false,
})

const emit = defineEmits<{
  submit: [data: {
    indicatorId: number
    questionText: string
    isActive: boolean
  }]
  cancel: []
}>()

// Non-imported types
const { instruments, fetchTree, clearCache } = useQuestionTree()
// Clear cache so cascade dropdowns always show fresh data
clearCache()
fetchTree()
// Form fields
const selectedInstrument = ref<number | null>(props.initialData?.instrumentId ?? null)
const selectedComponent = ref<number | null>(props.initialData?.componentId ?? null)
const selectedSub = ref<number | null>(props.initialData?.subComponentId ?? null)
const selectedIndicator = ref<number | null>(props.initialData?.indicatorId ?? null)
const questionText = ref(props.initialData?.questionText ?? '')
const isActive = ref(props.initialData?.isActive ?? true)
const confirmShow = ref(false)

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

// Confirm modal summary
const summaryLabels = computed(() => ({
  instrument: instruments.value.find((i) => i.id == selectedInstrument.value)?.title ?? '-',
  component: componentOptions.value.find((c) => c.value == selectedComponent.value)?.label ?? '-',
  sub: subOptions.value.find((s) => s.value == selectedSub.value)?.label ?? '-',
  indicator: indicatorOptions.value.find((i) => i.value == selectedIndicator.value)?.label ?? '-',
  text: questionText.value || '(kosong)',
  status: isActive.value ? 'Aktif' : 'Nonaktif',
}))

function canSubmit(): boolean {
  return !!selectedIndicator.value && !!questionText.value.trim()
}

function handleSave() {
  confirmShow.value = true
}

function handleConfirm() {
  if (!selectedIndicator.value) return
  emit('submit', {
    indicatorId: selectedIndicator.value,
    questionText: questionText.value.trim(),
    isActive: isActive.value,
  })
  // Modal will close automatically when submitting transitions false → see watch below
}

// Close confirm modal when API call finishes (submitting goes false → true → false)
watch(() => props.submitting, (val, oldVal) => {
  if (oldVal === true && val === false) {
    confirmShow.value = false
  }
})

// Confirm modal outside click
const confirmRef = ref<HTMLElement | null>(null)
function onBackdropClick(e: MouseEvent) {
  if (confirmRef.value && !confirmRef.value.contains(e.target as Node)) {
    confirmShow.value = false
  }
}

// Load tree
fetchTree()
</script>

<template>
  <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden max-w-full">
    <div class="p-6 border-b border-outline-variant/10 bg-surface-container-low/30">
      <h3 class="font-title-md text-title-md text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">edit_note</span>
        {{ mode === 'add' ? 'Form Pernyataan Baru' : 'Edit Pernyataan' }}
      </h3>
    </div>

    <div class="p-6 space-y-6">
      <!-- Cascade Selects -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">
            Instrument <span class="text-error">*</span>
          </label>
          <SearchableSelect
            v-model="selectedInstrument"
            :options="instruments.map((i) => ({ value: i.id, label: i.title }))"
            placeholder="Pilih Instrument..."
            :disabled="instrumentDisabled"
          />
        </div>
        <div>
          <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Komponen</label>
          <SearchableSelect
            v-model="selectedComponent"
            :options="componentOptions"
            :placeholder="selectedInstrument ? 'Pilih Komponen...' : 'Pilih Instrument dulu'"
            :disabled="!selectedInstrument"
          />
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Sub Komponen</label>
          <SearchableSelect
            v-model="selectedSub"
            :options="subOptions"
            :placeholder="selectedComponent ? 'Pilih Sub Komponen...' : 'Pilih Komponen dulu'"
            :disabled="!selectedComponent"
          />
        </div>
        <div>
          <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">
            Indikator <span class="text-error">*</span>
          </label>
          <SearchableSelect
            v-model="selectedIndicator"
            :options="indicatorOptions"
            :placeholder="selectedSub ? 'Pilih Indikator...' : 'Pilih Sub Komponen dulu'"
            :disabled="!selectedSub"
          />
        </div>
      </div>

      <hr class="border-outline-variant/20" />

      <!-- Question Text -->
      <div>
        <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">
          Teks Pernyataan <span class="text-error">*</span>
        </label>
        <textarea
          v-model="questionText"
          rows="4"
          placeholder="Tuliskan pernyataan evaluasi..."
          class="form-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
        ></textarea>
      </div>

      <!-- Status -->
      <div>
        <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
        <div class="flex items-center gap-3 pt-2">
          <button
            type="button"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
            :class="isActive ? 'bg-primary' : 'bg-outline-variant'"
            @click="isActive = !isActive"
          >
            <span
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
              :class="isActive ? 'translate-x-6' : 'translate-x-1'"
            />
          </button>
          <span class="text-body-sm font-body-sm" :class="isActive ? 'text-primary font-semibold' : 'text-on-surface-variant'">
            {{ isActive ? 'Aktif' : 'Nonaktif' }}
          </span>
        </div>
      </div>

      <!-- Error banner from parent is shown above the form -->
    </div>

    <!-- Footer -->
    <div class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex items-center justify-end gap-3">
      <button
        class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors"
        @click="emit('cancel')"
      >
        Batal
      </button>
      <button
        class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="!canSubmit()"
        @click="handleSave"
      >
        <span class="material-symbols-outlined text-[18px]">save</span>
        {{ mode === 'add' ? 'Simpan Pernyataan' : 'Simpan Perubahan' }}
      </button>
    </div>
  </div>

  <!-- ========== CONFIRM MODAL ========== -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="confirmShow" class="fixed inset-0 z-[100] flex items-center justify-center p-4" @click="onBackdropClick">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div ref="confirmRef" class="relative bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-lg z-10 modal-content">
          <div class="flex items-center justify-between p-6 pb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">save</span>
              </div>
              <h3 class="font-title-md text-title-md text-on-surface">Konfirmasi Simpan</h3>
            </div>
            <button class="w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors" @click="confirmShow = false">
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>

          <div class="px-6 pb-4 space-y-3">
            <p class="text-body-sm text-on-surface-variant">Pastikan data pernyataan berikut sudah benar:</p>
            <div class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/20 space-y-2.5 text-sm">
              <div class="flex justify-between"><span class="text-on-surface-variant">Instrument</span><span class="font-semibold text-on-surface text-right">{{ summaryLabels.instrument }}</span></div>
              <div class="flex justify-between"><span class="text-on-surface-variant">Komponen</span><span class="font-semibold text-on-surface text-right">{{ summaryLabels.component }}</span></div>
              <div class="flex justify-between"><span class="text-on-surface-variant">Sub Komponen</span><span class="font-semibold text-on-surface text-right">{{ summaryLabels.sub }}</span></div>
              <div class="flex justify-between"><span class="text-on-surface-variant">Indikator</span><span class="font-semibold text-on-surface text-right">{{ summaryLabels.indicator }}</span></div>
              <hr class="border-outline-variant/20"/>
              <div><span class="text-on-surface-variant text-xs">Pernyataan</span><p class="font-medium text-on-surface mt-0.5 leading-snug">{{ summaryLabels.text }}</p></div>
              <div class="flex justify-between"><span class="text-on-surface-variant">Status</span><span class="font-semibold" :class="isActive ? 'text-primary' : 'text-error'">{{ summaryLabels.status }}</span></div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 p-6 pt-2">
            <button class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors disabled:opacity-50" :disabled="submitting" @click="confirmShow = false">Batal</button>
            <button class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" :disabled="submitting" @click="handleConfirm">
              <span v-if="submitting" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
              <span v-else class="material-symbols-outlined text-[18px]">check</span>
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.form-input { transition: all 0.3s ease; }
.form-input:focus { box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); transform: translateY(-1px); }
.modal-content { animation: modalSlideIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
@keyframes modalSlideIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
