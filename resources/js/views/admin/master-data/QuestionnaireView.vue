<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useQuestionnaire } from '@/hooks/useQuestionnaire'
import { usePeriode } from '@/hooks/usePeriode'

const {
  questionnaires,
  loading,
  error,
  currentPage,
  perPage,
  totalItems,
  totalPages,
  searchQuery,
  statusFilter,
  periodFilter,
  fetchQuestionnaires,
  createQuestionnaire,
  updateQuestionnaire,
  deleteQuestionnaire,
  publishQuestionnaire,
  onSearch,
  onStatusFilter,
  onPeriodFilter,
} = useQuestionnaire()

const {
  periods: periodOptions,
  fetchPeriods,
} = usePeriode()

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const showViewModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingInstrument = ref<any>(null)
const viewingInstrument = ref<any>(null)
const formLoading = ref(false)

// Form
const form = ref({
  title: '',
  description: '',
  evaluationPeriodId: '',
  status: 'draft',
  durationMinutes: 30,
})

// Computed
const showingFrom = computed(() => (currentPage.value - 1) * perPage.value + 1)
const showingTo = computed(() => Math.min(currentPage.value * perPage.value, totalItems.value))

// Methods
function formatDate(dateStr: string): string {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function getStatusBadge(status: string) {
  switch (status) {
    case 'draft':
      return 'bg-amber-100 text-amber-700'
    case 'published':
      return 'bg-emerald-100 text-emerald-700'
    case 'closed':
      return 'bg-gray-100 text-gray-600'
    default:
      return ''
  }
}

function getStatusLabel(status: string) {
  switch (status) {
    case 'draft':
      return 'Draft'
    case 'published':
      return 'Published'
    case 'closed':
      return 'Closed'
    default:
      return status
  }
}

// Form handlers
function openAddModal() {
  formMode.value = 'add'
  editingId.value = null
  form.value = {
    title: '',
    description: '',
    evaluationPeriodId: '',
    status: 'draft',
    durationMinutes: 30,
  }
  showFormModal.value = true
}

function openEditModal(item: any) {
  formMode.value = 'edit'
  editingId.value = item.id
  form.value = {
    title: item.title,
    description: item.description || '',
    evaluationPeriodId: item.evaluationPeriodId,
    status: item.status,
    durationMinutes: item.durationMinutes,
  }
  showFormModal.value = true
}

function openViewModal(item: any) {
  viewingInstrument.value = item
  showViewModal.value = true
}

async function handleFormSubmit() {
  formLoading.value = true
  try {
    if (formMode.value === 'add') {
      await createQuestionnaire(form.value)
    } else {
      await updateQuestionnaire(editingId.value!, form.value)
    }
    showFormModal.value = false
  } catch (err) {
    console.error('Form submit error:', err)
  } finally {
    formLoading.value = false
  }
}

// Delete handlers
function openDeleteModal(item: any) {
  deletingInstrument.value = item
  showDeleteModal.value = true
}

async function confirmDelete() {
  try {
    await deleteQuestionnaire(deletingInstrument.value.id)
    showDeleteModal.value = false
    deletingInstrument.value = null
  } catch (err) {
    console.error('Delete error:', err)
  }
}

// More menu actions
function handleEdit(item: any) {
  openEditModal(item)
}

async function handleToggleStatus(item: any) {
  try {
    await publishQuestionnaire(item.id)
  } catch (err) {
    console.error('Toggle status error:', err)
  }
}

function handleDelete(item: any) {
  openDeleteModal(item)
}

// Pagination
function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  fetchQuestionnaires(page)
}

// Init
onMounted(() => {
  fetchQuestionnaires()
  fetchPeriods()
})
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center gap-2 text-sm">
      <a href="#" class="text-primary font-medium hover:underline">Instrument Penelitian</a>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4 fade-in">
      <div>
        <!-- <h2 class="font-headline-xl text-headline-xl text-on-surface">Instrument Penelitian</h2> -->
      </div>
      <button
        @click="openAddModal"
        class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
      >
        <span class="material-symbols-outlined">add</span>
        Tambah Instrument
      </button>
    </div>

    <!-- Action Bar & Content Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay">
      <!-- Action Bar -->
      <div class="p-6 border-b border-outline-variant/10 flex flex-col sm:flex-row gap-4 bg-surface-container-low/30">
        <div class="relative flex-1">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
          <input
            :value="searchQuery"
            @input="onSearch(($event.target as HTMLInputElement).value)"
            class="search-input w-full bg-white border border-outline-variant/50 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary-container outline-none transition-all text-body-sm font-body-sm"
            placeholder="Cari nama instrument..."
            type="text"
          />
        </div>
        <div class="flex items-center gap-3">
          <div class="relative min-w-[140px]">
            <select
              v-model="periodFilter"
              class="custom-select w-full bg-white border border-outline-variant/50 rounded-xl px-4 py-2.5 appearance-none focus:ring-2 focus:ring-primary-container outline-none text-body-sm font-body-sm cursor-pointer"
            >
              <option value="">Semua Tahun</option>
              <option v-for="year in periodOptions" :key="year" :value="year">{{ year }}</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline">expand_more</span>
          </div>
          <div class="relative min-w-[140px]">
            <select
              v-model="statusFilter"
              class="custom-select w-full bg-white border border-outline-variant/50 rounded-xl px-4 py-2.5 appearance-none focus:ring-2 focus:ring-primary-container outline-none text-body-sm font-body-sm cursor-pointer"
            >
              <option value="">Semua Status</option>
              <option value="draft">Draft</option>
              <option value="published">Published</option>
              <option value="closed">Closed</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline">expand_more</span>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="p-12 text-center">
        <span class="material-symbols-outlined text-[32px] text-outline animate-spin">progress_activity</span>
        <p class="text-body-sm text-on-surface-variant mt-2">Memuat data...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="questionnaires.length === 0" class="p-12 text-center">
        <span class="material-symbols-outlined text-[48px] text-outline">description</span>
        <p class="text-body-base text-on-surface-variant mt-3">Belum ada instrument penelitian.</p>
        <button
          @click="openAddModal"
          class="mt-4 bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-5 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95 mx-auto"
        >
          <span class="material-symbols-outlined">add</span>
          Tambah Instrument Pertama
        </button>
      </div>

      <!-- Data Table -->
      <div v-else class="overflow-x-auto data-table-scroll">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low/50">
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama Instrument</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Tahun</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Component</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Pertanyaan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
              v-for="item in questionnaires"
              :key="item.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
            >
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">assignment</span>
                  </div>
                  <div>
                    <span class="font-body-base font-semibold text-on-surface">{{ item.title }}</span>
                    <p class="text-body-sm text-on-surface-variant line-clamp-1 mt-0.5">{{ item.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="instrument-tag text-xs font-semibold bg-sky-100 text-sky-700 px-2.5 py-1 rounded-lg">{{ item.evaluation_period?.name || '-' }}</span>
              </td>
              <td class="px-6 py-5">
                <span class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" :class="getStatusBadge(item.status)">
                  {{ getStatusLabel(item.status) }}
                </span>
              </td>
              <td class="px-6 py-5">
                <span class="count-badge items-center">{{ item.components || '-' }}</span>
              </td>
              <td class="px-6 py-5">
                <span class="count-badge items-center">{{ item.questions || '-' }}</span>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-1">
                  <RouterLink
                    :to="`/admin/instrument/${item.id}/component`"
                    class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all"
                    title="Lihat Component"
                  >
                    <span class="material-symbols-outlined text-[18px]">subdirectory_arrow_right</span>
                  </RouterLink>
                  <button @click="openViewModal(item)" class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all" title="Lihat Detail">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                  </button>
                  <button @click="handleEdit(item)" class="p-2 text-on-surface-variant hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Edit">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </button>
                  <button @click="handleDelete(item)" class="p-2 text-on-surface-variant hover:text-error hover:bg-error/10 rounded-lg transition-all" title="Hapus">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="questionnaires.length > 0" class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-body-sm font-body-sm text-on-surface-variant">
          Menampilkan <span class="font-semibold text-on-surface">{{ showingFrom }}-{{ showingTo }}</span> dari <span class="font-semibold text-on-surface">{{ totalItems }}</span> instrument
        </p>
        <div class="flex items-center gap-2">
          <button
            class="page-btn w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant/50 text-outline hover:bg-white transition-colors disabled:opacity-50"
            :disabled="currentPage <= 1"
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
            :disabled="currentPage >= totalPages"
            @click="goToPage(currentPage + 1)"
          >
            <span class="material-symbols-outlined text-[20px]">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ==================== ADD/EDIT MODAL ==================== -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="showFormModal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        @click.self="showFormModal = false"
      >
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showFormModal = false"></div>
        <div class="relative bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-lg z-10 modal-content">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-outline-variant/10">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">{{ formMode === 'add' ? 'add' : 'edit' }}</span>
              </div>
              <div>
                <h3 class="font-title-md text-title-md text-on-surface">{{ formMode === 'add' ? 'Tambah Instrument' : 'Edit Instrument' }}</h3>
                <p class="text-body-sm text-on-surface-variant">{{ formMode === 'add' ? 'Buat instrument penelitian baru' : 'Ubah data instrument' }}</p>
              </div>
            </div>
            <button
              @click="showFormModal = false"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors"
            >
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 space-y-5">
            <!-- Nama Instrument -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Instrument</label>
              <input
                v-model="form.title"
                type="text"
                placeholder="Contoh: Kuesioner Kebijakan Lingkungan 2024"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Deskripsi</label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Deskripsi instrument penelitian..."
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
              ></textarea>
            </div>

            <!-- Periode & Status -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Periode</label>
                <div class="relative">
                  <select
                    v-model="form.evaluationPeriodId"
                    class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
                  >
                    <option value="">Pilih Periode</option>
                    <option v-for="period in periodOptions" :key="period.id" :value="period.id">{{ period.name }}</option>
                  </select>
                  <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-[20px]">expand_more</span>
                </div>
              </div>
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
                <div class="relative">
                  <select
                    v-model="form.status"
                    class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
                  >
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="closed">Closed</option>
                  </select>
                  <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-[20px]">expand_more</span>
                </div>
              </div>
            </div>

            <!-- Durasi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Durasi (Menit)</label>
              <input
                v-model.number="form.durationMinutes"
                type="number"
                min="1"
                placeholder="30"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 p-6 border-t border-outline-variant/10">
            <button
              @click="showFormModal = false"
              class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleFormSubmit"
              :disabled="!form.title || !form.description || !form.evaluationPeriodId"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ formMode === 'add' ? 'Simpan' : 'Ubah' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

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
              <h3 class="font-title-md text-title-md text-on-surface">Hapus Instrument</h3>
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
              Anda yakin ingin menghapus instrument berikut?
            </p>
            <div v-if="deletingInstrument" class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/20">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined text-[20px]">assignment</span>
                </div>
                <div>
                  <p class="font-body-base font-semibold text-on-surface">{{ deletingInstrument.name }}</p>
                  <p class="text-body-sm text-on-surface-variant">{{ deletingInstrument.period }} · {{ deletingInstrument.components }} komponen</p>
                </div>
              </div>
            </div>
            <p class="text-body-sm text-error mt-3 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px]">warning</span>
              Semua data di dalamnya akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.
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
  <!-- ==================== VIEW MODAL ==================== -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="showViewModal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        @click.self="showViewModal = false"
      >
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showViewModal = false"></div>
        <div class="relative bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-lg z-10 modal-content">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-outline-variant/10">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">visibility</span>
              </div>
              <div>
                <h3 class="font-title-md text-title-md text-on-surface">Detail Instrument</h3>
                <p class="text-body-sm text-on-surface-variant">Informasi lengkap instrument</p>
              </div>
            </div>
            <button
              @click="showViewModal = false"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors"
            >
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 space-y-5" v-if="viewingInstrument">
            <!-- Nama Instrument -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Instrument</label>
              <input
                :value="viewingInstrument.title"
                type="text"
                disabled
                class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed"
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Deskripsi</label>
              <textarea
                :value="viewingInstrument.description || ''"
                rows="2"
                disabled
                class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed resize-none"
              ></textarea>
            </div>

            <!-- Periode & Status -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Periode</label>
                <input
                  :value="viewingInstrument.evaluationPeriod?.name || '-'"
                  type="text"
                  disabled
                  class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed"
                />
              </div>
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
                <input
                  :value="getStatusLabel(viewingInstrument.status)"
                  type="text"
                  disabled
                  class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed"
                />
              </div>
            </div>

            <!-- Durasi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Durasi (Menit)</label>
              <input
                :value="viewingInstrument.durationMinutes"
                type="number"
                disabled
                class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed"
              />
            </div>

            <!-- Info Tambahan -->
            <div class="pt-4 border-t border-outline-variant/20">
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-secondary">Dibuat:</span>
                  <p class="font-medium text-on-surface">{{ formatDate(viewingInstrument.createdAt) }}</p>
                </div>
                <div>
                  <span class="text-secondary">Diupdate:</span>
                  <p class="font-medium text-on-surface">{{ formatDate(viewingInstrument.updatedAt) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 p-6 border-t border-outline-variant/10">
            <button
              @click="showViewModal = false"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95"
            >
              Tutup
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

/* ===== ACTION LINK ===== */
.action-link {
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.action-link:hover {
  background-color: #eef6ee;
}

/* ===== MORE MENU ===== */
.more-wrapper {
  position: relative;
  display: inline-block;
}
.more-dropdown {
  display: none;
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 4px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
  z-index: 50;
  min-width: 160px;
  overflow: hidden;
}
.more-wrapper:hover .more-dropdown {
  display: block;
}
.more-dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  font-size: 12px;
  color: #3c4a42;
  cursor: pointer;
  transition: background 0.15s;
}
.more-dropdown-item:hover {
  background: #f9fdf9;
}
.more-dropdown-item .material-symbols-outlined {
  font-size: 16px;
}
.more-dropdown-item.danger {
  color: #ba1a1a;
}
.more-dropdown-item.danger:hover {
  background: #fef2f2;
}
.more-dropdown-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 2px 0;
}

/* ===== COUNT BADGE ===== */
.count-badge {
  background: #eef6ee;
  color: #006c49;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 8px;
}

/* ===== INSTRUMENT TAG ===== */
.instrument-tag {
  display: inline-block;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 6px;
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

/* ===== CUSTOM SELECT ===== */
.custom-select {
  transition: all 0.3s ease;
  cursor: pointer;
}
.custom-select:hover {
  background-color: #e3eae3;
}
.custom-select:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

/* ===== DATA TABLE SCROLL ===== */
.data-table-scroll::-webkit-scrollbar {
  height: 6px;
}
.data-table-scroll::-webkit-scrollbar-thumb {
  background: #bbcabf;
  border-radius: 10px;
}

/* ===== MODAL STYLING ===== */
.modal-input {
  transition: all 0.3s ease;
}
.modal-input:focus {
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
  transform: translateY(-1px);
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
.fade-in-delay-2 {
  animation: fadeIn 0.5s ease-out 0.2s forwards;
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
