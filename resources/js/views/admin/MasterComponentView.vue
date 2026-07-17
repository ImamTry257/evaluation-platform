<script setup lang="ts">
import { ref, computed } from 'vue'

// State
const searchQuery = ref('')

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingComponent = ref<any>(null)

// Form
const form = ref({
  name: '',
  description: '',
  questionnaire: '',
})

// Mock data - Questionnaire options
const questionnaireOptions = ['Management Sampah', 'Pengelolaan Energi', 'Pengelolaan Air', 'Ruang Terbuka Hijau', 'Emisi Karbon']

// Mock data - Components
const components = ref([
  {
    id: 1,
    name: 'Pengelolaan Sampah',
    description: 'Evaluasi sistem pengelolaan limbah padat di lingkungan sekolah termasuk pemilahan, pengangkutan, dan pengolahan.',
    questionnaire: 'Management Sampah',
    totalSubComponents: 4,
    totalIndicators: 12,
    totalQuestions: 36,
    completeness: 85,
  },
  {
    id: 2,
    name: 'Efisiensi Energi',
    description: 'Pengukuran tingkat efisiensi pemakaian listrik kelas dan optimalisasi perangkat elektronik sekolah.',
    questionnaire: 'Pengelolaan Energi',
    totalSubComponents: 3,
    totalIndicators: 8,
    totalQuestions: 24,
    completeness: 72,
  },
  {
    id: 3,
    name: 'Pengelolaan Air Bersih',
    description: 'Pengukuran konsumsi air harian dan efisiensi sistem irigasi di lingkungan sekolah.',
    questionnaire: 'Pengelolaan Air',
    totalSubComponents: 2,
    totalIndicators: 6,
    totalQuestions: 18,
    completeness: 60,
  },
  {
    id: 4,
    name: 'Ruang Terbuka Hijau',
    description: 'Pemantauan berkala kondisi dan kualitas Ruang Terbuka Hijau di area sekolah.',
    questionnaire: 'Ruang Terbuka Hijau',
    totalSubComponents: 5,
    totalIndicators: 15,
    totalQuestions: 45,
    completeness: 90,
  },
  {
    id: 5,
    name: 'Emisi Karbon',
    description: 'Kalkulasi jejak karbon dari aktivitas transportasi dan energi sekolah.',
    questionnaire: 'Emisi Karbon',
    totalSubComponents: 3,
    totalIndicators: 9,
    totalQuestions: 27,
    completeness: 45,
  },
])

// Computed
const filteredComponents = computed(() => {
  return components.value.filter((c) =>
    c.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    c.questionnaire.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Methods
function getCompletionColor(completion: number) {
  if (completion >= 80) return 'text-primary bg-primary/10'
  if (completion >= 50) return 'text-tertiary bg-tertiary/10'
  return 'text-on-surface-variant bg-outline-variant/30'
}

// Form handlers
function openAddModal() {
  formMode.value = 'add'
  editingId.value = null
  form.value = { name: '', description: '', questionnaire: '' }
  showFormModal.value = true
}

function openEditModal(c: any) {
  formMode.value = 'edit'
  editingId.value = c.id
  form.value = {
    name: c.name,
    description: c.description,
    questionnaire: c.questionnaire,
  }
  showFormModal.value = true
}

function handleFormSubmit() {
  if (formMode.value === 'add') {
    const newId = Math.max(...components.value.map((c) => c.id)) + 1
    components.value.unshift({
      id: newId,
      name: form.value.name,
      description: form.value.description,
      questionnaire: form.value.questionnaire,
      totalSubComponents: 0,
      totalIndicators: 0,
      totalQuestions: 0,
      completeness: 0,
    })
  } else {
    const idx = components.value.findIndex((c) => c.id === editingId.value)
    if (idx !== -1) {
      components.value[idx] = {
        ...components.value[idx],
        name: form.value.name,
        description: form.value.description,
        questionnaire: form.value.questionnaire,
      }
    }
  }
  showFormModal.value = false
}

// Delete handlers
function openDeleteModal(c: any) {
  deletingComponent.value = c
  showDeleteModal.value = true
}

function confirmDelete() {
  components.value = components.value.filter((c) => c.id !== deletingComponent.value.id)
  showDeleteModal.value = false
  deletingComponent.value = null
}
</script>

<template>
  <div class="p-12 max-w-[1840px] w-full mx-auto">
    <!-- Header Section -->
    <section class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 fade-in">
      <div>
        <h2 class="font-headline-xl text-headline-xl text-on-surface">Master Komponen</h2>
        <p class="font-body-base text-body-base text-on-surface-variant mt-2 max-w-2xl">
          Kelola komponen evaluasi kebijakan lingkungan sekolah secara sistematis untuk memantau progres keberlanjutan instansi.
        </p>
      </div>
      <div>
        <button
          @click="openAddModal"
          class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
        >
          <span class="material-symbols-outlined">add</span>
          Tambah Komponen
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
            placeholder="Cari nama komponen..."
            type="text"
          />
        </div>
        <button class="action-btn bg-white border border-outline-variant/50 text-on-surface px-4 py-2.5 rounded-xl flex items-center gap-2 hover:bg-surface-container transition-colors">
          <span class="material-symbols-outlined text-[20px]">filter_list</span>
          <span class="text-body-sm font-body-sm">Filter Lanjut</span>
        </button>
      </div>

      <!-- Data Table -->
      <div class="overflow-x-auto data-table-scroll">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low/50">
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama Komponen</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Kuesioner</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Sub Komponen</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Indikator</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Pertanyaan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Kelengkapan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
              v-for="c in filteredComponents"
              :key="c.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
            >
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">widgets</span>
                  </div>
                  <div>
                    <span class="font-body-base font-semibold text-on-surface">{{ c.name }}</span>
                    <p class="text-body-sm text-on-surface-variant line-clamp-1 mt-0.5">{{ c.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="weight-badge text-xs font-medium bg-secondary-container text-on-secondary-container px-2 py-0.5 rounded">{{ c.questionnaire }}</span>
              </td>
              <td class="px-6 py-5 text-body-sm text-on-surface font-semibold font-body-sm">{{ c.totalSubComponents }}</td>
              <td class="px-6 py-5 text-body-sm text-on-surface font-semibold font-body-sm">{{ c.totalIndicators }}</td>
              <td class="px-6 py-5 text-body-sm text-on-surface font-semibold font-body-sm">{{ c.totalQuestions }}</td>
              <td class="px-6 py-5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="getCompletionColor(c.completeness)">
                  {{ c.completeness }}%
                </span>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-2">
                  <button class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors" title="Lihat Detail">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                  </button>
                  <button
                    @click="openEditModal(c)"
                    class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors"
                    title="Edit"
                  >
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </button>
                  <button
                    @click="openDeleteModal(c)"
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

  <!-- Add/Edit Component Modal -->
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
                <h3 class="font-title-md text-title-md text-on-surface">{{ formMode === 'add' ? 'Tambah Komponen' : 'Edit Komponen' }}</h3>
                <p class="text-body-sm text-on-surface-variant">{{ formMode === 'add' ? 'Buat komponen baru' : 'Ubah data komponen' }}</p>
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
            <!-- Nama Komponen -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Komponen</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Pengelolaan Sampah"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Deskripsi</label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Deskripsi komponen evaluasi..."
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
              ></textarea>
            </div>

            <!-- Kuesioner -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Kuesioner</label>
              <div class="relative">
                <select
                  v-model="form.questionnaire"
                  class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
                >
                  <option value="">Pilih Kuesioner</option>
                  <option v-for="q in questionnaireOptions" :key="q" :value="q">{{ q }}</option>
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
              @click="handleFormSubmit"
              :disabled="!form.name || !form.description || !form.questionnaire"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ formMode === 'add' ? 'Simpan Komponen' : 'Simpan Perubahan' }}
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
              <h3 class="font-title-md text-title-md text-on-surface">Hapus Komponen</h3>
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
              Anda yakin ingin menghapus komponen berikut?
            </p>
            <div v-if="deletingComponent" class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/20">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined text-[20px]">widgets</span>
                </div>
                <div>
                  <p class="font-body-base font-semibold text-on-surface">{{ deletingComponent.name }}</p>
                  <p class="text-body-sm text-on-surface-variant">{{ deletingComponent.totalSubComponents }} sub-komponen, {{ deletingComponent.totalIndicators }} indikator</p>
                </div>
              </div>
            </div>
            <p class="text-body-sm text-error mt-3 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px]">warning</span>
              Semua sub-komponen di dalamnya akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.
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
