<script setup lang="ts">
import { ref, computed } from 'vue'

// State
const searchQuery = ref('')
const componentFilter = ref('')

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingSubComponent = ref<any>(null)

// Form
const form = ref({
  name: '',
  description: '',
  parentComponent: '',
  icon: 'category',
})

// Icon options
const iconOptions = [
  { value: 'delete_sweep', label: 'Delete Sweep' },
  { value: 'recycling', label: 'Recycling' },
  { value: 'local_shipping', label: 'Local Shipping' },
  { value: 'water_drop', label: 'Water Drop' },
  { value: 'eco', label: 'Eco' },
  { value: 'forest', label: 'Forest' },
  { value: 'park', label: 'Park' },
  { value: 'category', label: 'Category' },
  { value: 'schedule', label: 'Schedule' },
  { value: 'school', label: 'School' },
  { value: 'fact_check', label: 'Fact Check' },
]

// Component options
const componentOptions = ['Pengelolaan Sampah', 'Efisiensi Energi', 'Pengelolaan Air Bersih', 'Ruang Terbuka Hijau', 'Emisi Karbon']

// Mock data - Sub Components
const subComponents = ref([
  {
    id: 1,
    name: 'Pemilahan Sampah Organik',
    description: 'Ketersediaan tempat sampah terpisah dan tingkat kepatuhan warga sekolah dalam memisahkan limbah organik harian.',
    parentComponent: 'Pengelolaan Sampah',
    icon: 'delete_sweep',
    indicators: 3,
    questions: 10,
    completion: 80,
    status: 'aktif',
  },
  {
    id: 2,
    name: 'Unit Pengolah Sampah (UPS)',
    description: 'Efektivitas operasional pusat pengolahan sampah internal sekolah termasuk teknik pengomposan.',
    parentComponent: 'Pengelolaan Sampah',
    icon: 'recycling',
    indicators: 4,
    questions: 12,
    completion: 100,
    status: 'aktif',
  },
  {
    id: 3,
    name: 'Manajemen Pengangkutan',
    description: 'Penjadwalan dan koordinasi dengan dinas kebersihan terkait residu sampah yang tidak bisa diolah.',
    parentComponent: 'Pengelolaan Sampah',
    icon: 'local_shipping',
    indicators: 2,
    questions: 8,
    completion: 45,
    status: 'draft',
  },
  {
    id: 4,
    name: 'Efisiensi Penerangan Kelas',
    description: 'Pengukuran penggunaan lampu LED dan pemanfaatan cahaya alami di ruang kelas.',
    parentComponent: 'Efisiensi Energi',
    icon: 'eco',
    indicators: 3,
    questions: 9,
    completion: 70,
    status: 'aktif',
  },
  {
    id: 5,
    name: 'Pengelolaan AC & Pendingin',
    description: 'Optimalisasi penggunaan pendingin udara dan pengaturan suhu efektif di lingkungan sekolah.',
    parentComponent: 'Efisiensi Energi',
    icon: 'schedule',
    indicators: 2,
    questions: 6,
    completion: 55,
    status: 'draft',
  },
  {
    id: 6,
    name: 'Penghematan Air Harian',
    description: 'Pemantauan konsumsi air harian dan efisiensi sistem irigasi di area sekolah.',
    parentComponent: 'Pengelolaan Air Bersih',
    icon: 'water_drop',
    indicators: 3,
    questions: 9,
    completion: 90,
    status: 'aktif',
  },
  {
    id: 7,
    name: 'Kualitas Air Bersih',
    description: 'Pengujian berkala kualitas air bersih yang digunakan di lingkungan sekolah.',
    parentComponent: 'Pengelolaan Air Bersih',
    icon: 'fact_check',
    indicators: 2,
    questions: 6,
    completion: 65,
    status: 'aktif',
  },
  {
    id: 8,
    name: 'Pemeliharaan Taman Sekolah',
    description: 'Perawatan dan pemeliharaan area hijau serta tanaman di lingkungan sekolah.',
    parentComponent: 'Ruang Terbuka Hijau',
    icon: 'park',
    indicators: 3,
    questions: 12,
    completion: 75,
    status: 'aktif',
  },
])

// Computed
const filteredSubComponents = computed(() => {
  return subComponents.value.filter((sc) => {
    const matchSearch = sc.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      sc.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchComponent = !componentFilter.value || sc.parentComponent === componentFilter.value
    return matchSearch && matchComponent
  })
})

// Methods
function getCompletionColor(completion: number) {
  if (completion >= 80) return 'text-primary bg-primary/10'
  if (completion >= 50) return 'text-tertiary bg-tertiary/10'
  return 'text-on-surface-variant bg-outline-variant/30'
}

function getStatusClass(status: string) {
  if (status === 'aktif') return 'bg-primary/10 text-primary'
  if (status === 'draft') return 'bg-secondary/10 text-secondary'
  return 'bg-outline-variant/30 text-on-surface-variant'
}

// Form handlers
function openAddModal() {
  formMode.value = 'add'
  editingId.value = null
  form.value = { name: '', description: '', parentComponent: '', icon: 'category' }
  showFormModal.value = true
}

function openEditModal(sc: any) {
  formMode.value = 'edit'
  editingId.value = sc.id
  form.value = {
    name: sc.name,
    description: sc.description,
    parentComponent: sc.parentComponent,
    icon: sc.icon,
  }
  showFormModal.value = true
}

function handleFormSubmit() {
  if (formMode.value === 'add') {
    const newId = Math.max(...subComponents.value.map((sc) => sc.id)) + 1
    subComponents.value.unshift({
      id: newId,
      name: form.value.name,
      description: form.value.description,
      parentComponent: form.value.parentComponent,
      icon: form.value.icon,
      indicators: 0,
      questions: 0,
      completion: 0,
      status: 'draft',
    })
  } else {
    const idx = subComponents.value.findIndex((sc) => sc.id === editingId.value)
    if (idx !== -1) {
      subComponents.value[idx] = {
        ...subComponents.value[idx],
        name: form.value.name,
        description: form.value.description,
        parentComponent: form.value.parentComponent,
        icon: form.value.icon,
      }
    }
  }
  showFormModal.value = false
}

// Delete handlers
function openDeleteModal(sc: any) {
  deletingSubComponent.value = sc
  showDeleteModal.value = true
}

function confirmDelete() {
  subComponents.value = subComponents.value.filter((sc) => sc.id !== deletingSubComponent.value.id)
  showDeleteModal.value = false
  deletingSubComponent.value = null
}
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <!-- Header Section -->
    <section class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 fade-in">
      <div>
        <h2 class="font-headline-xl text-headline-xl text-on-surface">Master Sub Komponen</h2>
        <p class="font-body-base text-body-base text-on-surface-variant mt-2 max-w-2xl">
          Kelola sub komponen evaluasi kebijakan lingkungan sekolah secara sistematis untuk memantau progres keberlanjutan instansi.
        </p>
      </div>
      <div>
        <button
          @click="openAddModal"
          class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
        >
          <span class="material-symbols-outlined">add</span>
          Tambah Sub Komponen
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
            placeholder="Cari nama sub komponen..."
            type="text"
          />
        </div>
        <div class="flex items-center gap-3">
          <div class="relative min-w-[200px]">
            <select
              v-model="componentFilter"
              class="custom-select w-full bg-white border border-outline-variant/50 rounded-xl px-4 py-2.5 appearance-none focus:ring-2 focus:ring-primary-container outline-none text-body-sm font-body-sm cursor-pointer"
            >
              <option value="">Semua Komponen</option>
              <option v-for="c in componentOptions" :key="c" :value="c">{{ c }}</option>
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
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama Sub Komponen</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Komponen</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Indikator</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Pertanyaan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Kelengkapan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
              v-for="sc in filteredSubComponents"
              :key="sc.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
            >
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">{{ sc.icon }}</span>
                  </div>
                  <div>
                    <span class="font-body-base font-semibold text-on-surface">{{ sc.name }}</span>
                    <p class="text-body-sm text-on-surface-variant line-clamp-1 mt-0.5">{{ sc.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="weight-badge text-xs font-medium bg-secondary-container text-on-secondary-container px-2 py-0.5 rounded">{{ sc.parentComponent }}</span>
              </td>
              <td class="px-6 py-5 text-body-sm text-on-surface font-semibold font-body-sm">{{ sc.indicators }}</td>
              <td class="px-6 py-5 text-body-sm text-on-surface font-semibold font-body-sm">{{ sc.questions }}</td>
              <td class="px-6 py-5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="getCompletionColor(sc.completion)">
                  {{ sc.completion }}%
                </span>
              </td>
              <td class="px-6 py-5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="getStatusClass(sc.status)">
                  {{ sc.status === 'aktif' ? 'Aktif' : 'Draft' }}
                </span>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-2">
                  <button class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors" title="Lihat Detail">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                  </button>
                  <button
                    @click="openEditModal(sc)"
                    class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors"
                    title="Edit"
                  >
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </button>
                  <button
                    @click="openDeleteModal(sc)"
                    class="p-2 text-error hover:bg-error-container/20 rounded-lg transition-colors"
                    title="Hapus"
                  >
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ==================== MODALS ==================== -->

  <!-- Add/Edit Sub Component Modal -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="showFormModal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        @click.self="showFormModal = false"
      >
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showFormModal = false"></div>
        <div class="relative bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-lg z-10 modal-content max-h-[90vh] overflow-y-auto">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-outline-variant/10 sticky top-0 bg-surface-container-lowest rounded-t-2xl z-10">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">{{ formMode === 'add' ? 'add' : 'edit' }}</span>
              </div>
              <div>
                <h3 class="font-title-md text-title-md text-on-surface">{{ formMode === 'add' ? 'Tambah Sub Komponen' : 'Edit Sub Komponen' }}</h3>
                <p class="text-body-sm text-on-surface-variant">{{ formMode === 'add' ? 'Buat sub komponen baru' : 'Ubah data sub komponen' }}</p>
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
            <!-- Nama Sub Komponen -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Sub Komponen</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Pemilahan Sampah Organik"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Deskripsi</label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Deskripsi sub komponen..."
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
              ></textarea>
            </div>

            <!-- Komponen Induk -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Komponen Induk</label>
              <div class="relative">
                <select
                  v-model="form.parentComponent"
                  class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
                >
                  <option value="">Pilih Komponen</option>
                  <option v-for="c in componentOptions" :key="c" :value="c">{{ c }}</option>
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-[20px]">expand_more</span>
              </div>
            </div>

            <!-- Icon -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Icon</label>
              <div class="grid grid-cols-4 gap-2">
                <button
                  v-for="icon in iconOptions"
                  :key="icon.value"
                  @click="form.icon = icon.value"
                  class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-all"
                  :class="form.icon === icon.value
                    ? 'border-primary bg-primary/10 text-primary'
                    : 'border-outline-variant/30 hover:border-outline-variant text-on-surface-variant hover:bg-surface-container-low'"
                >
                  <span class="material-symbols-outlined text-[24px]">{{ icon.value }}</span>
                  <span class="text-[10px] font-medium">{{ icon.label }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 p-6 border-t border-outline-variant/10 sticky bottom-0 bg-surface-container-lowest rounded-b-2xl">
            <button
              @click="showFormModal = false"
              class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleFormSubmit"
              :disabled="!form.name || !form.description || !form.parentComponent"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ formMode === 'add' ? 'Simpan Sub Komponen' : 'Simpan Perubahan' }}
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
              <h3 class="font-title-md text-title-md text-on-surface">Hapus Sub Komponen</h3>
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
              Anda yakin ingin menghapus sub komponen berikut?
            </p>
            <div v-if="deletingSubComponent" class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/20">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined text-[20px]">{{ deletingSubComponent.icon }}</span>
                </div>
                <div>
                  <p class="font-body-base font-semibold text-on-surface">{{ deletingSubComponent.name }}</p>
                  <p class="text-body-sm text-on-surface-variant">{{ deletingSubComponent.parentComponent }} · {{ deletingSubComponent.indicators }} indikator</p>
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

/* ===== TABLE BUTTONS ===== */
.table-btn {
  transition: all 0.3s ease;
  border-radius: 0.5rem;
}
.table-btn:hover {
  background-color: #eef6ee;
  color: #006c49;
  transform: translateY(-1px);
}

/* ===== WEIGHT BADGE ===== */
.weight-badge {
  transition: all 0.3s ease;
}
.weight-badge:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* ===== ACTION BUTTON ===== */
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

/* ===== DATA TABLE SCROLL ===== */
.data-table-scroll::-webkit-scrollbar {
  height: 6px;
}
.data-table-scroll::-webkit-scrollbar-thumb {
  background: #bbcabf;
  border-radius: 10px;
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
