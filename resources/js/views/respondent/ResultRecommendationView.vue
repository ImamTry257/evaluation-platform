<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const sessionId = route.params.sessionId as string

// State
const loading = ref(true)
const resultData = ref<any>(null)

// Mock data for now (will connect to API later)
const result = ref({
  score: 84,
  category: 'A',
  categoryLabel: 'Sangat Baik',
  conclusion: 'Secara umum implementasi kebijakan lingkungan di sekolah Anda telah berjalan dengan sangat baik. Fokus utama untuk pengembangan selanjutnya adalah pada aspek Edukasi dan Partisipasi Siswa untuk memastikan keberlanjutan program secara holistik.',
  mainRecommendation: 'Tingkatkan integrasi kurikulum hijau pada semester depan.',
  aspects: [
    { name: 'Kebijakan Struktural', score: 85 },
    { name: 'Program Utama', score: 72 },
    { name: 'Operasional', score: 80 },
    { name: 'Edukasi', score: 68 },
    { name: 'Evaluasi & Inovasi', score: 88 },
  ],
})

// Computed
const overallPercentage = computed(() => result.value.score)

// Progress ring
const circumference = 2 * Math.PI * 80 // radius = 80
const strokeDashoffset = computed(() => {
  return circumference - (overallPercentage.value / 100) * circumference
})

function getScoreColor(score: number) {
  if (score >= 75) return 'text-primary'
  if (score >= 60) return 'text-tertiary'
  return 'text-error'
}

function getScoreBarColor(score: number) {
  if (score >= 75) return 'bg-primary'
  if (score >= 60) return 'bg-tertiary'
  return 'bg-error'
}

function getCategoryColor(category: string) {
  const colors: Record<string, string> = {
    A: 'bg-primary-container/10 text-on-primary-container border-primary-container/20',
    B: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    C: 'bg-orange-100 text-orange-800 border-orange-200',
    D: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    E: 'bg-red-100 text-red-800 border-red-200',
  }
  return colors[category] || colors.C
}

// Fetch results
async function fetchResults() {
  loading.value = true
  try {
    // TODO: connect to real API
    // const { data } = await api.get(`/evaluations/${sessionId}/results`)
    // result.value = data.data
  } catch (err) {
    console.error('Failed to fetch results:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchResults()
})
</script>

<template>
  <div class="p-8 max-w-[1024px] w-full mx-auto">
    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <span class="material-symbols-outlined text-[48px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-base text-on-surface-variant mt-4">Memuat hasil evaluasi...</p>
    </div>

    <template v-else>
      <!-- Success Header -->
      <section class="flex flex-col items-center text-center mb-12 fade-in">
        <div class="hero-icon w-64 h-64 mb-6 float-animation relative">
          <div class="w-full h-full bg-gradient-to-br from-primary/10 to-primary-container/20 rounded-full flex items-center justify-center">
            <span class="material-symbols-outlined text-primary" style="font-size: 100px;">eco</span>
          </div>
          <div class="check-icon absolute -bottom-2 -right-2 bg-primary text-on-primary rounded-full p-4 shadow-lg flex items-center justify-center cursor-pointer">
            <span class="material-symbols-outlined text-[40px]">check_circle</span>
          </div>
        </div>
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-2 fade-in-delay">Evaluasi Berhasil Diselesaikan</h1>
        <p class="font-body-base text-body-base text-secondary max-w-lg fade-in-delay">
          Terima kasih telah mengisi instrumen evaluasi. Data Anda telah berhasil tersimpan dalam sistem.
        </p>
      </section>

      <!-- Main Summary Grid -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-12">
        <!-- Score Card -->
        <div class="score-card md:col-span-12 lg:col-span-5 bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant flex flex-col items-center justify-center text-center fade-in-delay-2">
          <div class="relative flex items-center justify-center mb-6">
            <svg class="w-48 h-48">
              <circle class="text-secondary-container" cx="96" cy="96" fill="transparent" r="80" stroke="currentColor" stroke-width="12"></circle>
              <circle
                class="text-primary progress-ring-circle"
                cx="96" cy="96" fill="transparent" r="80"
                stroke="currentColor"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="strokeDashoffset"
                stroke-linecap="round"
                stroke-width="12"
              ></circle>
            </svg>
            <div class="absolute flex flex-col items-center">
              <span class="font-display-lg text-display-lg text-primary leading-none">{{ overallPercentage }}</span>
              <span class="font-label-caps text-label-caps text-secondary uppercase tracking-widest">Persentase {{ overallPercentage }}%</span>
            </div>
          </div>
          <div
            class="score-badge px-6 py-2 rounded-full font-title-md text-title-md inline-flex items-center gap-2 border cursor-default"
            :class="getCategoryColor(result.category)"
          >
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">stars</span>
            {{ result.categoryLabel }}
          </div>
        </div>

        <!-- Conclusion Card -->
        <div class="conclusion-card md:col-span-12 lg:col-span-7 bg-surface-container-low p-8 rounded-xl shadow-sm border border-outline-variant flex flex-col justify-center fade-in-delay-2">
          <h3 class="font-headline-lg text-headline-lg text-on-surface mb-6 flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">analytics</span>
            Kesimpulan Evaluasi
          </h3>
          <p class="font-body-base text-body-base text-on-surface-variant leading-relaxed">
            {{ result.conclusion }}
          </p>
          <div class="recommendation-box mt-6 flex items-center gap-4 p-4 bg-white rounded-lg border border-outline-variant/30 cursor-default">
            <div class="h-10 w-10 rounded-full bg-tertiary/10 flex items-center justify-center text-tertiary">
              <span class="material-symbols-outlined">lightbulb</span>
            </div>
            <div>
              <p class="text-label-caps font-label-caps text-secondary uppercase">Rekomendasi Utama</p>
              <p class="text-body-sm font-body-sm text-on-surface">{{ result.mainRecommendation }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Breakdown Table -->
      <section class="mb-12 fade-in-delay-3">
        <h2 class="font-title-md text-title-md text-on-surface flex items-center gap-2 mb-6">
          <span class="material-symbols-outlined text-primary">segment</span>
          Detail Capaian Aspek
        </h2>

        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/50 overflow-hidden">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low border-b border-outline-variant">
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">No</th>
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama Aspek</th>
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Skor</th>
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Persentase</th>
                <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Capaian</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/30">
              <tr
                v-for="(aspect, index) in result.aspects"
                :key="aspect.name"
                class="hover:bg-surface-container-lowest/80 transition-colors"
              >
                <td class="px-6 py-4 text-body-sm text-on-surface font-body-sm">{{ index + 1 }}</td>
                <td class="px-6 py-4 font-body-base font-medium text-on-surface">{{ aspect.name }}</td>
                <td class="px-6 py-4">
                  <span class="font-title-md font-semibold" :class="getScoreColor(aspect.score)">{{ aspect.score }}</span>
                </td>
                <td class="px-6 py-4 text-body-sm text-on-surface font-body-sm">{{ aspect.score }}%</td>
                <td class="px-6 py-4">
                  <div class="w-full max-w-[200px] h-2 bg-secondary-container rounded-full overflow-hidden">
                    <div
                      class="progress-fill h-full rounded-full"
                      :class="getScoreBarColor(aspect.score)"
                      :style="{ width: aspect.score + '%' }"
                    ></div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Footer Actions -->
      <footer class="flex flex-col md:flex-row items-center justify-center gap-4 border-t border-outline-variant pt-8 fade-in-delay-3">
        <button class="btn-download w-full md:w-auto px-8 py-3 bg-primary text-on-primary font-title-md text-title-md rounded-lg shadow-sm flex items-center justify-center gap-2">
          <span class="material-symbols-outlined">download</span>
          Unduh PDF
        </button>
        <button class="btn-print w-full md:w-auto px-8 py-3 bg-white border border-outline text-on-surface font-title-md text-title-md rounded-lg flex items-center justify-center gap-2">
          <span class="material-symbols-outlined">print</span>
          Cetak Hasil
        </button>
        <button class="btn-dashboard w-full md:w-auto px-8 py-3 text-secondary font-body-base text-body-base flex items-center justify-center gap-2">
          <span class="material-symbols-outlined">dashboard</span>
          Kembali ke Dashboard
        </button>
      </footer>
    </template>
  </div>
</template>

<style scoped>
/* ===== HERO ICON ===== */
.hero-icon {
  transition: all 0.4s ease;
}
.hero-icon:hover {
  transform: scale(1.05);
}
.hero-icon:hover .material-symbols-outlined {
  animation: iconPulse 0.6s ease;
}
@keyframes iconPulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

/* ===== SCORE CARD ===== */
.score-card {
  transition: all 0.3s ease;
}
.score-card:hover {
  box-shadow: 0 10px 30px rgba(0, 108, 73, 0.1);
  transform: translateY(-2px);
}

/* ===== CONCLUSION CARD ===== */
.conclusion-card {
  transition: all 0.3s ease;
}
.conclusion-card:hover {
  box-shadow: 0 10px 30px rgba(0, 108, 73, 0.08);
}

/* ===== RECOMMENDATION BOX ===== */
.recommendation-box {
  transition: all 0.3s ease;
}
.recommendation-box:hover {
  background-color: rgba(255, 255, 255, 1);
  border-color: #10b981;
  transform: translateX(4px);
}
.recommendation-box:hover .material-symbols-outlined {
  animation: bulbGlow 0.5s ease;
}
@keyframes bulbGlow {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.15) rotate(10deg); }
}

/* ===== PROGRESS BAR FILL ===== */
.progress-fill {
  transition: all 0.4s ease;
}

/* ===== PROGRESS RING ===== */
.progress-ring-circle {
  transition: stroke-dashoffset 1s ease-in-out;
  transform: rotate(-90deg);
  transform-origin: 50% 50%;
}

/* ===== FLOAT ANIMATION ===== */
@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
  100% { transform: translateY(0px); }
}
.float-animation {
  animation: float 4s ease-in-out infinite;
}

/* ===== FOOTER BUTTONS ===== */
.btn-download {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.btn-download:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 108, 73, 0.3);
}
.btn-download:active {
  transform: scale(0.96);
}

.btn-print {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.btn-print:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}
.btn-print:active {
  transform: scale(0.96);
}

.btn-dashboard {
  transition: all 0.3s ease;
}
.btn-dashboard:hover {
  color: #006c49;
  transform: translateY(-1px);
}

/* ===== SCORE BADGE ===== */
.score-badge {
  transition: all 0.3s ease;
}
.score-badge:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

/* ===== CHECK ICON ===== */
.check-icon {
  transition: all 0.3s ease;
}
.check-icon:hover {
  transform: scale(1.1) rotate(10deg);
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
