<script setup lang="ts">
import { ref, computed } from 'vue'

// State
const searchQuery = ref('')
const subComponentFilter = ref('')

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingIndicator = ref<any>(null)

// Form
const form = ref({
  name: '',
  description: '',
  parentSubComponent: '',
  weight: 10,
  status: 'aktif',
})

// Sub component options
const subComponentOptions = [
  'Pemilahan Sampah Organik',
  'Unit Pengolah Sampah (UPS)',
  'Manajemen Pengangkutan',
  'Efisiensi Penerangan Kelas',
  'Pengelolaan AC & Pendingin',
  'Penghematan Air Harian',
  'Kualitas Air Bersih',
  'Pemeliharaan Taman Sekolah',
]

// Mock data - Indicators
const indicators = ref([
  {
    id: 1,
    name: 'Ketersediaan Tempat Sampah Terpisah',
    description: 'Mengukur ketersediaan infrastruktur tempat sampah terpilah (hijau, kuning, merah) di area-area strategis sekolah.',
    parentSubComponent: 'Pemilahan Sampah Organik',
    parentComponent: 'Pengelolaan Sampah',
    weight: 20,
    status: 'aktif',
    totalQuestions: 5,
  },
  {
    id: 2,
    name: 'Jadwal Pengangkutan Rutin',
    description: 'Meninjau frekuensi dan konsistensi pengangkutan sampah organik ke pusat pengolahan.',
    parentSubComponent: 'Pemilahan Sampah Organik',
    parentComponent: 'Pengelolaan Sampah',
    weight: 15,
    status: 'aktif',
    totalQuestions: 3,
  },
  {
    id: 3,
    name: 'Edukasi Civitas Akademika',
    description: 'Efektivitas program kampanye dan panduan teknis pemilahan kepada seluruh anggota institusi.',
    parentSubComponent: 'Pemilahan Sampah Organik',
    parentComponent: 'Pengelolaan Sampah',
    weight: 10,
    status: 'aktif',
    totalQuestions: 4,
  },
  {
    id: 4,
    name: 'Kualitas Pemilahan di Sumber',
    description: 'Tingkat kebersihan sampah organik (tidak tercampur plastik/logam) saat berada di tempat sampah.',
    parentSubComponent: 'Pemilahan Sampah Organik',
    parentComponent: 'Pengelolaan Sampah',
    weight: 15,
    status: 'draft',
    totalQuestions: 2,
  },
  {
    id: 5,
    name: 'Integrasi ke Komposter',
    description: 'Alur distribusi sampah organik dari tempat sampah menuju fasilitas pengomposan mandiri.',
    parentSubComponent: 'Unit Pengolah Sampah (UPS)',
    parentComponent: 'Pengelolaan Sampah',
    weight: 10,
    status: 'draft',
    totalQuestions: 6,
  },
  {
    id: 6,
    name: 'Penggunaan Lampu LED',
    description: 'Persentase penggunaan lampu LED dibandingkan lampu konvensional di seluruh ruangan.',
    parentSubComponent: 'Efisiensi Penerangan Kelas',
    parentComponent: 'Efisiensi Energi',
    weight: 20,
    status: 'aktif',
    totalQuestions: 3,
  },
  {
    id: 7,
    name: 'Pemanfaatan Cahaya Alami',
    description: 'Evaluasi desain jendela dan pencahayaan alami yang optimal di ruang kelas.',
    parentSubComponent: 'Efisiensi Penerangan Kelas',
    parentComponent: 'Efisiensi Energi',
    weight: 15,
    status: 'aktif',
    totalQuestions: 2,
  },
  {
    id: 8,
    name: 'Pengaturan Suhu AC',
    description: 'Pengukuran pengaturan suhu AC yang efisien dan sesuai standar kenyamanan.',
    parentSubComponent: 'Pengelolaan AC & Pendingin',
    parentComponent: 'Efisiensi Energi',
    weight: 25,
    status: 'draft',
    totalQuestions: 4,
  },
  {
    id: 9,
    name: 'Kualitas Air Bersih',
    description: 'Pengujian berkala parameter kualitas air bersih yang digunakan di lingkungan sekolah.',
    parentSubComponent: 'Kualitas Air Bersih',
    parentComponent: 'Pengelolaan Air Bersih',
    weight: 20,
    status: 'aktif',
    totalQuestions: 3,
  },
  {
    id: 10,
    name: 'Kondisi Vegetasi Taman',
    description: 'Penilaian kesehatan dan kerapatan vegetasi di area taman sekolah.',
    parentSubComponent: 'Pemeliharaan Taman Sekolah',
    parentComponent: 'Ruang Terbuka Hijau',
    weight: 15,
    status: 'aktif',
    totalQuestions: 5,
  },
])

// Computed
const filteredIndicators = computed(() => {
  return indicators.value.filter((ind) => {
    const matchSearch = ind.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      ind.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchSubComponent = !subComponentFilter.value || ind.parentSubComponent === subComponentFilter.value
    return matchSearch && matchSubComponent
  })
})

// Methods
function getStatusClass(status: string) {
  if (status === 'aktif') return 'bg-primary/10 text-primary'
  if (status === 'draft') return 'bg-secondary/10 text-secondary'
  return 'bg-outline-variant/30 text-on-surface-variant'
}

// Form handlers
function openAddModal() {
  formMode.value = 'add'
  editingId.value = null
  form.value = { name: '', description: '', parentSubComponent: '', weight: 10, status: 'aktif' }
  showFormModal.value = true
}

function openEditModal(ind: any) {
  formMode.value = 'edit'
  editingId.value = ind.id
  form.value = {
    name: ind.name,
    description: ind.description,
    parentSubComponent: ind.parentSubComponent,
    weight: ind.weight,
    status: ind.status,
  }
  showFormModal.value = true
}

function handleFormSubmit() {
  if (formMode.value === 'add') {
    const newId = Math.max(...indicators.value.map((i) => i.id)) + 1
    const parentComponent = form.value.parentSubComponent.includes('Sampah') ? 'Pengelolaan Sampah'
      : form.value.parentSubComponent.includes('Energi') || form.value.parentSubComponent.includes('Penerangan') || form.value.parentSubComponent.includes('AC') ? 'Efisiensi Energi'
      : form.value.parentSubComponent.includes('Air') ? 'Pengelolaan Air Bersih'
      : 'Ruang Terbuka Hijau'
    indicators.value.unshift({
      id: newId,
      name: form.value.name,
      description: form.value.description,
      parentSubComponent: form.value.parentSubComponent,
      parentComponent,
      weight: form.value.weight,
      status: form.value.status,
      totalQuestions: 0,
    })
  } else {
    const idx = indicators.value.findIndex((i) => i.id === editingId.value)
    if (idx !== -1) {
      indicators.value[idx] = {
        ...indicators.value[idx],
        name: form.value.name,
        description: form.value.description,
        parentSubComponent: form.value.parentSubComponent,
        weight: form.value.weight,
        status: form.value.status,
      }
    }
  }
  showFormModal.value = false
}

// Delete handlers
function openDeleteModal(ind: any) {
  deletingIndicator.value = ind
  showDeleteModal.value = true
}

function confirmDelete() {
  indicators.value = indicators.value.filter((i) => i.id !== deletingIndicator.value.id)
  showDeleteModal.value = false
  deletingIndicator.value = null
}
</script>

<template>
  <div class="p-12 max-w-[1840px] w-full mx-auto">
    <!-- Header Section -->
    <section class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 fade-in">
      <div>
        <h2 class="font-headline-xl text-headline-xl text-on-surface">Master Indikator</h2>
        <p class="font-body-base text-body-base text-on-surface-variant mt-2 max-w-2xl">
          Kelola indikator evaluasi kebijakan lingkungan sekolah secara sistematis untuk memantau progres keberlanjutan instansi.
        </p>
      </div>
      <div>
        <button
          @click="openAddModal"
          class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
        >
          <span class="material-symbols-outlined">add</span>
          Tambah Indikator
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
            placeholder="Cari nama indikator..."
            type="text"
          />
        </div>
        <div class="flex items-center gap-3">
          <div class="relative min-w-[220px]">
            <select
              v-model="subComponentFilter"
              class="custom-select w-full bg-white border border-outline-variant/50 rounded-xl px-4 py-2.5 appearance-none focus:ring-2 focus:ring-primary-container outline-none text-body-sm font-body-sm cursor-pointer"
            >
              <option value="">Semua Sub Komponen</option>
              <option v-for="sc in subComponentOptions" :key="sc" :value="sc">{{ sc }}</option>
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
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama Indikator</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Sub Komponen</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Bobot</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Pertanyaan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
              v-for="ind in filteredIndicators"
              :key="ind.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
            >
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">analytics</span>
                  </div>
                  <div>
                    <span class="font-body-base font-semibold text-on-surface">{{ ind.name }}</span>
                    <p class="text-body-sm text-on-surface-variant line-clamp-1 mt-0.5">{{ ind.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="weight-badge text-xs font-medium bg-secondary-container text-on-secondary-container px-2 py-0.5 rounded">{{ ind.parentSubComponent }}</span>
              </td>
              <td class="px-6 py-5">
                <span class="px-3 py-1 bg-surface-container-high rounded-lg font-bold text-primary text-sm">{{ ind.weight }}%</span>
              </td>
              <td class="px-6 py-5 text-body-sm text-on-surface font-semibold font-body-sm">{{ ind.totalQuestions }}</td>
              <td class="px-6 py-5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="getStatusClass(ind.status)">
                  {{ ind.status === 'aktif' ? 'Aktif' : 'Draft' }}
                </span>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-2">
                  <button class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors" title="Lihat Detail">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                  </button>
                  <button
                    @click="openEditModal(ind)"
                    class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors"
                    title="Edit"
                  >
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </button>
                  <button
                    @click="openDeleteModal(ind)"
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

  <!-- Add/Edit Indicator Modal -->
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
                placeholder="Contoh: Ketersediaan Tempat Sampah Terpisah"
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

            <!-- Sub Komponen Induk -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Sub Komponen Induk</label>
              <div class="relative">
                <select
                  v-model="form.parentSubComponent"
                  class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
                >
                  <option value="">Pilih Sub Komponen</option>
                  <option v-for="sc in subComponentOptions" :key="sc" :value="sc">{{ sc }}</option>
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-[20px]">expand_more</span>
              </div>
            </div>

            <!-- Bobot & Status -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Bobot (%)</label>
                <input
                  v-model.number="form.weight"
                  type="number"
                  min="1"
                  max="100"
                  placeholder="10"
                  class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
                />
              </div>
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
                <div class="relative">
                  <select
                    v-model="form.status"
                    class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
                  >
                    <option value="aktif">Aktif</option>
                    <option value="draft">Draft</option>
                  </select>
                  <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-[20px]">expand_more</span>
                </div>
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
              :disabled="!form.name || !form.description || !form.parentSubComponent"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ formMode === 'add' ? 'Simpan Indikator' : 'Simpan Perubahan' }}
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
                  <p class="text-body-sm text-on-surface-variant">{{ deletingIndicator.parentSubComponent }} · {{ deletingIndicator.totalQuestions }} pertanyaan</p>
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
