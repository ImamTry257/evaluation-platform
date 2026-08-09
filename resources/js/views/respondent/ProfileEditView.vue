<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import StepHeader from './Component/StepHeader.vue'
import SearchableSelect from '@/components/SearchableSelect.vue'

const router = useRouter()
const authStore = useAuthStore()

// Regional lock DIY (BE paksa provinsi 34, list kabupaten/kota hanya DIY)
const REGENCY_PROVINCE_CODE = '34'

const form = reactive({
  name: '',
  type: '',
  cityCode: null as number | null,
  cityName: '',
})

const selectedRegency = ref('')
const respondentTypes = ref<any[]>([])
const regencies = ref<any[]>([])
const isLoadingTypes = ref(false)
const isLoadingRegencies = ref(false)
const isLoadingProfile = ref(true)
const isSaving = ref(false)
const errorMessage = ref('')
const fieldErrors = reactive<Record<string, string[]>>({})

const typeOptions = computed(() => respondentTypes.value.map(t => ({ value: t.title, label: t.title })))
const regencyOptions = computed(() => regencies.value.map(r => ({ value: r.code, label: r.name })))

const userName = computed(() => authStore.user?.name || 'Responden')
const userEmail = computed(() => authStore.user?.email || '')

function clearFieldError(field: string) {
  if (fieldErrors[field]) delete fieldErrors[field]
}

function onRegencyChange() {
  clearFieldError('cityCode')
  clearFieldError('cityName')
  const regency = regencies.value.find(r => r.code === selectedRegency.value)
  if (regency) {
    form.cityCode = parseInt(regency.code.replace('.', ''), 10)
    form.cityName = regency.name
  } else {
    form.cityCode = null
    form.cityName = ''
  }
}

async function fetchTypes() {
  isLoadingTypes.value = true
  try {
    const response = await api.get('/respondent-types')
    respondentTypes.value = response.data.data || []
  } catch (e) {
    errorMessage.value = 'Gagal memuat daftar tipe responden.'
  } finally {
    isLoadingTypes.value = false
  }
}

async function fetchRegencies() {
  isLoadingRegencies.value = true
  try {
    const response = await api.get(`/locations/regencies/${REGENCY_PROVINCE_CODE}`)
    regencies.value = response.data.data || []
  } catch (e) {
    errorMessage.value = 'Gagal memuat daftar kabupaten/kota.'
  } finally {
    isLoadingRegencies.value = false
  }
}

async function fetchProfile() {
  isLoadingProfile.value = true
  try {
    const response = await api.get('/auth/profile')
    const p = response.data.data
    form.name = p.name || ''
    form.type = p.type || ''
    form.cityCode = p.cityCode ?? null
    form.cityName = p.cityName || ''

    // Preselect kabupaten/kota berdasarkan cityCode user
    if (p.cityCode) {
      const found = regencies.value.find(r => r.code.replace('.', '') === String(p.cityCode))
      if (found) selectedRegency.value = found.code
    }
  } catch (e: any) {
    errorMessage.value = e.response?.data?.message || 'Gagal memuat profil.'
  } finally {
    isLoadingProfile.value = false
  }
}

async function handleSave() {
  if (!form.type) {
    errorMessage.value = 'Pilih tipe responden terlebih dahulu.'
    return
  }
  if (!form.cityCode || !form.cityName) {
    errorMessage.value = 'Pilih kabupaten/kota terlebih dahulu.'
    return
  }

  isSaving.value = true
  errorMessage.value = ''
  Object.keys(fieldErrors).forEach(k => delete fieldErrors[k])

  try {
    await api.put('/auth/profile', {
      name: form.name,
      type: form.type,
      cityCode: form.cityCode,
      cityName: form.cityName,
    })

    // Sinkronkan user di store + localStorage agar header ikut ter-update
    if (authStore.user) {
      const updated = { ...authStore.user, name: form.name, type: form.type }
      authStore.user = updated
      localStorage.setItem('userRespondent', JSON.stringify(updated))
    }

    router.push({ path: '/respondent/profile', query: { updated: '1' } })
  } catch (error: any) {
    const data = error?.response?.data
    if (data?.errors) {
      Object.assign(fieldErrors, data.errors)
    } else {
      errorMessage.value = data?.message || 'Terjadi kesalahan. Silakan coba lagi.'
    }
  } finally {
    isSaving.value = false
  }
}

async function handleLogout() {
  await authStore.logout()
  router.push('/')
}

onMounted(async () => {
  await Promise.all([fetchTypes(), fetchRegencies()])
  await fetchProfile()
})
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Loading -->
    <div v-if="isLoadingProfile" class="flex flex-col items-center justify-center py-32">
      <span class="material-symbols-outlined text-[48px] text-outline animate-spin">progress_activity</span>
      <p class="text-body-base text-on-surface-variant mt-4">Memuat profil...</p>
    </div>

    <template v-else>
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
          <button @click="router.push('/respondent/profile')"
            class="inline-flex items-center gap-1.5 text-[#004592] text-body-sm font-medium hover:bg-[#004592]/10 px-3 py-2 rounded-lg transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Profil
          </button>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-6 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-center gap-2 fade-in">
          <span class="material-symbols-outlined text-lg">error</span>
          {{ errorMessage }}
        </div>

        <!-- Edit Profile Card -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/20 p-4 md:p-6 mb-4 fade-in">
          <h3 class="font-title-md font-bold text-title-md text-on-surface flex items-center gap-2 mb-6">
            <span class="material-symbols-outlined text-[#004592]">edit</span> Edit Profil
          </h3>

          <form @submit.prevent="handleSave" class="space-y-5 max-w-xl">
            <!-- Nama Lengkap -->
            <div class="space-y-1 form-group">
              <label class="form-label text-xs text-on-surface-variant uppercase font-bold ml-1" style="letter-spacing: 0.08em;">
                Nama Lengkap
              </label>
              <div class="relative group">
                <div class="input-icon absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant transition-colors">
                  <span class="material-symbols-outlined" style="font-size: 20px;">person</span>
                </div>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="Nama lengkap"
                  @input="clearFieldError('name')"
                  :class="['form-input block w-full h-11 pl-10 pr-4 bg-surface-container-lowest border rounded-xl text-base focus:ring-2 focus:ring-[#004592] focus:border-[#004592] transition-all placeholder:text-on-secondary-container', fieldErrors.name ? 'border-error' : 'border-outline-variant']"
                />
              </div>
              <p v-if="fieldErrors.name" class="text-error text-xs flex items-center gap-1 mt-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">error</span>
                {{ fieldErrors.name[0] }}
              </p>
            </div>

            <!-- Tipe Responden -->
            <div class="space-y-1 form-group">
              <label class="form-label text-xs text-on-surface-variant uppercase font-bold ml-1" style="letter-spacing: 0.08em;">
                Tipe Responden
              </label>
              <SearchableSelect
                v-model="form.type"
                :options="typeOptions"
                :loading="isLoadingTypes"
                :error="!!fieldErrors.type"
                icon="school"
                placeholder="Pilih tipe responden"
                @change="clearFieldError('type')"
              />
              <p v-if="fieldErrors.type" class="text-error text-xs flex items-center gap-1 mt-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">error</span>
                {{ fieldErrors.type[0] }}
              </p>
            </div>

            <!-- Kabupaten/Kota (DIY) -->
            <div class="space-y-1 form-group">
              <label class="form-label text-xs text-on-surface-variant uppercase font-bold ml-1" style="letter-spacing: 0.08em;">
                Kabupaten/Kota
              </label>
              <SearchableSelect
                v-model="selectedRegency"
                :options="regencyOptions"
                :loading="isLoadingRegencies"
                :error="!!fieldErrors.cityCode"
                icon="location_city"
                placeholder="Pilih kabupaten/kota"
                @change="onRegencyChange()"
              />
              <p class="text-[11px] text-on-surface-variant ml-1 mt-1 flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 13px;">location_on</span>
                Provinsi: Daerah Istimewa Yogyakarta
              </p>
              <p v-if="fieldErrors.cityCode" class="text-error text-xs flex items-center gap-1 mt-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">error</span>
                {{ fieldErrors.cityCode[0] }}
              </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-2">
              <button
                type="submit"
                :disabled="isSaving"
                class="btn-start inline-flex items-center gap-2 px-6 py-3 bg-[#004592] text-white font-semibold rounded-xl shadow-lg shadow-[#004592]/20 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="isSaving" class="animate-spin material-symbols-outlined" style="font-size: 20px;">progress_activity</span>
                <span v-if="isSaving">Menyimpan...</span>
                <template v-else>
                  Simpan Perubahan
                  <span class="material-symbols-outlined" style="font-size: 20px;">save</span>
                </template>
              </button>
              <button
                type="button"
                @click="router.push('/respondent/profile')"
                class="px-6 py-3 rounded-xl border border-outline-variant text-on-surface font-medium hover:bg-surface-container-low transition-all"
              >
                Batal
              </button>
            </div>
          </form>
        </div>

      </main>
    </template>
  </div>
</template>

<style scoped>
.fade-in { animation: fadeIn 0.6s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>
