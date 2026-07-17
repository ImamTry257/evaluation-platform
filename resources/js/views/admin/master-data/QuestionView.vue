<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, RouterLink, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const componentId = computed(() => route.params.componentId as string)
const subComponentId = computed(() => route.params.subComponentId as string)
const indicatorId = computed(() => route.params.indicatorId as string)

// State
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = 10

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingQuestion = ref<any>(null)

// Form
const form = ref({
  text: '',
  weight: 1.0,
})

// Mock data - Questions (context: Indikator "Keberadaan Organisasi")
const questions = ref([
  {
    id: 1,
    text: 'Apakah sekolah memiliki unit/organisasi khusus yang menangani program lingkungan?',
    weight: 1.0,
    status: 'active',
  },
  {
    id: 2,
    text: 'Seberapa aktif organisasi tersebut dalam menjalankan program lingkungan?',
    weight: 1.0,
    status: 'active',
  },
  {
    id: 3,
    text: 'Apakah anggota organisasi mendapat pelatihan terkait pengelolaan lingkungan?',
    weight: 0.8,
    status: 'inactive',
  },
])

// Computed
const filteredQuestions = computed(() => {
  return questions.value.filter((q) =>
    q.text.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const totalPages = computed(() => Math.ceil(filteredQuestions.value.length / perPage))

const paginatedQuestions = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredQuestions.value.slice(start, start + perPage)
})

const showingFrom = computed(() => (currentPage.value - 1) * perPage + 1)
const showingTo = computed(() => Math.min(currentPage.value * perPage, filteredQuestions.value.length))

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
  form.value = { text: '', weight: 1.0 }
  showFormModal.value = true
}

function openEditModal(q: any) {
  formMode.value = 'edit'
  editingId.value = q.id
  form.value = { text: q.text, weight: q.weight }
  showFormModal.value = true
}

function handleFormSubmit() {
  if (formMode.value === 'add') {
    const newId = Math.max(...questions.value.map((q) => q.id)) + 1
    questions.value.unshift({
      id: newId,
      text: form.value.text,
      weight: form.value.weight,
      status: 'active',
    })
  } else {
    const idx = questions.value.findIndex((q) => q.id === editingId.value)
    if (idx !== -1) {
      questions.value[idx] = {
        ...questions.value[idx],
        text: form.value.text,
        weight: form.value.weight,
      }
    }
  }
  showFormModal.value = false
}

// Delete handlers
function openDeleteModal(q: any) {
  deletingQuestion.value = q
  showDeleteModal.value = true
}

function confirmDelete() {
  questions.value = questions.value.filter((q) => q.id !== deletingQuestion.value.id)
  showDeleteModal.value = false
  deletingQuestion.value = null
}

// More menu actions
function handleView(item: any) {
  console.log('View:', item)
}

function handleEdit(item: any) {
  openEditModal(item)
}

function handleToggleStatus(item: any) {
  const idx = questions.value.findIndex((q) => q.id === item.id)
  if (idx !== -1) {
    questions.value[idx].status = item.status === 'active' ? 'inactive' : 'active'
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
      <RouterLink to="/admin/instrument" class="text-primary font-medium hover:underline cursor-pointer">Instrument Penelitian</RouterLink>
      <span class="text-outline">›</span>
      <RouterLink to="/admin/instrument/1" class="text-primary font-medium hover:underline cursor-pointer">Kuesioner Kebijakan Lingkungan</RouterLink>
      <span class="text-outline">›</span>
      <RouterLink :to="`/admin/component/${componentId}`" class="text-primary font-medium hover:underline cursor-pointer">Kebijakan Struktural</RouterLink>
      <span class="text-outline">›</span>
      <RouterLink :to="`/admin/component/${componentId}/sub-component`" class="text-primary font-medium hover:underline cursor-pointer">Kerangka Organisasi</RouterLink>
      <span class="text-outline">›</span>
      <RouterLink :to="`/admin/component/${componentId}/sub-component/${subComponentId}/indicator`" class="text-primary font-medium hover:underline cursor-pointer">Keberadaan Organisasi</RouterLink>
      <span class="text-outline">›</span>
      <span class="text-on-surface font-semibold">Pertanyaan</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 fade-in">
      <div class="flex items-center gap-4">
        <RouterLink :to="`/admin/sub-component/${subComponentId}/indicator`" class="back-btn flex items-center gap-1 text-primary text-sm font-medium hover:bg-primary/10 px-3 py-2 rounded-lg transition-colors no-underline">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          Kembali
        </RouterLink>
      </div>
      <button
        @click="openAddModal"
        class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
      >
        <span class="material-symbols-outlined">add</span>
        Tambah Pertanyaan
      </button>
    </div>

    <!-- Info Bar -->
    <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-sky-50 border border-sky-200 rounded-xl text-sm text-sky-700">
      <span class="material-symbols-outlined text-[16px]">info</span>
      <span>Skor jawaban menggunakan <strong>Likert Scale 1-7</strong>. Bobot menentukan kontribusi terhadap skor indikator.</span>
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
            placeholder="Cari pertanyaan..."
            type="text"
          />
        </div>
      </div>

      <!-- Data Table -->
      <div class="overflow-x-auto data-table-scroll">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low/50">
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase w-16">#</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Pertanyaan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Bobot</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
              v-for="(q, index) in paginatedQuestions"
              :key="q.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
              :class="{ 'opacity-50': q.status === 'inactive' }"
            >
              <td class="px-6 py-5 text-sm font-medium text-secondary">{{ String(index + showingFrom).padStart(2, '0') }}</td>
              <td class="px-6 py-5">
                <p class="text-sm font-semibold text-on-surface line-clamp-2">{{ q.text }}</p>
              </td>
              <td class="px-6 py-5">
                <span class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" :class="getStatusBadge(q.status)">
                  {{ getStatusLabel(q.status) }}
                </span>
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
                <div class="flex items-center justify-center gap-2">
                  <div class="relative more-wrapper">
                    <button class="more-btn w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors">
                      <span class="material-symbols-outlined text-[20px]">more_vert</span>
                    </button>
                    <div class="more-dropdown">
                      <div class="more-dropdown-item" @click="handleView(q)">
                        <span class="material-symbols-outlined">visibility</span>
                        View Detail
                      </div>
                      <div class="more-dropdown-item" @click="handleEdit(q)">
                        <span class="material-symbols-outlined">edit</span>
                        Edit
                      </div>
                      <div class="more-dropdown-item" @click="handleToggleStatus(q)">
                        <span class="material-symbols-outlined">toggle_on</span>
                        {{ q.status === 'active' ? 'Set Inactive' : 'Set Active' }}
                      </div>
                      <div class="more-dropdown-divider"></div>
                      <div class="more-dropdown-item danger" @click="handleDelete(q)">
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
          Menampilkan <span class="font-semibold text-on-surface">{{ showingFrom }}-{{ showingTo }}</span> dari <span class="font-semibold text-on-surface">{{ filteredQuestions.length }}</span> pertanyaan
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
                <h3 class="font-title-md text-title-md text-on-surface">{{ formMode === 'add' ? 'Tambah Pertanyaan' : 'Edit Pertanyaan' }}</h3>
                <p class="text-body-sm text-on-surface-variant">{{ formMode === 'add' ? 'Buat pertanyaan baru' : 'Ubah data pertanyaan' }}</p>
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
            <!-- Teks Pertanyaan -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Teks Pertanyaan</label>
              <textarea
                v-model="form.text"
                rows="3"
                placeholder="Tulis pertanyaan evaluasi..."
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
              ></textarea>
            </div>

            <!-- Bobot -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Bobot</label>
              <input
                v-model.number="form.weight"
                type="number"
                min="0"
                max="1"
                step="0.1"
                placeholder="1.0"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
              <p class="text-xs text-secondary mt-1">Nilai 0-1. Default: 1.0</p>
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
              :disabled="!form.text"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ formMode === 'add' ? 'Simpan Pertanyaan' : 'Simpan Perubahan' }}
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
              <h3 class="font-title-md text-title-md text-on-surface">Hapus Pertanyaan</h3>
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
              Anda yakin ingin menghapus pertanyaan berikut?
            </p>
            <div v-if="deletingQuestion" class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/20">
              <div>
                <p class="font-body-base font-semibold text-on-surface line-clamp-2">{{ deletingQuestion.text }}</p>
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
