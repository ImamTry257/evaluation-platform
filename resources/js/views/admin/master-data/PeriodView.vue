<script setup lang="ts">
import { ref, computed } from 'vue'

// State
const searchQuery = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const perPage = 10

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingPeriod = ref<any>(null)

const form = ref({
  name: '',
  startDate: '',
  endDate: '',
  status: 'aktif',
})

// Mock data
const periods = ref([
  {
    id: 1,
    name: 'Ganjil 2023/2024',
    startDate: '2023-09-01',
    endDate: '2024-01-31',
    status: 'aktif',
    createdAt: '2023-08-15T09:12:00',
  },
  {
    id: 2,
    name: 'Genap 2023/2024',
    startDate: '2024-02-01',
    endDate: '2024-06-30',
    status: 'mendatang',
    createdAt: '2023-12-12T14:45:00',
  },
  {
    id: 3,
    name: 'Khusus Musim Panas 2023',
    startDate: '2023-07-01',
    endDate: '2023-08-25',
    status: 'non-aktif',
    createdAt: '2023-06-20T10:00:00',
  },
  {
    id: 4,
    name: 'Genap 2022/2023',
    startDate: '2023-02-01',
    endDate: '2023-06-30',
    status: 'non-aktif',
    createdAt: '2023-01-10T16:30:00',
  },
])

// Computed
const filteredPeriods = computed(() => {
  return periods.value.filter((p) => {
    const matchSearch = p.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchStatus = !statusFilter.value || p.status === statusFilter.value
    return matchSearch && matchStatus
  })
})

const totalPages = computed(() => Math.ceil(filteredPeriods.value.length / perPage))

const paginatedPeriods = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredPeriods.value.slice(start, start + perPage)
})

const showingFrom = computed(() => (currentPage.value - 1) * perPage + 1)
const showingTo = computed(() => Math.min(currentPage.value * perPage, filteredPeriods.value.length))

// Methods
function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatDateTime(dateStr: string) {
  const d = new Date(dateStr)
  const date = d.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' })
  const time = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false })
  return `${date}, ${time}`
}

function getStatusClass(status: string) {
  switch (status) {
    case 'aktif':
      return 'bg-primary-container/20 text-primary'
    case 'mendatang':
      return 'bg-secondary-container text-on-secondary-container'
    case 'non-aktif':
      return 'bg-outline-variant/30 text-on-surface-variant'
    default:
      return ''
  }
}

function getStatusIcon(status: string) {
  switch (status) {
    case 'aktif':
      return 'event'
    case 'mendatang':
      return 'event'
    case 'non-aktif':
      return 'history'
    default:
      return 'event'
  }
}

function getStatusIconBg(status: string) {
  switch (status) {
    case 'aktif':
      return 'bg-primary/10 text-primary'
    case 'mendatang':
      return 'bg-secondary-container/50 text-secondary'
    case 'non-aktif':
      return 'bg-surface-container text-outline'
    default:
      return ''
  }
}

// Form handlers
function openAddModal() {
  formMode.value = 'add'
  editingId.value = null
  form.value = { name: '', startDate: '', endDate: '', status: 'aktif' }
  showFormModal.value = true
}

function openEditModal(period: any) {
  formMode.value = 'edit'
  editingId.value = period.id
  form.value = {
    name: period.name,
    startDate: period.startDate,
    endDate: period.endDate,
    status: period.status,
  }
  showFormModal.value = true
}

function handleSubmit() {
  if (formMode.value === 'add') {
    const newId = Math.max(...periods.value.map((p) => p.id)) + 1
    periods.value.unshift({
      id: newId,
      name: form.value.name,
      startDate: form.value.startDate,
      endDate: form.value.endDate,
      status: form.value.status,
      createdAt: new Date().toISOString(),
    })
  } else {
    const idx = periods.value.findIndex((p) => p.id === editingId.value)
    if (idx !== -1) {
      periods.value[idx] = {
        ...periods.value[idx],
        name: form.value.name,
        startDate: form.value.startDate,
        endDate: form.value.endDate,
        status: form.value.status,
      }
    }
  }
  showFormModal.value = false
}

// Delete handlers
function openDeleteModal(period: any) {
  deletingPeriod.value = period
  showDeleteModal.value = true
}

function confirmDelete() {
  periods.value = periods.value.filter((p) => p.id !== deletingPeriod.value.id)
  showDeleteModal.value = false
  deletingPeriod.value = null
}

function handleActivate(id: number) {
  periods.value.forEach((p) => {
    if (p.id === id) {
      p.status = 'aktif'
    } else if (p.status === 'aktif') {
      p.status = 'non-aktif'
    }
  })
}
</script>

<template>
  <div class="p-12 max-w-[1840px] w-full mx-auto">
    <!-- Header Section -->
    <section class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 fade-in">
      <div>
        <h2 class="font-headline-xl text-headline-xl text-on-surface">Periode Evaluasi</h2>
        <p class="font-body-base text-body-base text-on-surface-variant mt-2 max-w-2xl">
          Kelola periode waktu pelaksanaan evaluasi kebijakan lingkungan sekolah secara sistematis untuk memantau progres keberlanjutan instansi.
        </p>
      </div>
      <div>
        <button
          @click="openAddModal"
          class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
        >
          <span class="material-symbols-outlined">add</span>
          Tambah Periode
        </button>
      </div>
    </section>

    <!-- Action Bar & Content Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay">
      <!-- Action Bar -->
      <div class="p-6 border-b border-outline-variant/10 flex flex-col sm:flex-row gap-4 bg-surface-container-low/30">
        <div class="relative flex-1">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
          <input
            v-model="searchQuery"
            class="search-input w-full bg-white border border-outline-variant/50 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary-container outline-none transition-all text-body-sm font-body-sm"
            placeholder="Cari nama periode..."
            type="text"
          />
        </div>
        <div class="flex items-center gap-3">
          <div class="relative min-w-[160px]">
            <select
              v-model="statusFilter"
              class="custom-select w-full bg-white border border-outline-variant/50 rounded-xl px-4 py-2.5 appearance-none focus:ring-2 focus:ring-primary-container outline-none text-body-sm font-body-sm cursor-pointer"
            >
              <option value="">Semua Status</option>
              <option value="aktif">Aktif</option>
              <option value="non-aktif">Non-Aktif</option>
              <option value="mendatang">Mendatang</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline">expand_more</span>
          </div>
          <button class="action-btn bg-white border border-outline-variant/50 text-on-surface px-4 py-2.5 rounded-xl flex items-center gap-2 hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined text-[20px]">filter_list</span>
            <span class="text-body-sm font-body-sm">Filter Lanjut</span>
          </button>
        </div>
      </div>

      <!-- Data Table -->
      <div class="overflow-x-auto data-table-scroll">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low/50">
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama Periode</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Tanggal Mulai</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Tanggal Selesai</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Dibuat Pada</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
              v-for="period in paginatedPeriods"
              :key="period.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
              :class="{ 'opacity-75': period.status === 'non-aktif' }"
            >
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="getStatusIconBg(period.status)">
                    <span class="material-symbols-outlined text-[20px]">{{ getStatusIcon(period.status) }}</span>
                  </div>
                  <span class="font-body-base font-semibold text-on-surface">{{ period.name }}</span>
                </div>
              </td>
              <td class="px-6 py-5 text-body-sm text-on-surface-variant font-body-sm">{{ formatDate(period.startDate) }}</td>
              <td class="px-6 py-5 text-body-sm text-on-surface-variant font-body-sm">{{ formatDate(period.endDate) }}</td>
              <td class="px-6 py-5">
                <span class="status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="getStatusClass(period.status)">
                  <span v-if="period.status === 'aktif'" class="w-1.5 h-1.5 rounded-full bg-primary mr-1.5 animate-pulse"></span>
                  {{ period.status === 'aktif' ? 'Aktif' : period.status === 'mendatang' ? 'Mendatang' : 'Non-Aktif' }}
                </span>
              </td>
              <td class="px-6 py-5 text-body-sm text-outline font-body-sm">{{ formatDateTime(period.createdAt) }}</td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-2">
                  <button
                    v-if="period.status !== 'aktif'"
                    @click="handleActivate(period.id)"
                    class="bg-primary/5 hover:bg-primary/10 text-primary text-[11px] font-bold px-3 py-1.5 rounded-lg border border-primary/20 transition-all"
                  >
                    Aktifkan
                  </button>
                  <button
                    @click="openEditModal(period)"
                    class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors"
                    title="Edit"
                  >
                    <span class="material-symbols-outlined text-[20px]">edit</span>
                  </button>
                  <button
                    @click="openDeleteModal(period)"
                    class="p-2 text-error hover:bg-error-container/20 rounded-lg transition-colors"
                    title="Hapus"
                  >
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-body-sm font-body-sm text-on-surface-variant">
          Menampilkan <span class="font-semibold text-on-surface">{{ showingFrom }}-{{ showingTo }}</span> dari <span class="font-semibold text-on-surface">{{ filteredPeriods.length }}</span> entri
        </p>
        <div class="flex items-center gap-2">
          <button
            class="page-btn w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant/50 text-outline hover:bg-white transition-colors disabled:opacity-50"
            :disabled="currentPage === 1"
            @click="currentPage--"
          >
            <span class="material-symbols-outlined text-[20px]">chevron_left</span>
          </button>
          <button
            v-for="page in totalPages"
            :key="page"
            class="page-btn w-9 h-9 flex items-center justify-center rounded-lg border border-transparent text-body-sm font-medium transition-colors"
            :class="currentPage === page ? 'bg-primary text-on-primary font-bold' : 'hover:bg-surface-container'"
            @click="currentPage = page"
          >
            {{ page }}
          </button>
          <button
            class="page-btn w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant/50 text-outline hover:bg-white transition-colors disabled:opacity-50"
            :disabled="currentPage === totalPages || totalPages === 0"
            @click="currentPage++"
          >
            <span class="material-symbols-outlined text-[20px]">chevron_right</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Contextual Information (Bento Style Sub-section) -->
    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6 fade-in-delay-2">
      <div class="bg-white p-6 rounded-xl border border-outline-variant/10 shadow-sm flex flex-col justify-between card-hover">
        <div>
          <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-4">
            <span class="material-symbols-outlined">trending_up</span>
          </div>
          <h4 class="font-title-md text-title-md text-on-surface mb-2">Statistik Periode</h4>
          <p class="text-body-sm text-on-surface-variant">Total {{ periods.length }} periode telah dibuat sejak sistem aktif pada tahun 2021.</p>
        </div>
        <div class="mt-4 pt-4 border-t border-outline-variant/10 flex justify-between items-end">
          <span class="text-display-lg font-display-lg text-primary tracking-tighter">{{ periods.length }}</span>
          <span class="text-label-caps text-label-caps text-outline mb-2">PERIODE TOTAL</span>
        </div>
      </div>
      <div class="bg-white p-6 rounded-xl border border-outline-variant/10 shadow-sm col-span-1 md:col-span-2 overflow-hidden relative">
        <div class="relative z-10">
          <h4 class="font-title-md text-title-md text-on-surface mb-2">Panduan Aktivasi</h4>
          <p class="text-body-base text-on-surface-variant max-w-lg mb-6">
            Pastikan periode sebelumnya telah ditutup sebelum mengaktifkan periode baru. Sistem hanya mengizinkan <strong>satu periode aktif</strong> dalam satu waktu untuk menjaga integritas data evaluasi.
          </p>
          <a class="inline-flex items-center gap-2 text-primary font-bold text-body-sm hover:underline" href="#">
            Lihat SOP Manajemen Data
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </a>
        </div>
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-primary/5 rounded-full blur-3xl"></div>
      </div>
    </div>
  </div>

  <!-- ==================== MODALS ==================== -->

  <!-- Add/Edit Modal -->
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
                <h3 class="font-title-md text-title-md text-on-surface">{{ formMode === 'add' ? 'Tambah Periode' : 'Edit Periode' }}</h3>
                <p class="text-body-sm text-on-surface-variant">{{ formMode === 'add' ? 'Buat periode evaluasi baru' : 'Ubah data periode' }}</p>
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
            <!-- Nama Periode -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Periode</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Ganjil 2024/2025"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Tanggal Mulai</label>
                <input
                  v-model="form.startDate"
                  type="date"
                  class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface focus:ring-2 focus:ring-primary-container outline-none transition-all"
                />
              </div>
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Tanggal Selesai</label>
                <input
                  v-model="form.endDate"
                  type="date"
                  class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface focus:ring-2 focus:ring-primary-container outline-none transition-all"
                />
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
              <div class="relative">
                <select
                  v-model="form.status"
                  class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
                >
                  <option value="aktif">Aktif</option>
                  <option value="mendatang">Mendatang</option>
                  <option value="non-aktif">Non-Aktif</option>
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-[20px]">expand_more</span>
              </div>
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
              @click="handleSubmit"
              :disabled="!form.name || !form.startDate || !form.endDate"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ formMode === 'add' ? 'Simpan Periode' : 'Ubah' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- Delete Confirmation Modal -->
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
              <h3 class="font-title-md text-title-md text-on-surface">Hapus Periode</h3>
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
                <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-outline">
                  <span class="material-symbols-outlined text-[20px]">event</span>
                </div>
                <div>
                  <p class="font-body-base font-semibold text-on-surface">{{ deletingPeriod.name }}</p>
                  <p class="text-body-sm text-on-surface-variant">{{ formatDate(deletingPeriod.startDate) }} — {{ formatDate(deletingPeriod.endDate) }}</p>
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
</template>

<style scoped>
/* ===== INTERACTIVE CSS STYLING ===== */
.search-input {
  transition: all 0.3s ease;
}
.search-input:focus {
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
  transform: translateY(-1px);
}
.card-hover {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  cursor: pointer;
}
.card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}
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
.table-btn {
  transition: all 0.3s ease;
  border-radius: 0.5rem;
}
.table-btn:hover {
  background-color: #eef6ee;
  color: #006c49;
  transform: translateY(-1px);
}
.status-badge {
  transition: all 0.3s ease;
}
.status-badge:hover {
  transform: scale(1.1);
}
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
.action-btn {
  transition: all 0.3s ease;
}
.action-btn:hover {
  background-color: #eef6ee;
  border-color: #10b981;
  transform: translateY(-1px);
}
.action-btn:active {
  transform: scale(0.97);
}
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
