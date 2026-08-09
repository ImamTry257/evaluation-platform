<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import SearchableSelect from '@/components/SearchableSelect.vue'

// Loading state
const loading = ref(true)
const error = ref<string | null>(null)

// Loading state per select filter (pola sama dengan RegisterView)
const isLoadingTypes = ref(false)
const isLoadingRegencies = ref(false)

// Summary stats
const stats = ref({
  totalResponden: 0,
  selesai: 0,
  berjalan: 0,
  belumMulai: 0,
})

// Monitoring table
const monitoringData = ref<any[]>([])

// ===== Filter States =====
const selectedRespondentType = ref<string>('')
const selectedCityName = ref<string>('')
const allRespondentTypes = ref<any[]>([])
const allCityNames = ref<any[]>([])

// Computed properties for SearchableSelect options
const respondentTypeOptions = computed(() =>
  allRespondentTypes.value.map(type => ({
    value: type.title,
    label: type.title
  }))
)

const cityNameOptions = computed(() =>
  allCityNames.value.map(city => ({
    value: city.name,
    label: city.name
  }))
)

// ===== Instrument & Grafik skor per komponen (dari API /admin/dashboard) =====
const instrument = ref<{ id: number | null; title: string; status?: string } | null>(null)
const componentCharts = ref<
  { id: number; name: string; total: number; dist: { title: string; scoreTitle: string; count: number; countPrecentage: number }[]; dominantTitle?: string; dominantCount?: number }[]
>([])

const TITLE_COLORS = ['#dc2626', '#f97316', '#f59e0b', '#10b981', '#004592']

// Fetch dashboard data
async function fetchDashboard(filters?: { respondentType?: string; cityName?: string }) {
  loading.value = true
  error.value = null
  try {
    const params: Record<string, string> = {}

    if (filters?.respondentType) params.respondentType = filters.respondentType
    if (filters?.cityName) params.cityName = filters.cityName

    const { data } = await api.get('/admin/dashboard', { params })
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

// Fetch respondent types from API
async function fetchRespondentTypes() {
  isLoadingTypes.value = true
  try {
    const { data } = await api.get('/respondent-types')
    allRespondentTypes.value = data.data || []
  } catch (err) {
    console.error('Failed to fetch respondent types:', err)
  } finally {
    isLoadingTypes.value = false
  }
}

// Kabupaten/kota: regional lock DIY, konsisten dengan RegisterView
const REGENCY_PROVINCE_CODE = '34'

// Fetch regencies from API (users.city_name menyimpan nama kabupaten/kota)
async function fetchRegencies() {
  isLoadingRegencies.value = true
  try {
    const { data } = await api.get(`/locations/regencies/${REGENCY_PROVINCE_CODE}`)
    allCityNames.value = data.data || []
  } catch (err) {
    console.error('Failed to fetch regencies:', err)
  } finally {
    isLoadingRegencies.value = false
  }
}

// Apply filters
function applyFilters() {
  const params: { respondentType?: string; cityName?: string } = {}

  if (selectedRespondentType.value) {
    params.respondentType = selectedRespondentType.value
  }

  if (selectedCityName.value) {
    params.cityName = selectedCityName.value
  }

  fetchDashboard(params)
}

// Clear filters
function clearFilters() {
  selectedRespondentType.value = ''
  selectedCityName.value = ''
  fetchDashboard()
}

onMounted(() => {
  fetchDashboard()
  fetchRespondentTypes()
  fetchRegencies()
})

function compTotal(dist: { count: number }[]) {
  return dist.reduce((a, d) => a + d.count, 0)
}

function compTop(dist: { count: number }[]) {
  const counts = dist.map((d) => d.count)
  return counts.indexOf(Math.max(...counts))
}

// Tinggi bar dalam % mengikuti sumbu y persentase (countPrecentage 0-100).
// Minimum 4% biar bucket kosong tetap terlihat sebagai stub tipis (opacity 0.25).
function barHeight(pct: number) {
  return Math.max(4, Math.min(100, pct))
}

function getAvatarColor(id: number) {
  const colors = ['bg-emerald-100 text-emerald-700', 'bg-blue-100 text-blue-700', 'bg-purple-100 text-purple-700', 'bg-orange-100 text-orange-700']
  return colors[id % colors.length]
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
      <!-- relative z-20: kartu filter harus di atas kartu grafik (stacking context dari animasi fade-in),
           biar dropdown SearchableSelect (z-50 di dalam kartu ini) tidak ketutup kartu di bawahnya -->
      <div class="bg-white rounded-xl card-shadow p-6 mb-6 fade-in-delay relative z-20">
        <div class="flex flex-col md:flex-row md:items-center gap-6">
          <!-- Instrument Info -->
          <div class="flex items-center gap-4 flex-1">
            <span class="material-symbols-outlined text-[#004592] text-[34px]" style="font-variation-settings: 'FILL' 1;">assignment</span>
            <div>
              <p class="text-label-caps text-on-surface-variant uppercase mb-0.5">Instrument Penelitian</p>
              <h3 class="font-title-md text-title-md font-semibold">{{ instrument?.title || 'Belum ada instrument published' }}</h3>
            </div>
          </div>

          <!-- Filter Section -->
          <div class="flex flex-wrap gap-4 items-center">
            <!-- Respondent Type Filter -->
            <div class="w-full md:w-56">
              <label class="block text-[11px] text-on-surface-variant mb-1">Tipe Responden</label>
              <SearchableSelect
                v-model="selectedRespondentType"
                :options="respondentTypeOptions"
                placeholder="Semua Tipe"
                icon="groups"
                size="sm"
                :loading="isLoadingTypes"
              />
            </div>

            <!-- City Name Filter -->
            <div class="w-full md:w-56">
              <label class="block text-[11px] text-on-surface-variant mb-1">Kabupaten/Kota</label>
              <SearchableSelect
                v-model="selectedCityName"
                :options="cityNameOptions"
                placeholder="Semua Kabupaten/Kota"
                icon="location_city"
                size="sm"
                :loading="isLoadingRegencies"
              />
            </div>

            <!-- Apply Filter Button -->
            <div class="self-end">
              <button
                @click="applyFilters"
                :disabled="loading"
                class="px-4 py-2.5 bg-[#004592] text-white text-sm font-medium rounded-lg hover:bg-[#003577] transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <span class="material-symbols-outlined text-lg">filter_list</span>
                Terapkan Filter
              </button>
            </div>

            <!-- Clear Filter Button -->
            <div v-if="selectedRespondentType || selectedCityName" class="self-end">
              <button
                @click="clearFilters"
                :disabled="loading"
                class="px-4 py-2.5 border border-outline-variant text-on-surface text-sm font-medium rounded-lg hover:bg-surface-container-highest transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <span class="material-symbols-outlined text-lg">clear_all</span>
                Reset
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6">
        <div v-if="componentCharts.length === 0" class="bg-white rounded-xl card-shadow p-12 text-center fade-in-delay">
          <template v-if="instrument?.hasData">
            <span class="material-symbols-outlined text-[44px] text-[#004592]/50">filter_alt_off</span>
            <p class="text-body-base text-on-surface-variant mt-3 font-medium">Belum ada data untuk filter ini</p>
            <p class="text-body-sm text-on-surface-variant mt-1">Tidak ada responden yang cocok dengan filter terpilih</p>
            <button
              @click="clearFilters"
              class="mt-4 px-4 py-2 bg-[#004592] text-white text-sm font-medium rounded-lg hover:bg-[#003577] transition-colors duration-200"
            >
              Reset Filter
            </button>
          </template>
          <template v-else>
            <span class="material-symbols-outlined text-[44px] text-outline/60">query_stats</span>
            <p class="text-body-base text-on-surface-variant mt-3 font-medium">Belum ada data grafik skor komponen</p>
            <p class="text-body-sm text-on-surface-variant mt-1">Grafik muncul setelah ada sesi yang disubmit pada instrumen ini</p>
          </template>
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

    <!-- HIDDEN SEMENTARA: Tabel Monitoring Real-Time disembunyikan -->
    <div v-if="false" class="bg-white rounded-xl card-shadow overflow-hidden fade-in-delay-3">
      <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center">
        <h3 class="font-title-md text-title-md">Tabel Monitoring Real-Time</h3>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead class="bg-surface-container-low">
            <tr>
              <th class="px-6 py-4 text-label-caps text-on-surface-variant uppercase font-semibold">Responden</th>
              <th class="px-6 py-4 text-label-caps text-on-surface-variant uppercase font-semibold">Pertanyaan</th>
              <th class="px-6 py-4 text-label-caps text-on-surface-variant uppercase font-semibold">Progres</th>
              <th class="px-6 py-4 text-label-caps text-on-surface-variant uppercase font-semibold">Sisa Waktu</th>
              <th class="px-6 py-4 text-label-caps text-on-surface-variant uppercase font-semibold">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/30">
            <tr v-if="monitoringData.length === 0 && !loading">
              <td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">
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
            </tr>
          </tbody>
        </table>
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
