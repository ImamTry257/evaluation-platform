<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import SearchableSelect from './SearchableSelect.vue'
import { useQuestionTree } from '@/v2/questions/composables/useQuestionTree'

const { instruments, fetchTree } = useQuestionTree()

const emit = defineEmits<{
  filter: []
  reset: []
}>()

// Cascade state
const selectedInstrument = ref<number | null>(null)
const selectedComponent = ref<number | null>(null)
const selectedSub = ref<number | null>(null)
const selectedIndicator = ref<number | null>(null)

// Computed options for each level
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

// Cascade watchers — reset downstream on upstream change
watch(selectedInstrument, () => {
  selectedComponent.value = null
  selectedSub.value = null
  selectedIndicator.value = null
})

watch(selectedComponent, () => {
  selectedSub.value = null
  selectedIndicator.value = null
})

watch(selectedSub, () => {
  selectedIndicator.value = null
})

function handleFilter() {
  emit('filter')
}

function handleReset() {
  selectedInstrument.value = null
  selectedComponent.value = null
  selectedSub.value = null
  selectedIndicator.value = null
  emit('reset')
}

// Expose selected values for parent
function getFilterParams() {
  return {
    instrumentId: selectedInstrument.value,
    componentId: selectedComponent.value,
    subComponentId: selectedSub.value,
    indicatorId: selectedIndicator.value,
  }
}

defineExpose({ getFilterParams, handleReset, fetchTree })

// Fetch tree on mount
fetchTree()
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
    <div>
      <p class="filter-label mb-1.5">Instrument</p>
      <SearchableSelect
        v-model="selectedInstrument"
        :options="instruments.map((i) => ({ value: i.id, label: i.title }))"
        placeholder="Semua"
      />
    </div>
    <div>
      <p class="filter-label mb-1.5">Komponen</p>
      <SearchableSelect
        v-model="selectedComponent"
        :options="componentOptions"
        :placeholder="selectedInstrument ? 'Semua' : 'Pilih Instrument dulu'"
        :disabled="!selectedInstrument"
      />
    </div>
    <div>
      <p class="filter-label mb-1.5">Sub Komponen</p>
      <SearchableSelect
        v-model="selectedSub"
        :options="subOptions"
        :placeholder="selectedComponent ? 'Semua' : 'Pilih Komponen dulu'"
        :disabled="!selectedComponent"
      />
    </div>
    <div>
      <p class="filter-label mb-1.5">Indikator</p>
      <SearchableSelect
        v-model="selectedIndicator"
        :options="indicatorOptions"
        :placeholder="selectedSub ? 'Semua' : 'Pilih Sub Komponen dulu'"
        :disabled="!selectedSub"
      />
    </div>
  </div>

  <div class="flex items-center gap-3 mt-5">
    <button
      class="filter-btn h-[42px] px-6 bg-primary text-on-primary font-body-sm font-semibold rounded-xl flex items-center gap-2 shadow-sm whitespace-nowrap"
      @click="handleFilter"
    >
      <span class="material-symbols-outlined text-[18px]">filter_list</span>
      Filter
    </button>
    <button
      class="h-[42px] px-4 border border-outline-variant/50 rounded-xl text-on-surface-variant hover:bg-surface-container transition-colors flex items-center gap-2 font-body-sm"
      @click="handleReset"
    >
      <span class="material-symbols-outlined text-[18px]">refresh</span>
      Reset
    </button>
  </div>
</template>

<style scoped>
.filter-label { font-size: 10px; font-weight: 600; color: #6c7a71; text-transform: uppercase; letter-spacing: 0.05em; }
.filter-btn { transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.filter-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(0, 108, 73, 0.3); }
</style>
