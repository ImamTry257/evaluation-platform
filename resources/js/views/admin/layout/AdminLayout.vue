<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterView, RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const isDropdownOpen = ref(false)

function toggleDropdown() {
  isDropdownOpen.value = !isDropdownOpen.value
}

function closeDropdown() {
  isDropdownOpen.value = false
}

async function handleLogout() {
  closeDropdown()
  await authStore.logout()
  router.push({ name: 'login-admin' })
}

function onClickOutside(e: MouseEvent) {
  const wrapper = document.getElementById('userMenuWrapper')
  if (wrapper && !wrapper.contains(e.target as Node)) {
    closeDropdown()
  }
}

onMounted(() => document.addEventListener('click', onClickOutside))
onUnmounted(() => document.removeEventListener('click', onClickOutside))

const isSuperAdmin = computed(() => authStore.user?.role === 'SUPERADMIN')
const userName = computed(() => authStore.user?.name || 'Admin')
const userRole = computed(() => authStore.user?.role === 'SUPERADMIN' ? 'Super Administrator' : 'Administrator')

const isActive = (path: string) => route.path === path

const navGroups = computed(() => {
  const groups = []

  // Master Admin - only for SUPERADMIN
  if (isSuperAdmin.value) {
    groups.push({
      label: 'Master Admin',
      items: [
        { path: '/admin/master/user', icon: 'manage_accounts', label: 'Manajemen User', badge: null },
      ],
    })
  }

  groups.push(
    {
      label: 'Master Data',
      items: [
        { path: '/admin/period', icon: 'calendar_today', label: 'Period', badge: null },
        { path: '/admin/instrument', icon: 'assignment', label: 'Instrument Penelitian', badge: null },
      ],
    },
    {
      label: 'Pelaksanaan',
      items: [
        { path: '/admin/respondent', icon: 'groups', label: 'Responden', badge: null },
        // { path: '/admin/monitoring', icon: 'monitoring', label: 'Monitoring', badge: null },
        { path: '/admin/reports', icon: 'assessment', label: 'Reports', badge: null },
      ],
    },
    // {
    //   label: 'Lainnya',
    //   items: [
    //     { path: '/admin/reports', icon: 'assessment', label: 'Reports', badge: null },
    //   ],
    // }
  )

  return groups
})
</script>

<template>
  <div class="min-h-screen bg-background text-on-surface">
    <!-- Sidebar Navigation -->
    <aside class="fixed left-0 top-0 h-full w-[280px] z-50 py-6 bg-surface-container-low border-r border-outline-variant/30 hidden md:flex flex-col">
      <!-- Logo -->
      <div class="px-6 mb-8">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
            <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">eco</span>
          </div>
          <div>
            <h1 class="font-headline-lg text-headline-lg font-bold text-primary leading-none">PolicyEval</h1>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Environmental Policy</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-1 px-4">
        <!-- Dashboard -->
        <RouterLink
          to="/admin"
          class="flex items-center gap-3 py-3 px-4 rounded-lg transition-all"
          :class="isActive('/admin')
            ? 'bg-primary-container/10 text-primary font-semibold border-l-4 border-primary'
            : 'text-on-surface-variant hover:bg-surface-container'"
        >
          <span class="material-symbols-outlined">dashboard</span>
          <span class="font-body-base text-body-base">Dashboard</span>
        </RouterLink>

        <!-- Navigation Groups -->
        <template v-for="group in navGroups" :key="group.label">
          <div class="pt-4 pb-1 px-2">
            <p class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider">{{ group.label }}</p>
          </div>
          <RouterLink
            v-for="item in group.items"
            :key="item.path"
            :to="item.path"
            class="flex items-center gap-3 py-3 px-4 rounded-lg transition-all"
            :class="isActive(item.path)
              ? 'bg-primary-container/10 text-primary font-semibold border-l-4 border-primary'
              : 'text-on-surface-variant hover:bg-surface-container'"
          >
            <span class="material-symbols-outlined">{{ item.icon }}</span>
            <span class="font-body-base text-body-base">{{ item.label }}</span>
            <span
              v-if="item.badge"
              class="ml-auto bg-primary-container/20 text-on-primary-container text-[10px] font-semibold px-2 py-0.5 rounded-full"
            >
              {{ item.badge }}
            </span>
          </RouterLink>
        </template>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="md:ml-[280px] min-h-screen flex flex-col">
      <!-- Top Navigation Bar -->
      <header class="sticky top-0 w-full h-16 bg-surface/80 backdrop-blur-md border-b border-outline-variant/20 flex justify-between items-center px-lg z-40">
        <div class="flex items-center gap-4 flex-1">
        </div>
        <div class="flex items-center gap-4">
          <!-- User Menu Dropdown -->
          <div id="userMenuWrapper" class="relative">
            <button
              @click="toggleDropdown"
              class="flex items-center gap-3 px-4 py-1.5 rounded-lg transition-colors hover:bg-surface-container"
              :class="{ 'bg-surface-container': isDropdownOpen }"
            >
              <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined" style="font-size: 20px;">account_circle</span>
              </div>
              <span class="font-body-sm text-body-sm font-medium hidden sm:block">{{ userName }}</span>
              <span class="material-symbols-outlined text-on-surface-variant text-base transition-transform" :class="{ 'rotate-180': isDropdownOpen }">expand_more</span>
            </button>

            <!-- Dropdown Menu -->
            <Transition
              enter-active-class="transition ease-out duration-200"
              enter-from-class="opacity-0 scale-95 -translate-y-1"
              enter-to-class="opacity-100 scale-100 translate-y-0"
              leave-active-class="transition ease-in duration-150"
              leave-from-class="opacity-100 scale-100 translate-y-0"
              leave-to-class="opacity-0 scale-95 -translate-y-1"
            >
              <div
                v-if="isDropdownOpen"
                class="absolute top-full right-0 mt-2 min-w-[200px] bg-white rounded-xl shadow-lg border border-outline-variant/50 py-1 z-50"
              >
                <div class="px-4 py-3 border-b border-outline-variant/30">
                  <p class="font-body-sm text-sm font-semibold text-on-surface">{{ userName }}</p>
                  <p class="text-xs text-on-surface-variant">{{ userRole }}</p>
                </div>
                <div class="py-1">
                  <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors"
                  >
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span class="font-medium">Keluar</span>
                  </button>
                </div>
              </div>
            </Transition>
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
