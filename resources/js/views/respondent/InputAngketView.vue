<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEvaluation } from '@/hooks/respondent/useEvaluation'

const router = useRouter()
const {
  loading, error,
  fetchActiveQuestionnaire, startEvaluation,
  saveAnswer, submitEvaluation,
} = useEvaluation()

// Step state
const step = ref<'explain' | 'answer'>('explain')
const questionnaire = ref<any>(null)
const session = ref<any>(null)
const isChecked = ref(false)
const starting = ref(false)

// Answer state
const answers = ref<Record<number, number>>({})
const compPage = ref(0)
const showSubmitModal = ref(false)
const submitting = ref(false)
const toastMsg = ref('')
const toastVisible = ref(false)
let toastTimer: ReturnType<typeof setTimeout> | null = null

// Timer
const timeLeft = ref(0)
let timerInterval: ReturnType<typeof setInterval> | null = null

const countdown = computed(() => {
  const m = Math.floor(timeLeft.value / 60)
  const s = timeLeft.value % 60
  return `${m}:${s < 10 ? '0' : ''}${s}`
})

// Components from questionnaire
const components = computed(() => questionnaire.value?.components || [])

const totalQuestions = computed(() => {
  return components.value.reduce((acc: number, c: any) => {
    return acc + (c.sub_components?.reduce((a2: number, sc: any) => {
      return a2 + (sc.indicators?.reduce((a3: number, ind: any) => a3 + (ind.questions?.length || 0), 0) || 0)
    }, 0) || 0)
  }, 0)
})

const answeredCount = computed(() => Object.keys(answers.value).length)
const progressPct = computed(() => {
  if (totalQuestions.value === 0) return '0'
  return (answeredCount.value / totalQuestions.value * 100).toFixed(0)
})
const unansweredCount = computed(() => totalQuestions.value - answeredCount.value)
const isLastComp = computed(() => compPage.value === components.value.length - 1)

// Info items from questionnaire
const infoItems = computed(() => {
  if (!questionnaire.value) return []
  const q = questionnaire.value
  return [
    { icon: 'calendar_today', label: 'Periode', value: q.evaluationPeriod?.name || '-' },
    { icon: 'assignment', label: 'Instrument', value: q.title || '-' },
    // { icon: 'quiz', label: 'Pernyataan', value: totalQuestions.value + ' Butir' },
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
  { value: 2, bg: 'bg-orange-50', border: 'border-orange-100', text: 'text-orange-600' },
  { value: 3, bg: 'bg-yellow-50', border: 'border-yellow-100', text: 'text-yellow-600' },
  { value: 4, label: 'Netral', bg: 'bg-gray-50', border: 'border-gray-200', text: 'text-gray-500', subText: 'text-gray-600' },
  { value: 5, bg: 'bg-emerald-50', border: 'border-emerald-100', text: 'text-emerald-600' },
  { value: 6, bg: 'bg-primary/10', border: 'border-primary/30', text: 'text-primary' },
  { value: 7, label: 'Sangat Sesuai Sekali', bg: 'bg-primary', border: 'border-primary/20', text: 'text-white', isWhite: true },
]

// Flatten questions for a component
function getCompQuestions(comp: any) {
  const questions: any[] = []
  for (const sc of (comp.sub_components || [])) {
    for (const ind of (sc.indicators || [])) {
      for (const q of (ind.questions || [])) {
        questions.push(q)
      }
    }
  }
  return questions
}

// === Step 1: Start Evaluation ===
async function handleStartEvaluation() {
  if (!questionnaire.value) return
  starting.value = true
  error.value = null
  try {
    const result = await startEvaluation(questionnaire.value.id)
    session.value = result.session
    // Pre-fill existing answers
    if (result.session?.answers) {
      for (const a of result.session.answers) {
        answers.value[a.questionId] = a.score
      }
    }
    timeLeft.value = result.session?.remainingSeconds || (questionnaire.value.duration_minutes || 20) * 60
    step.value = 'answer'
    // Start timer
    timerInterval = setInterval(() => {
      if (timeLeft.value > 0) timeLeft.value--
    }, 1000)
  } catch (err) {
    // Error handled by hook
  } finally {
    starting.value = false
  }
}

// === Step 2: Answer Questions ===
function goToComp(i: number) {
  if (i >= 0 && i < components.value.length) {
    compPage.value = i
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}
function nextComp() {
  if (compPage.value < components.value.length - 1) {
    compPage.value++
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}
function prevComp() {
  if (compPage.value > 0) {
    compPage.value--
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

async function selectAnswer(questionId: number, score: number) {
  answers.value[questionId] = score
  showToast('Jawaban tersimpan')
  try {
    await saveAnswer(sessionId.value, questionId, score)
  } catch (err) {
    // Silently fail
  }
}

const sessionId = computed(() => session.value?.id || 0)

function showToast(msg: string) {
  toastMsg.value = msg
  toastVisible.value = true
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toastVisible.value = false }, 1800)
}

// Submit
function showSubmitModalFn() { showSubmitModal.value = true }
function closeSubmitModal() { showSubmitModal.value = false }
async function confirmSubmit() {
  submitting.value = true
  try {
    await submitEvaluation(sessionId.value)
    showSubmitModal.value = false
    router.push(`/respondent/result/${sessionId.value}`)
  } catch (err) {
    // Error handled by hook
  } finally {
    submitting.value = false
  }
}

// Fetch questionnaire on mount
onMounted(async () => {
  try {
    questionnaire.value = await fetchActiveQuestionnaire()
  } catch (err) {
    // Error handled by hook
  }
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
  if (toastTimer) clearTimeout(toastTimer)
})
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- ========== LOADING ========== -->
    <div v-if="loading && !questionnaire && step === 'explain'" class="flex flex-col items-center justify-center py-32">
      <span class="material-symbols-outlined text-[48px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-base text-on-surface-variant mt-4">Memuat kuesioner...</p>
    </div>

    <!-- ========== ERROR ========== -->
    <div v-else-if="error && !questionnaire && step === 'explain'" class="flex flex-col items-center justify-center py-32">
      <span class="material-symbols-outlined text-[48px] text-error">error</span>
      <p class="text-body-base text-error mt-4">{{ error }}</p>
    </div>

    <!-- ========== STEP INDICATOR ========== -->
    <div v-if="questionnaire" class="fixed top-0 left-0 right-0 z-[200] bg-surface/80 backdrop-blur-md border-b border-outline-variant">
      <div class="max-w-5xl mx-auto px-6 py-2.5 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-2xl">eco</span>
          <span class="font-title-md text-title-md font-bold text-primary hidden sm:inline">EcoPolicy</span>
        </div>
        <div class="flex items-center gap-0">
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
            :class="step === 'explain' ? 'bg-primary text-white' : step === 'answer' || !session ? 'bg-primary/10 text-primary' : 'text-on-surface-variant'">
            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border transition-all"
              :class="step === 'explain' ? 'bg-white text-primary border-white' : 'border-current'">1</span>
            <span class="hidden md:inline">Penjelasan</span>
          </div>
          <div class="w-6 h-px mx-1" :class="step === 'answer' ? 'bg-primary' : 'bg-outline-variant'"></div>
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
            :class="step === 'answer' ? 'bg-primary text-white' : 'text-on-surface-variant'">
            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border transition-all"
              :class="step === 'answer' ? 'bg-white text-primary border-white' : 'border-current'">2</span>
            <span class="hidden md:inline">Input Angket</span>
          </div>
          <div class="w-6 h-px mx-1 bg-outline-variant"></div>
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-on-surface-variant">
            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border border-current">3</span>
            <span class="hidden md:inline">Hasil</span>
          </div>
        </div>
        <div class="w-20"></div>
      </div>
    </div>

    <!-- ========== STEP 1: EXPLANATION ========== -->
    <template v-if="step === 'explain' && questionnaire">

      <main class="pt-16 pb-32 max-w-[1440px] mx-auto px-6 space-y-8">
        <!-- Tentang Platform -->
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/20 fade-in">
          <div class="flex items-center gap-3 mb-4">
            <span class="material-symbols-outlined text-primary">info</span>
            <h3 class="font-title-md text-title-md text-on-surface">Tentang Platform</h3>
          </div>
          <p class="font-body-base text-body-base text-secondary leading-relaxed">
            Evaluasi ini merupakan langkah strategis untuk mengukur efektivitas kebijakan lingkungan yang telah diterapkan. Kami menjamin <strong>kerahasiaan data</strong> Anda sepenuhnya; hasil evaluasi akan dianonimkan dan digunakan murni untuk kepentingan riset pengembangan sekolah hijau.
          </p>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 fade-in-delay">
          <div
            v-for="item in infoItems"
            :key="item.label"
            class="bg-primary/20 p-4 rounded-xl flex flex-col items-center text-center group hover:bg-primary-container transition-colors duration-200 cursor-pointer hover:-translate-y-1 hover:shadow-lg"
          >
            <span class="material-symbols-outlined text-primary mb-2 group-hover:text-white">{{ item.icon }}</span>
            <span class="font-label-caps text-label-caps text-outline uppercase mb-1 group-hover:text-white/80">{{ item.label }}</span>
            <span class="font-body-base font-bold text-on-surface group-hover:text-white">{{ item.value }}</span>
          </div>
        </div>

        <!-- Instruksi -->
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

        <!-- Skala Penilaian -->
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/20 fade-in-delay-2">
          <div class="flex items-center gap-3 mb-6">
            <span class="material-symbols-outlined text-primary">straighten</span>
            <h3 class="font-title-md text-title-md text-on-surface">Skala Penilaian (Likert 1-7)</h3>
          </div>
          <div class="flex flex-wrap md:flex-nowrap gap-2 w-full">
            <div
              v-for="scale in scaleItems"
              :key="scale.value"
              class="flex-1 min-w-[80px] p-3 rounded-lg border text-center transition-all duration-300 cursor-pointer hover:-translate-y-1.5 hover:shadow-lg"
              :class="[scale.bg, scale.border]"
            >
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
          <button
            :disabled="!isChecked || starting"
            class="px-8 h-12 rounded-xl bg-primary-container text-white font-bold flex items-center justify-center gap-2 transition-all duration-300"
            :class="isChecked && !starting ? 'hover:bg-primary shadow-md cursor-pointer' : 'opacity-50 cursor-not-allowed'"
            @click="handleStartEvaluation"
          >
            <span v-if="starting" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
            <template v-else>
              Mulai
              <span class="material-symbols-outlined">arrow_forward</span>
            </template>
          </button>
        </div>
      </footer>
    </template>

    <!-- ========== STEP 2: TABLE INPUT ========== -->
    <template v-else-if="step === 'answer' && session">
      <!-- Sticky Header -->
      <div class="fixed top-[42px] left-0 right-0 z-[150] bg-surface/80 backdrop-blur-md border-b border-outline-variant h-14 px-6 flex items-center">
        <div class="max-w-5xl w-full mx-auto flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">eco</span>
            <span class="font-title-md text-title-md font-bold text-primary hidden sm:inline">EcoPolicy</span>
          </div>
          <div class="flex items-center gap-6">
            <div class="flex items-center gap-2" :class="timeLeft <= 60 ? 'text-error' : 'text-tertiary'">
              <span class="material-symbols-outlined text-base">timer</span>
              <span class="font-title-md text-title-md font-bold tabular-nums">{{ countdown }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-primary">
              <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
              <span class="font-label-caps text-[10px] uppercase tracking-tighter">Tersimpan</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="fixed top-[98px] left-0 right-0 z-[140] bg-surface border-b border-outline-variant/50">
        <div class="max-w-5xl w-full mx-auto px-6 py-2 flex items-center justify-between">
          <span class="font-label-caps text-label-caps text-secondary uppercase">Progress</span>
          <span class="font-label-caps text-label-caps text-primary font-bold">{{ answeredCount }} dari {{ totalQuestions }} dijawab</span>
        </div>
        <div class="w-full h-1 bg-secondary-container">
          <div class="bg-primary h-full transition-all duration-500" :style="{ width: progressPct + '%' }"></div>
        </div>
      </div>

      <!-- Main Content -->
      <main class="pt-44 pb-40 max-w-5xl mx-auto px-6">
        <!-- Component Pagination Dots -->
        <div v-if="components.length > 1" class="flex items-center justify-center gap-3 mb-6">
          <div class="flex items-center gap-2">
            <div
              v-for="(comp, i) in components"
              :key="comp.id"
              class="h-2.5 rounded-full transition-all duration-300 cursor-pointer"
              :class="i === compPage ? 'bg-primary w-7' : i < compPage ? 'bg-primary-container w-2.5' : 'bg-outline-variant w-2.5'"
              @click="goToComp(i)"
            ></div>
          </div>
          <span class="ml-3 text-body-sm text-on-surface-variant font-medium">
            Komponen {{ compPage + 1 }} dari {{ components.length }}
          </span>
        </div>

        <!-- Component Table -->
        <div v-if="components[compPage]">
          <div class="flex items-center gap-2 mb-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary rounded-full text-xs font-semibold">
              <span class="material-symbols-outlined text-[14px]">folder</span>
              {{ components[compPage].name }}
            </span>
            <span class="text-body-sm text-on-surface-variant">
              — {{ getCompQuestions(components[compPage]).length }} pertanyaan
            </span>
          </div>

          <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full text-left" style="border-collapse: separate; border-spacing: 0;">
                <thead>
                  <tr class="bg-surface-container-low/60">
                    <th class="px-4 py-3 w-12 text-center font-label-caps text-label-caps text-outline uppercase text-xs">No</th>
                    <th class="px-4 py-3 font-label-caps text-label-caps text-outline uppercase text-xs">Pernyataan</th>
                    <th v-for="s in 7" :key="s" class="px-2 py-3 text-center font-label-caps text-label-caps text-outline uppercase text-xs" style="width: 56px;">{{ s }}</th>
                  </tr>
                  <tr class="bg-surface-container-low/30">
                    <th></th><th></th>
                    <th class="px-2 pb-2 text-center"><span class="text-[9px] text-red-600 font-semibold leading-tight block">Sgt Tidak<br>Sesuai</span></th>
                    <th class="px-2 pb-2 text-center"><span class="text-[9px] text-orange-600 font-semibold leading-tight block">2</span></th>
                    <th class="px-2 pb-2 text-center"><span class="text-[9px] text-yellow-600 font-semibold leading-tight block">3</span></th>
                    <th class="px-2 pb-2 text-center"><span class="text-[9px] text-gray-500 font-semibold leading-tight block">Netral</span></th>
                    <th class="px-2 pb-2 text-center"><span class="text-[9px] text-emerald-600 font-semibold leading-tight block">5</span></th>
                    <th class="px-2 pb-2 text-center"><span class="text-[9px] text-primary font-semibold leading-tight block">6</span></th>
                    <th class="px-2 pb-2 text-center"><span class="text-[9px] text-primary font-semibold leading-tight block">Sgt Sesuai<br>Sekali</span></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                  <tr
                    v-for="(q, qi) in getCompQuestions(components[compPage])"
                    :key="q.id"
                    :class="{ 'answered': answers[q.id] }"
                    class="transition-colors hover:bg-primary/[0.03]"
                  >
                    <td class="px-4 py-4 text-center text-body-sm font-semibold text-on-surface-variant">{{ qi + 1 }}</td>
                    <td class="px-4 py-4 text-body-sm text-on-surface leading-relaxed">{{ q.text }}</td>
                    <td
                      v-for="s in 7"
                      :key="s"
                      class="text-center cursor-pointer transition-all duration-200 p-2"
                      :class="answers[q.id] === s ? 'selected-cell' : 'hover-cell'"
                      @click="selectAnswer(q.id, s)"
                    >
                      <div class="radio-dot mx-auto"></div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Prev / Next / Submit -->
        <div class="mt-8 flex items-center justify-between">
          <button v-if="compPage > 0" @click="prevComp" class="flex items-center gap-2 px-6 py-3 rounded-xl font-title-md text-title-md text-secondary border border-outline-variant hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined">arrow_back</span> Sebelumnya
          </button>
          <div v-else></div>
          <button v-if="!isLastComp" @click="nextComp" class="flex items-center gap-2 px-10 py-3 rounded-xl font-title-md text-title-md bg-primary text-on-primary shadow-lg shadow-primary/20 transition-all hover:shadow-xl hover:-translate-y-0.5">
            Selanjutnya <span class="material-symbols-outlined">arrow_forward</span>
          </button>
          <button v-else @click="showSubmitModalFn" class="flex items-center gap-2 px-10 py-3 rounded-xl font-title-md text-title-md bg-tertiary text-on-tertiary shadow-lg shadow-tertiary/20 transition-all hover:shadow-xl hover:-translate-y-0.5">
            Kirim Evaluasi <span class="material-symbols-outlined">send</span>
          </button>
        </div>

        <!-- Warning -->
        <div v-if="unansweredCount > 0" class="mt-4 w-full bg-tertiary/10 border border-tertiary/20 rounded-xl p-4 flex items-center gap-3">
          <span class="material-symbols-outlined text-tertiary">warning</span>
          <span class="text-body-sm text-on-surface">Masih ada {{ unansweredCount }} pertanyaan yang belum dijawab.</span>
        </div>
      </main>

      <!-- Bottom Bar -->
      <div class="fixed bottom-0 left-0 right-0 bg-surface border-t border-outline-variant h-16 flex items-center z-[150]">
        <div class="max-w-5xl w-full mx-auto px-6 flex items-center justify-between">
          <span class="text-body-sm text-on-surface-variant">{{ answeredCount }}/{{ totalQuestions }} terjawab</span>
          <div class="flex items-center gap-3">
            <button @click="window.scrollTo({ top: 0, behavior: 'smooth' })" class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-primary text-body-sm font-medium hover:bg-primary/10 transition-colors">
              <span class="material-symbols-outlined text-[18px]">arrow_upward</span> Ke Atas
            </button>
            <button @click="showSubmitModalFn" class="flex items-center gap-1.5 px-6 py-2 rounded-lg bg-tertiary text-on-tertiary font-body-base font-semibold transition-all hover:shadow-lg">
              <span class="material-symbols-outlined text-[18px]">send</span> Kirim
            </button>
          </div>
        </div>
      </div>

      <!-- Toast -->
      <div
        class="fixed bottom-24 right-6 z-[300] bg-primary text-white px-5 py-3 rounded-xl text-body-sm font-medium flex items-center gap-2 shadow-lg transition-all duration-400"
        :class="toastVisible ? 'translate-y-0 opacity-100' : 'translate-y-5 opacity-0 pointer-events-none'"
      >
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ toastMsg }}
      </div>

      <!-- Submit Modal -->
      <div
        class="fixed inset-0 z-[500] flex items-center justify-center p-4 transition-opacity duration-300"
        :class="showSubmitModal ? 'bg-black/50 backdrop-blur-sm opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
        @click.self="closeSubmitModal"
      >
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8 transition-transform duration-300" :class="showSubmitModal ? 'scale-100' : 'scale-90'">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-tertiary/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-tertiary">send</span>
            </div>
            <h3 class="font-title-md text-title-md text-on-surface">Kirim Evaluasi?</h3>
          </div>
          <p class="text-body-base text-on-surface-variant mb-2">Anda yakin ingin mengirim seluruh jawaban evaluasi?</p>
          <p class="text-body-sm text-on-surface-variant mb-6">{{ answeredCount }} dari {{ totalQuestions }} pertanyaan sudah dijawab.</p>
          <div class="flex items-center justify-end gap-3">
            <button @click="closeSubmitModal" class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors">Batal</button>
            <button @click="confirmSubmit" :disabled="submitting" class="px-8 py-2.5 rounded-xl bg-tertiary text-on-tertiary font-body-base font-semibold shadow-sm transition-all hover:bg-tertiary/90 active:scale-95 disabled:opacity-50 flex items-center gap-2">
              <span v-if="submitting" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
              <span v-else class="material-symbols-outlined text-[18px]">check_circle</span>
              {{ submitting ? 'Mengirim...' : 'Ya, Kirim' }}
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.fade-in { animation: fadeIn 0.6s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.6s ease-out 0.15s forwards; opacity: 0; }
.fade-in-delay-2 { animation: fadeIn 0.6s ease-out 0.3s forwards; opacity: 0; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

.radio-dot {
  width: 22px; height: 22px; border-radius: 50%;
  border: 2px solid #bbcabf;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  flex-shrink: 0;
}
.selected-cell .radio-dot {
  border-color: #006c49; background: #006c49; transform: scale(1.15);
}
.selected-cell .radio-dot::after {
  content: ''; width: 8px; height: 8px; border-radius: 50%; background: white;
}
.hover-cell:hover {
  background: rgba(16, 185, 129, 0.12); border-radius: 8px;
}
tr.answered { background: rgba(16, 185, 129, 0.06); }
tr.answered:hover { background: rgba(16, 185, 129, 0.08); }
</style>
