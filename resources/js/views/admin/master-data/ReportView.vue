<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useReport } from '@/hooks/useReport'

const {
  reports,
  loading,
  error,
  currentPage,
  perPage,
  totalItems,
  totalPages,
  searchQuery,
  stats,
  fetchReports,
  exportExcel,
  exportPdf,
  onSearch,
} = useReport()

// Modal state
const showViewModal = ref(false)
const viewingReport = ref<any>(null)

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
     hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  })
}

function getCategoryBadge(category: string) {
  switch (category) {
    case 'A':
      return 'bg-emerald-100 text-emerald-700'
    case 'B':
      return 'bg-blue-100 text-blue-700'
    case 'C':
      return 'bg-yellow-100 text-yellow-700'
    case 'D':
      return 'bg-orange-100 text-orange-700'
    case 'E':
      return 'bg-red-100 text-red-700'
    default:
      return 'bg-gray-100 text-gray-600'
  }
}

function getStatusBadge(status: string) {
  switch (status) {
    case 'SUBMITTED':
      return 'bg-primary/10 text-primary font-semibold'
    case 'IN PROGRESS':
      return 'bg-amber-100 text-amber-700 font-semibold'
    default:
      return 'bg-gray-100 text-gray-600 font-semibold'
  }
}

// View modal
function openViewModal(item: any) {
  viewingReport.value = item
  showViewModal.value = true
}

// Pagination
function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  fetchReports(page)
}

// Export handlers
async function handleExportExcel() {
  await exportExcel()
}

async function handleExportPdf(sessionId: number) {
  await exportPdf(sessionId)
}

async function handleExportExcelSession(sessionId: number) {
  await exportExcel({ sessionId })
}

// Init
onMounted(() => {
  fetchReports()
})
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center gap-2 text-sm">
      <span class="text-on-surface font-semibold">Reports</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 fade-in">
      <div>
        <h2 class="font-headline-xl text-headline-xl text-on-surface">Laporan Pengisian Angket</h2>
      </div>
      <div class="flex items-center gap-3">
        <button
          @click="handleExportExcel"
          class="bg-white border border-outline-variant text-on-surface px-4 py-2.5 rounded-xl flex items-center gap-2 hover:bg-surface-container transition-colors"
        >
          <span class="material-symbols-outlined text-[18px]">download</span>
          Export Excel
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 fade-in-delay">
      <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center gap-4">
        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
          <span class="material-symbols-outlined">assessment</span>
        </div>
        <div>
          <p class="text-xs font-bold text-secondary uppercase tracking-wider">Total Evaluasi</p>
          <p class="text-2xl font-bold text-on-surface">{{ stats.totalSessions }}</p>
        </div>
      </div>
      <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center gap-4">
        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-700">
          <span class="material-symbols-outlined">groups</span>
        </div>
        <div>
          <p class="text-xs font-bold text-secondary uppercase tracking-wider">Responden</p>
          <p class="text-2xl font-bold text-on-surface">{{ stats.totalRespondents }}</p>
        </div>
      </div>
      <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-700">
          <span class="material-symbols-outlined">trending_up</span>
        </div>
        <div>
          <p class="text-xs font-bold text-secondary uppercase tracking-wider">Rata-rata Skor</p>
          <p class="text-2xl font-bold text-on-surface">{{ stats.averagePercentage?.toFixed(1) || '0' }}%</p>
        </div>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="mb-4 flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
      <span class="material-symbols-outlined text-[16px]">error</span>
      <span>{{ error }}</span>
    </div>

    <!-- Content Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay-2">
      <!-- Search Bar -->
      <div class="p-6 border-b border-outline-variant/10 bg-surface-container-low/30">
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
          <input
            :value="searchQuery"
            @input="onSearch(($event.target as HTMLInputElement).value)"
            class="search-input w-full bg-white border border-outline-variant/50 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary-container outline-none transition-all text-body-sm font-body-sm"
            placeholder="Cari berdasarkan nama responden..."
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
      <div v-else-if="reports.length === 0" class="p-12 text-center">
        <span class="material-symbols-outlined text-[48px] text-outline">assessment</span>
        <p class="text-body-base text-on-surface-variant mt-3">Tidak ada data laporan ditemukan.</p>
      </div>

      <!-- Data Table -->
      <table v-else class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low/50">
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Responden</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Instrument</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Pengisian Ke</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Presentase</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Tanggal Mulai isi Angket</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Tanggal Penyelesaian Angket</th>
            <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/10">
          <tr
            v-for="item in reports"
            :key="item.id"
            class="table-row hover:bg-surface-container-low/30 transition-colors"
          >
            <td class="px-6 py-5">
              <div class="flex items-center gap-3">
                <div>
                  <span class="font-body-base font-semibold text-on-surface">{{ item.respondent || '-' }}</span>
                </div>
              </div>
            </td>
            <td class="px-6 py-5 text-body-sm text-on-surface">{{ item.questionnaire || '-' }}</td>
            <td class="px-6 py-5 text-body-sm text-on-surface">
              {{ item.submissionOrder || '-' }}</td>
            <td class="px-6 py-5">
              <span class="font-title-md font-semibold text-primary">{{ ( item.percentage != null ? `${item.percentage}%` : '' ) || '' }}</span>
            </td>
            <td class="px-6 py-5 text-body-sm text-on-surface">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" :class="getStatusBadge(item.status)">
                {{ item.status || '-' }}
              </span>
            </td>
            <!-- <td class="px-6 py-5">
              <span class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" :class="getCategoryBadge(item.category)">
                {{ item.category || '-' }}
              </span>
            </td> -->
            <td class="px-6 py-5 text-body-sm text-on-surface">{{ formatDate(item.startedAt) }}</td>
            <td class="px-6 py-5 text-body-sm text-on-surface">{{ formatDate(item.submittedAt) }}</td>
            <td class="px-6 py-5">
              <div class="flex items-center justify-center gap-1">
                <button @click="openViewModal(item)" class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all" title="Lihat Detail">
                  <span class="material-symbols-outlined text-[18px]">visibility</span>
                </button>
                <!-- <button @click="handleExportPdf(item.id)" class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all" title="Export PDF">
                  <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                </button> -->
                <button @click="handleExportExcelSession(item.id)" class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all" title="Export Excel">
                  <span class="material-symbols-outlined text-[18px]">table_chart</span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination Footer -->
      <div v-if="reports.length > 0" class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-body-sm font-body-sm text-on-surface-variant">
          Menampilkan <span class="font-semibold text-on-surface">{{ showingFrom }}-{{ showingTo }}</span> dari <span class="font-semibold text-on-surface">{{ totalItems }}</span> laporan
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
                <span class="material-symbols-outlined text-primary">assessment</span>
              </div>
              <div>
                <h3 class="font-title-md text-title-md text-on-surface">Detail Laporan</h3>
                <p class="text-body-sm text-on-surface-variant">Hasil evaluasi responden</p>
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
          <div class="p-6 space-y-5" v-if="viewingReport">
            <!-- Responden Info -->
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-lg">
                {{ viewingReport.respondent?.charAt(0) || 'U' }}
              </div>
              <div>
                <h4 class="font-title-md text-title-md text-on-surface">{{ viewingReport.respondent || '-' }}</h4>
                <p class="text-body-sm text-on-surface-variant">{{ viewingReport.questionnaire || '-' }}</p>
              </div>
            </div>

            <!-- Persentase -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Persentase</label>
              <span class="block bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface">
                {{ viewingReport.percentage || '-' }}%
              </span>
            </div>

            <!-- Tanggal -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Tanggal Isi Angket</label>
              <span class="block bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base text-on-surface">
                {{ formatDate(viewingReport.submittedAt) }}
              </span>
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
