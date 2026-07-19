<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useIndicator } from '@/hooks/useIndicator'

const route = useRoute()
const componentId = computed(() => Number(route.params.componentId))
const subComponentId = computed(() => Number(route.params.subComponentId))

const {
  indicators,
  breadCrumbList,
  loading,
  error,
  currentPage,
  perPage,
  totalItems,
  totalPages,
  searchQuery,
  fetchIndicators,
  createIndicator,
  updateIndicator,
  deleteIndicator,
  onSearch,
} = useIndicator(subComponentId.value)

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const showViewModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingIndicator = ref<any>(null)
const viewingIndicator = ref<any>(null)
const formLoading = ref(false)

// Form
const form = ref({
  name: '',
  description: '',
})

// Computed
const filteredIndicators = computed(() => {
  return indicators.value.filter((ind) =>
    ind.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Methods
function getStatusBadge(status: string) {
  return status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'
}

function getStatusLabel(status: string) {
  return status === 'active' ? 'Active' : 'Inactive'
}

// Form handlers
function openAddModal() {
  formMode.value = 'add'
  editingId.value = null
  form.value = { name: '', description: '' }
  showFormModal.value = true
}

function openEditModal(ind: any) {
  formMode.value = 'edit'
  editingId.value = ind.id
  form.value = { name: ind.name, description: ind.description }
  showFormModal.value = true
}

async function handleFormSubmit() {
  formLoading.value = true
  try {
    if (formMode.value === 'add') {
      await createIndicator({
        subComponentId: subComponentId.value,
        name: form.value.name,
        description: form.value.description,
      })
    } else if (editingId.value) {
      await updateIndicator(editingId.value, {
        subComponentId: subComponentId.value,
        name: form.value.name,
        description: form.value.description,
      })
    }
    showFormModal.value = false
  } catch (err) {
    // Error handled by hook
  } finally {
    formLoading.value = false
  }
}

// Delete handlers
function openDeleteModal(ind: any) {
  deletingIndicator.value = ind
  showDeleteModal.value = true
}

async function confirmDelete() {
  if (deletingIndicator.value) {
    await deleteIndicator(deletingIndicator.value.id)
    showDeleteModal.value = false
    deletingIndicator.value = null
  }
}

// More menu actions
function openViewModal(item: any) {
  viewingIndicator.value = item
  showViewModal.value = true
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

function handleEdit(item: any) {
  openEditModal(item)
}

function handleDelete(item: any) {
  openDeleteModal(item)
}

// Pagination
function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  fetchIndicators(page)
}

// Init
onMounted(() => {
  fetchIndicators()
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
      <span class="text-on-surface font-semibold">Indikator</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 fade-in">
      <div class="flex items-center gap-4">
        <RouterLink :to="`/admin/component/${componentId}/sub-component`" class="back-btn flex items-center gap-1 text-primary text-sm font-medium hover:bg-primary/10 px-3 py-2 rounded-lg transition-colors no-underline">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          Kembali
        </RouterLink>
      </div>
      <button
        @click="openAddModal"
        class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
      >
        <span class="material-symbols-outlined">add</span>
        Tambah Indikator
      </button>
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
            placeholder="Cari nama indikator..."
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
      <div v-else-if="indicators.length === 0" class="p-12 text-center">
        <span class="material-symbols-outlined text-[48px] text-outline">analytics</span>
        <p class="text-body-base text-on-surface-variant mt-3">Belum ada indikator untuk sub komponen ini.</p>
        <button
          @click="openAddModal"
          class="mt-4 bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-5 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95 mx-auto"
        >
          <span class="material-symbols-outlined">add</span>
          Tambah Indikator Pertama
        </button>
      </div>

      <!-- Data Table -->
      <div v-else class="overflow-x-auto data-table-scroll">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low/50">
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama Indikator</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Pertanyaan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
              v-for="ind in filteredIndicators"
              :key="ind.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
              :class="{ 'opacity-50': ind.status === 'inactive' }"
            >
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <span class="material-symbols-outlined text-[14px] text-outline">subdirectory_arrow_right</span>
                  <div>
                    <span class="font-body-base font-semibold text-on-surface">{{ ind.name }}</span>
                    <p class="text-body-sm text-on-surface-variant line-clamp-1 mt-0.5">{{ ind.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" :class="getStatusBadge(ind.status)">
                  {{ getStatusLabel(ind.status) }}
                </span>
              </td>
              <td class="px-6 py-5">
                <span class="count-badge">{{ ind.questionCount ?? 0 }}</span>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-1">
                  <RouterLink
                    :to="`/admin/component/${componentId}/sub-component/${subComponentId}/indicator/${ind.id}/question`"
                    class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all"
                    title="Lihat Pertanyaan"
                  >
                    <span class="material-symbols-outlined text-[18px]">subdirectory_arrow_right</span>
                  </RouterLink>
                  <button @click="openViewModal(ind)" class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all" title="Lihat Detail">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                  </button>
                  <button @click="handleEdit(ind)" class="p-2 text-on-surface-variant hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Edit">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </button>
                  <button @click="handleDelete(ind)" class="p-2 text-on-surface-variant hover:text-error hover:bg-error/10 rounded-lg transition-all" title="Hapus">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10">
        <p class="text-body-sm font-body-sm text-on-surface-variant">
          Menampilkan <span class="font-semibold text-on-surface">{{ filteredIndicators.length }}</span> dari <span class="font-semibold text-on-surface">{{ indicators.length }}</span> indikator
        </p>
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
                <h3 class="font-title-md text-title-md text-on-surface">{{ formMode === 'add' ? 'Tambah Indikator' : 'Edit Indikator' }}</h3>
                <p class="text-body-sm text-on-surface-variant">{{ formMode === 'add' ? 'Buat indikator baru' : 'Ubah data indikator' }}</p>
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
            <!-- Nama Indikator -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Indikator</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Keberadaan Organisasi"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Deskripsi</label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Deskripsi indikator evaluasi..."
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
              ></textarea>
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
              :disabled="!form.name || !form.description"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ formMode === 'add' ? 'Simpan Indikator' : 'Simpan Perubahan' }}
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
              <h3 class="font-title-md text-title-md text-on-surface">Hapus Indikator</h3>
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
              Anda yakin ingin menghapus indikator berikut?
            </p>
            <div v-if="deletingIndicator" class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/20">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined text-[20px]">analytics</span>
                </div>
                <div>
                  <p class="font-body-base font-semibold text-on-surface">{{ deletingIndicator.name }}</p>
                  <p class="text-body-sm text-on-surface-variant">{{ deletingIndicator.questions }} pertanyaan</p>
                </div>
              </div>
            </div>
            <p class="text-body-sm text-error mt-3 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px]">warning</span>
              Semua pertanyaan di dalamnya akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.
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
                <h3 class="font-title-md text-title-md text-on-surface">Detail Indikator</h3>
                <p class="text-body-sm text-on-surface-variant">Informasi lengkap indikator</p>
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
          <div class="p-6 space-y-5" v-if="viewingIndicator">
            <!-- Nama Indikator -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Indikator</label>
              <input
                :value="viewingIndicator.name"
                type="text"
                disabled
                class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed"
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Deskripsi</label>
              <textarea
                :value="viewingIndicator.description || ''"
                rows="2"
                disabled
                class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed resize-none"
              ></textarea>
            </div>

            <!-- Info Tambahan -->
            <div class="pt-4 border-t border-outline-variant/20">
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-secondary">Dibuat:</span>
                  <p class="font-medium text-on-surface">{{ formatDate(viewingIndicator.createdAt) }}</p>
                </div>
                <div>
                  <span class="text-secondary">Diupdate:</span>
                  <p class="font-medium text-on-surface">{{ formatDate(viewingIndicator.updatedAt) }}</p>
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

/* ===== ACTION LINK ===== */
.action-link {
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  text-decoration: none;
}
.action-link:hover {
  background-color: #eef6ee;
  text-decoration: none;
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

/* ===== STATUS BADGE ===== */
.status-badge {
  transition: all 0.3s ease;
}
.status-badge:hover {
  transform: scale(1.05);
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
