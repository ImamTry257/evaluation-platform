<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEvaluation } from '@/hooks/respondent/useEvaluation'
import { useAuthStore } from '@/stores/auth'
import StepHeader from './Component/StepHeader.vue'

const authStore = useAuthStore()
const userName = computed(() => authStore.user?.name || 'Responden')
const userEmail = computed(() => authStore.user?.email || '')

const router = useRouter()
const { loading, error, fetchActiveQuestionnaire, startEvaluation } = useEvaluation()

const questionnaire = ref<any>(null)
const availability = ref<any>(null)
const isChecked = ref(false)
const starting = ref(false)
const checking = ref(false)
const lastCheckedAt = ref('baru saja')
const retryNote = ref('')

// Get the current component from localStorage for resuming
const getCurrentComponentFromStorage = () => {
  const saved = localStorage.getItem('savedEvaluationComponent')
  return saved ? parseInt(saved) : null
}

const currentComponent = ref(getCurrentComponentFromStorage())

const infoItems = computed(() => {
  if (!questionnaire.value) return []
  const q = questionnaire.value
  return [
    { icon: 'calendar_today', label: 'Periode', value: q.evaluationPeriod?.name || '-' },
    { icon: 'assignment', label: 'Instrument', value: q.title || '-' },
    { icon: 'timer', label: 'Estimasi', value: (q.durationMinutes || 20) + ' Menit' },
  ]
})

const instructions = [
  { title: 'Pilih Skala yang Sesuai', desc: 'Klik radio button pada kolom skala yang paling menggambarkan kondisi Anda.' },
  { title: 'Jawaban Tersimpan Otomatis', desc: 'Setiap kali Anda memilih jawaban, data langsung tersimpan ke server secara real-time.' },
  { title: 'Review & Submit', desc: 'Anda dapat mengubah jawaban kapan saja sebelum menekan tombol Kirim Evaluasi.' },
]

const scaleItems = [
  { value: 1, label: 'Sangat Tidak Sesuai', bg: 'bg-red-50', border: 'border-red-100', text: 'text-red-600', subText: 'text-red-800' },
  { value: 2, label: 'Tidak Sesuai', bg: 'bg-orange-50', border: 'border-orange-100', text: 'text-orange-600' },
  { value: 3, label: 'Kurang Sesuai', bg: 'bg-yellow-50', border: 'border-yellow-100', text: 'text-yellow-600' },
  { value: 4, label: 'Netral', bg: 'bg-gray-50', border: 'border-gray-200', text: 'text-gray-500', subText: 'text-gray-600' },
  { value: 5, label: 'Cukup Sesuai', bg: 'bg-blue-50', border: 'border-blue-100', text: 'text-blue-600' },
  { value: 6, label: 'Sesuai', bg: 'bg-[#004592]/10', border: 'border-[#004592]/30', text: 'text-[#004592]' },
  { value: 7, label: 'Sangat Sesuai Sekali', bg: 'bg-[#004592]', border: 'border-[#004592]/20', text: 'text-white', isWhite: true },
]

async function handleStartEvaluation() {
  if (!questionnaire.value) return
  starting.value = true
  error.value = null
  try {
    const result = await startEvaluation(questionnaire.value.id, currentComponent.value)
    const sessionId = result.session?.evaluation?.id || result.session?.id
    const targetComponent = currentComponent.value || 1
    router.push(`/respondent/evaluation/${sessionId}/component/${targetComponent}`)
    // Clear the saved component after successful start
    localStorage.removeItem('savedEvaluationComponent')
  } catch (err) {
    // Error handled by hook
  } finally {
    starting.value = false
  }
}

async function handleLogout() {
  await authStore.logout()
  router.push('/')
}

/**
 * Terapkan payload dari GET /evaluations/active-questionnaire.
 * available=true  → lanjut render halaman penjelasan (questionnaire terisi).
 * available=false → kosongkan questionnaire agar empty-state tampil,
 *                   simpan payload availability untuk chip status/periode dinamis.
 */
function applyAvailability(res: any) {
  if (res && res.available && res.questionnaire) {
    questionnaire.value = res.questionnaire
    availability.value = null
  } else {
    questionnaire.value = null
    availability.value = res || null
  }
}

async function retryLoad() {
  if (checking.value) return
  checking.value = true
  error.value = null
  retryNote.value = ''
  try {
    applyAvailability(await fetchActiveQuestionnaire())
  } catch {
    const now = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
    lastCheckedAt.value = now
    retryNote.value = 'Kuesioner masih belum tersedia. Silakan coba lagi nanti.'
  } finally {
    checking.value = false
  }
}

onMounted(async () => {
  try {
    // Check if user has existing in-progress session
    const currentComponent = getCurrentComponentFromStorage()
    let sessionData = null
    
    if (currentComponent) {
      // Try to fetch the session to check if it's still in progress
      try {
        // Use the existing evaluation data if available, otherwise fetch
        if (!sessionData) {
          sessionData = await startEvaluation(questionnaire.value?.id, currentComponent)
        }
        // Successfully restored session, navigate directly
        const sessionId = sessionData.session?.evaluation?.id || sessionData.session?.id
        router.push(`/respondent/evaluation/${sessionId}/component/${currentComponent}`)
        return
      } catch (resumeError) {
        // Session may be expired or invalid, continue normally
        console.log('Session resume failed, continuing normally:', resumeError)
        localStorage.removeItem('savedEvaluationComponent')
      }
    }
    
    // No existing session or couldn't resume, fetch active questionnaire
    const res = await fetchActiveQuestionnaire()
    applyAvailability(res)
  } catch (err) { 
    // Error handled by hook
  }
})
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Loading -->
    <div v-if="loading && !questionnaire" class="flex flex-col items-center justify-center py-32">
      <span class="material-symbols-outlined text-[48px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-base text-on-surface-variant mt-4">Memuat kuesioner...</p>
    </div>

    <!-- Error / No active questionnaire -->
    <template v-else-if="!questionnaire">
      <StepHeader
        :current-step="1"
        :user-name="userName"
        :user-email="userEmail"
        :show-steps="true"
        @logout="handleLogout"
        @go-profile="router.push('/respondent/profile')"
      />

      <!-- decorative blob -->
      <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-[#004592]/5 blur-3xl"></div>
      </div>

      <main class="relative z-10 pt-28 pb-24 min-h-screen flex flex-col items-center justify-center px-6">
        <div class="w-full max-w-lg">
          <div class="bg-surface-container-lowest rounded-2xl shadow-[0_18px_50px_-20px_rgba(0,69,146,0.28)] border border-outline-variant/40 p-8 sm:p-10 text-center fade-in">
            <!-- status pill -->
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-widest text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-3 py-1">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
              {{ availability?.reasonLabel || 'Belum ada evaluasi' }}
            </span>

            <!-- icon -->
            <div class="mt-8 flex justify-center">
              <div class="ring-pulse w-24 h-24 rounded-full bg-[#004592]/10 flex items-center justify-center floaty">
                <span class="material-symbols-outlined text-[#004592] text-6xl" style="font-variation-settings: 'FILL' 1;">assignment</span>
              </div>
            </div>

            <!-- title -->
            <h1 class="mt-7 text-2xl sm:text-[28px] font-bold text-on-surface leading-snug">Belum Ada Kuesioner<br/>yang Tersedia</h1>

            <!-- friendly explanation -->
            <p class="mt-4 text-sm sm:text-[15px] text-on-surface-variant leading-relaxed max-w-md mx-auto">
              Saat ini belum ada kegiatan evaluasi yang terbuka untuk Anda. Kuesioner akan tersedia secara otomatis
              begitu admin membuka periode evaluasi tertentu. Silakan kembali lagi beberapa saat ke depan.
            </p>

            <!-- info chips -->
            <div class="mt-8 grid grid-cols-3 gap-3">
              <div class="bg-[#f5f7fa] rounded-xl px-3 py-3 border border-outline-variant/60">
                <span class="material-symbols-outlined text-outline text-lg block">calendar_today</span>
                <div class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1">Periode</div>
                <div class="font-semibold text-sm text-on-surface/50">{{ availability?.period?.name || '-' }}</div>
              </div>
              <div class="bg-[#f5f7fa] rounded-xl px-3 py-3 border border-outline-variant/60">
                <span class="material-symbols-outlined text-outline text-lg block">schedule</span>
                <div class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1">Status</div>
                <div class="font-semibold text-xs text-amber-600">{{ availability?.reasonLabel || 'Menunggu' }}</div>
              </div>
              <div class="bg-[#f5f7fa] rounded-xl px-3 py-3 border border-outline-variant/60">
                <span class="material-symbols-outlined text-outline text-lg block">timer</span>
                <div class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1">Estimasi</div>
                <div class="font-semibold text-sm text-on-surface/50">{{ availability?.estimatedMinutes ? availability.estimatedMinutes + ' Menit' : '-' }}</div>
              </div>
            </div>

            <!-- actions -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
              <button
                :disabled="checking"
                class="w-full sm:w-auto px-7 h-12 rounded-xl bg-[#004592] text-white font-bold inline-flex items-center justify-center gap-2 transition-all duration-300 hover:bg-[#2f6fed] hover:shadow-lg active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                @click="retryLoad"
              >
                <span v-if="checking" class="material-symbols-outlined text-[20px] animate-spin">progress_activity</span>
                <span v-else class="material-symbols-outlined text-[20px]">refresh</span>
                {{ checking ? 'Memeriksa kuesioner...' : 'Muat Ulang & Cek Lagi' }}
              </button>
              <button
                class="w-full sm:w-auto px-7 h-12 rounded-xl border border-[#c6d2e4] text-[#004592] font-semibold inline-flex items-center justify-center gap-2 transition-all duration-300 hover:bg-[#f2f7fd] active:scale-95"
                @click="handleLogout"
              >
                <span class="material-symbols-outlined text-[20px]">logout</span>
                Keluar
              </button>
            </div>

            <!-- last checked + retry note -->
            <p class="mt-6 text-xs text-on-surface-variant/80 inline-flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[14px]">sync</span>
              Terakhir diperiksa: {{ lastCheckedAt }}
            </p>
            <p v-if="retryNote" class="mt-3 text-xs text-amber-600">{{ retryNote }}</p>
          </div>
        </div>
      </main>
    </template>

    <template v-else-if="questionnaire">
      <!-- Step Header -->
      <StepHeader
        :current-step="1"
        :user-name="userName"
        :user-email="userEmail"
        :show-steps="true"
        @logout="handleLogout"
        @go-profile="router.push('/respondent/profile')"
      />

      <!-- Content -->
      <main class="pt-14 pb-32 max-w-[1440px] mx-auto px-6 space-y-8">
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/20 fade-in">
          <div class="flex items-center gap-3 mb-4">
            <span class="material-symbols-outlined text-[#004592]">info</span>
            <h3 class="font-title-md text-title-md text-on-surface">Tentang Platform</h3>
          </div>
          <p class="font-body-base text-body-base text-secondary leading-relaxed">
            Evaluasi ini merupakan langkah strategis untuk mengukur efektivitas kebijakan lingkungan yang telah diterapkan. Kami menjamin <strong>kerahasiaan data</strong> Anda sepenuhnya; hasil evaluasi akan dianonimkan dan digunakan murni untuk kepentingan riset pengembangan sekolah hijau.
          </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 fade-in-delay">
          <div v-for="item in infoItems" :key="item.label"
            class="bg-surface-container-low p-4 rounded-xl flex flex-col items-center text-center group hover:bg-[#004592] transition-all duration-200 cursor-pointer hover:-translate-y-1 hover:shadow-lg">
            <span class="material-symbols-outlined text-[#004592] mb-2 group-hover:text-white">{{ item.icon }}</span>
            <span class="font-label-caps text-label-caps text-outline uppercase mb-1 group-hover:text-white/80">{{ item.label }}</span>
            <span class="font-body-base font-bold text-on-surface group-hover:text-white">{{ item.value }}</span>
          </div>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/20 fade-in-delay-2">
          <div class="flex items-center gap-3 mb-6">
            <span class="material-symbols-outlined text-[#004592]">rule</span>
            <h3 class="font-title-md text-title-md text-on-surface">Instruksi Pengisian</h3>
          </div>
          <ul class="space-y-4">
            <li v-for="inst in instructions" :key="inst.title" class="flex gap-4 items-start px-3 py-2 rounded-lg -mx-3 hover:bg-[#004592]/[0.03] transition-colors">
              <span class="material-symbols-outlined text-[#004592] mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
              <div>
                <span class="font-body-base font-medium text-on-surface">{{ inst.title }}</span>
                <p class="text-body-sm text-secondary">{{ inst.desc }}</p>
              </div>
            </li>
          </ul>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/20 fade-in-delay-2">
          <div class="flex items-center gap-3 mb-6">
            <span class="material-symbols-outlined text-[#004592]">straighten</span>
            <h3 class="font-title-md text-title-md text-on-surface">Skala Penilaian (Likert 1-7)</h3>
          </div>
          <div class="flex flex-wrap md:flex-nowrap gap-2 w-full">
            <div v-for="scale in scaleItems" :key="scale.value"
              class="flex-1 min-w-[80px] p-3 rounded-lg border text-center transition-all duration-300 cursor-pointer hover:-translate-y-1.5 hover:shadow-lg"
              :class="[scale.bg, scale.border]">
              <span class="block text-2xl font-bold" :class="scale.text">{{ scale.value }}</span>
              <span v-if="scale.label" class="text-[10px] font-bold uppercase leading-tight" :class="scale.isWhite ? 'text-white' : scale.subText || scale.text">{{ scale.label }}</span>
            </div>
          </div>
        </div>
      </main>

      <!-- Footer -->
      <footer class="fixed bottom-0 w-full bg-surface-container-lowest border-t border-outline-variant/30 px-6 py-4 z-50 shadow-[0_-4px_20px_rgba(0,0,0,0.03)]">
        <div class="max-w-[1440px] mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
          <div v-if="error" class="text-error text-body-sm">{{ error }}</div>
          <label class="flex items-center gap-3 cursor-pointer group transition-colors duration-300 hover:text-[#004592]">
            <div class="relative">
              <input v-model="isChecked" class="peer hidden" type="checkbox" />
              <div class="w-6 h-6 border-2 border-outline rounded-lg flex items-center justify-center transition-all peer-checked:bg-[#004592] peer-checked:border-[#004592]">
                <span class="material-symbols-outlined text-white text-lg scale-0 transition-transform peer-checked:scale-100">check</span>
              </div>
            </div>
            <span class="font-body-base text-on-surface group-hover:text-[#004592] transition-colors">Saya telah membaca dan memahami seluruh instruksi</span>
          </label>
          <button :disabled="!isChecked || starting"
            class="px-8 h-12 rounded-xl bg-[#004592] text-white font-bold flex items-center justify-center gap-2 transition-all duration-300"
            :class="isChecked && !starting ? 'hover:bg-[#2f6fed] shadow-md cursor-pointer' : 'opacity-50 cursor-not-allowed'"
            @click="handleStartEvaluation">
            <span v-if="starting" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
            <template v-else>Mulai Evaluasi<span class="material-symbols-outlined">arrow_forward</span></template>
          </button>
        </div>
      </footer>
    </template>
  </div>
</template>

<style scoped>
.fade-in { animation: fadeIn 0.6s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.6s ease-out 0.15s forwards; opacity: 0; }
.fade-in-delay-2 { animation: fadeIn 0.6s ease-out 0.3s forwards; opacity: 0; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

/* Empty state decorations */
.ring-pulse { position: relative; }
.ring-pulse::after {
  content: "";
  position: absolute;
  inset: -8px;
  border-radius: 9999px;
  border: 2px solid rgba(0, 69, 146, 0.18);
  animation: pulseRing 2.6s ease-out infinite;
}
@keyframes pulseRing { 0% { transform: scale(0.92); opacity: 1; } 100% { transform: scale(1.18); opacity: 0; } }
.floaty { animation: floaty 4s ease-in-out infinite; }
@keyframes floaty { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
</style>
