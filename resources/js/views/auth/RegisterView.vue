<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

const form = reactive({
  fullName: '',
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
})

// Password strength calculation
const passwordStrength = computed(() => {
  const val = form.password
  let strength = 0
  if (val.length > 5) strength += 25
  if (val.match(/[A-Z]/)) strength += 25
  if (val.match(/[0-9]/)) strength += 25
  if (val.match(/[^A-Za-z0-9]/)) strength += 25
  return strength
})

const strengthLabel = computed(() => {
  const s = passwordStrength.value
  if (s === 0) return { text: 'Masukkan kata sandi', color: 'text-on-surface-variant', barClass: 'bg-error w-0' }
  if (s <= 25) return { text: 'Lemah', color: 'text-error', barClass: 'bg-error' }
  if (s <= 50) return { text: 'Cukup', color: 'text-tertiary', barClass: 'bg-tertiary-container' }
  if (s <= 75) return { text: 'Kuat', color: 'text-primary', barClass: 'bg-primary-container opacity-60' }
  return { text: 'Sangat Kuat', color: 'text-primary', barClass: 'bg-primary' }
})

async function handleRegister() {
  if (form.password !== form.confirmPassword) {
    errorMessage.value = 'Konfirmasi kata sandi tidak cocok.'
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    await authStore.register({
      name: form.fullName,
      username: form.username,
      email: form.email,
      password: form.password,
      password_confirmation: form.confirmPassword,
    })

    router.push('/login')
  } catch (error: any) {
    errorMessage.value = error?.response?.data?.message || 'Terjadi kesalahan. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="h-full flex flex-col md:flex-row overflow-hidden bg-surface">
    <!-- Left Column: Illustration -->
    <section class="hidden lg:flex relative flex-1 login-illustration-container items-center justify-center p-8 overflow-hidden border-r border-outline-variant/30">
      <div class="relative z-10 w-full flex flex-col items-center justify-center">
        <div class="text-center px-6">
          <h2 class="text-3xl font-semibold text-on-surface mb-2 fade-in gradient-text">
            Bergabung Bersama Kami
          </h2>
          <p class="text-base text-on-surface-variant max-w-md mx-auto fade-in-delay leading-relaxed">
            Buat akun untuk berpartisipasi dalam evaluasi dan berkontribusi masa depan pendidikan berkelanjutan.
          </p>
        </div>
      </div>
      <!-- Decorative circles -->
      <div class="decorative-circle absolute -top-24 -left-24 w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
      <div class="decorative-circle absolute -bottom-24 -right-24 w-96 h-96 bg-primary/10 rounded-full blur-3xl"></div>
    </section>

    <!-- Right Column: Register Form -->
    <main class="flex-1 h-full bg-surface-container-lowest flex flex-col justify-center items-center px-6 md:px-8 relative overflow-y-auto">
      <div class="w-full max-w-md py-8">
        <!-- Brand Header -->
        <header class="mb-6 text-center">
          <div class="flex items-center justify-center gap-2.5 mb-4 fade-in">
            <div class="brand-logo w-11 h-11 bg-primary rounded-xl flex items-center justify-center shadow-sm pulse-ring">
              <span class="material-symbols-outlined text-white" style="font-size: 28px; font-variation-settings: 'FILL' 1;">eco</span>
            </div>
            <span class="brand-name text-2xl tracking-tight text-primary font-bold">PolicyEval</span>
          </div>
          <h1 class="text-2xl font-semibold text-on-surface leading-tight mb-2 fade-in-delay">
            Buat Akun
          </h1>
          <p class="text-sm text-on-surface-variant mx-auto fade-in-delay-2 leading-relaxed">
            Isi data diri Anda untuk memulai perjalanan.
          </p>
        </header>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-center gap-2 fade-in">
          <span class="material-symbols-outlined text-lg">error</span>
          {{ errorMessage }}
        </div>

        <!-- Form -->
        <form @submit.prevent="handleRegister" class="space-y-4 fade-in-delay-3">
          <!-- Full Name -->
          <div class="space-y-1 form-group">
            <label class="form-label text-xs text-on-surface-variant uppercase font-bold ml-1" style="letter-spacing: 0.08em;">
              Nama Lengkap
            </label>
            <div class="relative group">
              <div class="input-icon absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant transition-colors">
                <span class="material-symbols-outlined" style="font-size: 20px;">person</span>
              </div>
              <input
                v-model="form.fullName"
                type="text"
                placeholder="John Doe"
                required
                class="form-input block w-full h-11 pl-10 pr-4 bg-surface-container-lowest border border-outline-variant rounded-xl text-base focus:ring-2 focus:ring-primary focus:border-primary transition-all placeholder:text-on-secondary-container"
              />
            </div>
          </div>

          <!-- Username -->
          <div class="space-y-1 form-group">
            <label class="form-label text-xs text-on-surface-variant uppercase font-bold ml-1" style="letter-spacing: 0.08em;">
              Username
            </label>
            <div class="relative group">
              <div class="input-icon absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant transition-colors">
                <span class="material-symbols-outlined" style="font-size: 20px;">alternate_email</span>
              </div>
              <input
                v-model="form.username"
                type="text"
                placeholder="johndoe22"
                required
                class="form-input block w-full h-11 pl-10 pr-4 bg-surface-container-lowest border border-outline-variant rounded-xl text-base focus:ring-2 focus:ring-primary focus:border-primary transition-all placeholder:text-on-secondary-container"
              />
            </div>
          </div>

          <!-- Email Address -->
          <div class="space-y-1 form-group">
            <label class="form-label text-xs text-on-surface-variant uppercase font-bold ml-1" style="letter-spacing: 0.08em;">
              Email
            </label>
            <div class="relative group">
              <div class="input-icon absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant transition-colors">
                <span class="material-symbols-outlined" style="font-size: 20px;">mail</span>
              </div>
              <input
                v-model="form.email"
                type="email"
                placeholder="name@school.edu"
                required
                class="form-input block w-full h-11 pl-10 pr-4 bg-surface-container-lowest border border-outline-variant rounded-xl text-base focus:ring-2 focus:ring-primary focus:border-primary transition-all placeholder:text-on-secondary-container"
              />
            </div>
          </div>

          <!-- Password -->
          <div class="space-y-1 form-group">
            <label class="form-label text-xs text-on-surface-variant uppercase font-bold ml-1" style="letter-spacing: 0.08em;">
              Kata Sandi
            </label>
            <div class="relative group">
              <div class="input-icon absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant transition-colors">
                <span class="material-symbols-outlined" style="font-size: 20px;">lock</span>
              </div>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                required
                class="form-input block w-full h-11 pl-10 pr-10 bg-surface-container-lowest border border-outline-variant rounded-xl text-base focus:ring-2 focus:ring-primary focus:border-primary transition-all placeholder:text-on-secondary-container"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="password-toggle absolute inset-y-0 right-0 pr-3 flex items-center text-on-surface-variant"
              >
                <span class="material-symbols-outlined" style="font-size: 20px;">{{ showPassword ? 'visibility' : 'visibility_off' }}</span>
              </button>
            </div>
            <!-- Password Strength Indicator -->
            <div class="pt-1">
              <div class="flex gap-1 w-full bg-secondary-container rounded-full overflow-hidden">
                <div class="strength-meter h-1 rounded-full transition-all duration-300" :class="strengthLabel.barClass" :style="{ width: passwordStrength + '%' }"></div>
              </div>
              <p class="text-[11px] mt-1 font-medium" :class="strengthLabel.color">{{ strengthLabel.text }}</p>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="space-y-1 form-group">
            <label class="form-label text-xs text-on-surface-variant uppercase font-bold ml-1" style="letter-spacing: 0.08em;">
              Konfirmasi Kata Sandi
            </label>
            <div class="relative group">
              <div class="input-icon absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant transition-colors">
                <span class="material-symbols-outlined" style="font-size: 20px;">lock_reset</span>
              </div>
              <input
                v-model="form.confirmPassword"
                :type="showConfirmPassword ? 'text' : 'password'"
                placeholder="••••••••"
                required
                class="form-input block w-full h-11 pl-10 pr-10 bg-surface-container-lowest border border-outline-variant rounded-xl text-base focus:ring-2 focus:ring-primary focus:border-primary transition-all placeholder:text-on-secondary-container"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="password-toggle absolute inset-y-0 right-0 pr-3 flex items-center text-on-surface-variant"
              >
                <span class="material-symbols-outlined" style="font-size: 20px;">{{ showConfirmPassword ? 'visibility' : 'visibility_off' }}</span>
              </button>
            </div>
          </div>

          <!-- Info Box -->
          <div class="info-box bg-surface-container rounded-lg p-3 flex gap-3 items-start border border-primary/10">
            <span class="material-symbols-outlined text-primary flex-shrink-0" style="font-size: 20px;">info</span>
            <p class="text-xs text-on-surface-variant leading-relaxed">
              Dengan membuat akun, Anda akan dapat berpartisipasi dalam kuesioner evaluasi kebijakan lingkungan.
            </p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="btn-start w-full h-12 bg-primary text-white font-semibold rounded-xl shadow-lg shadow-primary/20 flex items-center justify-center gap-2 mt-1 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isLoading" class="animate-spin material-symbols-outlined" style="font-size: 20px;">progress_activity</span>
            <span v-if="isLoading">Memproses...</span>
            <template v-else>
              Buat Akun
              <span class="material-symbols-outlined" style="font-size: 20px;">arrow_forward</span>
            </template>
          </button>
        </form>

        <!-- Secondary Actions -->
        <footer class="mt-6 pt-6 border-t border-outline-variant/30 text-center">
          <p class="text-sm text-on-surface-variant">
            Sudah punya akun?
            <router-link to="/login" class="login-link text-primary font-bold">Masuk</router-link>
          </p>
          <div class="opacity-50 mt-4">
            <p class="text-xs uppercase text-outline" style="letter-spacing: 0.15em;">
              Developed by Environmental Policy Support Team
            </p>
          </div>
        </footer>
      </div>

      <!-- Mobile background icon -->
      <div class="lg:hidden absolute bottom-6 right-6 opacity-5 pointer-events-none">
        <span class="material-symbols-outlined" style="font-size: 100px;">nature_people</span>
      </div>
    </main>
  </div>
</template>

<style scoped>
.login-illustration-container {
  background: linear-gradient(135deg, #f4fbf4 0%, #eef6ee 100%);
  position: relative;
  overflow: hidden;
}

.login-illustration-container::before {
  content: '';
  position: absolute;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
  top: -100px;
  right: -100px;
  border-radius: 50%;
  animation: illustrationFloat 8s ease-in-out infinite;
}

.login-illustration-container::after {
  content: '';
  position: absolute;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.08) 0%, transparent 70%);
  bottom: -50px;
  left: -50px;
  border-radius: 50%;
  animation: illustrationFloat 10s ease-in-out infinite reverse;
}

@keyframes illustrationFloat {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(20px, -20px) scale(1.1); }
}

.brand-logo {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.brand-logo:hover {
  transform: scale(1.1) rotate(5deg);
}

.brand-name {
  transition: all 0.3s ease;
}

.brand-name:hover {
  color: #006c49;
}

.form-input {
  transition: all 0.3s ease;
}

.form-input:hover {
  border-color: #10b981;
  box-shadow: 0 2px 10px rgba(16, 185, 129, 0.1);
}

.form-input:focus {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.12);
}

.input-icon {
  transition: all 0.3s ease;
}

.form-group:focus-within .input-icon {
  color: #10b981;
  transform: scale(1.1);
}

.password-toggle {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.password-toggle:hover {
  color: #006c49;
  transform: scale(1.1);
}

.form-label {
  transition: all 0.3s ease;
}

.form-group:focus-within .form-label {
  color: #10b981;
}

.btn-start {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.btn-start:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
  background-color: #047857;
}

.btn-start:active:not(:disabled) {
  transform: scale(0.96);
}

.login-link {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.login-link:hover {
  color: #006c49;
  transform: translateX(4px);
}

.info-box {
  transition: all 0.3s ease;
}

.info-box:hover {
  border-color: #10b981;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1);
}

.decorative-circle {
  transition: all 0.4s ease;
}

.decorative-circle:hover {
  transform: scale(1.1);
}

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
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}

.gradient-text {
  background: linear-gradient(135deg, #006c49, #10b981);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.pulse-ring {
  animation: pulseRing 2s ease-in-out infinite;
}

@keyframes pulseRing {
  0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
  100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
</style>
