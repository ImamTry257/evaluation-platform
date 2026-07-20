<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEvaluation } from '@/hooks/respondent/useEvaluation'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const { loading, error, saveAnswer, submitEvaluation } = useEvaluation()

// Route params
const sessionId = computed(() => Number(route.params.sessionId) || 0)
const pageId = computed(() => Number(route.params.compIndex) || 1)

// Data
const session = ref<any>(null)
const questionnaire = ref<any>(null)
const statements = ref<any>(null)
const answers = ref<Record<number, number>>({})
const showSubmitModal = ref(false)
const showResetModal = ref(false)
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

// Components
const components = computed(() => questionnaire.value?.components || [])
const compPage = computed(() => Math.max(0, pageId.value - 1))

const answeredCount = computed(() => session.value?.evaluation?.answers?.length || 0)
const statementCount = computed(() => statements.value?.count || 0)
const progressPct = computed(() => {
  if (statementCount.value === 0) return '0'
  return (answeredCount.value / statementCount.value * 100).toFixed(0)
})
const unansweredCount = computed(() => statementCount.value - answeredCount.value)
const isLastComp = computed(() => compPage.value >= components.value.length - 1)

// Navigation
function goToComp(i: number) {
  if (i >= 0 && i < components.value.length) {
    router.push(`/respondent/evaluation/${sessionId.value}/component/${i + 1}`)
  }
}
function nextComp() {
  if (!isLastComp.value) goToComp(compPage.value + 1)
}
function prevComp() {
  if (compPage.value > 0) goToComp(compPage.value - 1)
}

// Answer
async function selectAnswer(questionId: number, score: number) {
  answers.value[questionId] = score

  console.log(answers)
  showToast('Jawaban tersimpan')
  try {
    await saveAnswer(sessionId.value, questionId, score)
  } catch (err) { /* silently fail */ }
}

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
  } catch (err) { /* Error handled by hook */ } finally {
    submitting.value = false
  }
}

// Reset (back to explanation)
function handleBackToExplain() { showResetModal.value = true }
function closeResetModal() { showResetModal.value = false }
function confirmReset() {
  showResetModal.value = false
  if (timerInterval) { clearInterval(timerInterval); timerInterval = null }
  router.push('/respondent')
}

// Timer
function startTimer() {
  if (timerInterval) clearInterval(timerInterval)
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) timeLeft.value--
  }, 1000)
}

// Fetch session data
async function fetchSession() {
  if (!sessionId.value) return
  try {
    const { data } = await api.get(`/evaluations/${sessionId.value}/component/${pageId.value}`)
    const payload = data.data
    session.value = payload.session
    questionnaire.value = payload.session.evaluation
    statements.value = payload.session.statements

    // Pre-fill answers
    console.log(statements.value)
    if (questionnaire.value?.answers) {
      for (const a of questionnaire.value.answers) {
        answers.value[a.questionId] = a.score
      }
    }

    timeLeft.value = session.value?.remainingSeconds || (questionnaire.value?.durationMinutes || 20) * 60
    startTimer()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Sesi evaluasi tidak ditemukan atau bukan milik Anda'
    setTimeout(() => router.push('/respondent'), 2000)
  }
}

onMounted(() => { fetchSession() })

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
  if (toastTimer) clearTimeout(toastTimer)
})
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Loading -->
    <div v-if="loading && !session" class="flex flex-col items-center justify-center py-32">
      <span class="material-symbols-outlined text-[48px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-base text-on-surface-variant mt-4">Memuat sesi evaluasi...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error && !session" class="flex flex-col items-center justify-center py-32">
      <span class="material-symbols-outlined text-[48px] text-error">error</span>
      <p class="text-body-base text-error mt-4">{{ error }}</p>
    </div>

    <template v-else-if="session">
      <!-- Step Header -->
      <div class="fixed top-0 left-0 right-0 z-[200] bg-surface/80 backdrop-blur-md border-b border-outline-variant">
        <div class="max-w-5xl mx-auto px-6 py-2.5 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-2xl">eco</span>
            <span class="font-title-md text-title-md font-bold text-primary hidden sm:inline">EcoPolicy</span>
          </div>
          <div class="flex items-center gap-0">
            <button @click="handleBackToExplain"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-on-surface-variant hover:bg-surface-container cursor-pointer transition-all">
              <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border border-current">1</span>
              <span class="hidden md:inline">Penjelasan</span>
            </button>
            <div class="w-6 h-px mx-1 bg-primary"></div>
            <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-white">
              <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold bg-white text-primary border-white">2</span>
              <span class="hidden md:inline">Input Angket</span>
            </span>
            <div class="w-6 h-px mx-1 bg-outline-variant"></div>
            <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-on-surface-variant">
              <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold border border-current">3</span>
              <span class="hidden md:inline">Hasil</span>
            </span>
          </div>
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-1.5" :class="timeLeft <= 60 ? 'text-error' : 'text-tertiary'">
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
      <div class="fixed top-[42px] left-0 right-0 z-[140] bg-surface border-b border-outline-variant/50">
        <div class="max-w-5xl w-full mx-auto px-6 py-6 flex items-center justify-between">
          <span class="font-label-caps text-label-caps text-secondary uppercase">Progress</span>
          <span class="font-label-caps text-label-caps text-primary font-bold">{{ answeredCount }} dari {{ statementCount }} dijawab</span>
        </div>
        <div class="w-full h-1 bg-secondary-container">
          <div class="bg-primary h-full transition-all duration-500" :style="{ width: progressPct + '%' }"></div>
        </div>
      </div>

      <!-- Main Content -->
      <main class="pt-[98px] pb-40 max-w-5xl mx-auto px-6">
        <!-- Component Pagination Dots -->
        <div v-if="components.length > 1" class="flex items-center justify-center gap-3 mb-6">
          <div class="flex items-center gap-2">
            <div v-for="(comp, i) in components" :key="comp.id"
              class="h-2.5 rounded-full transition-all duration-300 cursor-pointer"
              :class="i === compPage ? 'bg-primary w-7' : i < compPage ? 'bg-primary-container w-2.5' : 'bg-outline-variant w-2.5'"
              @click="goToComp(i)"></div>
          </div>
          <span class="ml-3 text-body-sm text-on-surface-variant font-medium">Komponen {{ compPage + 1 }} dari {{ components.length }}</span>
        </div>

        <!-- Component Table -->
        <div v-if="statements?.count != 0">
          <div class="flex items-center gap-2 mb-4 mt-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary rounded-full text-xs font-semibold">
              <span class="material-symbols-outlined text-[14px]">folder</span>
              {{ statements?.indicator }}
            </span>
            <span class="text-body-sm text-on-surface-variant"> — {{ statements?.count }} pernyataan</span>
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
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                  <tr v-for="(q, qi) in statements?.statementList" :key="q.id"
                    :class="{ 'answered': answers[q.id] }" class="transition-colors hover:bg-primary/[0.03]">
                    <td class="px-4 py-4 text-center text-body-sm font-semibold text-on-surface-variant">{{ qi + 1 }}</td>
                    <td class="px-4 py-4 text-body-sm text-on-surface leading-relaxed">{{ q.question_text }}</td>
                    <td v-for="s in 7" :key="s"
                      class="text-center cursor-pointer transition-all duration-200 p-2"
                      :class="answers[q.id] === s ? 'selected-cell' : 'hover-cell'"
                      @click="selectAnswer(q.id, s)">
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
          <button v-if="pageId != 1" @click="prevComp"
            class="flex items-center gap-2 px-6 py-3 rounded-xl font-title-md text-title-md text-secondary border border-outline-variant hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined">arrow_back</span> Sebelumnya
          </button>
          <div v-else></div>
          <button v-if="!isLastComp" @click="nextComp"
            class="flex items-center gap-2 px-10 py-3 rounded-xl font-title-md text-title-md bg-primary text-on-primary shadow-lg shadow-primary/20 transition-all hover:shadow-xl hover:-translate-y-0.5">
            Selanjutnya <span class="material-symbols-outlined">arrow_forward</span>
          </button>
          <button v-else @click="showSubmitModalFn"
            class="flex items-center gap-2 px-10 py-3 rounded-xl font-title-md text-title-md bg-tertiary text-on-tertiary shadow-lg shadow-tertiary/20 transition-all hover:shadow-xl hover:-translate-y-0.5">
            Submit <span class="material-symbols-outlined">send</span>
          </button>
        </div>
<!-- 
        <div v-if="unansweredCount > 0" class="mt-4 w-full bg-tertiary/10 border border-tertiary/20 rounded-xl p-4 flex items-center gap-3">
          <span class="material-symbols-outlined text-tertiary">warning</span>
          <span class="text-body-sm text-on-surface">Masih ada {{ unansweredCount }} pertanyaan yang belum dijawab.</span>
        </div> -->
      </main>

      <!-- Toast -->
      <div class="fixed bottom-24 right-6 z-[300] bg-primary text-white px-5 py-3 rounded-xl text-body-sm font-medium flex items-center gap-2 shadow-lg transition-all duration-400"
        :class="toastVisible ? 'translate-y-0 opacity-100' : 'translate-y-5 opacity-0 pointer-events-none'">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        {{ toastMsg }}
      </div>
    </template>

    <!-- Submit Modal -->
    <div class="fixed inset-0 z-[500] flex items-center justify-center p-4 transition-opacity duration-300"
      :class="showSubmitModal ? 'bg-black/50 backdrop-blur-sm opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
      @click.self="closeSubmitModal">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8 transition-transform duration-300" :class="showSubmitModal ? 'scale-100' : 'scale-90'">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-xl bg-tertiary/10 flex items-center justify-center"><span class="material-symbols-outlined text-tertiary">send</span></div>
          <h3 class="font-title-md text-title-md text-on-surface">Kirim Evaluasi?</h3>
        </div>
        <p class="text-body-base text-on-surface-variant mb-2">Anda yakin ingin mengirim seluruh jawaban evaluasi?</p>
        <p class="text-body-sm text-on-surface-variant mb-6">{{ answeredCount }} dari {{ statementCount }} pertanyaan sudah dijawab.</p>
        <div class="flex items-center justify-end gap-3">
          <button @click="closeSubmitModal" class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors">Batal</button>
          <button @click="confirmSubmit" :disabled="submitting"
            class="px-8 py-2.5 rounded-xl bg-tertiary text-on-tertiary font-body-base font-semibold shadow-sm transition-all hover:bg-tertiary/90 active:scale-95 disabled:opacity-50 flex items-center gap-2">
            <span v-if="submitting" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
            <span v-else class="material-symbols-outlined text-[18px]">check_circle</span>
            {{ submitting ? 'Mengirim...' : 'Ya, Kirim' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Reset Modal -->
    <div class="fixed inset-0 z-[500] flex items-center justify-center p-4 transition-opacity duration-300"
      :class="showResetModal ? 'bg-black/50 backdrop-blur-sm opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
      @click.self="closeResetModal">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8 transition-transform duration-300" :class="showResetModal ? 'scale-100' : 'scale-90'">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-xl bg-tertiary/10 flex items-center justify-center"><span class="material-symbols-outlined text-tertiary">warning</span></div>
          <h3 class="font-title-md text-title-md text-on-surface">Mulai Ulang?</h3>
        </div>
        <p class="text-body-base text-on-surface-variant mb-2">Kembali ke halaman penjelasan akan <strong>menghapus semua jawaban</strong> yang sudah diisi.</p>
        <p class="text-body-sm text-on-surface-variant mb-6">Data yang lama akan hilang dan diganti data yang baru saat memulai evaluasi ulang.</p>
        <div class="flex items-center justify-end gap-3">
          <button @click="closeResetModal" class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors">Batal</button>
          <button @click="confirmReset"
            class="px-8 py-2.5 rounded-xl bg-tertiary text-on-tertiary font-body-base font-semibold shadow-sm transition-all hover:bg-tertiary/90 active:scale-95 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">refresh</span> Ya, Mulai Ulang
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
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
