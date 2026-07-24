<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'

interface SelectOption {
  value: string | number
  label: string
}

const props = withDefaults(defineProps<{
  options: SelectOption[]
  modelValue: string | number | null
  placeholder?: string
  disabled?: boolean
}>(), {
  placeholder: 'Pilih...',
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number | null]
}>()

const isOpen = ref(false)
const search = ref('')
const label = ref(props.placeholder)
const containerRef = ref<HTMLElement | null>(null)
const searchInput = ref<HTMLInputElement | null>(null)
const highlightedIndex = ref(-1)

function getLabel(val: string | number | null): string {
  if (val === null || val === '' || val === undefined) return props.placeholder
  return props.options.find((o) => o.value == val)?.label || props.placeholder
}

watch(
  () => props.modelValue,
  (val) => { label.value = getLabel(val) },
  { immediate: true },
)

watch(
  () => props.options,
  () => { label.value = getLabel(props.modelValue) },
)

const filteredOptions = () => {
  const q = search.value.toLowerCase()
  if (!q) return props.options
  return props.options.filter((o) => o.label.toLowerCase().includes(q))
}

function toggle() {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    search.value = ''
    highlightedIndex.value = -1
    // Auto-focus search input on next tick after DOM render
    setTimeout(() => searchInput.value?.focus(), 0)
  }
}

function select(opt: SelectOption) {
  label.value = opt.label
  isOpen.value = false
  emit('update:modelValue', opt.value)
}

function onKeydown(e: KeyboardEvent) {
  if (!isOpen.value) {
    if (e.key === 'Enter' || e.key === 'ArrowDown') {
      e.preventDefault()
      toggle()
    }
    return
  }

  const opts = filteredOptions()

  switch (e.key) {
    case 'Escape':
      e.preventDefault()
      isOpen.value = false
      break
    case 'Enter':
      e.preventDefault()
      if (highlightedIndex.value >= 0 && opts[highlightedIndex.value]) {
        select(opts[highlightedIndex.value])
      } else if (opts.length > 0) {
        select(opts[0])
      }
      break
    case 'ArrowDown':
      e.preventDefault()
      highlightedIndex.value = Math.min(highlightedIndex.value + 1, opts.length - 1)
      break
    case 'ArrowUp':
      e.preventDefault()
      highlightedIndex.value = highlightedIndex.value <= 0 ? -1 : highlightedIndex.value - 1
      break
  }
}

function onClickOutside(e: MouseEvent) {
  if (containerRef.value && !containerRef.value.contains(e.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', onClickOutside))
onUnmounted(() => document.removeEventListener('click', onClickOutside))
</script>

<template>
  <div ref="containerRef" class="search-select relative">
    <button
      type="button"
      class="search-select-trigger"
      :class="{ open: isOpen }"
      :disabled="disabled"
      @click="toggle"
      @keydown="onKeydown"
    >
      <span :class="{ placeholder: !modelValue && modelValue !== 0 }">{{ label }}</span>
      <span class="material-symbols-outlined arrow text-[18px]">expand_more</span>
    </button>

    <Transition name="dropdown">
      <div v-if="isOpen" class="search-select-panel">
        <div class="search-box">
          <input
            v-model="search"
            type="text"
            placeholder="Cari..."
            autocomplete="off"
            ref="searchInput"
            @keydown="onKeydown"
          />
        </div>
        <div class="options-list">
          <div
            v-for="(opt, idx) in filteredOptions()"
            :key="opt.value"
            class="opt"
            :class="{ selected: modelValue != null && modelValue == opt.value, highlighted: idx === highlightedIndex }"
            @mousedown.prevent="select(opt)"
          >
            {{ opt.label }}
          </div>
          <div v-if="filteredOptions().length === 0" class="no-result">
            Tidak ditemukan
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.search-select-trigger {
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
  width: 100%; background: #fff; border: 1px solid rgba(187,202,191,0.5);
  border-radius: 0.75rem; padding: 10px 14px;
  font-size: 14px; font-family: 'Poppins', sans-serif; color: #161d19;
  cursor: pointer; transition: all 0.3s ease; text-align: left;
}
.search-select-trigger:hover { background-color: #eef6ee; }
.search-select-trigger:disabled { opacity: 0.6; cursor: not-allowed; }
.search-select-trigger .placeholder { color: #6c7a71; }
.search-select-trigger .arrow { transition: transform 0.2s; color: #6c7a71; }
.search-select-trigger.open .arrow { transform: rotate(180deg); }

.search-select-panel {
  position: absolute; top: calc(100% + 4px); left: 0; right: 0;
  background: #fff; border: 1px solid rgba(187,202,191,0.5);
  border-radius: 0.75rem; box-shadow: 0 8px 25px rgba(0,0,0,0.1);
  z-index: 50; max-height: 260px; overflow: hidden;
  display: flex; flex-direction: column;
}
.search-box { padding: 8px; border-bottom: 1px solid rgba(187,202,191,0.3); }
.search-box input {
  width: 100%; padding: 8px 12px; border: 1px solid rgba(187,202,191,0.5);
  border-radius: 0.5rem; font-size: 13px; font-family: 'Poppins', sans-serif;
  outline: none; background: #f4fbf4; color: #161d19; transition: all 0.2s;
  box-sizing: border-box;
}
.search-box input:focus { border-color: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,0.15); }
.options-list { overflow-y: auto; flex: 1; padding: 4px; }
.options-list::-webkit-scrollbar { width: 4px; }
.options-list::-webkit-scrollbar-thumb { background: #bbcabf; border-radius: 4px; }
.opt {
  padding: 8px 12px; font-size: 13px; font-family: 'Poppins', sans-serif;
  color: #161d19; border-radius: 0.5rem; cursor: pointer; transition: all 0.15s;
}
.opt:hover { background: #eef6ee; color: #006c49; }
.opt.selected { background: rgba(16,185,129,0.1); color: #006c49; font-weight: 600; }
.opt.highlighted { background: #d4f0d4; color: #006c49; font-weight: 600; }
.no-result { padding: 16px 12px; text-align: center; font-size: 13px; color: #6c7a71; }

.dropdown-enter-active, .dropdown-leave-active { transition: all 0.15s ease-out; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-4px); }
</style>
