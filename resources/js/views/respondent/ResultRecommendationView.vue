<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useEvaluation } from '@/hooks/respondent/useEvaluation'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const userName = computed(() => authStore.user?.name || 'Responden')
const userEmail = computed(() => authStore.user?.email || '')
const showUserMenu = ref(false)

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}

const route = useRoute()
const router = useRouter()
const sessionId = Number(route.params.sessionId)
const { loading, error, fetchResults } = useEvaluation()

// State
const resultData = ref<any>(null)

// Computed from API data
const overallPercentage = computed(() => resultData.value?.result?.overallPercentage || 0)
const overallCategory = computed(() => resultData.value?.result?.overallCategory || '-')
const overallScore = computed(() => resultData.value?.result?.overallScore || 0)
const conclusion = computed(() => resultData.value?.result?.conclusion || '-')
const details = computed(() => resultData.value?.result?.details || [])

const circumference = 2 * Math.PI * 80
const strokeDashoffset = computed(() => {
  return circumference - (overallPercentage.value / 100) * circumference
})

const categoryLabel = computed(() => {
  const labels: Record<string, string> = {
    A: 'Sangat Baik',
    B: 'Baik',
    C: 'Sedang',
    D: 'Kurang',
    E: 'Sangat Kurang',
  }
  return labels[overallCategory.value] || '-'
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
async function loadResults() {
  try {
    resultData.value = await fetchResults(sessionId)
  } catch (err) {
    // Error handled by hook
  }
}

onMounted(() => {
  loadResults()
})
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Step Header -->
    <div class="fixed top-0 left-0 right-0 z-[200] bg-surface/80 backdrop-blur-md border-b border-outline-variant">
      <div class="max-w-12xl mx-auto px-6 py-2.5 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-2xl">eco</span>
          <span class="font-title-md text-title-md font-bold text-primary hidden sm:inline">EcoPolicy</span>
        </div>
        <div class="flex items-center gap-0">
          <RouterLink to="/respondent"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-on-surface-variant hover:bg-surface-container cursor-pointer transition-all">
            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border border-current">1</span>
            <span class="hidden md:inline">Penjelasan</span>
          </RouterLink>
          <div class="w-6 h-px mx-1 bg-outline-variant"></div>
          <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-on-surface-variant">
            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border border-current">2</span>
            <span class="hidden md:inline">Input Angket</span>
          </span>
          <div class="w-6 h-px mx-1 bg-primary"></div>
          <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-white">
            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold bg-white text-primary border-white">3</span>
            <span class="hidden md:inline">Hasil</span>
          </span>
        </div>
        <!-- User Menu -->
        <div class="relative">
          <button @click="showUserMenu = !showUserMenu"
            class="w-9 h-9 rounded-full bg-primary-container/20 border-2 border-outline-variant/50 flex items-center justify-center hover:shadow-md transition-all">
            <span class="material-symbols-outlined text-primary text-[20px]">person</span>
          </button>
          <div v-if="showUserMenu"
            class="absolute top-full right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-outline-variant/30 py-1 z-[300]"
            @click.self="showUserMenu = false">
            <div class="px-4 py-3 border-b border-outline-variant/30">
              <p class="font-body-base font-semibold text-on-surface text-sm">{{ userName }}</p>
              <p class="text-xs text-on-surface-variant">{{ userEmail }}</p>
            </div>
            <div class="py-1">
              <button @click="router.push('/respondent/profile')" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined text-[18px]">person</span> Profil Saya
              </button>
              <div class="my-1 border-t border-outline-variant/30"></div>
              <button @click="handleLogout" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-error hover:bg-red-50 transition-colors font-medium">
                <span class="material-symbols-outlined text-[18px]">logout</span> Keluar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="pt-16 p-8 max-w-[1024px] w-full mx-auto">
    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <span class="material-symbols-outlined text-[48px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-base text-on-surface-variant mt-4">Memuat hasil evaluasi...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="flex flex-col items-center justify-center py-20">
      <span class="material-symbols-outlined text-[48px] text-error">error</span>
      <p class="text-body-base text-error mt-4">{{ error }}</p>
      <button @click="router.push('/respondent')" class="mt-4 text-primary text-body-sm font-semibold hover:underline">Kembali ke Beranda</button>
    </div>

    <template v-else-if="resultData">
      <!-- Success Header -->
      <section class="flex flex-col items-center text-center mb-12 fade-in">
        <div class="w-48 h-48 mb-6 float-animation relative">
          <div class="w-full h-full bg-gradient-to-br from-primary/10 to-primary-container/20 rounded-full flex items-center justify-center">
            <span class="material-symbols-outlined text-primary" style="font-size: 80px;">eco</span>
          </div>
          <div class="absolute -bottom-2 -right-2 bg-primary text-on-primary rounded-full p-3 shadow-lg flex items-center justify-center">
            <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
          </div>
        </div>
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-2 fade-in-delay">Angket Berhasil Diselesaikan</h1>
        <p class="font-body-base text-body-base text-secondary max-w-lg fade-in-delay">
          Terima kasih telah mengisi. Data Anda telah berhasil tersimpan dalam sistem.
        </p>
      </section>

      <!-- Main Summary Grid -->
      <div class="flex flex-col items-center gap-6 mb-12">
        <!-- Score Card -->
        <div class="w-full max-w-sm bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant flex flex-col items-center justify-center text-center fade-in-delay-2">
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
              <span class="font-display-lg text-display-lg text-primary leading-none">{{ overallPercentage }}%</span>
              <span class="font-label-caps text-label-caps text-secondary uppercase tracking-widest">Persentase</span>
            </div>
          </div>
          <!-- <div
            class="px-6 py-2 rounded-full font-title-md text-title-md inline-flex items-center gap-2 border cursor-default"
            :class="getCategoryColor(overallCategory)"
          >
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">stars</span>
            {{ categoryLabel }}
          </div> -->
        </div>

        <!-- Conclusion Card -->
        <!-- <div class="md:col-span-7 bg-surface-container-low p-8 rounded-xl shadow-sm border border-outline-variant flex flex-col justify-center fade-in-delay-2">
          <h3 class="font-headline-lg text-headline-lg text-on-surface mb-6 flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">analytics</span>
            Kesimpulan Angket
          </h3>
          <p class="font-body-base text-body-base text-on-surface-variant leading-relaxed">
            {{ conclusion }}
          </p>
        </div> -->
      </div>

      <!-- Detailed Breakdown -->
      <section class="mb-12 fade-in-delay-2">
        <h2 class="font-title-md text-title-md text-on-surface flex items-center gap-2 mb-6">
          <span class="material-symbols-outlined text-primary">segment</span>
          Detail Capaian Per Komponen
        </h2>

        <div class="space-y-4">
          <div v-for="detail in details" :key="detail.indicator?.id || detail.indicatorId" class="px-4 py-3 rounded-lg hover:bg-primary/[0.03] transition-colors">
            <div class="flex justify-between items-end mb-2">
              <span class="font-body-base text-on-surface font-medium">{{ detail.indicator?.name || 'Indikator' }}</span>
              <span class="font-label-caps" :class="getScoreColor(detail.percentage)">{{ detail.percentage }}%</span>
            </div>
            <div class="h-2 w-full bg-secondary-container rounded-full overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-700"
                :class="getScoreBarColor(detail.percentage)"
                :style="{ width: detail.percentage + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Footer Actions -->
      <footer class="flex justify-center border-t border-outline-variant pt-8 fade-in-delay-2">
        <!--
        <button class="w-full md:w-auto px-8 py-3 bg-primary text-on-primary font-title-md text-title-md rounded-lg shadow-sm flex items-center justify-center gap-2 transition-all hover:shadow-lg hover:-translate-y-0.5">
          <span class="material-symbols-outlined">download</span>
          Unduh PDF
        </button>
        -->
        <button
          @click="router.push('/respondent')"
          class="px-8 py-3 text-secondary font-body-base text-body-base flex items-center justify-center gap-2 hover:text-primary transition-colors"
        >
          <span class="material-symbols-outlined">refresh</span>
          Kembali ke Beranda
        </button>
      </footer>
    </template>
    </div>
  </div>
</template>

<style scoped>
.progress-ring-circle {
  transition: stroke-dashoffset 1s ease-in-out;
  transform: rotate(-90deg);
  transform-origin: 50% 50%;
}
.float-animation {
  animation: float 4s ease-in-out infinite;
}
@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
  100% { transform: translateY(0px); }
}
.fade-in { animation: fadeIn 0.6s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.6s ease-out 0.15s forwards; opacity: 0; }
.fade-in-delay-2 { animation: fadeIn 0.6s ease-out 0.3s forwards; opacity: 0; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
