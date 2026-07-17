<script setup lang="ts">
import { ref } from 'vue'

// Duration
const defaultSession = ref(45)
const countdownAlert = ref(5)

// Logic toggles
const autoSave = ref(true)
const resumeOption = ref(true)

// Save handler
function handleSave() {
  const settings = {
    duration: {
      defaultSession: defaultSession.value,
      countdownAlert: countdownAlert.value,
    },
    logic: {
      autoSave: autoSave.value,
      resumeOption: resumeOption.value,
    },
  }
  console.log('Settings saved:', settings)
  // TODO: POST /api/v1/admin/settings
}
</script>

<template>
  <div class="p-12 max-w-[1840px] w-full mx-auto">
    <!-- Header -->
    <header class="mb-10 fade-in">
      <h2 class="font-headline-xl font-bold text-headline-xl text-on-surface mb-2">Platform Settings</h2>
      <p class="text-body-base text-secondary">Configure your evaluation environment and security preferences.</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Duration -->
      <section class="bg-white rounded-xl custom-shadow overflow-hidden border border-outline-variant/30 fade-in-delay">
        <div class="p-4 border-b border-outline-variant/30 bg-surface-container-low flex items-center gap-3">
          <span class="material-symbols-outlined text-primary">timer</span>
          <h3 class="font-title-md text-title-md">Duration</h3>
        </div>
        <div class="p-6 space-y-6">
          <div class="space-y-2">
            <label class="font-label-caps text-label-caps text-secondary block">Default Session (Minutes)</label>
            <input
              v-model.number="defaultSession"
              type="number"
              min="1"
              class="w-full h-10 px-4 bg-white border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:outline-none transition-all"
            />
          </div>
          <div class="space-y-2">
            <label class="font-label-caps text-label-caps text-secondary block">Countdown Alert (Minutes)</label>
            <input
              v-model.number="countdownAlert"
              type="number"
              min="1"
              class="w-full h-10 px-4 bg-white border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:outline-none transition-all"
            />
            <p class="text-body-sm text-secondary">Warning displayed before session ends.</p>
          </div>
        </div>
      </section>

      <!-- Logic -->
      <section class="bg-white rounded-xl custom-shadow overflow-hidden border border-outline-variant/30 fade-in-delay">
        <div class="p-4 border-b border-outline-variant/30 bg-surface-container-low flex items-center gap-3">
          <span class="material-symbols-outlined text-primary">schema</span>
          <h3 class="font-title-md text-title-md">Logic</h3>
        </div>
        <div class="p-6 space-y-6">
          <!-- Auto Save -->
          <div class="flex items-center justify-between">
            <div>
              <p class="font-body-base font-semibold">Auto Save</p>
              <p class="text-body-sm text-secondary">Save progress after every answer</p>
            </div>
            <label class="relative inline-block w-10 align-middle select-none cursor-pointer">
              <input v-model="autoSave" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-outline-variant rounded-full peer peer-checked:bg-primary transition-colors duration-200"></div>
              <div class="absolute left-[2px] top-[2px] w-5 h-5 bg-white rounded-full border border-gray-200 transition-transform duration-200 peer-checked:translate-x-full peer-checked:border-white shadow-sm"></div>
            </label>
          </div>
          <!-- Resume Option -->
          <div class="flex items-center justify-between">
            <div>
              <p class="font-body-base font-semibold">Resume Option</p>
              <p class="text-body-sm text-secondary">Allow returning to previous state</p>
            </div>
            <label class="relative inline-block w-10 align-middle select-none cursor-pointer">
              <input v-model="resumeOption" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-outline-variant rounded-full peer peer-checked:bg-primary transition-colors duration-200"></div>
              <div class="absolute left-[2px] top-[2px] w-5 h-5 bg-white rounded-full border border-gray-200 transition-transform duration-200 peer-checked:translate-x-full peer-checked:border-white shadow-sm"></div>
            </label>
          </div>
        </div>
      </section>
    </div>

    <!-- Save Button -->
    <div class="mt-8 flex justify-end">
      <button
        @click="handleSave"
        class="bg-primary text-white px-6 py-3 rounded-xl font-body-base font-semibold hover:bg-primary/90 active:scale-95 transition-all flex items-center gap-2 shadow-sm"
      >
        <span class="material-symbols-outlined text-[20px]">save</span>
        Simpan Perubahan
      </button>
    </div>
  </div>
</template>

<style scoped>
/* ===== CUSTOM SHADOW ===== */
.custom-shadow {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
}

/* ===== FADE IN ANIMATION ===== */
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
