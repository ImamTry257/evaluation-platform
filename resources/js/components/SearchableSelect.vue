<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

interface Option {
  value: string
  label: string
}

interface Props {
  modelValue: string
  options: Option[]
  placeholder?: string
  disabled?: boolean
  loading?: boolean
  error?: boolean
  icon?: string
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Pilih...',
  disabled: false,
  loading: false,
  error: false,
  icon: '',
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  change: [value: string]
}>()

const isOpen = ref(false)
const search = ref('')
const activeIndex = ref(-1)
const rootEl = ref<HTMLElement | null>(null)
const searchInput = ref<HTMLInputElement | null>(null)

const selectedLabel = computed(() => {
  const found = props.options.find(o => o.value === props.modelValue)
  return found ? found.label : ''
})

const filteredOptions = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return props.options
  return props.options.filter(o => o.label.toLowerCase().includes(q))
})

// Shared registry: hanya satu SearchableSelect boleh terbuka dalam satu waktu
let activeClose: (() => void) | null = null
const myClose = () => { isOpen.value = false }

function open() {
  if (props.disabled || props.loading) return
  if (activeClose && activeClose !== myClose) activeClose()
  activeClose = myClose
  isOpen.value = true
  search.value = ''
  activeIndex.value = -1
  requestAnimationFrame(() => searchInput.value?.focus())
}

function close() {
  if (activeClose === myClose) activeClose = null
  isOpen.value = false
}

function select(option: Option) {
  emit('update:modelValue', option.value)
  emit('change', option.value)
  close()
}

function onTriggerKeydown(e: KeyboardEvent) {
  if (e.key === 'ArrowDown' && !isOpen.value) {
    e.preventDefault()
    open()
  }
}

function onSearchKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') {
    e.preventDefault()
    close()
  } else if (e.key === 'ArrowDown') {
    e.preventDefault()
    activeIndex.value = Math.min(activeIndex.value + 1, filteredOptions.value.length - 1)
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    activeIndex.value = Math.max(activeIndex.value - 1, 0)
  } else if (e.key === 'Enter') {
    e.preventDefault()
    const opt = filteredOptions.value[activeIndex.value]
    if (opt) select(opt)
  }
}

function onDocumentClick(e: MouseEvent) {
  if (rootEl.value && !rootEl.value.contains(e.target as Node)) {
    close()
  }
}

watch(isOpen, open => {
  if (!open) search.value = ''
})

onMounted(() => document.addEventListener('click', onDocumentClick))
onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick)
  if (activeClose === myClose) activeClose = null
})
</script>

<template>
  <div ref="rootEl" class="relative group">
    <!-- Trigger -->
    <button
      type="button"
      :disabled="disabled || loading"
      :class="[
        'form-input flex items-center justify-between gap-2 w-full h-11 pl-10 pr-3 bg-surface-container-lowest border rounded-xl text-base',
        'focus:ring-2 focus:ring-[#004592] focus:border-[#004592] transition-all disabled:opacity-60 disabled:cursor-not-allowed',
        error ? 'border-error' : 'border-outline-variant',
      ]"
      @click="isOpen ? close() : open()"
      @keydown="onTriggerKeydown"
    >
      <div class="input-icon absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant transition-colors" v-if="icon">
        <span class="material-symbols-outlined" style="font-size: 20px;">{{ icon }}</span>
      </div>
      <span class="truncate" :class="selectedLabel ? 'text-on-surface' : 'text-on-secondary-container'">
        {{ loading ? 'Memuat...' : (selectedLabel || placeholder) }}
      </span>
      <span
        class="material-symbols-outlined text-on-surface-variant transition-transform duration-200 flex-shrink-0"
        :class="isOpen ? 'rotate-180' : ''"
        style="font-size: 20px;"
      >expand_more</span>
    </button>

    <!-- Dropdown -->
    <div
      v-if="isOpen"
      @click.self="close"
      class="absolute z-50 mt-1.5 w-full bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg overflow-hidden fade-in"
    >
      <!-- Search -->
      <div class="p-2 border-b border-outline-variant/40">
        <div class="relative">
          <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none" style="font-size: 18px;">search</span>
          <input
            ref="searchInput"
            v-model="search"
            type="text"
            placeholder="Cari..."
            class="w-full h-9 pl-8 pr-3 bg-surface-container-low rounded-lg border border-outline-variant text-sm text-on-surface outline-none focus:ring-2 focus:ring-[#004592] focus:border-[#004592] transition-all"
            @keydown.stop="onSearchKeydown"
          />
        </div>
      </div>

      <!-- Options -->
      <ul @click.self="close" class="max-h-52 overflow-y-auto py-1">
        <li
          v-if="filteredOptions.length === 0"
          class="px-3 py-2.5 text-sm text-on-surface-variant"
        >Tidak ada hasil</li>
        <li
          v-for="(opt, i) in filteredOptions"
          :key="opt.value"
          @click="select(opt)"
          @mouseenter="activeIndex = i"
          :class="[
            'px-3 py-2.5 text-sm cursor-pointer flex items-center justify-between gap-2 transition-colors',
            i === activeIndex ? 'bg-[#004592]/10 text-[#004592]' : 'text-on-surface hover:bg-surface-container-low',
          ]"
        >
          {{ opt.label }}
          <span
            v-if="opt.value === modelValue"
            class="material-symbols-outlined text-[#004592] flex-shrink-0"
            style="font-size: 18px;"
          >check</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.fade-in { animation: dropdownIn 0.15s ease-out forwards; }
@keyframes dropdownIn {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
