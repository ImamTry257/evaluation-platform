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
