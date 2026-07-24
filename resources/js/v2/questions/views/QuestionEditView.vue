<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import QuestionForm from '@/v2/questions/components/QuestionForm.vue'
import apiV2 from '@/services/apiV2'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const submitting = ref(false)
const error = ref<string | null>(null)
const initialData = ref<Record<string, any> | null>(null)

onMounted(async () => {
  try {
    const res = await apiV2.get(`/admin/questions/${route.params.id}`)
    const q = res.data.data
    initialData.value = {
      instrumentId: q.indicator?.subComponent?.component?.questionnaire?.id ?? null,
      componentId: q.indicator?.subComponent?.component?.id ?? null,
      subComponentId: q.indicator?.subComponent?.id ?? null,
      indicatorId: q.indicator?.id ?? null,
      questionText: q.questionText,
      isActive: q.isActive,
    }
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Gagal memuat data pernyataan'
  } finally {
    loading.value = false
  }
})

async function onSubmit(data: { indicatorId: number; questionText: string; isActive: boolean }) {
  submitting.value = true
  error.value = null
  try {
    await apiV2.put(`/admin/questions/${route.params.id}`, data)
    router.push({ name: 'statements' })
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Gagal menyimpan perubahan'
  } finally {
    submitting.value = false
  }
}

function onCancel() {
  router.push({ name: 'statements' })
}
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <nav class="mb-6 flex items-center gap-2 text-sm">
      <a class="text-primary font-medium hover:underline cursor-pointer" @click="onCancel">Semua Pernyataan</a>
      <span class="text-outline">›</span>
      <span class="text-on-surface font-semibold">Edit Pernyataan</span>
    </nav>

    <div class="flex items-center gap-4 mb-8 fade-in">
      <button class="back-btn flex items-center gap-1 text-primary text-sm font-medium hover:bg-primary/10 px-3 py-2 rounded-lg transition-colors" @click="onCancel">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        Kembali
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="p-12 text-center">
      <span class="material-symbols-outlined text-[32px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-sm text-on-surface-variant mt-2">Memuat data pernyataan...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error && !initialData" class="p-12 text-center">
      <span class="material-symbols-outlined text-[48px] text-outline">error</span>
      <p class="text-body-base text-on-surface-variant mt-3">{{ error }}</p>
    </div>

    <!-- Form -->
    <template v-else-if="initialData">
      <div v-if="error" class="mb-6 p-4 rounded-xl bg-error/10 border border-error/20 text-body-sm text-error flex items-center gap-2 fade-in">
        <span class="material-symbols-outlined text-[18px]">error</span>
        {{ error }}
      </div>

      <QuestionForm
        mode="edit"
        :initial-data="initialData"
        :instrument-disabled="true"
        :submitting="submitting"
        @submit="onSubmit"
        @cancel="onCancel"
      />
    </template>

    <!-- Loading overlay -->
    <Teleport to="body">
      <div v-if="submitting" class="fixed inset-0 z-[200] bg-black/20 flex items-center justify-center">
        <div class="bg-white rounded-xl px-8 py-6 shadow-xl flex items-center gap-4">
          <span class="material-symbols-outlined text-[28px] text-primary animate-spin">progress_activity</span>
          <span class="font-body-base font-medium text-on-surface">Menyimpan perubahan...</span>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.back-btn { transition: all 0.2s ease; }
.back-btn:hover { background-color: rgba(16, 185, 129, 0.1); }
.fade-in { animation: fadeIn 0.5s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
