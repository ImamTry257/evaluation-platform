<script setup lang="ts">
import { ref, reactive } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useLogin } from '@/hooks/useLogin'

const router = useRouter()
const authStore = useAuthStore()

const showPassword = ref(false)
const { isLoading, errorMessage, fieldErrors, login, clearFieldError } = useLogin()

const form = reactive({
  username: '',
  password: '',
})

async function handleLogin() {
  const success = await login(form.username, form.password)
  if (!success) return

  if (authStore.user?.role === 'ADMIN') {
    router.push('/admin')
  } else {
    router.push({ name: 'respondent-home' })
  }
}
</script>

<template>
  <!-- Hero: Login Responden + Deskripsi & Ketua Tim -->
  <section
    class="hero relative min-h-screen flex items-center justify-center overflow-hidden py-28"
    style="background: linear-gradient(135deg, #00315f 0%, #004592 50%, #2f6fed 100%);"
  >
    <div class="relative z-10 max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-12 items-center w-full">
      <!-- Kiri: Card Login Responden -->
      <div class="fade-in bg-white/95 backdrop-blur rounded-3xl shadow-2xl p-8 w-full max-w-md mx-auto lg:ml-0 order-2 lg:order-1">
        <div class="text-center mb-6">
          <h2 class="text-2xl font-bold text-[#004592] mb-1">Login Responden</h2>
          <p class="text-sm text-slate-500">Masuk untuk mengisi evaluasi</p>
        </div>

        <form class="space-y-4" @submit.prevent="handleLogin">
          <!-- Error Message -->
          <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">error</span>
            {{ errorMessage }}
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider ml-1 mb-1" for="home-username">Nama Pengguna</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                <span class="material-symbols-outlined text-lg">person</span>
              </div>
              <input
                id="home-username"
                v-model="form.username"
                :class="['login-input w-full h-12 pl-12 pr-4 bg-slate-50 border rounded-xl text-sm', fieldErrors.username ? 'border-red-400' : '']"
                placeholder="budi123"
                @input="clearFieldError('username')"
              />
            </div>
            <p v-if="fieldErrors.username" class="text-red-600 text-xs flex items-center gap-1 mt-1">
              <span class="material-symbols-outlined text-sm">error</span>
              {{ fieldErrors.username[0] }}
            </p>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider ml-1 mb-1" for="home-password">Kata Sandi</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                <span class="material-symbols-outlined text-lg">lock</span>
              </div>
              <input
                id="home-password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                :class="['login-input w-full h-12 pl-12 pr-12 bg-slate-50 border rounded-xl text-sm', fieldErrors.password ? 'border-red-400' : '']"
                placeholder="••••••••"
                @input="clearFieldError('password')"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-4 flex items-center text-slate-400 hover:text-[#004592] transition-colors"
                @click="showPassword = !showPassword"
              >
                <span class="material-symbols-outlined text-lg">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
              </button>
            </div>
            <p v-if="fieldErrors.password" class="text-red-600 text-xs flex items-center gap-1 mt-1">
              <span class="material-symbols-outlined text-sm">error</span>
              {{ fieldErrors.password[0] }}
            </p>
          </div>
          <button
            type="submit"
            :disabled="isLoading"
            class="press-scale w-full h-12 bg-[#004592] text-white rounded-xl font-semibold flex items-center justify-center gap-2 hover:bg-[#2f6fed] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isLoading" class="animate-spin material-symbols-outlined text-lg">progress_activity</span>
            <template v-else>
              Masuk <span class="material-symbols-outlined text-lg">arrow_forward</span>
            </template>
          </button>
        </form>

        <div class="mt-5 pt-5 border-t border-slate-100 text-center">
          <p class="text-sm text-slate-500">
            Belum punya akun?
            <RouterLink to="/register" class="text-[#004592] font-bold hover:text-[#2f6fed]">Daftar</RouterLink>
          </p>
        </div>
      </div>

      <!-- Kanan: Deskripsi + Foto ketua tim -->
      <div class="order-1 lg:order-2 flex flex-col items-center gap-10 w-full">
        <div class="text-center fade-in-delay max-w-xl mx-auto">
          <p class="text-base md:text-lg text-blue-50 leading-relaxed">
            Platform digital untuk mengevaluasi implementasi kebijakan lingkungan di sekolah melalui assessmen berbasis kuesioner terstruktur.
          </p>
          <p class="text-base md:text-lg font-medium text-white mt-2 leading-relaxed">
            Pertumbuhan keberlanjutan institusi Anda dengan mudah.
          </p>
        </div>

        <div class="team-card fade-in-delay-2 max-w-xs w-full mx-auto flex flex-col items-center text-center">
          <div class="team-avatar w-44 h-44 rounded-full overflow-hidden border-4 border-white/20 shadow-2xl mx-auto">
            <img class="w-full h-full object-cover" alt="Foto Prof. Edi" src="/images/prof-edi.jpeg" />
          </div>
          <div class="mt-5 flex flex-col items-center">
            <h3 class="text-2xl font-bold text-white leading-tight drop-shadow">Prof. Dr. Edi Istiyono, M.Si</h3>
            <p class="text-sm text-blue-100 mt-1">Universitas Negeri Yogyakarta</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* ===== HERO ORBS ===== */
.hero {
  position: relative;
  overflow: hidden;
}
.hero::before {
  content: '';
  position: absolute;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(47, 111, 237, 0.3) 0%, transparent 70%);
  top: -200px;
  right: -200px;
  border-radius: 50%;
  animation: orbFloat 8s ease-in-out infinite;
}
.hero::after {
  content: '';
  position: absolute;
  width: 420px;
  height: 420px;
  background: radial-gradient(circle, rgba(147, 197, 253, 0.18) 0%, transparent 70%);
  bottom: -120px;
  left: -120px;
  border-radius: 50%;
  animation: orbFloat 10s ease-in-out infinite reverse;
}
@keyframes orbFloat {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(30px, -30px) scale(1.1); }
}

/* ===== PRESS SCALE ===== */
.press-scale {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.press-scale:active {
  transform: scale(0.96);
}

/* ===== TEAM CARD ===== */
.team-card {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.team-card:hover {
  transform: translateY(-6px) scale(1.02);
  box-shadow: 0 20px 45px rgba(0, 69, 146, 0.15);
  border-color: #2f6fed;
}
.team-card:hover .team-avatar {
  transform: scale(1.04);
}
.team-avatar {
  transition: all 0.4s ease;
}

/* ===== LOGIN INPUT ===== */
.login-input {
  transition: all 0.3s ease;
}
.login-input:hover {
  border-color: #2f6fed;
  box-shadow: 0 2px 10px rgba(0, 69, 146, 0.1);
}
.login-input:focus {
  border-color: #004592;
  box-shadow: 0 4px 18px rgba(0, 69, 146, 0.15);
  transform: translateY(-2px);
  outline: none;
}

/* ===== FADE IN ANIMATIONS ===== */
.fade-in { animation: fadeIn 0.7s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.7s ease-out 0.2s forwards; opacity: 0; }
.fade-in-delay-2 { animation: fadeIn 0.7s ease-out 0.4s forwards; opacity: 0; }
.fade-in-delay-3 { animation: fadeIn 0.7s ease-out 0.6s forwards; opacity: 0; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
