<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEvaluation } from '@/hooks/respondent/useEvaluation'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const userName = computed(() => authStore.user?.name || 'Responden')
const userEmail = computed(() => authStore.user?.email || '')

const router = useRouter()
const { loading, error, fetchActiveQuestionnaire, startEvaluation } = useEvaluation()

const questionnaire = ref<any>(null)
const isChecked = ref(false)
const starting = ref(false)
const showUserMenu = ref(false)

const infoItems = computed(() => {
  if (!questionnaire.value) return []
  const q = questionnaire.value
  return [
    { icon: 'calendar_today', label: 'Periode', value: q.evaluationPeriod?.title || '-' },
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
  { value: 5, label: 'Cukup Sesuai', bg: 'bg-emerald-50', border: 'border-emerald-100', text: 'text-emerald-600' },
  { value: 6, label: 'Sesuai', bg: 'bg-primary/10', border: 'border-primary/30', text: 'text-primary' },
  { value: 7, label: 'Sangat Sesuai Sekali', bg: 'bg-primary', border: 'border-primary/20', text: 'text-white', isWhite: true },
]

async function handleStartEvaluation() {
  if (!questionnaire.value) return
  starting.value = true
  error.value = null
  try {
    const result = await startEvaluation(questionnaire.value.id)
    const sessionId = result.session?.evaluation?.id || result.session?.id
    router.push(`/respondent/evaluation/${sessionId}/component/1`)
  } catch (err) {
    // Error handled by hook
  } finally {
    starting.value = false
  }
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}

onMounted(async () => {
  try {
    questionnaire.value = await fetchActiveQuestionnaire()
  } catch (err) { /* Error handled by hook */ }
})
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Loading -->
    <div v-if="loading && !questionnaire" class="flex flex-col items-center justify-center py-32">
      <span class="material-symbols-outlined text-[48px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-base text-on-surface-variant mt-4">Memuat kuesioner...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error && !questionnaire" class="flex flex-col items-center justify-center py-32">
      <span class="material-symbols-outlined text-[48px] text-error">error</span>
      <p class="text-body-base text-error mt-4">{{ error }}</p>
    </div>

    <template v-else-if="questionnaire">
      <!-- Step Header -->
      <div class="fixed top-0 left-0 right-0 z-[200] bg-surface/80 backdrop-blur-md border-b border-outline-variant">
        <div class="max-w-5xl mx-auto px-6 py-2.5 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-2xl">eco</span>
            <span class="font-title-md text-title-md font-bold text-primary hidden sm:inline">EcoPolicy</span>
          </div>
          <div class="flex items-center gap-0">
            <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-white">
              <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold bg-white text-primary border-white">1</span>
              <span class="hidden md:inline">Penjelasan</span>
            </span>
            <div class="w-6 h-px mx-1 bg-outline-variant"></div>
            <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-on-surface-variant">
              <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border border-current">2</span>
              <span class="hidden md:inline">Input Angket</span>
            </span>
            <div class="w-6 h-px mx-1 bg-outline-variant"></div>
            <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-on-surface-variant">
              <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border border-current">3</span>
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

      <!-- Content -->
      <main class="pt-14 pb-32 max-w-[1440px] mx-auto px-6 space-y-8">
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/20 fade-in">
          <div class="flex items-center gap-3 mb-4">
            <span class="material-symbols-outlined text-primary">info</span>
            <h3 class="font-title-md text-title-md text-on-surface">Tentang Platform</h3>
          </div>
          <p class="font-body-base text-body-base text-secondary leading-relaxed">
            Evaluasi ini merupakan langkah strategis untuk mengukur efektivitas kebijakan lingkungan yang telah diterapkan. Kami menjamin <strong>kerahasiaan data</strong> Anda sepenuhnya; hasil evaluasi akan dianonimkan dan digunakan murni untuk kepentingan riset pengembangan sekolah hijau.
          </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 fade-in-delay">
          <div v-for="item in infoItems" :key="item.label"
            class="bg-surface-container-low p-4 rounded-xl flex flex-col items-center text-center group hover:bg-primary-container transition-all duration-200 cursor-pointer hover:-translate-y-1 hover:shadow-lg">
            <span class="material-symbols-outlined text-primary mb-2 group-hover:text-white">{{ item.icon }}</span>
            <span class="font-label-caps text-label-caps text-outline uppercase mb-1 group-hover:text-white/80">{{ item.label }}</span>
            <span class="font-body-base font-bold text-on-surface group-hover:text-white">{{ item.value }}</span>
          </div>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/20 fade-in-delay-2">
          <div class="flex items-center gap-3 mb-6">
            <span class="material-symbols-outlined text-primary">rule</span>
            <h3 class="font-title-md text-title-md text-on-surface">Instruksi Pengisian</h3>
          </div>
          <ul class="space-y-4">
            <li v-for="inst in instructions" :key="inst.title" class="flex gap-4 items-start px-3 py-2 rounded-lg -mx-3 hover:bg-primary/[0.03] transition-colors">
              <span class="material-symbols-outlined text-primary-container mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
              <div>
                <span class="font-body-base font-medium text-on-surface">{{ inst.title }}</span>
                <p class="text-body-sm text-secondary">{{ inst.desc }}</p>
              </div>
            </li>
          </ul>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/20 fade-in-delay-2">
          <div class="flex items-center gap-3 mb-6">
            <span class="material-symbols-outlined text-primary">straighten</span>
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
          <label class="flex items-center gap-3 cursor-pointer group transition-colors duration-300 hover:text-primary">
            <div class="relative">
              <input v-model="isChecked" class="peer hidden" type="checkbox" />
              <div class="w-6 h-6 border-2 border-outline rounded-lg flex items-center justify-center transition-all peer-checked:bg-primary peer-checked:border-primary">
                <span class="material-symbols-outlined text-white text-lg scale-0 transition-transform peer-checked:scale-100">check</span>
              </div>
            </div>
            <span class="font-body-base text-on-surface group-hover:text-primary transition-colors">Saya telah membaca dan memahami seluruh instruksi</span>
          </label>
          <button :disabled="!isChecked || starting"
            class="px-8 h-12 rounded-xl bg-primary-container text-white font-bold flex items-center justify-center gap-2 transition-all duration-300"
            :class="isChecked && !starting ? 'hover:bg-primary shadow-md cursor-pointer' : 'opacity-50 cursor-not-allowed'"
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
</style>
