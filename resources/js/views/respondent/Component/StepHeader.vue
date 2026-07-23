<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
  currentStep?: number
  userName: string
  userEmail: string
  showSteps?: boolean
}>()

const emit = defineEmits<{
  logout: []
  goProfile: []
  navigateStep: [step: number]
}>()

const showUserMenu = ref(false)

const steps = [
  { number: 1, label: 'Penjelasan' },
  { number: 2, label: 'Input Angket' },
  { number: 3, label: 'Hasil' },
]

function handleLogout() {
  showUserMenu.value = false
  emit('logout')
}

function handleProfile() {
  showUserMenu.value = false
  emit('goProfile')
}
</script>

<template>
  <div class="fixed top-0 left-0 right-0 z-[200] bg-surface/80 backdrop-blur-md border-b border-outline-variant">
    <div class="max-w-12xl mx-auto px-6 py-2.5 flex items-center justify-between">
      <!-- Brand -->
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-2xl">eco</span>
        <span class="font-title-md text-title-md font-bold text-primary hidden sm:inline">EcoPolicy</span>
      </div>

      <!-- Step Indicator -->
      <div v-if="showSteps !== false" class="flex items-center gap-0">
        <template v-for="(step, index) in steps" :key="step.number">
          <button
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
            :class="currentStep === step.number
              ? 'bg-primary text-white cursor-default'
              : 'text-on-surface-variant hover:bg-surface-container cursor-pointer'"
            @click="emit('navigateStep', step.number)"
          >
            <span
              class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
              :class="currentStep === step.number
                ? 'bg-white text-primary border-white'
                : 'border border-current'"
            >
              {{ step.number }}
            </span>
            <span class="hidden md:inline">{{ step.label }}</span>
          </button>
          <div v-if="index < steps.length - 1" class="w-6 h-px mx-1 bg-outline-variant"></div>
        </template>
      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-4">
        <!-- Extra content slot (timer, saved indicator, etc.) -->
        <slot name="extra" />

        <!-- User Menu -->
        <div class="relative">
          <button @click="showUserMenu = !showUserMenu"
            class="w-9 h-9 rounded-full bg-primary-container/20 border-2 border-outline-variant/50 flex items-center justify-center hover:shadow-md transition-all">
            <span class="material-symbols-outlined text-primary text-[20px]">person</span>
          </button>
          <div v-if="showUserMenu"
            class="absolute top-full right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-outline-variant/30 py-1 z-[300]"
            @click.self="showUserMenu = false">
            <div class="px-4 py-3 border-b border-outline-variant/30">
              <p class="font-body-base font-semibold text-on-surface text-sm">{{ userName }}</p>
              <p class="text-xs text-on-surface-variant">{{ userEmail }}</p>
            </div>
            <div class="py-1">
              <button @click="handleProfile" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface-variant hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined text-[18px]">person</span> Profil Saya
              </button>
              <div class="my-1 border-t border-outline-variant/30"></div>
              <button @click="handleLogout" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-error hover:bg-red-50 transition-colors font-medium">
                <span class="material-symbols-outlined text-[18px]">logout</span> Keluar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
