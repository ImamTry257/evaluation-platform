<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useQuestion } from '@/hooks/useQuestion'

const route = useRoute()
const router = useRouter()
const indicatorId = computed(() => Number(route.params.indicatorId))

const { createQuestion, breadCrumbList, fetchBreadCrumbList, loading } = useQuestion(indicatorId.value)

const form = ref({
  questionText: '',
  weight: 1.0,
  isActive: 1,
})

const formLoading = ref(false)

onMounted(() => {
  fetchBreadCrumbList(indicatorId.value)
})

async function handleSubmit() {
  formLoading.value = true
  try {
    await createQuestion({
      indicatorId: indicatorId.value,
      questionText: form.value.questionText,
      weight: form.value.weight,
      isActive: form.value.isActive,
    })
    router.back()
  } catch (err) {
    // Error handled by hook
  } finally {
    formLoading.value = false
  }
}
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
      <span class="text-on-surface font-semibold">Tambah Pernyataan</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 fade-in">
      <div class="flex items-center gap-4">
        <button @click="router.back()" class="back-btn flex items-center gap-1 text-white text-sm font-medium bg-primary hover:bg-primary/80 hover:text-primary px-3 py-2 rounded-lg transition-colors text-primary text-sm font-medium hover:bg-primary/10 px-3 py-2 rounded-lg transition-colors">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          Kembali
        </button>
      </div>
    </div>

    <!-- Form Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay max-w-3xl">
      <div class="p-6 border-b border-outline-variant/10 bg-surface-container-low/30">
        <h3 class="font-title-md text-title-md text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">edit_note</span>
          Form Pernyataan
        </h3>
      </div>

      <div class="p-6 space-y-6">
        <!-- Teks Pernyataan -->
        <div>
          <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Teks Pernyataan <span class="text-error">*</span></label>
          <textarea
            v-model="form.questionText"
            rows="4"
            placeholder="Tuliskan Pernyataan evaluasi..."
            class="form-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
          ></textarea>
        </div>

        <!-- Bobot & Status -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Bobot</label>
            <input
              v-model.number="form.weight"
              type="number"
              min="0"
              max="1"
              step="0.1"
              placeholder="1.0"
              class="form-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
            />
            <p class="text-xs text-secondary mt-1">Nilai 0-1. Default: 1.0</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
            <div class="flex items-center gap-3 pt-2">
              <button
                type="button"
                @click="form.isActive = form.isActive === 1 ? 0 : 1"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                :class="form.isActive ? 'bg-primary' : 'bg-outline-variant'"
              >
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  :class="form.isActive ? 'translate-x-6' : 'translate-x-1'"
                />
              </button>
              <span class="text-body-sm font-body-sm" :class="form.isActive ? 'text-primary font-semibold' : 'text-on-surface-variant'">
                {{ form.isActive ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex items-center justify-end gap-3">
        <button
          @click="router.back()"
          class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors"
        >
          Batal
        </button>
        <button
          @click="handleSubmit"
          :disabled="!form.questionText || formLoading"
          class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ formLoading ? 'Menyimpan...' : 'Simpan Pernyataan' }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.form-input {
  transition: all 0.3s ease;
}
.form-input:focus {
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
  transform: translateY(-1px);
}

.back-btn {
  transition: all 0.2s ease;
  text-decoration: none;
}
.back-btn:hover {
  background-color: rgba(16, 185, 129, 0.1);
  text-decoration: none;
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
