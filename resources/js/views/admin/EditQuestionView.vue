<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const form = ref({
  text: '',
  type: 'pilihan_ganda',
  weight: 10,
  indicator: '',
  status: 'aktif',
})

const typeOptions = [
  { value: 'pilihan_ganda', label: 'Pilihan Ganda' },
  { value: 'ya_tidak', label: 'Ya/Tidak' },
  { value: 'isian_bebas', label: 'Isian Bebas' },
  { value: 'skala_likert', label: 'Skala Likert' },
]

const indicatorOptions = [
  'Ketersediaan Tempat Sampah Terpisah',
  'Jadwal Pengangkutan Rutin',
  'Edukasi Civitas Akademika',
  'Kualitas Pemilahan di Sumber',
  'Integrasi ke Komposter',
  'Penggunaan Lampu LED',
  'Pemanfaatan Cahaya Alami',
  'Pengaturan Suhu AC',
  'Kualitas Air Bersih',
  'Kondisi Vegetasi Taman',
]

// Mock data - in real app, fetch from API by route.params.id
const mockQuestions: Record<string, any> = {
  '1': { text: 'Apakah sekolah memiliki tempat sampah organik khusus di setiap kelas?', type: 'pilihan_ganda', weight: 25, indicator: 'Ketersediaan Tempat Sampah Terpisah', status: 'aktif' },
  '2': { text: 'Berapakah jumlah tempat sampah komunal yang tersedia di area kantin?', type: 'pilihan_ganda', weight: 35, indicator: 'Ketersediaan Tempat Sampah Terpisah', status: 'aktif' },
  '3': { text: 'Apakah terdapat petunjuk visual (poster) pemilahan sampah pada setiap unit tempat sampah?', type: 'ya_tidak', weight: 20, indicator: 'Ketersediaan Tempat Sampah Terpisah', status: 'aktif' },
  '4': { text: 'Kapan terakhir kali dilakukan evaluasi terhadap kebersihan tempat sampah organik?', type: 'isian_bebas', weight: 20, indicator: 'Ketersediaan Tempat Sampah Terpisah', status: 'draft' },
}

const questionId = route.params.id as string

onMounted(() => {
  const data = mockQuestions[questionId]
  if (data) {
    form.value = { ...data }
  }
})

function handleSubmit() {
  // In real app, this would call an API
  console.log('Updating:', form.value)
  router.push('/admin/question')
}
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-on-surface-variant mb-6 fade-in">
      <router-link to="/admin/question" class="hover:text-primary transition-colors cursor-pointer">Question</router-link>
      <span class="material-symbols-outlined text-[16px]">chevron_right</span>
      <span class="text-on-surface font-medium">Edit Pertanyaan</span>
    </nav>

    <!-- Header Section -->
    <section class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4 fade-in">
      <div>
        <h2 class="font-headline-xl text-headline-xl text-on-surface">Edit Pertanyaan</h2>
        <p class="font-body-base text-body-base text-on-surface-variant mt-2 max-w-2xl">
          Ubah data pertanyaan evaluasi kebijakan lingkungan sekolah.
        </p>
      </div>
    </section>

    <!-- Form Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay max-w-3xl">
      <div class="p-6 border-b border-outline-variant/10 bg-surface-container-low/30">
        <h3 class="font-title-md text-title-md text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">edit_note</span>
          Form Pertanyaan
        </h3>
      </div>

      <div class="p-6 space-y-6">
        <!-- Teks Pertanyaan -->
        <div>
          <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Teks Pertanyaan <span class="text-error">*</span></label>
          <textarea
            v-model="form.text"
            rows="4"
            placeholder="Tuliskan pertanyaan evaluasi..."
            class="form-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
          ></textarea>
        </div>

        <!-- Tipe & Bobot -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Tipe Jawaban <span class="text-error">*</span></label>
            <div class="relative">
              <select
                v-model="form.type"
                class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
              >
                <option v-for="t in typeOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
              </select>
              <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-[20px]">expand_more</span>
            </div>
          </div>
          <div>
            <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Bobot (%) <span class="text-error">*</span></label>
            <input
              v-model.number="form.weight"
              type="number"
              min="1"
              max="100"
              placeholder="10"
              class="form-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
            />
          </div>
        </div>

        <!-- Indikator -->
        <div>
          <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Indikator <span class="text-error">*</span></label>
          <div class="relative">
            <select
              v-model="form.indicator"
              class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
            >
              <option value="">Pilih Indikator</option>
              <option v-for="ind in indicatorOptions" :key="ind" :value="ind">{{ ind }}</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-[20px]">expand_more</span>
          </div>
        </div>

        <!-- Status -->
        <div>
          <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
          <div class="relative">
            <select
              v-model="form.status"
              class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface appearance-none focus:ring-2 focus:ring-primary-container outline-none transition-all cursor-pointer"
            >
              <option value="aktif">Aktif</option>
              <option value="draft">Draft</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-[20px]">expand_more</span>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex items-center justify-end gap-3">
        <router-link
          to="/admin/question"
          class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors"
        >
          Batal
        </router-link>
        <button
          @click="handleSubmit"
          :disabled="!form.text || !form.indicator"
          class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Simpan Perubahan
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.form-input {
  transition: all 0.3s ease;
}
.form-input:focus {
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
  transform: translateY(-1px);
}

.fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
.fade-in-delay {
  animation: fadeIn 0.5s ease-out 0.1s forwards;
  opacity: 0;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
