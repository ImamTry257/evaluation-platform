<script setup lang="ts">
import type { Question } from '@/v2/questions/types/question'

defineProps<{
  questions: Question[]
  loading: boolean
  page?: number
  limit?: number
}>()

const emit = defineEmits<{
  view: [id: number]
  edit: [id: number]
  delete: [question: Question]
}>()

function getIndicatorName(q: Question): string {
  return q.indicator?.name ?? '-'
}

function getInstrumentName(q: Question): string {
  return q.indicator?.subComponent?.component?.questionnaire?.title ?? '-'
}

function formatDate(d: string): string {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-surface-container-low/50">
          <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase w-14">#</th>
          <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase min-w-[250px]">Pernyataan</th>
          <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
          <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
        </tr>
      </thead>
      <tbody v-if="!loading && questions.length > 0" class="divide-y divide-outline-variant/10">
        <tr
          v-for="(q, idx) in questions"
          :key="q.id"
          class="table-row"
          :class="{ 'opacity-50': !q.isActive }"
        >
          <td class="px-6 py-5 text-sm font-medium text-secondary">{{ String(((page || 1) - 1) * (limit || 10) + idx + 1).padStart(2, '0') }}</td>
          <td class="px-6 py-5">
            <div>
              <p class="text-sm font-semibold text-on-surface line-clamp-2">{{ q.questionText }}</p>
            </div>
          </td>
          <td class="px-6 py-5">
            <span
              class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
              :class="q.isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
            >
              {{ q.isActive ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td class="px-6 py-5">
            <div class="flex items-center justify-center gap-1">
              <button class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all" title="Lihat Detail" @click="emit('view', q.id)">
                <span class="material-symbols-outlined text-[18px]">visibility</span>
              </button>
              <button class="p-2 text-on-surface-variant hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Edit" @click="emit('edit', q.id)">
                <span class="material-symbols-outlined text-[18px]">edit</span>
              </button>
              <button class="p-2 text-on-surface-variant hover:text-error hover:bg-error/10 rounded-lg transition-all" title="Hapus" @click="emit('delete', q)">
                <span class="material-symbols-outlined text-[18px]">delete</span>
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Loading -->
    <div v-if="loading" class="p-12 text-center">
      <span class="material-symbols-outlined text-[32px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-sm text-on-surface-variant mt-2">Memuat data...</p>
    </div>

    <!-- Empty -->
    <div v-else-if="questions.length === 0" class="p-16 text-center">
      <div class="w-16 h-16 rounded-2xl bg-primary/5 flex items-center justify-center mx-auto mb-4">
        <span class="material-symbols-outlined text-[32px] text-primary">search_off</span>
      </div>
      <p class="text-body-base text-on-surface-variant font-medium">Tidak ada pernyataan ditemukan</p>
      <p class="text-body-sm text-on-surface-variant mt-1">Coba ubah filter atau kata kunci pencarian</p>
    </div>
  </div>
</template>

<style scoped>
.table-row { transition: all 0.3s ease; cursor: pointer; }
.table-row:hover { background-color: rgba(238, 246, 238, 0.5); transform: scale(1.01); }
.status-badge { transition: all 0.3s ease; }
.status-badge:hover { transform: scale(1.05); }
.hierarchy-tag { display: inline-block; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 6px; }
</style>
