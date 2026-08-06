<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterLink } from 'vue-router'

const isScrolled = ref(false)
const isMobileMenuOpen = ref(false)

function handleScroll() {
  isScrolled.value = window.scrollY > 50
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <div class="min-h-screen bg-background text-on-surface flex flex-col">
    <!-- Sticky Navbar -->
    <header
      class="fixed top-0 left-0 right-0 z-50 bg-white transition-all duration-300"
      :class="{ 'nav-scrolled': isScrolled }"
    >
      <nav class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <!-- Logo -->
        <RouterLink to="/" class="flex items-center gap-3 icon-hover-spin cursor-pointer">
          <div class="float-hover">
            <img
              src="https://www.uny.ac.id/sites/default/files/inline-images/logo-uny.png"
              alt="Logo UNY"
              class="w-10 h-10 object-contain"
            />
          </div>
          <span class="font-bold text-lg text-on-surface-variant hidden md:block leading-tight max-w-[500px]">
            Platform Evaluasi Implementasi Kebijakan Lingkungan
          </span>
        </RouterLink>

        <!-- Desktop Nav -->
        <div class="hidden lg:flex items-center gap-8">
          <RouterLink to="/" class="nav-link text-[#004592] font-semibold border-b-2 border-[#004592] pb-1 hover:-translate-y-0.5 transition-all duration-300">
            Home
          </RouterLink>
          <RouterLink to="/about" class="nav-link text-on-surface-variant hover:text-[#004592] hover:-translate-y-0.5 transition-all duration-300 font-medium">
            Tentang Kami
          </RouterLink>
          <!-- <div class="h-6 w-px bg-outline"></div> -->
          <!-- <div class="flex gap-3">
            <RouterLink
              to="/login"
              class="press-scale px-5 py-2.5 text-[#004592] font-semibold border-2 border-[#004592] rounded-xl hover:bg-[#004592] hover:text-white transition-all duration-500"
            >
              Login
            </RouterLink>
            <RouterLink
              to="/register"
              class="press-scale px-5 py-2.5 bg-[#004592] text-white rounded-xl font-semibold hover:bg-[#2f6fed] transition-all duration-500"
            >
              Daftar
            </RouterLink>
          </div> -->
        </div>

        <!-- Mobile Menu Button -->
        <button
          class="lg:hidden p-2 text-on-surface rounded-xl hover:bg-outline/50 transition-colors"
          @click="isMobileMenuOpen = !isMobileMenuOpen"
        >
          <span class="material-symbols-outlined">{{ isMobileMenuOpen ? 'close' : 'menu' }}</span>
        </button>
      </nav>

      <!-- Mobile Menu -->
      <div
        v-if="isMobileMenuOpen"
        class="lg:hidden bg-white px-6 py-4 space-y-4"
      >
        <RouterLink to="/" class="block text-[#004592] font-semibold" @click="isMobileMenuOpen = false">
          Home
        </RouterLink>
        <RouterLink to="/about" class="block text-on-surface-variant hover:text-[#004592] font-medium" @click="isMobileMenuOpen = false">
          About Us
        </RouterLink>
        <div class="pt-4 flex flex-col gap-3">
          <RouterLink
            to="/login"
            class="press-scale px-5 py-2.5 text-[#004592] font-semibold border-2 border-[#004592] rounded-xl hover:bg-[#004592] hover:text-white transition-all duration-500 text-center"
            @click="isMobileMenuOpen = false"
          >
            Login
          </RouterLink>
          <RouterLink
            to="/register"
            class="press-scale px-5 py-2.5 bg-[#004592] text-white rounded-xl font-semibold hover:bg-[#2f6fed] transition-all duration-500 text-center"
            @click="isMobileMenuOpen = false"
          >
            Register
          </RouterLink>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 pt-20">
      <RouterView />
    </main>

    <!-- Footer -->
    <footer class="bg-white py-8">
      <div class="max-w-7xl mx-auto px-6 text-center">
        <p class="text-on-surface-variant text-sm font-bold">
          © 2026 All rights reserved.
        </p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.nav-scrolled {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
}

.icon-hover-spin:hover .material-symbols-outlined {
  animation: spinOnce 0.5s ease;
}

@keyframes spinOnce {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
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

.nav-link {
  position: relative;
}

.nav-link::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 50%;
  width: 0;
  height: 2px;
  background: #004592;
  transition: all 0.3s ease;
  transform: translateX(-50%);
}

.nav-link:hover::after {
  width: 100%;
}
</style>
