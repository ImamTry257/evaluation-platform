<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useLoginAdmin } from '@/hooks/useLoginAdmin'

const { isLoading, errorMessage, login, fieldErrors, clearFieldError } = useLoginAdmin()

const showPassword = ref(false)

const form = reactive({
  email: '',
  password: '',
})

async function handleLogin() {
  isLoading.value = true
  errorMessage.value = ''
  Object.keys(fieldErrors).forEach(k => delete fieldErrors[k])

  await login(form.email, form.password)
}
</script>

<template>
  <div class="h-full flex items-center justify-center px-4 bg-surface selection:bg-primary-fixed selection:text-on-primary-container">
    <div class="w-full max-w-[400px]">

      <!-- Brand Header -->
      <header class="mb-8 text-center fade-in">
        <div class="flex items-center justify-center gap-2.5 mb-4">
          <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center shadow-sm">
            <span class="material-symbols-outlined text-white text-[24px]" style="font-variation-settings: 'FILL' 1;">eco</span>
          </div>
          <span class="font-headline-lg text-[22px] tracking-tight text-primary font-bold">PolicyEval</span>
        </div>
        <!-- Accent line -->
        <div class="h-[3px] w-12 mx-auto rounded-full mb-6" style="background: linear-gradient(90deg, #10b981, #006c49);"></div>
      </header>

      <!-- Login Card -->
      <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30 p-8 fade-in-delay">
        <!-- Title -->
        <div class="mb-6">
          <h1 class="font-headline-lg text-headline-lg text-on-surface leading-tight mb-1">
            Masuk
          </h1>
          <p class="font-body-sm text-body-sm text-on-surface-variant">
            Akses panel administrasi evaluasi kebijakan.
          </p>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 font-body-sm text-body-sm flex items-center gap-2">
          <span class="material-symbols-outlined text-[18px]">error</span>
          {{ errorMessage }}
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-5">
          <!-- Email Field -->
          <div class="space-y-1.5 form-group">
            <label class="font-label-caps text-[11px] text-on-surface-variant uppercase tracking-[0.08em] font-bold ml-1" for="email">
              Email
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-outline transition-colors">
                <span class="material-symbols-outlined text-[18px]">mail</span>
              </div>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="admin@gmail.com"
                @input="clearFieldError('email')"
                :class="[
                  'form-input w-full h-11 pl-11 pr-4 bg-surface border rounded-xl font-body-base text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none placeholder:text-outline-variant/60',
                  fieldErrors.email ? 'border-error' : 'border-outline-variant'
                ]"
              />
            </div>
            <p v-if="fieldErrors.email" class="text-error font-body-sm text-body-sm flex items-center gap-1 mt-1">
              <span class="material-symbols-outlined text-[16px]">error</span>
              {{ fieldErrors.email[0] }}
            </p>
          </div>

          <!-- Password Field -->
          <div class="space-y-1.5 form-group">
            <label class="font-label-caps text-[11px] text-on-surface-variant uppercase tracking-[0.08em] font-bold ml-1" for="password-admin">
              Kata Sandi
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-outline transition-colors">
                <span class="material-symbols-outlined text-[18px]">lock</span>
              </div>
              <input
                id="password-admin"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                @input="clearFieldError('password')"
                :class="[
                  'form-input w-full h-11 pl-11 pr-11 bg-surface border rounded-xl font-body-base text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none placeholder:text-outline-variant/60',
                  fieldErrors.password ? 'border-error' : 'border-outline-variant'
                ]"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-outline transition-colors hover:text-primary-dark"
              >
                <span class="material-symbols-outlined text-[18px]">{{ showPassword ? 'visibility' : 'visibility_off' }}</span>
              </button>
            </div>
            <p v-if="fieldErrors.password" class="text-error font-body-sm text-body-sm flex items-center gap-1 mt-1">
              <span class="material-symbols-outlined text-[16px]">error</span>
              {{ fieldErrors.password[0] }}
            </p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full h-12 bg-primary-container text-on-primary rounded-xl font-title-md flex items-center justify-center gap-2 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 hover:bg-primary-dark active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:transform-none disabled:hover:shadow-none mt-2"
          >
            <span v-if="isLoading" class="animate-spin material-symbols-outlined text-[18px]">progress_activity</span>
            <span v-if="!isLoading">Masuk</span>
            <span v-if="!isLoading" class="material-symbols-outlined text-[18px]">arrow_forward</span>
          </button>
        </form>
      </div>

      <!-- Footer -->
      <footer class="mt-6 text-center fade-in-delay-2">
        <p class="font-body-sm text-body-sm text-on-surface-variant">
          Kembali ke
          <router-link to="/login" class="text-primary font-semibold transition-colors hover:text-primary-dark">halaman responden</router-link>
        </p>
      </footer>

    </div>
  </div>
</template>

<style scoped>
/* ===== FORM INPUTS ===== */
.form-input {
  transition: all 0.3s ease;
}
.form-input:hover {
  border-color: #10b981;
  box-shadow: 0 2px 10px rgba(16, 185, 129, 0.1);
}
.form-input:focus {
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.12);
}

/* ===== FORM GROUP ===== */
.form-group:focus-within label {
  color: #10b981;
}
.form-group:focus-within .text-outline {
  color: #10b981;
}

/* ===== ANIMATIONS ===== */
.fade-in { animation: fadeIn 0.5s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.5s ease-out 0.1s forwards; opacity: 0; }
.fade-in-delay-2 { animation: fadeIn 0.5s ease-out 0.2s forwards; opacity: 0; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
