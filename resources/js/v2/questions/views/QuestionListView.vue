<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import apiV2 from '@/services/apiV2'
import CascadeFilter from '@/v2/questions/components/CascadeFilter.vue'
import QuestionTable from '@/v2/questions/components/QuestionTable.vue'
import DeleteConfirmModal from '@/v2/questions/components/DeleteConfirmModal.vue'
import { useQuestionList } from '@/v2/questions/composables/useQuestionList'
import type { Question } from '@/v2/questions/types/question'

const router = useRouter()

const {
  questions,
  loading,
  filters,
  meta,
  fetchQuestions,
  setSearch,
  setPage,
  applyFilter,
  resetFilter,
} = useQuestionList()

const cascadeFilterRef = ref<InstanceType<typeof CascadeFilter> | null>(null)
const searchInput = ref<HTMLInputElement | null>(null)
const deleteModal = ref(false)
const deletingQuestion = ref<Question | null>(null)
const deleteError = ref<string | null>(null)
const deleteLoading = ref(false)
const searchTimer = ref<ReturnType<typeof setTimeout> | null>(null)

onMounted(() => {
  fetchQuestions()
})

function onSearchInput(e: Event) {
  const value = (e.target as HTMLInputElement).value
  if (searchTimer.value) clearTimeout(searchTimer.value)
  searchTimer.value = setTimeout(() => {
    setSearch(value)
  }, 350)
}

function onFilter() {
  const params = cascadeFilterRef.value?.getFilterParams()
  if (params) {
    filters.instrumentId = params.instrumentId ?? undefined
    filters.componentId = params.componentId ?? undefined
    filters.subComponentId = params.subComponentId ?? undefined
    filters.indicatorId = params.indicatorId ?? undefined
  }
  applyFilter()
}

function onReset() {
  if (searchInput.value) searchInput.value.value = ''
  resetFilter()
}

function onPageChange(page: number) {
  setPage(page)
}

function openDelete(q: Question) {
  deletingQuestion.value = q
  deleteError.value = null
  deleteModal.value = true
}

async function confirmDelete() {
  if (!deletingQuestion.value) return
  deleteError.value = null
  deleteLoading.value = true
  try {
    await apiV2.delete(`/admin/questions/${deletingQuestion.value.id}`)
    deleteModal.value = false
    deletingQuestion.value = null
    deleteLoading.value = false
    fetchQuestions()
  } catch (e: any) {
    deleteLoading.value = false
    const status = e?.response?.status
    if (status === 404) {
      // Question already deleted — just refresh list
      deleteModal.value = false
      deletingQuestion.value = null
      fetchQuestions()
    } else {
      deleteError.value = e?.response?.data?.message || 'Gagal menghapus pernyataan'
    }
  }
}

function cancelDelete() {
  deleteModal.value = false
  deletingQuestion.value = null
}
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center gap-2 text-sm">
      <span class="text-primary font-medium">Semua Pernyataan</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4 fade-in">
      <div>
        <h2 class="font-headline-lg text-headline-lg font-semibold text-on-surface">Semua Pernyataan</h2>
        <p class="text-body-sm text-on-surface-variant mt-1">Kelola seluruh pernyataan dari semua instrumen penelitian</p>
      </div>
      <!-- TODO: tambah route -->
      <button class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95" @click="router.push({ name: 'statements-add' })">
        <span class="material-symbols-outlined">add</span>
        Tambah Pernyataan
      </button>
    </div>

    <!-- Content Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay">
      <!-- Filter -->
      <div class="p-6 border-b border-outline-variant/10 bg-surface-container-low/30">
        <!-- Search -->
        <div class="relative mb-5">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
          <input
            ref="searchInput"
            class="search-input w-full bg-white border border-outline-variant/50 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary-container outline-none transition-all text-body-sm font-body-sm"
            placeholder="Cari pernyataan..."
            type="text"
            @input="onSearchInput"
          />
        </div>

        <CascadeFilter ref="cascadeFilterRef" @filter="onFilter" @reset="onReset" />
      </div>

      <!-- Table -->
      <QuestionTable
        :questions="questions"
        :loading="loading"
        :page="meta.page"
        :limit="meta.limit"
        @view="(id) => router.push({ name: 'statements-detail', params: { id } })"
        @edit="(id) => router.push({ name: 'statements-edit', params: { id } })"
        @delete="openDelete"
      />

      <!-- Pagination -->
      <div
        v-if="!loading && questions.length > 0"
        class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex flex-col sm:flex-row items-center justify-between gap-4"
      >
        <p class="text-body-sm font-body-sm text-on-surface-variant">
          Menampilkan
          <span class="font-semibold text-on-surface">{{ ((meta.page - 1) * meta.limit) + 1 }}</span>-
          <span class="font-semibold text-on-surface">{{ Math.min(meta.page * meta.limit, meta.total) }}</span>
          dari
          <span class="font-semibold text-on-surface">{{ meta.total }}</span> pernyataan
        </p>
        <div class="flex items-center gap-2">
          <button
            class="page-btn w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant/50 text-outline hover:bg-white transition-colors disabled:opacity-50"
            :disabled="meta.page <= 1"
            @click="onPageChange(meta.page - 1)"
          >
            <span class="material-symbols-outlined text-[20px]">chevron_left</span>
          </button>
          <button
            v-for="p in meta.totalPages"
            :key="p"
            class="page-btn w-9 h-9 flex items-center justify-center rounded-lg border border-transparent text-body-sm font-medium transition-colors"
            :class="meta.page === p ? 'bg-primary text-on-primary font-bold' : 'hover:bg-surface-container'"
            @click="onPageChange(p)"
          >
            {{ p }}
          </button>
          <button
            class="page-btn w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant/50 text-outline hover:bg-white transition-colors disabled:opacity-50"
            :disabled="meta.page >= meta.totalPages"
            @click="onPageChange(meta.page + 1)"
          >
            <span class="material-symbols-outlined text-[20px]">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Modal -->
  <DeleteConfirmModal
    :show="deleteModal"
    :question="deletingQuestion"
    :error="deleteError"
    :loading="deleteLoading"
    @confirm="confirmDelete"
    @cancel="cancelDelete"
  />
</template>

<style scoped>
.search-input { transition: all 0.3s ease; }
.search-input:focus { box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); transform: translateY(-2px); border-color: #10b981; }
.page-btn { transition: all 0.3s ease; }
.page-btn:hover:not(:disabled) { background-color: #e3eae3; transform: translateY(-1px); }
.page-btn:active:not(:disabled) { transform: scale(0.95); }
.fade-in { animation: fadeIn 0.5s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.5s ease-out 0.1s forwards; opacity: 0; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
