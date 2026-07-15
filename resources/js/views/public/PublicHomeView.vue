<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div>
    <!-- Hero Section -->
    <section
      id="home"
      class="relative min-h-screen flex items-center justify-center overflow-hidden hero-bg"
      style="background: linear-gradient(135deg, #054b35 0%, #059669 50%, #34d399 100%);"
    >
      <div class="relative z-10 max-w-12xl mx-auto px-6 text-center">
        <!-- Title -->
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight text-white mb-6 fade-in">
          Evaluasi Kebijakan Lingkungan Sekolah
        </h1>

        <!-- Description -->
        <p class="text-lg text-emerald-100 mx-auto mb-8 leading-relaxed fade-in-delay max-w-3xl">
          Platform digital untuk mengevaluasi implementasi kebijakan lingkungan di sekolah melalui assessmen berbasis kuesioner terstruktur.
          <br />
          Pertumbuhan keberlanjutan institusi Anda dengan mudah.
        </p>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10 fade-in-delay-2">
          <RouterLink
            to="/login"
            class="press-scale px-8 py-4 bg-white text-emerald-700 rounded-2xl font-bold text-lg shadow-lg hover:bg-emerald-700 hover:text-white transition-all duration-500 flex items-center justify-center gap-3"
          >
            Mulai Evaluasi
            <span class="material-symbols-outlined">arrow_forward</span>
          </RouterLink>
          <RouterLink
            to="/register"
            class="press-scale px-8 py-4 border-2 border-white text-white rounded-2xl font-bold text-lg hover:bg-white hover:text-emerald-700 transition-all duration-500"
          >
            Daftar Sekolah
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-3xl font-bold text-center text-on-surface mb-12">
          Fitur Utama
        </h2>
        <div class="grid md:grid-cols-3 gap-8">
          <!-- Feature 1 -->
          <div class="float-hover p-6 bg-background rounded-2xl border border-outline text-center">
            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <span class="material-symbols-outlined text-primary text-3xl">assessment</span>
            </div>
            <h3 class="text-xl font-semibold text-on-surface mb-2">Evaluasi Terstruktur</h3>
            <p class="text-on-surface-variant">
              Instrumen evaluasi berbasis kuesioner dengan skala Likert untuk pengukuran yang akurat.
            </p>
          </div>

          <!-- Feature 2 -->
          <div class="float-hover p-6 bg-background rounded-2xl border border-outline text-center">
            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <span class="material-symbols-outlined text-primary text-3xl">analytics</span>
            </div>
            <h3 class="text-xl font-semibold text-on-surface mb-2">Analisis Otomatis</h3>
            <p class="text-on-surface-variant">
              Perhitungan skor, kategori, dan rekomendasi dilakukan secara otomatis oleh sistem.
            </p>
          </div>

          <!-- Feature 3 -->
          <div class="float-hover p-6 bg-background rounded-2xl border border-outline text-center">
            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <span class="material-symbols-outlined text-primary text-3xl">download</span>
            </div>
            <h3 class="text-xl font-semibold text-on-surface mb-2">Export Laporan</h3>
            <p class="text-on-surface-variant">
              Hasil evaluasi dapat di-export dalam format Excel dan PDF untuk dokumentasi.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-background">
      <div class="max-w-7xl mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold text-on-surface mb-6">Tentang Platform</h2>
        <p class="text-on-surface-variant max-w-3xl mx-auto text-lg leading-relaxed">
          Platform Evaluasi Kebijakan Lingkungan Sekolah dirancang untuk membantu institusi pendidikan
          dalam mengukur dan meningkatkan implementasi kebijakan lingkungan. Dengan instrumen evaluasi
          yang terstruktur dan analisis otomatis, platform ini memudahkan proses assessment keberlanjutan
          di sekolah.
        </p>
      </div>
    </section>
  </div>
</template>

<style scoped>
.hero-bg {
  position: relative;
  overflow: hidden;
}

.hero-bg::before {
  content: '';
  position: absolute;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(52, 211, 153, 0.3) 0%, transparent 70%);
  top: -200px;
  right: -200px;
  border-radius: 50%;
  animation: orbFloat 8s ease-in-out infinite;
}

.hero-bg::after {
  content: '';
  position: absolute;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(167, 243, 208, 0.2) 0%, transparent 70%);
  bottom: -100px;
  left: -100px;
  border-radius: 50%;
  animation: orbFloat 10s ease-in-out infinite reverse;
}

@keyframes orbFloat {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(30px, -30px) scale(1.1); }
}

.fade-in {
  animation: fadeIn 0.8s ease-out forwards;
}

.fade-in-delay {
  animation: fadeIn 0.8s ease-out 0.2s forwards;
  opacity: 0;
}

.fade-in-delay-2 {
  animation: fadeIn 0.8s ease-out 0.4s forwards;
  opacity: 0;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.float-hover {
  transition: transform 0.3s ease;
}

.float-hover:hover {
  transform: translateY(-4px);
}

.press-scale:active {
  transform: scale(0.96);
}
</style>
