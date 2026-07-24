<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiV2 from '@/services/apiV2'
import type { Question } from '@/v2/questions/types/question'

const route = useRoute()
const router = useRouter()
const question = ref<Question | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    const res = await apiV2.get(`/admin/questions/${route.params.id}`)
    question.value = res.data.data
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Gagal memuat detail pernyataan'
  } finally {
    loading.value = false
  }
})

function formatDate(d: string): string {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <nav class="mb-6 flex items-center gap-2 text-sm">
      <a class="text-primary font-medium hover:underline cursor-pointer" @click="router.push({ name: 'statements' })">Semua Pernyataan</a>
      <span class="text-outline">›</span>
      <span class="text-on-surface font-semibold">Detail Pernyataan</span>
    </nav>

    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4 fade-in">
      <div class="flex items-center gap-4">
        <button class="back-btn flex items-center gap-1 text-primary text-sm font-medium hover:bg-primary/10 px-3 py-2 rounded-lg transition-colors" @click="router.push({ name: 'statements' })">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          Kembali
        </button>
      </div>
      <button v-if="question" class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95" @click="router.push({ name: 'statements-edit', params: { id: question.id } })">
        <span class="material-symbols-outlined">edit</span>
        Edit Pernyataan
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="p-12 text-center">
      <span class="material-symbols-outlined text-[32px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-sm text-on-surface-variant mt-2">Memuat data...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="p-12 text-center">
      <span class="material-symbols-outlined text-[48px] text-outline">error</span>
      <p class="text-body-base text-on-surface-variant mt-3">{{ error }}</p>
    </div>

    <!-- Detail -->
    <div v-else-if="question" class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay">
      <div class="p-6 border-b border-outline-variant/10 bg-surface-container-low/30">
        <h3 class="font-title-md text-title-md text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">visibility</span>
          Detail Pernyataan
        </h3>
      </div>

      <div class="p-6 space-y-6">
        <!-- Hierarchy -->
        <div class="grid grid-cols-2 gap-4 p-4 bg-surface-container-low/50 rounded-xl border border-outline-variant/20">
          <div>
            <span class="text-label-caps text-label-caps text-outline uppercase tracking-wider">Instrument</span>
            <p class="text-body-sm font-semibold text-on-surface mt-0.5">{{ question.indicator?.subComponent?.component?.questionnaire?.title || '-' }}</p>
          </div>
          <div>
            <span class="text-label-caps text-label-caps text-outline uppercase tracking-wider">Komponen</span>
            <p class="text-body-sm font-semibold text-on-surface mt-0.5">{{ question.indicator?.subComponent?.component?.name || '-' }}</p>
          </div>
          <div>
            <span class="text-label-caps text-label-caps text-outline uppercase tracking-wider">Sub Komponen</span>
            <p class="text-body-sm font-semibold text-on-surface mt-0.5">{{ question.indicator?.subComponent?.name || '-' }}</p>
          </div>
          <div>
            <span class="text-label-caps text-label-caps text-outline uppercase tracking-wider">Indikator</span>
            <p class="text-body-sm font-semibold text-on-surface mt-0.5">{{ question.indicator?.name || '-' }}</p>
          </div>
        </div>

        <hr class="border-outline-variant/20"/>

        <!-- Text -->
        <div>
          <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Teks Pernyataan</label>
          <textarea :value="question.questionText" rows="4" disabled class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed resize-none"></textarea>
        </div>

        <!-- Status -->
        <div>
          <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" :class="question.isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">
            {{ question.isActive ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <!-- Info -->
        <div class="pt-4 border-t border-outline-variant/20">
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="text-secondary text-xs">Nomor Urut:</span>
              <p class="font-medium text-on-surface">{{ question.orderNumber }}</p>
            </div>
            <div>
              <span class="text-secondary text-xs">Dibuat:</span>
              <p class="font-medium text-on-surface">{{ formatDate(question.createdAt) }}</p>
            </div>
            <div>
              <span class="text-secondary text-xs">Diupdate:</span>
              <p class="font-medium text-on-surface">{{ formatDate(question.updatedAt) }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex items-center justify-end gap-3">
        <button class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95" @click="router.push({ name: 'statements' })">Tutup</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.back-btn { transition: all 0.2s ease; }
.back-btn:hover { background-color: rgba(16, 185, 129, 0.1); }
.fade-in { animation: fadeIn 0.5s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.5s ease-out 0.1s forwards; opacity: 0; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
