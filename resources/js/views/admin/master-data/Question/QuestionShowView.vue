<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useQuestion } from '@/hooks/useQuestion'

const route = useRoute()
const router = useRouter()
const indicatorId = computed(() => Number(route.params.indicatorId))
const questionId = computed(() => Number(route.params.id))

const { getQuestion, breadCrumbList, fetchBreadCrumbList } = useQuestion(indicatorId.value)

const question = ref<any>(null)
const initialLoading = ref(true)

function formatDate(dateStr: string): string {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

onMounted(async () => {
  fetchBreadCrumbList(indicatorId.value)
  try {
    question.value = await getQuestion(questionId.value)
  } catch (err) {
    // Error handled by hook
  } finally {
    initialLoading.value = false
  }
})
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center gap-2 text-sm">
      <RouterLink to="/admin/instrument" class="text-primary font-medium hover:underline cursor-pointer">Instrument Penelitian</RouterLink>
      <span class="text-outline">›</span>
      <RouterLink v-if="breadCrumbList?.questionnaire" :to="`/admin/instrument/${breadCrumbList.questionnaire.id}`" class="text-primary font-medium hover:underline cursor-pointer">
        {{ breadCrumbList.questionnaire.title }}
      </RouterLink>
      <span v-else class="text-primary font-medium">-</span>
      <span class="text-outline">›</span>
      <RouterLink v-if="breadCrumbList?.component" :to="`/admin/instrument/${breadCrumbList.questionnaire?.id}/component`" class="text-primary font-medium hover:underline cursor-pointer">
        {{ breadCrumbList.component.name }}
      </RouterLink>
      <span v-else class="text-primary font-medium">-</span>
      <span class="text-outline">›</span>
      <RouterLink v-if="breadCrumbList?.subComponent" :to="`/admin/instrument/${breadCrumbList.questionnaire?.id}/component/${breadCrumbList.component?.id}/sub-component`" class="text-primary font-medium hover:underline cursor-pointer">
        {{ breadCrumbList.subComponent.name }}
      </RouterLink>
      <span v-else class="text-primary font-medium">-</span>
      <span class="text-outline">›</span>
      <RouterLink v-if="breadCrumbList?.indicator" :to="`/admin/instrument/${breadCrumbList.questionnaire?.id}/component/${breadCrumbList.component?.id}/sub-component/${breadCrumbList.subComponent?.id}/indicator`" class="text-primary font-medium hover:underline cursor-pointer">
        {{ breadCrumbList.indicator.name }}
      </RouterLink>
      <span v-else class="text-primary font-medium">-</span>
      <span class="text-outline">›</span>
      <span class="text-on-surface font-semibold">Detail Pernyataan</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4 fade-in">
      <div class="flex items-center gap-4">
        <button @click="router.back()" class="back-btn flex items-center gap-1 text-white text-sm font-medium bg-primary hover:bg-primary/80 hover:text-primary px-3 py-2 rounded-lg transition-colors text-primary text-sm font-medium hover:bg-primary/10 px-3 py-2 rounded-lg transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          Kembali
        </button>
      </div>
      <RouterLink
        v-if="question"
        :to="{ name: 'admin-question-edit', params: { indicatorId, id: questionId } }"
        class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
      >
        <span class="material-symbols-outlined">edit</span>
        Edit Pernyataan
      </RouterLink>
    </div>

    <!-- Loading -->
    <div v-if="initialLoading" class="p-12 text-center">
      <span class="material-symbols-outlined text-[32px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-sm text-on-surface-variant mt-2">Memuat data Pernyataan...</p>
    </div>

    <!-- Detail Card -->
    <div v-else-if="question" class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay max-w-8xl">
      <div class="p-6 border-b border-outline-variant/10 bg-surface-container-low/30">
        <h3 class="font-title-md text-title-md text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">visibility</span>
          Detail Pernyataan
        </h3>
      </div>

      <div class="p-6 space-y-6">
        <!-- Teks Pernyataan -->
        <div>
          <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Teks Pernyataan</label>
          <textarea
            :value="question.questionText"
            rows="4"
            disabled
            class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed resize-none"
          ></textarea>
        </div>

        <!-- Bobot & Status -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Bobot</label>
            <div class="flex items-center gap-3">
              <div class="bar w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="bar-fill h-full bg-primary rounded-full" :style="{ width: (question.weight * 100) + '%' }"></div>
              </div>
              <span class="text-sm font-semibold text-primary">{{ question.weight }}</span>
            </div>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
            <span
              class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
              :class="question.isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
            >
              {{ question.isActive ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>

        <!-- Info Tambahan -->
        <div class="pt-4 border-t border-outline-variant/20">
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="text-secondary">Urutan:</span>
              <p class="font-medium text-on-surface">{{ question.orderNumber }}</p>
            </div>
            <div>
              <span class="text-secondary">Dibuat:</span>
              <p class="font-medium text-on-surface">{{ formatDate(question.createdAt) }}</p>
            </div>
            <div>
              <span class="text-secondary">Diupdate:</span>
              <p class="font-medium text-on-surface">{{ formatDate(question.updatedAt) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex items-center justify-end gap-3">
        <button
          @click="router.back()"
          class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.back-btn {
  transition: all 0.2s ease;
  text-decoration: none;
}
.back-btn:hover {
  background-color: rgba(16, 185, 129, 0.1);
  text-decoration: none;
}

.bar-fill {
  transition: width 0.3s ease;
}

.status-badge {
  transition: all 0.3s ease;
}
.status-badge:hover {
  transform: scale(1.05);
}

.fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
.fade-in-delay {
  animation: fadeIn 0.5s ease-out 0.1s forwards;
  opacity: 0;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
