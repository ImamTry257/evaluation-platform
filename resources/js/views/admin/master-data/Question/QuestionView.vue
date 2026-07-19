<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, RouterLink, useRouter } from 'vue-router'
import { useQuestion } from '@/hooks/useQuestion'

const route = useRoute()
const router = useRouter()
const componentId = computed(() => Number(route.params.componentId))
const subComponentId = computed(() => Number(route.params.subComponentId))
const indicatorId = computed(() => Number(route.params.indicatorId))

const {
  questions,
  breadCrumbList,
  loading,
  error,
  currentPage,
  perPage,
  totalItems,
  totalPages,
  searchQuery,
  fetchQuestions,
  updateQuestion,
  deleteQuestion,
  onSearch,
} = useQuestion(indicatorId.value)

// Delete modal state
const showDeleteModal = ref(false)
const deletingQuestion = ref<any>(null)

// Computed
const filteredQuestions = computed(() => {
  return questions.value.filter((q) =>
    q.questionText?.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const showingFrom = computed(() => (currentPage.value - 1) * perPage.value + 1)
const showingTo = computed(() => Math.min(currentPage.value * perPage.value, totalItems.value))

// Delete handlers
function openDeleteModal(q: any) {
  deletingQuestion.value = q
  showDeleteModal.value = true
}

async function confirmDelete() {
  if (deletingQuestion.value) {
    await deleteQuestion(deletingQuestion.value.id)
    showDeleteModal.value = false
    deletingQuestion.value = null
  }
}

async function toggleStatus(item: any) {
  const newStatus = item.isActive ? 0 : 1
  await updateQuestion(item.id, {
    indicatorId: indicatorId.value,
    questionText: item.questionText,
    weight: item.weight,
    isActive: newStatus,
  })
  fetchQuestions(currentPage.value)
}

function formatDate(dateStr: string): string {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

// Pagination
function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  fetchQuestions(page)
}

// Init
onMounted(() => {
  fetchQuestions()
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
      <span class="text-on-surface font-semibold">Pernyataan</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4 fade-in">
      <div class="flex items-center gap-4">
        <RouterLink :to="`/admin/component/${componentId}/sub-component/${subComponentId}/indicator`" class="back-btn flex items-center gap-1 text-white text-sm font-medium bg-primary hover:bg-primary/80 hover:text-primary px-3 py-2 rounded-lg transition-colors text-primary text-sm font-medium hover:bg-primary/10 px-3 py-2 rounded-lg transition-colors no-underline">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          Kembali
        </RouterLink>
      </div>
      <RouterLink
        :to="{ name: 'admin-question-add', params: { indicatorId } }"
        class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
      >
        <span class="material-symbols-outlined">add</span>
        Tambah Pernyataan
      </RouterLink>
    </div>

    <!-- Action Bar & Content Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay">
      <!-- Action Bar -->
      <div class="p-6 border-b border-outline-variant/10 bg-surface-container-low/30">
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
          <input
            v-model="searchQuery"
            class="search-input w-full bg-white border border-outline-variant/50 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary-container outline-none transition-all text-body-sm font-body-sm"
            placeholder="Cari Pernyataan..."
            type="text"
          />
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="p-12 text-center">
        <span class="material-symbols-outlined text-[32px] text-outline animate-spin">progress_activity</span>
        <p class="text-body-sm text-on-surface-variant mt-2">Memuat data...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="questions.length === 0" class="p-12 text-center">
        <span class="material-symbols-outlined text-[48px] text-outline">quiz</span>
        <p class="text-body-base text-on-surface-variant mt-3">Belum ada Pernyataan untuk indikator ini.</p>
        <RouterLink
          :to="{ name: 'admin-question-add', params: { indicatorId } }"
          class="mt-4 bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-5 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95 mx-auto inline-flex"
        >
          <span class="material-symbols-outlined">add</span>
          Tambah Pernyataan Pertama
        </RouterLink>
      </div>

      <!-- Data Table -->
      <div v-else class="overflow-x-auto data-table-scroll">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low/50">
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase w-16">#</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Pernyataan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Bobot</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
              v-for="(q, index) in filteredQuestions"
              :key="q.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
              :class="{ 'opacity-50': !q.isActive }"
            >
              <td class="px-6 py-5 text-sm font-medium text-secondary">{{ String(index + showingFrom).padStart(2, '0') }}</td>
              <td class="px-6 py-5">
                <p class="text-sm font-semibold text-on-surface line-clamp-2">{{ q.questionText }}</p>
              </td>
              <td class="px-6 py-5">
                <button
                  @click="toggleStatus(q)"
                  class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold cursor-pointer transition-all hover:scale-105"
                  :class="q.isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                >
                  {{ q.isActive ? 'Active' : 'Inactive' }}
                </button>
              </td>
              <td class="px-6 py-5">
                <div class="weight-bar flex items-center gap-2">
                  <div class="bar w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="bar-fill h-full bg-primary rounded-full" :style="{ width: (q.weight * 100) + '%' }"></div>
                  </div>
                  <span class="text-xs font-semibold text-primary">{{ q.weight }}</span>
                </div>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-1">
                  <RouterLink
                    :to="{ name: 'admin-question-show', params: { indicatorId, id: q.id } }"
                    class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all"
                    title="Lihat Detail"
                  >
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                  </RouterLink>
                  <RouterLink
                    :to="{ name: 'admin-question-edit', params: { indicatorId, id: q.id } }"
                    class="p-2 text-on-surface-variant hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                    title="Edit"
                  >
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </RouterLink>
                  <button @click="openDeleteModal(q)" class="p-2 text-on-surface-variant hover:text-error hover:bg-error/10 rounded-lg transition-all" title="Hapus">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer -->
      <div v-if="questions.length > 0" class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-body-sm font-body-sm text-on-surface-variant">
          Menampilkan <span class="font-semibold text-on-surface">{{ showingFrom }}-{{ showingTo }}</span> dari <span class="font-semibold text-on-surface">{{ totalItems }}</span> Pernyataan
        </p>
        <div class="flex items-center gap-2">
          <button
            class="page-btn w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant/50 text-outline hover:bg-white transition-colors disabled:opacity-50"
            :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)"
          >
            <span class="material-symbols-outlined text-[20px]">chevron_left</span>
          </button>
          <button
            v-for="page in totalPages"
            :key="page"
            class="page-btn w-9 h-9 flex items-center justify-center rounded-lg border border-transparent text-body-sm font-medium transition-colors"
            :class="currentPage === page ? 'bg-primary text-on-primary font-bold' : 'hover:bg-surface-container'"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
          <button
            class="page-btn w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant/50 text-outline hover:bg-white transition-colors disabled:opacity-50"
            :disabled="currentPage === totalPages || totalPages === 0"
            @click="goToPage(currentPage + 1)"
          >
            <span class="material-symbols-outlined text-[20px]">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ==================== DELETE MODAL ==================== -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        @click.self="showDeleteModal = false"
      >
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div class="relative bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-md z-10 modal-content">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 pb-0">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-error/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-error">delete</span>
              </div>
              <h3 class="font-title-md text-title-md text-on-surface">Hapus Pernyataan</h3>
            </div>
            <button
              @click="showDeleteModal = false"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors"
            >
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6">
            <p class="text-body-base text-on-surface-variant mb-4">
              Anda yakin ingin menghapus Pernyataan berikut?
            </p>
            <div v-if="deletingQuestion" class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/20">
              <div>
                <p class="font-body-base font-semibold text-on-surface line-clamp-2">{{ deletingQuestion.questionText }}</p>
                <p class="text-body-sm text-on-surface-variant mt-1">Bobot: {{ deletingQuestion.weight }}</p>
              </div>
            </div>
            <p class="text-body-sm text-error mt-3 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px]">warning</span>
              Tindakan ini tidak dapat dibatalkan.
            </p>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 p-6 pt-0">
            <button
              @click="showDeleteModal = false"
              class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors"
            >
              Batal
            </button>
            <button
              @click="confirmDelete"
              class="px-5 py-2.5 rounded-xl bg-error text-on-error font-body-base font-semibold shadow-sm transition-all hover:bg-error/90 active:scale-95"
            >
              Ya, Hapus
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* ===== SEARCH INPUT ===== */
.search-input {
  transition: all 0.3s ease;
}
.search-input:focus {
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
  transform: translateY(-2px);
  border-color: #10b981;
}

/* ===== BACK BUTTON ===== */
.back-btn {
  transition: all 0.2s ease;
  text-decoration: none;
}
.back-btn:hover {
  background-color: rgba(16, 185, 129, 0.1);
  text-decoration: none;
}

/* ===== TABLE ===== */
.table-row {
  transition: all 0.3s ease;
  cursor: pointer;
}
.table-row:hover {
  background-color: rgba(238, 246, 238, 0.5);
  transform: scale(1.01);
}
.table-row:hover td {
  color: #161d19;
}

/* ===== WEIGHT BAR ===== */
.weight-bar {
  display: flex;
  align-items: center;
  gap: 8px;
}
.weight-bar .bar {
  width: 60px;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}
.weight-bar .bar-fill {
  height: 100%;
  background: #006c49;
  border-radius: 3px;
  transition: width 0.3s ease;
}

/* ===== STATUS BADGE ===== */
.status-badge {
  transition: all 0.3s ease;
}
.status-badge:hover {
  transform: scale(1.05);
}

/* ===== PAGE BUTTONS ===== */
.page-btn {
  transition: all 0.3s ease;
}
.page-btn:hover:not(:disabled) {
  background-color: #e3eae3;
  transform: translateY(-1px);
}
.page-btn:active:not(:disabled) {
  transform: scale(0.95);
}

/* ===== DATA TABLE SCROLL ===== */
.data-table-scroll::-webkit-scrollbar {
  height: 6px;
}
.data-table-scroll::-webkit-scrollbar-thumb {
  background: #bbcabf;
  border-radius: 10px;
}

/* Modal Transition */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.95) translateY(10px);
}
.modal-content {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* ===== FADE IN ANIMATIONS ===== */
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
