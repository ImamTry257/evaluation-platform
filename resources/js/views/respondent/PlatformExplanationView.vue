<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEvaluation } from '@/hooks/respondent/useEvaluation'

const router = useRouter()
const { loading, error, fetchActiveQuestionnaire, startEvaluation } = useEvaluation()

const isChecked = ref(false)
const scrolled = ref(false)
const questionnaire = ref<any>(null)
const starting = ref(false)

// Dynamic info items from API
const infoItems = computed(() => {
  if (!questionnaire.value) {
    return [
      { icon: 'calendar_today', label: 'Periode', value: '...' },
      { icon: 'assignment', label: 'Kuesioner', value: '...' },
      { icon: 'quiz', label: 'Pertanyaan', value: '...' },
      { icon: 'timer', label: 'Estimasi', value: '...' },
    ]
  }
  const q = questionnaire.value
  const totalQuestions = q.components?.reduce((acc: number, c: any) => {
    return acc + (c.sub_components?.reduce((a2: number, sc: any) => {
      return a2 + (sc.indicators?.reduce((a3: number, ind: any) => a3 + (ind.questions?.length || 0), 0) || 0)
    }, 0) || 0)
  }, 0) || 0
  return [
    { icon: 'calendar_today', label: 'Periode', value: q.evaluation_period?.title || '-' },
    { icon: 'assignment', label: 'Kuesioner', value: q.title || '-' },
    { icon: 'quiz', label: 'Pertanyaan', value: totalQuestions + ' Butir' },
    { icon: 'timer', label: 'Estimasi', value: (q.duration_minutes || 20) + ' Menit' },
  ]
})

const instructions = [
  {
    title: 'Pilih Skala yang Sesuai',
    desc: 'Klik radio button pada kolom skala yang paling menggambarkan kondisi Anda.',
  },
  {
    title: 'Jawaban Tersimpan Otomatis',
    desc: 'Setiap kali Anda memilih jawaban, data langsung tersimpan ke server secara real-time.',
  },
  {
    title: 'Review & Submit',
    desc: 'Anda dapat mengubah jawaban kapan saja sebelum menekan tombol Kirim Evaluasi.',
  },
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

function handleScroll() {
  scrolled.value = window.scrollY > 50
}

async function handleStartEvaluation() {
  if (!questionnaire.value) return
  starting.value = true
  try {
    const result = await startEvaluation(questionnaire.value.id)
    router.push(`/respondent/evaluation/${result.session.id}`)
  } catch (err) {
    // Error handled by hook
  } finally {
    starting.value = false
  }
}

onMounted(async () => {
  window.addEventListener('scroll', handleScroll)
  try {
    questionnaire.value = await fetchActiveQuestionnaire()
  } catch (err) {
    // Error handled by hook
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <div class="bg-background text-on-surface selection:bg-primary-container selection:text-on-primary-container min-h-screen">
    <!-- Sticky Header -->
    <header
      class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-md shadow-sm transition-all duration-300"
      :class="scrolled ? 'shadow-md' : ''"
    >
      <div class="flex justify-between items-center h-16 px-6 max-w-[1440px] mx-auto">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-3xl">eco</span>
          <h1 class="font-headline-lg text-headline-lg font-bold text-primary">EcoPolicy</h1>
        </div>
        <div class="flex items-center gap-4">
          <div class="w-8 h-8 rounded-full bg-primary-container/20 overflow-hidden border border-outline-variant/30">
            <img class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB8g0ZQfuZ05pOzHiAb6SoPDylE5O1Av_-NMy47kc-XejbNtB2oOSofTvfPXHqX01jv3_jqLjJVidyXRNkBEeB4Nzhl2-dmYJU37styOAhFTFafC_eZYr_yJkl2n9_Di-filh3na00U3Nj0U1XSXPW-RU7i8Gy5h8M1dJ0qxwbTuoD_YFFTf4JuQTB2gwYr0piG-uck4564JZdwu0lsmlKwab9pEo2m8IwVJtHSZYeOXjDBVcQAlA" />
          </div>
        </div>
      </div>
    </header>

    <main class="pt-24 pb-32 max-w-[1440px] mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
      <!-- Right: Content Cards -->
      <section class="lg:col-span-12 space-y-8">
        <!-- Tentang Platform -->
        <div class="content-card bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/20 fade-in">
          <div class="flex items-center gap-3 mb-4">
            <span class="material-symbols-outlined text-primary">info</span>
            <h3 class="font-title-md text-title-md text-on-surface">Tentang Platform</h3>
          </div>
          <p class="font-body-base text-body-base text-secondary leading-relaxed">
            Evaluasi ini merupakan langkah strategis untuk mengukur efektivitas kebijakan lingkungan yang telah diterapkan. Kami menjamin <strong>kerahasiaan data</strong> Anda sepenuhnya; hasil evaluasi akan dianonimkan dan digunakan murni untuk kepentingan riset pengembangan sekolah hijau. Kejujuran Anda adalah kunci perbaikan kualitas lingkungan pendidikan kita.
          </p>
        </div>

        <!-- Informasi Evaluasi Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 fade-in-delay">
          <div
            v-for="item in infoItems"
            :key="item.label"
            class="info-grid-item bg-surface-container-low p-4 rounded-xl flex flex-col items-center text-center group hover:bg-primary-container transition-colors duration-200"
          >
            <span class="material-symbols-outlined text-primary mb-2 group-hover:text-white">{{ item.icon }}</span>
            <span class="font-label-caps text-label-caps text-outline uppercase mb-1 group-hover:text-white/80">{{ item.label }}</span>
            <span class="font-body-base font-bold text-on-surface group-hover:text-white">{{ item.value }}</span>
          </div>
        </div>

        <!-- Instruksi Pengisian -->
        <div class="content-card bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/20 fade-in-delay-2">
          <div class="flex items-center gap-3 mb-6">
            <span class="material-symbols-outlined text-primary">rule</span>
            <h3 class="font-title-md text-title-md text-on-surface">Instruksi Pengisian</h3>
          </div>
          <ul class="space-y-4">
            <li
              v-for="inst in instructions"
              :key="inst.title"
              class="instruction-item flex gap-4 items-start"
            >
              <span class="material-symbols-outlined text-primary-container mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
              <div>
                <span class="font-body-base font-medium text-on-surface">{{ inst.title }}</span>
                <p class="text-body-sm text-secondary">{{ inst.desc }}</p>
              </div>
            </li>
          </ul>
        </div>

        <!-- Skala Penilaian -->
        <div class="content-card bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant/20 fade-in-delay-3">
          <div class="flex items-center gap-3 mb-6">
            <span class="material-symbols-outlined text-primary">straighten</span>
            <h3 class="font-title-md text-title-md text-on-surface">Skala Penilaian (Likert 1-7)</h3>
          </div>
          <div class="flex flex-wrap md:flex-nowrap gap-2 w-full">
            <div
              v-for="scale in scaleItems"
              :key="scale.value"
              class="flex-1 min-w-[80px] scale-segment p-3 rounded-lg border text-center"
              :class="[scale.bg, scale.border]"
            >
              <span class="block text-2xl font-bold" :class="scale.text">{{ scale.value }}</span>
              <span v-if="scale.label" class="text-[10px] font-bold uppercase leading-tight" :class="scale.isWhite ? 'text-white' : scale.subText || scale.text">{{ scale.label }}</span>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Fixed Bottom Confirmation Bar -->
    <footer class="fixed bottom-0 w-full bg-surface-container-lowest border-t border-outline-variant/30 px-6 py-4 z-50 shadow-[0_-4px_20px_rgba(0,0,0,0.03)]">
      <div class="max-w-[1440px] mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Error -->
        <div v-if="error" class="text-error text-body-sm">{{ error }}</div>

        <label class="checkbox-label flex items-center gap-3 cursor-pointer group transition-colors duration-300 hover:text-primary">
          <div class="relative">
            <input
              v-model="isChecked"
              class="peer hidden"
              type="checkbox"
            />
            <div class="w-6 h-6 border-2 border-outline rounded-lg flex items-center justify-center transition-all peer-checked:bg-primary peer-checked:border-primary">
              <span class="material-symbols-outlined text-white text-lg scale-0 transition-transform peer-checked:scale-100">check</span>
            </div>
          </div>
          <span class="font-body-base text-on-surface group-hover:text-primary transition-colors">Saya telah membaca dan memahami seluruh instruksi</span>
        </label>
        <div class="flex items-center gap-4 w-full md:w-auto">
          <button
            :disabled="!isChecked || loading || starting"
            class="btn-start flex-1 md:flex-none px-8 h-12 rounded-xl bg-primary-container text-white font-bold flex items-center justify-center gap-2 transition-all duration-300"
            :class="isChecked && !loading && !starting ? 'hover:bg-primary shadow-md cursor-pointer' : 'opacity-50 cursor-not-allowed'"
            @click="handleStartEvaluation"
          >
            <span v-if="starting" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
            <template v-else>
              Mulai Evaluasi
              <span class="material-symbols-outlined">arrow_forward</span>
            </template>
          </button>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* ===== SCALE SEGMENTS ===== */
.scale-segment {
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.scale-segment:hover {
  transform: translateY(-6px) scale(1.05);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
  z-index: 10;
}
.scale-segment:active {
  transform: translateY(-2px) scale(1.02);
}

/* ===== CONTENT CARDS ===== */
.content-card {
  transition: all 0.3s ease;
}
.content-card:hover {
  box-shadow: 0 8px 30px rgba(16, 108, 73, 0.1);
  border-color: #10b981;
}

/* ===== INFO GRID ITEMS ===== */
.info-grid-item {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  cursor: pointer;
}
.info-grid-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.2);
}

/* ===== INSTRUCTION LIST ===== */
.instruction-item {
  transition: all 0.3s ease;
  padding: 0.75rem;
  border-radius: 0.5rem;
  margin: 0 -0.75rem;
}
.instruction-item:hover {
  background-color: rgba(16, 185, 129, 0.05);
  transform: translateX(4px);
}
.instruction-item:hover .material-symbols-outlined {
  animation: checkBounce 0.4s ease;
}
@keyframes checkBounce {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.2); }
}

/* ===== FOOTER BUTTONS ===== */
.btn-start {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.btn-start:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
}
.btn-start:active:not(:disabled) {
  transform: scale(0.96);
}

/* ===== CHECKBOX LABEL ===== */
.checkbox-label {
  transition: all 0.3s ease;
}
.checkbox-label:hover {
  color: #006c49;
}

/* ===== FADE IN ANIMATIONS ===== */
.fade-in { animation: fadeIn 0.6s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.6s ease-out 0.15s forwards; opacity: 0; }
.fade-in-delay-2 { animation: fadeIn 0.6s ease-out 0.3s forwards; opacity: 0; }
.fade-in-delay-3 { animation: fadeIn 0.6s ease-out 0.45s forwards; opacity: 0; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>
