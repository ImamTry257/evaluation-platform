<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEvaluation } from '@/hooks/respondent/useEvaluation'
import { useAuthStore } from '@/stores/auth'
import StepHeader from './Component/StepHeader.vue'

const authStore = useAuthStore()
const userName = computed(() => authStore.user?.name || 'Responden')
const userEmail = computed(() => authStore.user?.email || '')

async function handleLogout() {
  await authStore.logout()
  router.push('/')
}

const route = useRoute()
const router = useRouter()
const sessionId = Number(route.params.sessionId)
const { loading, error, fetchResults } = useEvaluation()

// State
const resultData = ref<any>(null)

// Computed from API data

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
      <StepHeader
        :current-step="3"
        :user-name="userName"
        :user-email="userEmail"
        :show-steps="true"
        @logout="handleLogout"
        @go-profile="router.push('/respondent/profile')"
        @navigate-step="step => { if (step === 1) router.push('/respondent') }"
      />

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
      <button @click="router.push('/respondent')" class="mt-4 text-[#004592] text-body-sm font-semibold hover:underline">Kembali ke Beranda</button>
    </div>

    <template v-else-if="resultData">
      <!-- Success Header -->
      <section class="flex flex-col items-center text-center mb-12 fade-in">
        <div class="w-48 h-48 mb-6 float-animation flex items-center justify-center">
          <div class="bg-[#004592] text-on-primary rounded-full p-5 shadow-lg flex items-center justify-center">
            <span class="material-symbols-outlined text-[48px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
          </div>
        </div>
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-2 fade-in-delay">Angket Berhasil Diselesaikan</h1>
        <p class="font-body-base text-body-base text-secondary max-w-lg fade-in-delay">
          Terima kasih telah mengisi. Data Anda telah berhasil tersimpan dalam sistem.
        </p>
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
          class="px-8 py-3 text-secondary font-body-base text-body-base flex items-center justify-center gap-2 hover:text-[#004592] transition-colors"
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
