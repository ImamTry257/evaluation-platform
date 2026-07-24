<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { usePeriode } from '@/hooks/usePeriode'

const {
  periods,
  loading,
  error,
  currentPage,
  perPage,
  totalItems,
  totalPages,
  searchQuery,
  statusFilter,
  fetchPeriods,
  createPeriod,
  updatePeriod,
  deletePeriod,
  onSearch,
  onStatusFilter,
} = usePeriode()

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const showViewModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingPeriod = ref<any>(null)
const viewingPeriod = ref<any>(null)
const formLoading = ref(false)

// Form
const form = ref({
  name: '',
  description: '',
  startDate: '',
  endDate: '',
  isActive: true,
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

function getStatusBadge(isActive: boolean) {
  return isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'
}

function getStatusLabel(isActive: boolean) {
  return isActive ? 'Aktif' : 'Nonaktif'
}

// Form handlers
function openAddModal() {
  formMode.value = 'add'
  editingId.value = null
  form.value = {
    name: '',
    description: '',
    startDate: '',
    endDate: '',
    isActive: true,
  }
  showFormModal.value = true
}

function openEditModal(item: any) {
  formMode.value = 'edit'
  editingId.value = item.id
  form.value = {
    name: item.name,
    description: item.description || '',
    startDate: item.startDate ? item.startDate.split('T')[0] : '',
    endDate: item.endDate ? item.endDate.split('T')[0] : '',
    isActive: item.isActive,
  }
  showFormModal.value = true
}

async function handleFormSubmit() {
  formLoading.value = true
  try {
    if (formMode.value === 'add') {
      await createPeriod(form.value)
    } else {
      await updatePeriod(editingId.value!, form.value)
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
  deletingPeriod.value = item
  showDeleteModal.value = true
}

async function confirmDelete() {
  try {
    await deletePeriod(deletingPeriod.value.id)
    showDeleteModal.value = false
    deletingPeriod.value = null
  } catch (err) {
    console.error('Delete error:', err)
  }
}

// More menu actions
function openViewModal(item: any) {
  viewingPeriod.value = item
  showViewModal.value = true
}

function handleEdit(item: any) {
  openEditModal(item)
}

function handleToggleStatus(item: any) {
  openEditModal(item)
}

function handleDelete(item: any) {
  openDeleteModal(item)
}

// Pagination
function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  fetchPeriods(page)
}

// Init
onMounted(() => {
  fetchPeriods()
})
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center gap-2 text-sm">
      <span class="text-on-surface font-semibold">Periode</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4 fade-in">
      <div>
        <h2 class="font-headline-xl font-bold text-headline-xl text-on-surface">Manajemen Periode</h2>
      </div>
      <button
        @click="openAddModal"
        class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
      >
        <span class="material-symbols-outlined">add</span>
        Tambah
      </button>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="mb-4 flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
      <span class="material-symbols-outlined text-[16px]">error</span>
      <span>{{ error }}</span>
    </div>

    <!-- Action Bar & Content Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay">
      <!-- Action Bar -->
      <div class="p-6 border-b border-outline-variant/10 bg-surface-container-low/30">
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
          <input
            :value="searchQuery"
            @input="onSearch(($event.target as HTMLInputElement).value)"
            class="search-input w-full bg-white border border-outline-variant/50 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary-container outline-none transition-all text-body-sm font-body-sm"
            placeholder="Cari nama periode..."
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
      <div v-else-if="periods.length === 0" class="p-12 text-center">
        <span class="material-symbols-outlined text-[48px] text-outline">calendar_today</span>
        <p class="text-body-base text-on-surface-variant mt-3">Tidak ada data periode ditemukan.</p>
      </div>

      <!-- Data Table -->
      <table v-else class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low/50">
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama Period</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Deskripsi</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Tanggal Mulai</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Tanggal Selesai</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/10">
          <tr
            v-for="item in periods"
            :key="item.id"
            class="table-row hover:bg-surface-container-low/30 transition-colors"
          >
            <td class="px-6 py-5">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined text-[20px]">calendar_today</span>
                </div>
                <div>
                  <span class="font-body-base font-semibold text-on-surface">{{ item.name }}</span>
                </div>
              </div>
            </td>
            <td class="px-6 py-5 text-body-sm text-on-surface-variant">{{ item.description || '-' }}</td>
            <td class="px-6 py-5 text-body-sm text-on-surface">{{ formatDate(item.startDate) }}</td>
            <td class="px-6 py-5 text-body-sm text-on-surface">{{ formatDate(item.endDate) }}</td>
            <td class="px-6 py-5">
              <span class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" :class="getStatusBadge(item.isActive)">
                {{ getStatusLabel(item.isActive) }}
              </span>
            </td>
            <td class="px-6 py-5">
              <div class="flex items-center justify-center gap-1">
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

      <!-- Pagination Footer -->
      <div v-if="periods.length > 0" class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-body-sm font-body-sm text-on-surface-variant">
          Menampilkan <span class="font-semibold text-on-surface">{{ showingFrom }}-{{ showingTo }}</span> dari <span class="font-semibold text-on-surface">{{ totalItems }}</span> periode
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
                <h3 class="font-title-md text-title-md text-on-surface">{{ formMode === 'add' ? 'Tambah Period' : 'Edit Period' }}</h3>
                <p class="text-body-sm text-on-surface-variant">{{ formMode === 'add' ? 'Buat periode baru' : 'Ubah data periode' }}</p>
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
            <!-- Nama Period -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Period</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Periode 2024 Ganjil"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Deskripsi</label>
              <textarea
                v-model="form.description"
                rows="2"
                placeholder="Deskripsi periode..."
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
              ></textarea>
            </div>

            <!-- Tanggal Mulai & Selesai — v-calendar DatePicker -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Tanggal Mulai</label>
                <DatePicker
                  v-model="form.startDate"
                  mode="date"
                  :model-config="{ type: 'string', mask: 'YYYY-MM-DD' }"
                  :popover="{ visibility: 'focus' }"
                  :is-required="true"
                >
                  <template #default="{ inputValue, togglePopover }">
                    <input
                      :value="inputValue"
                      readonly
                      @click="togglePopover"
                      placeholder="Pilih tanggal mulai..."
                      class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
                    />
                  </template>
                </DatePicker>
              </div>
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Tanggal Selesai</label>
                <DatePicker
                  v-model="form.endDate"
                  mode="date"
                  :model-config="{ type: 'string', mask: 'YYYY-MM-DD' }"
                  :popover="{ visibility: 'focus' }"
                  :is-required="true"
                >
                  <template #default="{ inputValue, togglePopover }">
                    <input
                      :value="inputValue"
                      readonly
                      @click="togglePopover"
                      placeholder="Pilih tanggal selesai..."
                      class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
                    />
                  </template>
                </DatePicker>
              </div>
            </div>

            <!-- Status → toggle switch (konsisten dengan QuestionForm) -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
              <div class="flex items-center gap-3 pt-2">
                <button
                  type="button"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                  :class="form.isActive ? 'bg-primary' : 'bg-outline-variant'"
                  @click="form.isActive = !form.isActive"
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

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 p-6 border-t border-outline-variant/10">
            <button
              @click="showFormModal = false"
              :disabled="formLoading"
              class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleFormSubmit"
              :disabled="!form.name || !form.startDate || !form.endDate || formLoading"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="formLoading" class="material-symbols-outlined text-[18px] animate-spin mr-1">progress_activity</span>
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
              <h3 class="font-title-md text-title-md text-on-surface">Hapus Period</h3>
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
              Anda yakin ingin menghapus periode berikut?
            </p>
            <div v-if="deletingPeriod" class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/20">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined text-[20px]">calendar_today</span>
                </div>
                <div>
                  <p class="font-body-base font-semibold text-on-surface">{{ deletingPeriod.name }}</p>
                  <p class="text-body-sm text-on-surface-variant">{{ formatDate(deletingPeriod.startDate) }} - {{ formatDate(deletingPeriod.endDate) }}</p>
                </div>
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
                <h3 class="font-title-md text-title-md text-on-surface">Detail Period</h3>
                <p class="text-body-sm text-on-surface-variant">Informasi lengkap periode</p>
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
          <div class="p-6 space-y-5" v-if="viewingPeriod">
            <!-- Nama Period -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Period</label>
              <input
                :value="viewingPeriod.name"
                type="text"
                disabled
                class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed"
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Deskripsi</label>
              <textarea
                :value="viewingPeriod.description || ''"
                rows="2"
                disabled
                class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed resize-none"
              ></textarea>
            </div>

            <!-- Tanggal Mulai & Selesai -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Tanggal Mulai</label>
                <input
                  :value="viewingPeriod.startDate ? viewingPeriod.startDate.split('T')[0] : ''"
                  type="date"
                  disabled
                  class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed"
                />
              </div>
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Tanggal Selesai</label>
                <input
                  :value="viewingPeriod.endDate ? viewingPeriod.endDate.split('T')[0] : ''"
                  type="date"
                  disabled
                  class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed"
                />
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
              <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-not-allowed opacity-60">
                  <input type="radio" :checked="viewingPeriod.isActive === true" disabled class="accent-primary w-4 h-4" />
                  <span class="text-body-sm text-on-surface">Aktif</span>
                </label>
                <label class="flex items-center gap-2 cursor-not-allowed opacity-60">
                  <input type="radio" :checked="viewingPeriod.isActive === false" disabled class="accent-primary w-4 h-4" />
                  <span class="text-body-sm text-on-surface">Nonaktif</span>
                </label>
              </div>
            </div>

            <!-- Info Tambahan -->
            <div class="pt-4 border-t border-outline-variant/20">
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-secondary">Dibuat:</span>
                  <p class="font-medium text-on-surface">{{ formatDate(viewingPeriod.created_at) }}</p>
                </div>
                <div>
                  <span class="text-secondary">Diupdate:</span>
                  <p class="font-medium text-on-surface">{{ formatDate(viewingPeriod.updated_at) }}</p>
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

/* ===== V-CALENDAR THEME OVERRIDES ===== */
:deep(.vc-popover-content) {
  --vc-accent-50: #ecfdf5;
  --vc-accent-100: #d1fae5;
  --vc-accent-200: #a7f3d0;
  --vc-accent-300: #6ee7b7;
  --vc-accent-400: #34d399;
  --vc-accent-500: #10b981;
  --vc-accent-600: #059669;
  --vc-accent-700: #047857;
}
:deep(.vc-popover-content) {
  border-radius: 0.75rem;
  border: 1px solid rgba(187,202,191,0.5);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
:deep(.vc-day.is-today .vc-day-layer) {
  color: #10b981;
}
:deep(.vc-day.is-active .vc-day-content) {
  background: #10b981;
  color: #fff;
}
:deep(.vc-day .vc-day-content:hover) {
  background: #eef6ee;
}
</style>
