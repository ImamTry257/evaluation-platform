<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

// Loading state
const loading = ref(true)
const error = ref<string | null>(null)

// Summary stats
const stats = ref({
  totalResponden: 0,
  selesai: 0,
  berjalan: 0,
  belumMulai: 0,
})

// Completion percentage
const completionPercent = computed(() => {
  if (stats.value.totalResponden === 0) return 0
  return Math.round((stats.value.selesai / stats.value.totalResponden) * 100)
})

// Weekly progress data
const weeklyData = ref<{ day: string; date: string; value: number }[]>([])

const maxWeeklyValue = computed(() => Math.max(...weeklyData.value.map((d) => d.value)))

// Monitoring table
const monitoringData = ref<any[]>([])

// Fetch dashboard data
async function fetchDashboard() {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get('/admin/dashboard')
    const payload = data.data
    stats.value = {
      totalResponden: payload.summary.totalRespondent,
      selesai: payload.summary.submitted,
      berjalan: payload.summary.inProgress,
      belumMulai: payload.summary.notStarted,
    }
    weeklyData.value = payload.weeklyProgress || []
    monitoringData.value = payload.activeSessions || []
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal memuat data dashboard'
    console.error('Dashboard fetch error:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboard()
})

function getProgressPercent(current: number, total: number) {
  return Math.round((current / total) * 100)
}

function getStatusClass(status: string) {
  switch (status) {
    case 'Aktif':
      return 'bg-primary/10 text-primary'
    case 'Idle':
      return 'bg-amber-100 text-amber-700'
    case 'Selesai':
      return 'bg-primary text-white'
    default:
      return 'bg-surface-container-highest text-outline'
  }
}

function getAvatarColor(id: number) {
  const colors = ['bg-emerald-100 text-emerald-700', 'bg-blue-100 text-blue-700', 'bg-purple-100 text-purple-700', 'bg-orange-100 text-orange-700']
  return colors[id % colors.length]
}

// Chart period
const chartPeriod = ref('7 Hari Terakhir')
</script>

<template>
  <div class="p-12 max-w-[1840px] w-full mx-auto">
    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <span class="material-symbols-outlined text-[48px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-base text-on-surface-variant mt-4">Memuat data dashboard...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
      <span class="material-symbols-outlined text-[48px] text-error">error</span>
      <p class="text-body-base text-error mt-4">{{ error }}</p>
      <button @click="fetchDashboard" class="mt-4 text-primary text-body-sm font-semibold hover:underline">Coba Lagi</button>
    </div>

    <template v-else>
    <!-- Header -->
    <div class="mb-8 fade-in">
      <h2 class="header-title font-headline-xl font-bold text-headline-xl text-on-surface cursor-default">Monitoring Evaluasi</h2>
      <p class="text-on-surface-variant font-body-base">Pantau real-time perkembangan pengisian kuesioner kebijakan lingkungan sekolah.</p>
    </div>

    <!-- Summary Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Responden -->
      <div class="summary-card bg-white p-6 rounded-xl card-shadow flex flex-col gap-2 fade-in">
        <div class="flex justify-between items-start">
          <span class="text-label-caps text-on-surface-variant uppercase">Total Responden</span>
          <div class="summary-icon p-2 bg-primary/5 rounded-lg">
            <span class="material-symbols-outlined text-primary">groups</span>
          </div>
        </div>
        <div class="mt-2">
          <span class="text-headline-xl font-bold">{{ stats.totalResponden.toLocaleString() }}</span>
          <span class="text-xs text-on-surface-variant ml-2">dari total responden</span>
        </div>
      </div>

      <!-- Evaluasi Selesai -->
      <div class="summary-card bg-white p-6 rounded-xl card-shadow flex flex-col gap-2 fade-in-delay">
        <div class="flex justify-between items-start">
          <span class="text-label-caps text-on-surface-variant uppercase">Evaluasi Selesai</span>
          <div class="summary-icon p-2 bg-primary-container/10 rounded-lg">
            <span class="material-symbols-outlined text-primary">check_circle</span>
          </div>
        </div>
        <div class="mt-2">
          <span class="text-headline-xl font-bold">{{ stats.selesai.toLocaleString() }}</span>
          <span class="text-xs text-on-surface-variant ml-2">{{ Math.round((stats.selesai / stats.totalResponden) * 100) }}% dari total</span>
        </div>
      </div>

      <!-- Sedang Berjalan -->
      <div class="summary-card bg-white p-6 rounded-xl card-shadow flex flex-col gap-2 fade-in-delay-2">
        <div class="flex justify-between items-start">
          <span class="text-label-caps text-on-surface-variant uppercase">Sedang Berjalan</span>
          <div class="summary-icon p-2 bg-amber-100 rounded-lg">
            <span class="material-symbols-outlined text-amber-600">pending</span>
          </div>
        </div>
        <div class="mt-2">
          <span class="text-headline-xl font-bold">{{ stats.berjalan.toLocaleString() }}</span>
          <span class="text-xs text-on-surface-variant ml-2">{{ Math.round((stats.berjalan / stats.totalResponden) * 100) }}% dari total</span>
        </div>
      </div>

      <!-- Belum Memulai -->
      <div class="summary-card bg-white p-6 rounded-xl card-shadow flex flex-col gap-2 fade-in-delay-3">
        <div class="flex justify-between items-start">
          <span class="text-label-caps text-on-surface-variant uppercase">Belum Memulai</span>
          <div class="summary-icon p-2 bg-surface-container-highest rounded-lg">
            <span class="material-symbols-outlined text-outline">history</span>
          </div>
        </div>
        <div class="mt-2">
          <span class="text-headline-xl font-bold">{{ stats.belumMulai.toLocaleString() }}</span>
          <span class="text-xs text-on-surface-variant ml-2">{{ Math.round((stats.belumMulai / stats.totalResponden) * 100) }}% dari total</span>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
      <!-- Persentase Penyelesaian -->
      <div class="chart-card lg:col-span-4 bg-white p-6 rounded-xl card-shadow fade-in-delay">
        <h3 class="font-title-md text-title-md mb-6">Persentase Penyelesaian</h3>
        <div class="relative h-64 flex items-center justify-center">
          <svg class="circular-chart w-48 h-48 transform -rotate-90" viewBox="0 0 36 36">
            <path class="text-surface-container-highest" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3"></path>
            <path class="text-primary" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" :stroke-dasharray="`${completionPercent}, 100`" stroke-linecap="round" stroke-width="3"></path>
          </svg>
          <div class="absolute flex flex-col items-center">
            <span class="text-display-lg font-bold">{{ completionPercent }}%</span>
            <span class="text-label-caps text-on-surface-variant">Global Progress</span>
          </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-4 justify-center">
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-primary"></div>
            <span class="text-body-sm text-on-surface-variant">Selesai</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-surface-container-highest"></div>
            <span class="text-body-sm text-on-surface-variant">Tertunda</span>
          </div>
        </div>
      </div>

      <!-- Progres Responden Mingguan -->
      <div class="chart-card lg:col-span-8 bg-white p-6 rounded-xl card-shadow fade-in-delay-2">
        <div class="flex justify-between items-center mb-6">
          <h3 class="font-title-md text-title-md">Progres Responden Mingguan</h3>
          <select v-model="chartPeriod" class="custom-select bg-surface-container-low border-none rounded-lg text-body-sm px-3 py-1.5">
            <option>7 Hari Terakhir</option>
            <option>30 Hari Terakhir</option>
          </select>
        </div>
        <div class="h-64 flex items-end justify-between gap-2 px-2">
          <div
            v-for="item in weeklyData"
            :key="item.day"
            class="bar-item flex flex-col items-center flex-1 gap-2"
          >
            <div
              class="bar-fill w-full bg-primary/20 rounded-t-lg"
              :style="{ height: (item.value / maxWeeklyValue) * 100 + '%' }"
            ></div>
            <span class="text-[10px] text-on-surface-variant">{{ item.day }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Monitoring Table -->
    <div class="bg-white rounded-xl card-shadow overflow-hidden fade-in-delay-3">
      <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center">
        <h3 class="font-title-md text-title-md">Tabel Monitoring Real-Time</h3>
        <div class="flex gap-2">
          <button class="action-btn flex items-center gap-2 px-3 py-1.5 border border-outline-variant rounded-lg text-body-sm">
            <span class="material-symbols-outlined text-[18px]">filter_list</span>
            Filter
          </button>
          <button class="action-btn flex items-center gap-2 px-3 py-1.5 border border-outline-variant rounded-lg text-body-sm">
            <span class="material-symbols-outlined text-[18px]">download</span>
            Export
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead class="bg-surface-container-low">
            <tr>
              <th class="px-6 py-4 text-label-caps text-on-surface-variant uppercase font-semibold">Responden</th>
              <th class="px-6 py-4 text-label-caps text-on-surface-variant uppercase font-semibold">Sekolah</th>
              <th class="px-6 py-4 text-label-caps text-on-surface-variant uppercase font-semibold">Pertanyaan</th>
              <th class="px-6 py-4 text-label-caps text-on-surface-variant uppercase font-semibold">Progres</th>
              <th class="px-6 py-4 text-label-caps text-on-surface-variant uppercase font-semibold">Sisa Waktu</th>
              <th class="px-6 py-4 text-label-caps text-on-surface-variant uppercase font-semibold">Status</th>
              <th class="px-6 py-4"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30">
            <tr v-if="monitoringData.length === 0 && !loading">
              <td colspan="7" class="px-6 py-8 text-center text-on-surface-variant">
                Tidak ada sesi aktif saat ini
              </td>
            </tr>
            <tr
              v-for="row in monitoringData"
              :key="row.id"
              class="table-row"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold" :class="getAvatarColor(row.id)">
                    {{ row.userName?.split(' ').map((w: string) => w[0]).join('').substring(0, 2).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-body-sm text-body-sm font-medium">{{ row.userName }}</p>
                    <p class="text-[11px] text-on-surface-variant">{{ row.questionnaireTitle }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 font-body-sm text-body-sm">{{ row.questionnaireTitle }}</td>
              <td class="px-6 py-4 font-body-sm text-body-sm">Q{{ String(row.answeredCount).padStart(2, '0') }} / {{ row.totalQuestions }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex-1 bg-surface-container-highest h-2 rounded-full overflow-hidden min-w-[80px]">
                    <div
                      class="bg-primary h-full transition-all duration-400"
                      :style="{ width: row.progress + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs font-semibold">{{ row.progress }}%</span>
                </div>
              </td>
              <td class="px-6 py-4 font-body-sm text-body-sm">{{ row.timeRemaining }}</td>
              <td class="px-6 py-4">
                <span
                  class="px-2 py-1 text-[10px] font-bold rounded-full uppercase tracking-tighter"
                  :class="'bg-primary/10 text-primary'"
                >
                  Aktif
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <button class="more-btn text-on-surface-variant">
                  <span class="material-symbols-outlined">more_vert</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant flex items-center justify-between">
        <p class="text-body-sm text-on-surface-variant">Menampilkan {{ monitoringData.length }} dari {{ stats.totalResponden.toLocaleString() }} responden</p>
        <div class="flex gap-2">
          <button class="page-btn px-3 py-1 border border-outline-variant rounded-md text-xs font-medium disabled:opacity-50" disabled>Sebelumnya</button>
          <button class="page-btn px-3 py-1 border border-outline-variant rounded-md text-xs font-medium">Selanjutnya</button>
        </div>
      </div>
    </div>
    </template>
  </div>
</template>

<style scoped>
/* ===== CARD SHADOW ===== */
.card-shadow {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
}

/* ===== SUMMARY CARD ===== */
.summary-card {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  cursor: pointer;
}
.summary-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}
.summary-card:hover .summary-icon {
  animation: iconBounce 0.4s ease;
}
@keyframes iconBounce {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.2); }
}
.summary-icon {
  transition: all 0.3s ease;
}

/* ===== CHART CARD ===== */
.chart-card {
  transition: all 0.3s ease;
}
.chart-card:hover {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

/* ===== BAR CHART ===== */
.bar-item {
  transition: all 0.3s ease;
  cursor: pointer;
}
.bar-item:hover {
  transform: scaleY(1.05);
  transform-origin: bottom;
}
.bar-item:hover .bar-fill {
  filter: brightness(1.1);
}
.bar-fill {
  transition: all 0.4s ease;
}

/* ===== CIRCULAR CHART ===== */
.circular-chart {
  transition: all 0.3s ease;
}
.circular-chart:hover {
  transform: scale(1.05) rotate(-90deg);
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
  padding: 0.375rem 0.75rem;
}
.table-btn:hover {
  background-color: #eef6ee;
  color: #006c49;
  transform: translateY(-1px);
}

/* ===== MORE VERT BUTTON ===== */
.more-btn {
  transition: all 0.3s ease;
  border-radius: 50%;
  padding: 0.25rem;
}
.more-btn:hover {
  background-color: rgba(16, 185, 129, 0.1);
  color: #006c49;
  transform: rotate(90deg);
}

/* ===== PAGINATION ===== */
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
  box-shadow: 0 0 0 2px rgba(0, 108, 73, 0.2);
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
.fade-in-delay-3 {
  animation: fadeIn 0.5s ease-out 0.3s forwards;
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

/* ===== HEADER TITLE ===== */
.header-title {
  transition: all 0.3s ease;
}
.header-title:hover {
  color: #006c49;
}
</style>
