<script setup lang="ts">
import { ref, computed } from 'vue'

// State
const searchQuery = ref('')
const periodFilter = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const perPage = 6

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingQuestionnaire = ref<any>(null)

// Form
const form = ref({
  name: '',
  description: '',
  period: '',
  status: 'draft',
  duration: 30,
  aspects: 0,
  questions: 0,
})

// Mock data - Questionnaires
const questionnaires = ref([
  {
    id: 1,
    name: 'Evaluasi Penggunaan Energi 2024',
    description: 'Survei tingkat efisiensi pemakaian listrik kelas dan optimalisasi perangkat elektronik sekolah.',
    period: 'Ganjil 2023/24',
    status: 'terbit',
    duration: 45,
    aspects: 5,
    questions: 32,
  },
  {
    id: 2,
    name: 'Survei Pengelolaan Sampah Mandiri',
    description: 'Draf awal instrumen penilaian kebersihan kantin dan area terbuka hijau oleh siswa.',
    period: 'Genap 2023/24',
    status: 'draft',
    duration: 20,
    aspects: 3,
    questions: 12,
  },
  {
    id: 3,
    name: 'Audit RTH Sekolah Tahap 1',
    description: 'Instrumen lama tahun ajaran 2022/2023 untuk pemantauan berkala Ruang Terbuka Hijau.',
    period: 'Genap 2022/23',
    status: 'arsip',
    duration: 60,
    aspects: 8,
    questions: 45,
  },
  {
    id: 4,
    name: 'Evaluasi Penggunaan Air Bersih',
    description: 'Pengukuran konsumsi air harian dan efisiensi sistem irigasi di lingkungan sekolah.',
    period: 'Ganjil 2024/25',
    status: 'draft',
    duration: 30,
    aspects: 4,
    questions: 18,
  },
  {
    id: 5,
    name: 'Survei Kebiasaan Daur Ulang',
    description: 'Pemetaan partisipasi warga sekolah dalam program daur ulang dan pengurangan limbah.',
    period: 'Ganjil 2023/24',
    status: 'terbit',
    duration: 25,
    aspects: 3,
    questions: 15,
  },
  {
    id: 6,
    name: 'Audit Emisi Karbon Sekolah',
    description: 'Kalkulasi jejak karbon dari aktivitas transportasi dan energi sekolah.',
    period: 'Genap 2023/24',
    status: 'arsip',
    duration: 40,
    aspects: 6,
    questions: 28,
  },
])

// Mock data - Period options
const periodOptions = ['Ganjil 2023/24', 'Genap 2023/24', 'Ganjil 2024/25', 'Genap 2022/23']

// Computed
const filteredQuestionnaires = computed(() => {
  return questionnaires.value.filter((q) => {
    const matchSearch = q.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchPeriod = !periodFilter.value || q.period === periodFilter.value
    const matchStatus = !statusFilter.value || q.status === statusFilter.value
    return matchSearch && matchPeriod && matchStatus
  })
})

const totalPages = computed(() => Math.ceil(filteredQuestionnaires.value.length / perPage))

const paginatedQuestionnaires = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredQuestionnaires.value.slice(start, start + perPage)
})

const showingFrom = computed(() => (currentPage.value - 1) * perPage + 1)
const showingTo = computed(() => Math.min(currentPage.value * perPage, filteredQuestionnaires.value.length))

// Stats
const totalResponses = ref(1482)
const avgDuration = ref('18:45')
const incompleteCount = ref(2)

// Methods
function getStatusConfig(status: string) {
  switch (status) {
    case 'terbit':
      return {
        badge: 'bg-primary/10 text-primary',
        dot: 'bg-primary',
        label: 'Terbit',
        icon: 'description',
        iconBg: 'bg-primary/10 text-primary',
      }
    case 'draft':
      return {
        badge: 'bg-secondary/10 text-secondary',
        dot: 'bg-secondary',
        label: 'Draft',
        icon: 'assignment_late',
        iconBg: 'bg-surface-container text-secondary',
      }
    case 'arsip':
      return {
        badge: 'bg-tertiary-container/30 text-on-tertiary-container',
        dot: 'bg-tertiary',
        label: 'Arsip',
        icon: 'history',
        iconBg: 'bg-tertiary-container/10 text-tertiary',
      }
    default:
      return {
        badge: '',
        dot: '',
        label: '',
        icon: 'description',
        iconBg: '',
      }
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
    aspects: 0,
    questions: 0,
  }
  showFormModal.value = true
}

function openEditModal(q: any) {
  formMode.value = 'edit'
  editingId.value = q.id
  form.value = {
    name: q.name,
    description: q.description,
    period: q.period,
    status: q.status,
    duration: q.duration,
    aspects: q.aspects,
    questions: q.questions,
  }
  showFormModal.value = true
}

function handleFormSubmit() {
  if (formMode.value === 'add') {
    const newId = Math.max(...questionnaires.value.map((q) => q.id)) + 1
    questionnaires.value.unshift({
      id: newId,
      name: form.value.name,
      description: form.value.description,
      period: form.value.period,
      status: form.value.status,
      duration: form.value.duration,
      aspects: form.value.aspects,
      questions: form.value.questions,
    })
  } else {
    const idx = questionnaires.value.findIndex((q) => q.id === editingId.value)
    if (idx !== -1) {
      questionnaires.value[idx] = {
        ...questionnaires.value[idx],
        name: form.value.name,
        description: form.value.description,
        period: form.value.period,
        status: form.value.status,
        duration: form.value.duration,
        aspects: form.value.aspects,
        questions: form.value.questions,
      }
    }
  }
  showFormModal.value = false
}

// Delete handlers
function openDeleteModal(q: any) {
  deletingQuestionnaire.value = q
  showDeleteModal.value = true
}

function confirmDelete() {
  questionnaires.value = questionnaires.value.filter((q) => q.id !== deletingQuestionnaire.value.id)
  showDeleteModal.value = false
  deletingQuestionnaire.value = null
}

// Action handlers
function handlePublish(q: any) {
  const idx = questionnaires.value.findIndex((item) => item.id === q.id)
  if (idx !== -1) {
    questionnaires.value[idx].status = 'terbit'
  }
}

function handleArchive(q: any) {
  const idx = questionnaires.value.findIndex((item) => item.id === q.id)
  if (idx !== -1) {
    questionnaires.value[idx].status = 'arsip'
  }
}

function handleRestore(q: any) {
  const idx = questionnaires.value.findIndex((item) => item.id === q.id)
  if (idx !== -1) {
    questionnaires.value[idx].status = 'draft'
  }
}
</script>

<template>
  <div class="p-12 max-w-[1840px] w-full mx-auto">
    <!-- Page Header -->
    <div class="px-gutter lg:px-margin pt-8">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div class="max-w-2xl">
          <h2 class="font-headline-xl text-headline-xl font-bold text-on-surface mb-2 fade-in">Manajemen Kuesioner</h2>
          <p class="font-body-base text-body-base text-secondary fade-in-delay">
            Kelola instrumen evaluasi kebijakan lingkungan sekolah secara komprehensif. Sesuaikan pertanyaan, periode, dan target responden untuk mendapatkan data yang akurat.
          </p>
        </div>
        <div>
          <button
            @click="openAddModal"
            class="btn-start bg-primary-container text-white px-6 py-3 rounded-xl font-medium flex items-center gap-2 shadow-md fade-in-delay-2"
          >
            <span class="material-symbols-outlined">add</span>
            Buat Kuesioner
          </button>
        </div>
      </div>

      <!-- Action Bar & Content Card -->
      <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay mb-8">
        <!-- Action Bar -->
        <div class="p-6 border-b border-outline-variant/10 flex flex-col sm:flex-row gap-4 bg-surface-container-low/30">
          <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
            <input
              v-model="searchQuery"
              class="search-input w-full bg-white border border-outline-variant/50 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary-container outline-none transition-all text-body-sm font-body-sm"
              placeholder="Cari nama kuesioner..."
              type="text"
            />
          </div>
          <div class="flex items-center gap-3">
            <div class="relative min-w-[160px]">
              <select
                v-model="periodFilter"
                class="custom-select w-full bg-white border border-outline-variant/50 rounded-xl px-4 py-2.5 appearance-none focus:ring-2 focus:ring-primary-container outline-none text-body-sm font-body-sm cursor-pointer"
              >
                <option value="">Semua Periode</option>
                <option v-for="period in periodOptions" :key="period" :value="period">{{ period }}</option>
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
                <option value="terbit">Terbit</option>
                <option value="arsip">Arsip</option>
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
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama Kuesioner</th>
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Periode</th>
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Durasi</th>
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Aspek</th>
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Pertanyaan</th>
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10">
              <tr
                v-for="q in paginatedQuestionnaires"
                :key="q.id"
                class="table-row hover:bg-surface-container-low/30 transition-colors"
                :class="{ 'opacity-75': q.status === 'arsip' }"
              >
                <td class="px-6 py-5">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="getStatusConfig(q.status).iconBg">
                      <span class="material-symbols-outlined text-[20px]">{{ getStatusConfig(q.status).icon }}</span>
                    </div>
                    <div>
                      <span class="font-body-base font-semibold text-on-surface">{{ q.name }}</span>
                      <p class="text-body-sm text-on-surface-variant line-clamp-1 mt-0.5">{{ q.description }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5">
                  <span class="weight-badge text-xs font-medium bg-secondary-container text-on-secondary-container px-2 py-0.5 rounded">{{ q.period }}</span>
                </td>
                <td class="px-6 py-5 text-body-sm text-on-surface-variant font-body-sm">{{ q.duration }} mnt</td>
                <td class="px-6 py-5 text-body-sm text-on-surface-variant font-body-sm">{{ q.aspects }}</td>
                <td class="px-6 py-5 text-body-sm text-on-surface-variant font-body-sm">{{ q.questions }}</td>
                <td class="px-6 py-5">
                  <span class="status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="getStatusConfig(q.status).badge">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="getStatusConfig(q.status).dot"></span>
                    {{ getStatusConfig(q.status).label }}
                  </span>
                </td>
                <td class="px-6 py-5">
                  <div class="flex items-center justify-center gap-1">
                    <button class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors" title="Lihat">
                      <span class="material-symbols-outlined text-[18px]">{{ q.status === 'arsip' ? 'analytics' : 'visibility' }}</span>
                    </button>
                    <button
                      @click="openEditModal(q)"
                      class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors"
                      title="Edit"
                    >
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>
                    <template v-if="q.status === 'draft'">
                      <button
                        @click="handlePublish(q)"
                        class="bg-primary/5 hover:bg-primary/10 text-primary text-[11px] font-bold px-3 py-1.5 rounded-lg border border-primary/20 transition-all"
                      >
                        Publish
                      </button>
                    </template>
                    <template v-else-if="q.status === 'terbit'">
                      <button
                        @click="handleArchive(q)"
                        class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors"
                        title="Arsipkan"
                      >
                        <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                      </button>
                    </template>
                    <template v-else-if="q.status === 'arsip'">
                      <button
                        @click="handleRestore(q)"
                        class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors"
                        title="Restore"
                      >
                        <span class="material-symbols-outlined text-[18px]">restore</span>
                      </button>
                    </template>
                    <button
                      @click="openDeleteModal(q)"
                      class="p-2 text-error hover:bg-error-container/20 rounded-lg transition-colors"
                      :title="q.status === 'arsip' ? 'Hapus Permanen' : 'Hapus'"
                    >
                      <span class="material-symbols-outlined text-[18px]">{{ q.status === 'arsip' ? 'delete_forever' : 'delete' }}</span>
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
            Menampilkan <span class="font-semibold text-on-surface">{{ showingFrom }}-{{ showingTo }}</span> dari <span class="font-semibold text-on-surface">{{ filteredQuestionnaires.length }}</span> kuesioner
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

      <!-- Dashboard Insight Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 fade-in-delay-3 mb-8">
        <!-- Total Respon -->
        <div class="info-grid-item bg-white p-6 rounded-xl border border-outline-variant shadow-sm relative overflow-hidden group glass-card">
          <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-primary/10 rounded-lg text-primary grid-icon">
              <span class="material-symbols-outlined">checklist</span>
            </div>
            <span class="weight-badge text-xs font-bold text-primary bg-primary/10 px-2 py-1 rounded-md">+12%</span>
          </div>
          <h4 class="text-secondary text-sm font-medium mb-1">Total Respon Diterima</h4>
          <p class="text-3xl font-bold text-on-surface">{{ totalResponses.toLocaleString() }}</p>
          <div class="absolute bottom-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform">
            <span class="material-symbols-outlined text-[80px]">poll</span>
          </div>
        </div>

        <!-- Rata-rata Durasi -->
        <div class="info-grid-item bg-white p-6 rounded-xl border border-outline-variant shadow-sm relative overflow-hidden group glass-card">
          <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-primary/10 rounded-lg text-primary grid-icon">
              <span class="material-symbols-outlined">timer</span>
            </div>
            <span class="status-badge text-xs font-bold text-secondary bg-surface-container px-2 py-1 rounded-md">Stable</span>
          </div>
          <h4 class="text-secondary text-sm font-medium mb-1">Rata-rata Durasi Pengisian</h4>
          <p class="text-3xl font-bold text-on-surface">{{ avgDuration }}</p>
          <p class="text-xs text-secondary mt-1">Target: &lt; 20 Menit</p>
        </div>

        <!-- Kuesioner Belum Lengkap -->
        <div class="info-grid-item bg-white p-6 rounded-xl border border-outline-variant shadow-sm relative overflow-hidden group glass-card">
          <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-tertiary-container/10 rounded-lg text-tertiary grid-icon">
              <span class="material-symbols-outlined">error</span>
            </div>
            <span class="weight-badge text-xs font-bold text-tertiary bg-tertiary-container/20 px-2 py-1 rounded-md">-4%</span>
          </div>
          <h4 class="text-secondary text-sm font-medium mb-1">Kuesioner Belum Lengkap</h4>
          <p class="text-3xl font-bold text-on-surface">{{ incompleteCount }}</p>
          <p class="text-xs text-secondary mt-1">Perlu tinjauan segera</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ==================== MODALS ==================== -->

  <!-- Add/Edit Questionnaire Modal -->
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
                <h3 class="font-title-md text-title-md text-on-surface">{{ formMode === 'add' ? 'Buat Kuesioner Baru' : 'Edit Kuesioner' }}</h3>
                <p class="text-body-sm text-on-surface-variant">{{ formMode === 'add' ? 'Isi data untuk membuat kuesioner' : 'Ubah data kuesioner' }}</p>
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
            <!-- Nama Kuesioner -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Kuesioner</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Evaluasi Penggunaan Energi 2024"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Deskripsi</label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Deskripsi singkat tentang kuesioner ini..."
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
              ></textarea>
            </div>

            <!-- Periode & Status -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Periode</label>
                <div class="relative">
                  <select
                    v-model="form.period"
                    class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
                  >
                    <option value="">Pilih Periode</option>
                    <option v-for="period in periodOptions" :key="period" :value="period">{{ period }}</option>
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
                    <option value="terbit">Terbit</option>
                    <option value="arsip">Arsip</option>
                  </select>
                  <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-[20px]">expand_more</span>
                </div>
              </div>
            </div>

            <!-- Durasi & Aspek -->
            <div class="grid grid-cols-2 gap-4">
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
              <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Jumlah Aspek</label>
                <input
                  v-model.number="form.aspects"
                  type="number"
                  min="0"
                  placeholder="5"
                  class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
                />
              </div>
            </div>

            <!-- Jumlah Pertanyaan -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Jumlah Pertanyaan</label>
              <input
                v-model.number="form.questions"
                type="number"
                min="0"
                placeholder="32"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
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
              :disabled="!form.name || !form.description || !form.period"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ formMode === 'add' ? 'Simpan Kuesioner' : 'Simpan Perubahan' }}
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
                <span class="material-symbols-outlined text-error">{{ deletingQuestionnaire?.status === 'arsip' ? 'delete_forever' : 'delete' }}</span>
              </div>
              <h3 class="font-title-md text-title-md text-on-surface">{{ deletingQuestionnaire?.status === 'arsip' ? 'Hapus Permanen' : 'Hapus Kuesioner' }}</h3>
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
              Anda yakin ingin menghapus kuesioner berikut?
            </p>
            <div v-if="deletingQuestionnaire" class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/20">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="getStatusConfig(deletingQuestionnaire.status).iconBg">
                  <span class="material-symbols-outlined text-[20px]">{{ getStatusConfig(deletingQuestionnaire.status).icon }}</span>
                </div>
                <div>
                  <p class="font-body-base font-semibold text-on-surface">{{ deletingQuestionnaire.name }}</p>
                  <p class="text-body-sm text-on-surface-variant">{{ deletingQuestionnaire.period }} · {{ deletingQuestionnaire.questions }} pertanyaan</p>
                </div>
              </div>
            </div>
            <p v-if="deletingQuestionnaire?.status === 'arsip'" class="text-body-sm text-tertiary mt-3 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px]">warning</span>
              Kuesioner ini sudah diarsipkan. Hapus permanen berarti tidak bisa dikembalikan.
            </p>
            <p v-else class="text-body-sm text-error mt-3 flex items-center gap-1.5">
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
              {{ deletingQuestionnaire?.status === 'arsip' ? 'Ya, Hapus Permanen' : 'Ya, Hapus' }}
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

/* ===== GLASS CARD ===== */
.glass-card {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(229, 231, 235, 0.5);
  transition: all 0.3s ease;
}
.glass-card:hover {
  box-shadow: 0 8px 30px rgba(16, 185, 129, 0.1);
  border-color: #10b981;
}

/* ===== CONTENT CARD ===== */
.content-card {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.content-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 35px rgba(16, 185, 129, 0.12);
  border-color: #10b981;
}
.content-card:hover .content-icon {
  animation: contentIconPulse 0.5s ease;
}
@keyframes contentIconPulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.15) rotate(5deg); }
}

/* ===== INFO GRID ITEM ===== */
.info-grid-item {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  cursor: pointer;
}
.info-grid-item:hover {
  transform: translateY(-6px);
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.25);
}

/* ===== STATUS BADGES ===== */
.status-badge {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.status-badge:hover {
  transform: scale(1.15);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* ===== WEIGHT BADGE ===== */
.weight-badge {
  transition: all 0.3s ease;
}
.weight-badge:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* ===== TABLE BUTTONS ===== */
.table-btn {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  border-radius: 0.5rem;
}
.table-btn:hover {
  background-color: #f0fdf4;
  color: #006c49;
  transform: translateY(-2px) scale(1.1);
}
.table-btn:active {
  transform: scale(0.9);
}

/* ===== EDIT/DELETE BUTTONS ===== */
.edit-btn {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.edit-btn:hover {
  color: #006c49;
  transform: scale(1.2) rotate(5deg);
}
.edit-btn:active {
  transform: scale(0.9);
}
.delete-btn {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.delete-btn:hover {
  color: #ba1a1a;
  transform: scale(1.2) rotate(-5deg);
}
.delete-btn:active {
  transform: scale(0.9);
}

/* ===== BUTTONS ===== */
.btn-back {
  transition: all 0.4s ease;
  border-color: #6c7a71;
}
.btn-back:hover {
  background-color: #dde4dd;
  transform: translateY(-2px);
  border-color: #10b981;
}
.btn-back:active {
  transform: scale(0.96);
}

.btn-start {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.btn-start:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
  background-color: #006c49;
}
.btn-start:active:not(:disabled) {
  transform: scale(0.96);
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
  animation: fadeIn 0.6s ease-out forwards;
}
.fade-in-delay {
  animation: fadeIn 0.6s ease-out 0.15s forwards;
  opacity: 0;
}
.fade-in-delay-2 {
  animation: fadeIn 0.6s ease-out 0.3s forwards;
  opacity: 0;
}
.fade-in-delay-3 {
  animation: fadeIn 0.6s ease-out 0.45s forwards;
  opacity: 0;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
