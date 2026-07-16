<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// State
const searchQuery = ref('')
const indicatorFilter = ref('')
const currentPage = ref(1)
const perPage = 10

// Delete modal
const showDeleteModal = ref(false)
const deletingQuestion = ref<any>(null)

// Indicator options
const indicatorOptions = [
  'Ketersediaan Tempat Sampah Terpisah',
  'Jadwal Pengangkutan Rutin',
  'Edukasi Civitas Akademika',
  'Kualitas Pemilahan di Sumber',
  'Integrasi ke Komposter',
  'Penggunaan Lampu LED',
  'Pemanfaatan Cahaya Alami',
  'Pengaturan Suhu AC',
  'Kualitas Air Bersih',
  'Kondisi Vegetasi Taman',
]

// Type options
const typeOptions = [
  { value: 'pilihan_ganda', label: 'Pilihan Ganda' },
  { value: 'ya_tidak', label: 'Ya/Tidak' },
  { value: 'isian_bebas', label: 'Isian Bebas' },
  { value: 'skala_likert', label: 'Skala Likert' },
]

// Mock data - Questions
const questions = ref([
  {
    id: 1,
    text: 'Apakah sekolah memiliki tempat sampah organik khusus di setiap kelas?',
    type: 'pilihan_ganda',
    weight: 25,
    indicator: 'Ketersediaan Tempat Sampah Terpisah',
    status: 'aktif',
  },
  {
    id: 2,
    text: 'Berapakah jumlah tempat sampah komunal yang tersedia di area kantin?',
    type: 'pilihan_ganda',
    weight: 35,
    indicator: 'Ketersediaan Tempat Sampah Terpisah',
    status: 'aktif',
  },
  {
    id: 3,
    text: 'Apakah terdapat petunjuk visual (poster) pemilahan sampah pada setiap unit tempat sampah?',
    type: 'ya_tidak',
    weight: 20,
    indicator: 'Ketersediaan Tempat Sampah Terpisah',
    status: 'aktif',
  },
  {
    id: 4,
    text: 'Kapan terakhir kali dilakukan evaluasi terhadap kebersihan tempat sampah organik?',
    type: 'isian_bebas',
    weight: 20,
    indicator: 'Ketersediaan Tempat Sampah Terpisah',
    status: 'draft',
  },
  {
    id: 5,
    text: 'Seberapa sering jadwal pengangkutan sampah organik dilaksanakan dalam seminggu?',
    type: 'pilihan_ganda',
    weight: 30,
    indicator: 'Jadwal Pengangkutan Rutin',
    status: 'aktif',
  },
  {
    id: 6,
    text: 'Apakah petugas kebersihan telah mendapat pelatihan pemilahan sampah organik?',
    type: 'ya_tidak',
    weight: 25,
    indicator: 'Edukasi Civitas Akademika',
    status: 'aktif',
  },
  {
    id: 7,
    text: 'Seberapa efektif kampanye pemilahan sampah yang telah dilakukan di sekolah?',
    type: 'skala_likert',
    weight: 20,
    indicator: 'Edukasi Civitas Akademika',
    status: 'draft',
  },
  {
    id: 8,
    text: 'Apakah semua lampu di ruang kelas sudah menggunakan teknologi LED?',
    type: 'ya_tidak',
    weight: 30,
    indicator: 'Penggunaan Lampu LED',
    status: 'aktif',
  },
  {
    id: 9,
    text: 'Berapa persen penggunaan cahaya alami di ruang kelas pada jam pelajaran?',
    type: 'pilihan_ganda',
    weight: 20,
    indicator: 'Pemanfaatan Cahaya Alami',
    status: 'aktif',
  },
  {
    id: 10,
    text: 'Apakah suhu AC diatur pada rentang 24-26°C sesuai standar?',
    type: 'ya_tidak',
    weight: 25,
    indicator: 'Pengaturan Suhu AC',
    status: 'draft',
  },
  {
    id: 11,
    text: 'Bagaimana kondisi kesehatan vegetasi di area taman sekolah?',
    type: 'skala_likert',
    weight: 15,
    indicator: 'Kondisi Vegetasi Taman',
    status: 'aktif',
  },
  {
    id: 12,
    text: 'Apakah sekolah memiliki program penghematan air harian yang terstruktur?',
    type: 'ya_tidak',
    weight: 20,
    indicator: 'Penghematan Air Harian',
    status: 'aktif',
  },
])

// Computed
const filteredQuestions = computed(() => {
  return questions.value.filter((q) => {
    const matchSearch = q.text.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchIndicator = !indicatorFilter.value || q.indicator === indicatorFilter.value
    return matchSearch && matchIndicator
  })
})

const totalPages = computed(() => Math.ceil(filteredQuestions.value.length / perPage))

const paginatedQuestions = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredQuestions.value.slice(start, start + perPage)
})

const showingFrom = computed(() => (currentPage.value - 1) * perPage + 1)
const showingTo = computed(() => Math.min(currentPage.value * perPage, filteredQuestions.value.length))

// Methods
function getStatusClass(status: string) {
  if (status === 'aktif') return 'bg-primary/10 text-primary'
  if (status === 'draft') return 'bg-secondary/10 text-secondary'
  return 'bg-outline-variant/30 text-on-surface-variant'
}

function getTypeLabel(type: string) {
  return typeOptions.find((t) => t.value === type)?.label || type
}

function goToEdit(id: number) {
  router.push(`/admin/question/${id}/edit`)
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
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <!-- Header Section -->
    <section class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 fade-in">
      <div>
        <h2 class="font-headline-xl text-headline-xl text-on-surface">Manajemen Pertanyaan</h2>
        <p class="font-body-base text-body-base text-on-surface-variant mt-2 max-w-2xl">
          Kelola pertanyaan evaluasi kebijakan lingkungan sekolah secara sistematis untuk memantau progres keberlanjutan instansi.
        </p>
      </div>
      <div>
        <router-link
          to="/admin/question/add"
          class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95 inline-flex"
        >
          <span class="material-symbols-outlined">add</span>
          Tambah Pertanyaan
        </router-link>
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
            placeholder="Cari pertanyaan..."
            type="text"
          />
        </div>
        <div class="flex items-center gap-3">
          <div class="relative min-w-[220px]">
            <select
              v-model="indicatorFilter"
              class="custom-select w-full bg-white border border-outline-variant/50 rounded-xl px-4 py-2.5 appearance-none focus:ring-2 focus:ring-primary-container outline-none text-body-sm font-body-sm cursor-pointer"
            >
              <option value="">Semua Indikator</option>
              <option v-for="ind in indicatorOptions" :key="ind" :value="ind">{{ ind }}</option>
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
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase w-16">No</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Pertanyaan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Bobot</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
              v-for="(q, index) in paginatedQuestions"
              :key="q.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
            >
              <td class="px-6 py-5 text-sm font-medium text-secondary">{{ String(index + showingFrom).padStart(2, '0') }}</td>
              <td class="px-6 py-5">
                <p class="text-sm font-semibold text-on-surface line-clamp-1">{{ q.text }}</p>
                <p class="text-[12px] text-on-surface-variant mt-0.5">Tipe: {{ getTypeLabel(q.type) }}</p>
              </td>
              <td class="px-6 py-5">
                <span class="weight-badge px-2.5 py-1 bg-primary-container/10 text-primary font-bold text-xs rounded-full border border-primary/20">{{ q.weight }}%</span>
              </td>
              <td class="px-6 py-5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="getStatusClass(q.status)">
                  {{ q.status === 'aktif' ? 'Aktif' : 'Draft' }}
                </span>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-2">
                  <button class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors" title="Lihat Detail">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                  </button>
                  <button
                    @click="goToEdit(q.id)"
                    class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors"
                    title="Edit"
                  >
                    <span class="material-symbols-outlined text-[18px]">edit_note</span>
                  </button>
                  <button
                    @click="openDeleteModal(q)"
                    class="p-2 text-error hover:bg-error-container/20 rounded-lg transition-colors"
                    title="Hapus"
                  >
                    <span class="material-symbols-outlined text-[18px]">delete_outline</span>
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
                <span class="material-symbols-outlined text-error">delete_outline</span>
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
                <p class="text-body-sm text-on-surface-variant mt-1">{{ deletingQuestion.indicator }} · {{ deletingQuestion.weight }}% bobot</p>
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
.modal-content {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
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
