<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

const form = reactive({
  username: '',
  password: '',
  rememberMe: false,
})

async function handleLogin() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    await authStore.login(form.username, form.password)

    if (authStore.user?.role === 'ADMIN') {
      router.push('/admin')
    } else {
      router.push('/respondent')
    }
  } catch (error: any) {
    errorMessage.value = error?.response?.data?.message || 'Terjadi kesalahan. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="h-full flex flex-col md:flex-row overflow-hidden bg-surface selection:bg-primary-fixed selection:text-on-primary-container">
    <!-- Left Column: Illustration -->
    <section class="hidden lg:flex relative flex-1 login-illustration-container items-center justify-center p-8 overflow-hidden border-r border-outline-variant/30">
      <div class="relative z-10 w-full max-w-2xl flex flex-col items-center justify-center">
        <div class="text-center px-6">
          <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2 fade-in gradient-text">Mewujudkan Sekolah Hijau</h2>
          <p class="font-body-base text-body-base text-on-surface-variant max-w-md mx-auto fade-in-delay">
            Mendorong transformasi institusi pendidikan melalui instrumen evaluasi kebijakan yang presisi dan berkelanjutan.
          </p>
        </div>
      </div>
      <!-- Decorative circle -->
      <div class="decorative-circle absolute -top-24 -left-24 w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
      <div class="decorative-circle absolute -bottom-24 -right-24 w-96 h-96 bg-primary/10 rounded-full blur-3xl"></div>
    </section>

    <!-- Right Column: Login Form -->
    <main class="w-full lg:w-1/2 h-full bg-surface-container-lowest flex flex-col justify-center items-center px-6 md:px-8 relative">
      <div class="w-full max-w-[440px] py-8">
        <!-- Brand Header -->
        <header class="mb-8 text-center">
          <div class="flex items-center justify-center gap-2.5 mb-6 fade-in">
            <div class="brand-logo w-11 h-11 bg-primary rounded-xl flex items-center justify-center shadow-sm pulse-ring">
              <span class="material-symbols-outlined text-white text-[28px]" style="font-variation-settings: 'FILL' 1;">eco</span>
            </div>
            <span class="brand-name font-headline-lg text-[24px] tracking-tight text-primary font-bold">PolicyEval</span>
          </div>
          <h1 class="font-headline-lg text-headline-lg text-on-surface leading-tight mb-2 fade-in-delay">
            Selamat Datang Kembali
          </h1>
          <p class="font-body-sm text-body-sm text-on-surface-variant max-w-sm mx-auto fade-in-delay-2">
            Platform Evaluasi Kebijakan Lingkungan Sekolah. Silakan masuk untuk mengelola data evaluasi sekolah Anda.
          </p>
        </header>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 font-body-sm text-body-sm flex items-center gap-2 fade-in">
          <span class="material-symbols-outlined text-[20px]">error</span>
          {{ errorMessage }}
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-6 fade-in-delay-3">
          <!-- Username Field -->
          <div class="space-y-1.5 form-group">
            <label class="form-label font-label-caps text-[11px] text-on-surface-variant uppercase tracking-[0.08em] font-bold ml-1" for="username">
              Nama Pengguna
            </label>
            <div class="relative group">
              <div class="input-icon absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-outline transition-colors">
                <span class="material-symbols-outlined text-[20px]">person</span>
              </div>
              <input
                id="username"
                v-model="form.username"
                type="text"
                placeholder="nama@sekolah.id"
                required
                class="form-input w-full h-12 pl-12 pr-4 bg-surface border border-outline-variant rounded-xl font-body-base text-body-base focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none placeholder:text-outline-variant/60"
              />
            </div>
          </div>

          <!-- Password Field -->
          <div class="space-y-1.5 form-group">
            <label class="form-label font-label-caps text-[11px] text-on-surface-variant uppercase tracking-[0.08em] font-bold ml-1" for="password">
              Kata Sandi
            </label>
            <div class="relative group">
              <div class="input-icon absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-outline transition-colors">
                <span class="material-symbols-outlined text-[20px]">lock</span>
              </div>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                required
                class="form-input w-full h-12 pl-12 pr-12 bg-surface border border-outline-variant rounded-xl font-body-base text-body-base focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none placeholder:text-outline-variant/60"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="password-toggle absolute inset-y-0 right-0 pr-4 flex items-center text-outline"
              >
                <span class="material-symbols-outlined text-[20px]">{{ showPassword ? 'visibility' : 'visibility_off' }}</span>
              </button>
            </div>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between font-body-sm text-body-sm">
            <label class="flex items-center cursor-pointer group">
              <input
                v-model="form.rememberMe"
                type="checkbox"
                class="checkbox-custom peer h-4.5 w-4.5 rounded border-outline-variant text-primary focus:ring-primary cursor-pointer"
              />
              <span class="ml-2.5 text-on-surface-variant group-hover:text-on-surface select-none transition-colors">Ingat Saya</span>
            </label>
            <a class="forgot-link text-primary font-semibold" href="#">Lupa Kata Sandi?</a>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="btn-start w-full h-13 bg-primary-container text-on-primary rounded-xl font-title-md text-title-md flex items-center justify-center gap-2 mt-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isLoading" class="animate-spin material-symbols-outlined text-[20px]">progress_activity</span>
            <span v-if="isLoading" class="ml-2">Memproses...</span>
            <template v-else>
              Masuk ke Platform
              <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
            </template>
          </button>
        </form>

        <!-- Secondary Actions -->
        <footer class="mt-12 pt-8 border-t border-outline-variant/30 text-center">
          <p class="font-body-sm text-body-sm text-on-surface-variant mb-6">
            Butuh bantuan akses? <a class="help-link text-primary font-bold" href="#">Hubungi Admin</a>
          </p>
          <!-- Developer attribution -->
          <div class="opacity-50">
            <p class="font-label-caps text-[10px] uppercase tracking-[0.15em] text-outline">
              Developed by Environmental Policy Support Team
            </p>
          </div>
        </footer>
      </div>

      <!-- Mobile subtle background icon -->
      <div class="lg:hidden absolute bottom-6 right-6 opacity-5 pointer-events-none mobile-icon">
        <span class="material-symbols-outlined text-[100px]">nature_people</span>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* ===== LOGIN ILLUSTRATION ===== */
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

/* ===== BRAND HEADER ===== */
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

/* ===== FORM INPUTS ===== */
.form-input {
  transition: all 0.3s ease;
}
.form-input:hover {
  border-color: #10b981;
  box-shadow: 0 2px 10px rgba(16, 185, 129, 0.1);
}
.form-input:focus {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(16, 185, 129, 0.15);
}

/* ===== INPUT ICON ===== */
.input-icon {
  transition: all 0.3s ease;
}
.form-group:focus-within .input-icon {
  color: #10b981;
  transform: scale(1.1);
}

/* ===== PASSWORD TOGGLE ===== */
.password-toggle {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.password-toggle:hover {
  color: #006c49;
  transform: scale(1.1);
}

/* ===== CHECKBOX ===== */
.checkbox-custom {
  transition: all 0.3s ease;
}
.checkbox-custom:hover {
  border-color: #10b981;
}

/* ===== LABEL ===== */
.form-label {
  transition: all 0.3s ease;
}
.form-group:focus-within .form-label {
  color: #10b981;
}

/* ===== FORGOT PASSWORD LINK ===== */
.forgot-link {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.forgot-link:hover {
  color: #006c49;
  transform: translateX(4px);
}

/* ===== SUBMIT BUTTON ===== */
.btn-start {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.btn-start:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
  background-color: #006c49;
}
.btn-start:active:not(:disabled) {
  transform: scale(0.96);
}

/* ===== HELP LINK ===== */
.help-link {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.help-link:hover {
  color: #006c49;
  transform: translateX(4px);
}

/* ===== DECORATIVE CIRCLES ===== */
.decorative-circle {
  transition: all 0.4s ease;
}
.decorative-circle:hover {
  transform: scale(1.1);
}

/* ===== MOBILE ICON ===== */
.mobile-icon {
  transition: all 0.3s ease;
}
.mobile-icon:hover {
  transform: scale(1.1);
}

/* ===== FADE IN ANIMATIONS ===== */
.fade-in { animation: fadeIn 0.6s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.6s ease-out 0.15s forwards; opacity: 0; }
.fade-in-delay-2 { animation: fadeIn 0.6s ease-out 0.3s forwards; opacity: 0; }
.fade-in-delay-3 { animation: fadeIn 0.6s ease-out 0.45s forwards; opacity: 0; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

/* ===== GRADIENT TEXT ===== */
.gradient-text {
  background: linear-gradient(135deg, #006c49, #10b981);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* ===== PULSE RING ===== */
.pulse-ring {
  animation: pulseRing 2s ease-in-out infinite;
}
@keyframes pulseRing {
  0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
  100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
</style>
