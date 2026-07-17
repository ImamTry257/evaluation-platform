<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'

// State
const searchQuery = ref('')
const periodFilter = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const perPage = 10

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingInstrument = ref<any>(null)

// Form
const form = ref({
  name: '',
  description: '',
  period: '',
  status: 'draft',
  duration: 30,
})

// Mock data - Instruments
const instruments = ref([
  {
    id: 1,
    name: 'Kuesioner Kebijakan Lingkungan',
    description: 'Evaluasi kebijakan hijau sekolah',
    period: '2024',
    status: 'published',
    duration: 45,
    components: 5,
    subComponents: 12,
    indicators: 32,
    questions: 96,
  },
  {
    id: 2,
    name: 'Evaluasi Pengelolaan Sampah',
    description: 'Instrument tahun sebelumnya',
    period: '2022',
    status: 'published',
    duration: 40,
    components: 4,
    subComponents: 10,
    indicators: 28,
    questions: 84,
  },
  {
    id: 3,
    name: 'Instrumen Adiwiyata Mandiri',
    description: 'Draft instrument baru',
    period: '2025',
    status: 'draft',
    duration: 60,
    components: 3,
    subComponents: 8,
    indicators: 20,
    questions: 60,
  },
  {
    id: 4,
    name: 'Audit Emisi Karbon Sekolah',
    description: 'Kalkulasi jejak karbon dari aktivitas transportasi dan energi sekolah',
    period: '2023',
    status: 'closed',
    duration: 35,
    components: 6,
    subComponents: 14,
    indicators: 36,
    questions: 108,
  },
])

// Mock data - Period options
const periodOptions = ['2022', '2023', '2024', '2025']

// Computed
const filteredInstruments = computed(() => {
  return instruments.value.filter((i) => {
    const matchSearch = i.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchPeriod = !periodFilter.value || i.period === periodFilter.value
    const matchStatus = !statusFilter.value || i.status === statusFilter.value
    return matchSearch && matchPeriod && matchStatus
  })
})

const totalPages = computed(() => Math.ceil(filteredInstruments.value.length / perPage))

const paginatedInstruments = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredInstruments.value.slice(start, start + perPage)
})

const showingFrom = computed(() => (currentPage.value - 1) * perPage + 1)
const showingTo = computed(() => Math.min(currentPage.value * perPage, filteredInstruments.value.length))

// Methods
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
    name: '',
    description: '',
    period: '',
    status: 'draft',
    duration: 30,
  }
  showFormModal.value = true
}

function openEditModal(item: any) {
  formMode.value = 'edit'
  editingId.value = item.id
  form.value = {
    name: item.name,
    description: item.description,
    period: item.period,
    status: item.status,
    duration: item.duration,
  }
  showFormModal.value = true
}

function handleFormSubmit() {
  if (formMode.value === 'add') {
    const newId = Math.max(...instruments.value.map((i) => i.id)) + 1
    instruments.value.unshift({
      id: newId,
      name: form.value.name,
      description: form.value.description,
      period: form.value.period,
      status: form.value.status,
      duration: form.value.duration,
      components: 0,
      subComponents: 0,
      indicators: 0,
      questions: 0,
    })
  } else {
    const idx = instruments.value.findIndex((i) => i.id === editingId.value)
    if (idx !== -1) {
      instruments.value[idx] = {
        ...instruments.value[idx],
        name: form.value.name,
        description: form.value.description,
        period: form.value.period,
        status: form.value.status,
        duration: form.value.duration,
      }
    }
  }
  showFormModal.value = false
}

// Delete handlers
function openDeleteModal(item: any) {
  deletingInstrument.value = item
  showDeleteModal.value = true
}

function confirmDelete() {
  instruments.value = instruments.value.filter((i) => i.id !== deletingInstrument.value.id)
  showDeleteModal.value = false
  deletingInstrument.value = null
}

// More menu actions
function handleView(item: any) {
  console.log('View:', item)
}

function handleEdit(item: any) {
  openEditModal(item)
}

function handleToggleStatus(item: any) {
  const idx = instruments.value.findIndex((i) => i.id === item.id)
  if (idx !== -1) {
    if (item.status === 'draft') {
      instruments.value[idx].status = 'published'
    } else if (item.status === 'published') {
      instruments.value[idx].status = 'closed'
    } else {
      instruments.value[idx].status = 'draft'
    }
  }
}

function handleDelete(item: any) {
  openDeleteModal(item)
}
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
        <h2 class="font-headline-xl text-headline-xl text-on-surface">Instrument Penelitian</h2>
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
            v-model="searchQuery"
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

      <!-- Data Table -->
      <div class="overflow-x-auto data-table-scroll">
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
              v-for="item in paginatedInstruments"
              :key="item.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
            >
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">assignment</span>
                  </div>
                  <div>
                    <span class="font-body-base font-semibold text-on-surface">{{ item.name }}</span>
                    <p class="text-body-sm text-on-surface-variant line-clamp-1 mt-0.5">{{ item.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="instrument-tag text-xs font-semibold bg-sky-100 text-sky-700 px-2.5 py-1 rounded-lg">{{ item.period }}</span>
              </td>
              <td class="px-6 py-5">
                <span class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" :class="getStatusBadge(item.status)">
                  {{ getStatusLabel(item.status) }}
                </span>
              </td>
              <td class="px-6 py-5">
                <span class="count-badge">{{ item.components }}</span>
              </td>
              <td class="px-6 py-5">
                <span class="count-badge">{{ item.questions }}</span>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-2">
                  <span class="action-link text-xs font-semibold text-primary cursor-pointer hover:bg-primary/10 px-2 py-1 rounded-lg">
                    <RouterLink to="/admin/component">
                      <span class="material-symbols-outlined text-[14px]">subdirectory_arrow_right</span>
                    Lihat Component
                    </RouterLink>
                  </span>
                  <div class="relative more-wrapper">
                    <button class="more-btn w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors">
                      <span class="material-symbols-outlined text-[20px]">more_vert</span>
                    </button>
                    <div class="more-dropdown">
                      <div class="more-dropdown-item" @click="handleView(item)">
                        <span class="material-symbols-outlined">visibility</span>
                        View Detail
                      </div>
                      <div class="more-dropdown-item" @click="handleEdit(item)">
                        <span class="material-symbols-outlined">edit</span>
                        Edit
                      </div>
                      <div class="more-dropdown-item" @click="handleToggleStatus(item)">
                        <span class="material-symbols-outlined">toggle_on</span>
                        Toggle Status
                      </div>
                      <div class="more-dropdown-divider"></div>
                      <div class="more-dropdown-item danger" @click="handleDelete(item)">
                        <span class="material-symbols-outlined">delete</span>
                        Hapus
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-body-sm font-body-sm text-on-surface-variant">
          Menampilkan <span class="font-semibold text-on-surface">{{ showingFrom }}-{{ showingTo }}</span> dari <span class="font-semibold text-on-surface">{{ filteredInstruments.length }}</span> instrument
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
                v-model="form.name"
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

            <!-- Tahun & Status -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Tahun</label>
                <div class="relative">
                  <select
                    v-model="form.period"
                    class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
                  >
                    <option value="">Pilih Tahun</option>
                    <option v-for="year in periodOptions" :key="year" :value="year">{{ year }}</option>
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
                v-model.number="form.duration"
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
              :disabled="!form.name || !form.description || !form.period"
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
