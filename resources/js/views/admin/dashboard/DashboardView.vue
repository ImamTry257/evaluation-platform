<script setup lang="ts">
import { ref, onMounted } from 'vue'
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
    monitoringData.value = payload.activeSessions || []
    instrument.value = payload.instrument || null
    componentCharts.value = payload.componentCharts || []
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
      return 'bg-[#004592]/10 text-[#004592]'
    case 'Idle':
      return 'bg-amber-100 text-amber-700'
    case 'Selesai':
      return 'bg-[#004592] text-white'
    default:
      return 'bg-surface-container-highest text-outline'
  }
}

function getAvatarColor(id: number) {
  const colors = ['bg-emerald-100 text-emerald-700', 'bg-blue-100 text-blue-700', 'bg-purple-100 text-purple-700', 'bg-orange-100 text-orange-700']
  return colors[id % colors.length]
}

// ===== Instrument & Grafik skor per komponen (dari API /admin/dashboard) =====
const instrument = ref<{ id: number | null; title: string; status?: string } | null>(null)
const componentCharts = ref<
  { id: number; name: string; total: number; dist: { title: string; scoreTitle: string; count: number, countPrecentage: number, }[]; dominantTitle?: string; dominantCount?: number }[]
>([])

const TITLE_COLORS = ['#dc2626', '#f97316', '#f59e0b', '#10b981', '#004592']

function compTotal(dist: { count: number }[]) {
  return dist.reduce((a, d) => a + d.count, 0)
}

function compTop(dist: { count: number }[]) {
  const counts = dist.map((d) => d.count)
  return counts.indexOf(Math.max(...counts))
}

// Tinggi bar dalam % mengikuti sumbu y persentase (countPrecentage 0–100).
// Minimum 4% biar bucket kosong tetap terlihat sebagai stub tipis (opacity 0.25).
function barHeight(pct: number) {
  return Math.max(4, Math.min(100, pct))
}
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
      <button @click="fetchDashboard" class="mt-4 text-[#004592] text-body-sm font-semibold hover:underline">Coba Lagi</button>
    </div>

    <template v-else>
    <!-- Header -->
    <div class="mb-8 fade-in">
      <h2 class="header-title font-headline-xl font-bold text-headline-xl text-on-surface cursor-default">Monitoring Evaluasi</h2>
      <p class="text-on-surface-variant font-body-base">Pantau real-time perkembangan pengisian kuesioner kebijakan lingkungan sekolah.</p>
    </div>

    <!-- Instrument & Grafik Skor per Komponen -->
    <section class="mb-8">
      <div class="bg-white rounded-xl card-shadow p-6 mb-6 flex items-center gap-4 fade-in-delay">
        <span class="material-symbols-outlined text-[#004592] text-[34px]" style="font-variation-settings: 'FILL' 1;">assignment</span>
        <div>
          <p class="text-label-caps text-on-surface-variant uppercase mb-0.5">Instrument Penelitian</p>
          <h3 class="font-title-md text-title-md font-semibold">{{ instrument?.title || 'Belum ada instrument published' }}</h3>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6">
        <div v-if="componentCharts.length === 0" class="bg-white rounded-xl card-shadow p-12 text-center fade-in-delay">
          <span class="material-symbols-outlined text-[44px] text-outline/60">query_stats</span>
          <p class="text-body-base text-on-surface-variant mt-3 font-medium">Belum ada data grafik skor komponen</p>
          <p class="text-body-sm text-on-surface-variant mt-1">Grafik muncul setelah ada sesi yang disubmit pada instrumen ini</p>
        </div>

        <div
          v-for="(comp, i) in componentCharts"
          :key="comp.id"
          class="chart-card bg-white p-6 rounded-xl card-shadow fade-in-delay"
        >
          <div class="flex items-start justify-between mb-3 px-1">
            <div>
              <h3 class="font-title-md text-title-md font-semibold">{{ comp.name }}</h3>
              <p class="text-[11px] text-on-surface-variant mt-0.5">Component {{ i + 1 }} · {{ comp.total }} response</p>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] font-semibold">
              <span class="w-2.5 h-2.5 rounded-full" :style="{ background: TITLE_COLORS[compTop(comp.dist)] }"></span>
              Dominan: {{ comp.dominantTitle || '-' }}
            </div>
          </div>

          <div class="flex items-end gap-2 h-[260px] px-1">
            <div class="flex flex-col justify-between h-full mr-1 text-[10px] text-outline pr-1 text-right">
              <span>100%</span>
              <span>75%</span>
              <span>50%</span>
              <span>25%</span>
              <span>0%</span>
            </div>
            <div class="flex items-end gap-2 flex-1 h-full border-b border-l border-outline-variant/40">
              <div
                v-for="(bucket, j) in comp.dist"
                :key="bucket.scoreTitle"
                class="flex-1 flex flex-col items-center justify-end h-[220px]"
              >
                <div class="text-xs font-bold text-on-surface">{{ bucket.countPrecentage }}%</div>
                <div
                  class="w-full max-w-[46px] rounded-t-lg"
                  :style="{ height: barHeight(bucket.countPrecentage) + '%', background: TITLE_COLORS[j], opacity: bucket.count ? 1 : 0.25 }"
                ></div>
                <div class="text-[10px] text-on-surface-variant text-center mt-2 leading-tight whitespace-nowrap">{{ bucket.title }}</div>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2 mt-4 text-[11px] text-on-surface-variant px-1 pt-3 border-t border-outline-variant/10">
            <span class="material-symbols-outlined text-[14px]">analytics</span>
            <span>Kategori terbanyak: <b class="text-on-surface">{{ comp.dominantTitle || '-' }}</b> ({{ comp.dominantCount ?? 0 }} dari {{ comp.total }} response)</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Summary Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Responden -->
      <div class="summary-card bg-white p-6 rounded-xl card-shadow flex flex-col gap-2 fade-in">
        <div class="flex justify-between items-start">
          <span class="text-label-caps text-on-surface-variant uppercase">Total Responden</span>
          <div class="summary-icon p-2 bg-[#004592]/5 rounded-lg">
            <span class="material-symbols-outlined text-[#004592]">groups</span>
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
          <div class="summary-icon p-2 bg-[#004592]/10 rounded-lg">
            <span class="material-symbols-outlined text-[#004592]">check_circle</span>
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

    <!-- HIDDEN SEMENTARA: Tabel Monitoring Real-Time disembunyikan (ubah v-if="false" jadi true untuk menampilkan lagi) -->
    <div v-if="false" class="bg-white rounded-xl card-shadow overflow-hidden fade-in-delay-3">
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
                      class="bg-[#004592] h-full transition-all duration-400"
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
                  :class="'bg-[#004592]/10 text-[#004592]'"
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
  color: #004592;
  transform: translateY(-1px);
}

/* ===== MORE VERT BUTTON ===== */
.more-btn {
  transition: all 0.3s ease;
  border-radius: 50%;
  padding: 0.25rem;
}
.more-btn:hover {
  background-color: rgba(0, 69, 146, 0.1);
  color: #004592;
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
  border-color: #2f6fed;
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
  box-shadow: 0 0 0 2px rgba(0, 69, 146, 0.2);
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
  color: #004592;
}
</style>
