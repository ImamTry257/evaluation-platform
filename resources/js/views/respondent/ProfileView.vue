<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import StepHeader from './Component/StepHeader.vue'

const router = useRouter()
const authStore = useAuthStore()

const userName = computed(() => authStore.user?.name || 'Responden')
const userEmail = computed(() => authStore.user?.email || '')
const userRole = computed(() => authStore.user?.role || 'RESPONDENT')

const loading = ref(true)
const error = ref<string | null>(null)

const profile = ref<any>(null)

const formattedJoinDate = computed(() => {
  if (!profile.value?.createdAt) return '-'
  return new Date(profile.value.createdAt).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
})

async function fetchProfile() {
  loading.value = true
  error.value = null
  try {
    const response = await api.get('/auth/profile')
    profile.value = response.data.data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal memuat profil'
  } finally {
    loading.value = false
  }
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  fetchProfile()
})
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-32">
      <span class="material-symbols-outlined text-[48px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-base text-on-surface-variant mt-4">Memuat profil...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="flex flex-col items-center justify-center py-32">
      <span class="material-symbols-outlined text-[48px] text-error">error</span>
      <p class="text-body-base text-error mt-4">{{ error }}</p>
    </div>

    <template v-else-if="profile">
      <!-- Step Header -->
      <StepHeader
        :user-name="userName"
        :user-email="userEmail"
        @logout="handleLogout"
        @go-profile="router.push('/respondent/profile')"
      />

      <!-- Content -->
      <main class="pt-20 pb-32 max-w-[900px] mx-auto px-6">

        <!-- Back Link -->
        <div class="mb-6 fade-in">
          <button @click="router.back()"
            class="inline-flex items-center gap-1.5 text-primary text-body-sm font-medium hover:bg-primary/10 px-3 py-2 rounded-lg transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Beranda
          </button>
        </div>

        <!-- Profile Header Card -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/20 overflow-hidden mb-8 fade-in">
          <!-- Cover -->
          <div class="h-32 bg-gradient-to-br from-primary/20 via-primary-container/30 to-primary/10 relative">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiMwMDZjNDkiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djItSDJ2LTJoMzRtMC04VjE4SDJ2MmgzNG0wLThWMTBIMXYyaDM0bTAtOFYySDF2MmgzNCIvPjwvZz48L2c+PC9zdmc+')] opacity-30"></div>
          </div>
          <!-- Avatar + Info -->
          <div class="px-4 py-4 relative">
            <div class="w-28 h-28 rounded-2xl bg-primary-container border-4 border-white shadow-lg flex items-center justify-center mb-4 cursor-pointer hover:scale-105 hover:shadow-lg transition-all duration-300">
              <span class="material-symbols-outlined text-white text-[48px]" style="font-variation-settings: 'FILL' 1;">person</span>
            </div>
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
              <div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface mb-1">{{ profile.name }}</h1>
                <p class="text-body-sm text-on-surface-variant mb-2">{{ profile.email }}</p>
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-semibold">
                    <span class="material-symbols-outlined text-[14px]">school</span> {{ profile.role }}
                  </span>
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                  </span>
                </div>
              </div>
              <button class="px-6 py-2.5 rounded-xl border border-outline-variant text-on-surface font-body-base font-medium flex items-center gap-2 self-start hover:bg-surface-container-low hover:border-primary/50 transition-all">
                <span class="material-symbols-outlined text-[18px]">edit</span> Edit Profil
              </button>
            </div>
          </div>
        </div>


        <!-- Profile Info Card -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/20 p-4 mb-4 fade-in-delay">
          <h3 class="font-title-md font-bold text-title-md text-on-surface flex items-center gap-2 mb-6">
            <span class="material-symbols-outlined text-primary">person</span> Informasi Akun
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider mb-2 text-xs">Nama Lengkap</label>
              <span class="block pb-3 text-body-base text-on-surface">{{ profile.name }}</span>
            </div>
            <div>
              <label class="block text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider mb-2 text-xs">Email</label>
              <span class="block pb-3 text-body-base text-on-surface">{{ profile.email }}</span>
            </div>
            <div>
              <label class="block text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider mb-2 text-xs">Username</label>
              <span class="block pb-3 text-body-base text-on-surface">{{ profile.username }}</span>
            </div>
            <div>
              <label class="block text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider mb-2 text-xs">Peran</label>
              <span class="block pb-3 text-body-base text-on-surface">{{ profile.role }}</span>
            </div>
            <div>
              <label class="block text-label-caps font-label-caps text-on-surface-variant uppercase tracking-wider mb-2 text-xs">Tanggal Bergabung</label>
              <span class="block pb-3 text-body-base text-on-surface">{{ formattedJoinDate }}</span>
            </div>
          </div>
        </div>

      </main>
    </template>
  </div>
</template>

<style scoped>
.fade-in { animation: fadeIn 0.6s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.6s ease-out 0.15s forwards; opacity: 0; }
.fade-in-delay-2 { animation: fadeIn 0.6s ease-out 0.3s forwards; opacity: 0; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>
