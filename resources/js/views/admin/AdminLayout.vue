<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterView, RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const authStore = useAuthStore()

const isActive = (path: string) => route.path === path

const navItems = [
  { path: '/admin', icon: 'dashboard', label: 'Dashboard' },
  { path: '/admin/period', icon: 'calendar_today', label: 'Period' },
  { path: '/admin/questionnaire', icon: 'description', label: 'Questionnaire' },
  { path: '/admin/component', icon: 'widgets', label: 'Component' },
  { path: '/admin/sub-component', icon: 'view_list', label: 'Sub Component' },
  { path: '/admin/indicator', icon: 'analytics', label: 'Indicator' },
  { path: '/admin/question', icon: 'quiz', label: 'Question' },
  { path: '/admin/respondent', icon: 'group', label: 'Respondent' },
  { path: '/admin/recommendation', icon: 'check_circle', label: 'Recommendation' },
  { path: '/admin/reports', icon: 'assessment', label: 'Reports' },
]
</script>

<template>
  <div class="min-h-screen bg-background text-on-surface">
    <!-- Sidebar Navigation -->
    <aside class="fixed left-0 top-0 h-full w-[280px] z-50 py-6 bg-surface-container-low border-r border-outline-variant/30 hidden md:flex flex-col">
      <div class="px-6 mb-10">
        <h1 class="font-headline-lg text-headline-lg font-bold text-primary tracking-tight">PolicyEval</h1>
        <p class="font-label-caps text-label-caps text-on-surface-variant mt-1">Environmental Admin</p>
      </div>
      <nav class="flex-1 space-y-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center gap-3 py-3 px-6 transition-all"
          :class="isActive(item.path)
            ? 'bg-surface-container-high text-primary font-bold border-l-4 border-primary'
            : 'text-on-surface-variant hover:bg-surface-container'"
        >
          <span class="material-symbols-outlined">{{ item.icon }}</span>
          <span class="font-body-base text-body-base">{{ item.label }}</span>
        </RouterLink>
        <div class="pt-4 mt-4 border-t border-outline-variant/20">
          <RouterLink
            to="/admin/settings"
            class="flex items-center gap-3 text-on-surface-variant py-3 px-6 hover:bg-surface-container transition-all"
          >
            <span class="material-symbols-outlined">settings</span>
            <span class="font-body-base text-body-base">Settings</span>
          </RouterLink>
        </div>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="md:ml-[280px] min-h-screen flex flex-col">
      <!-- Top Navigation Bar -->
      <header class="sticky top-0 w-full h-16 bg-surface/80 backdrop-blur-md border-b border-outline-variant/20 flex justify-between items-center px-lg z-40">
        <div class="flex items-center gap-4 flex-1">
          <div class="relative w-full max-w-md hidden sm:block">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
            <input
              class="search-input w-full bg-surface-container-low border border-outline-variant rounded-xl pl-10 pr-4 py-2 focus:ring-2 focus:ring-primary-container outline-none transition-all text-body-sm font-body-sm"
              placeholder="Cari data evaluasi..."
              type="text"
            />
          </div>
        </div>
        <div class="flex items-center gap-4">
          <button class="header-btn w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined">notifications</span>
          </button>
          <button class="header-btn w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined">help_outline</span>
          </button>
          <div class="h-8 w-px bg-outline-variant/30 mx-2"></div>
          <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
              <p class="font-label-caps text-label-caps text-on-surface">Admin Utama</p>
              <p class="text-[10px] text-on-surface-variant">System Master</p>
            </div>
            <img
              class="w-9 h-9 rounded-full object-cover border border-outline-variant"
              src="https://lh3.googleusercontent.com/aida-public/AB6AXuAV8jU_Ux58fsdINUMIhuKW5V3ln1Uz0Fw_LkVZes6LeNGcTFMOnTIT7ZneDBiQCoa03PuMLjvMqtMHrmzdZXColUokzKOL_IK8BIeEnqsnamtdDj5phLlrsDXs__FyWqtDoJfbjzu9uizJt8waHmyxwxq35kl8bZ1f1Lt_77RqR7hrY69CYiy5KCFd-AJ6ZozSyZ0Zz4Uv25U8Vhe8KbPGh6BkP2wpDu7QDmtU5Vlukirpi5s7EQ"
              alt="Admin avatar"
            />
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <div class="flex-1">
        <RouterView />
      </div>

      <!-- Footer -->
      <footer class="mt-auto py-8 px-8 border-t border-outline-variant/10 text-center">
        <p class="text-label-caps text-label-caps text-outline uppercase tracking-widest">
          PolicyEval © 2024 • Administrator Platform • v2.4.0
        </p>
      </footer>
    </main>
  </div>
</template>

<style scoped>
/* ===== INTERACTIVE CSS STYLING ===== */
.header-btn {
  transition: all 0.3s ease;
  border-radius: 50%;
}
.header-btn:hover {
  background-color: #e3eae3;
  transform: scale(1.1);
}
.header-btn:active {
  transform: scale(0.95);
}
.search-input {
  transition: all 0.3s ease;
}
.search-input:focus {
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
  transform: translateY(-1px);
}
</style>
