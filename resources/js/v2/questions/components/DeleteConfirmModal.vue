<script setup lang="ts">
import type { Question } from '@/v2/questions/types/question'

defineProps<{
  show: boolean
  question: Question | null
  error?: string | null
  loading?: boolean
}>()

const emit = defineEmits<{
  confirm: []
  cancel: []
}>()
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4" @click.self="emit('cancel')">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="emit('cancel')"></div>
        <div class="relative bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-md z-10 modal-content">
          <!-- Header -->
          <div class="flex items-center justify-between p-6 pb-0">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-error/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-error">delete</span>
              </div>
              <h3 class="font-title-md text-title-md text-on-surface">Hapus Pernyataan</h3>
            </div>
            <button class="w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors" @click="emit('cancel')">
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>

          <!-- Body -->
          <div class="p-6">
            <p class="text-body-base text-on-surface-variant mb-4">Anda yakin ingin menghapus Pernyataan ini?</p>
            <p class="text-body-sm text-error mt-3 flex items-center gap-1.5" :class="{ 'mb-0': !error }">
              <span class="material-symbols-outlined text-[16px]">warning</span>
              Tindakan ini tidak dapat dibatalkan.
            </p>
            <p v-if="error" class="text-body-sm text-error mt-3 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px]">error</span>
              {{ error }}
            </p>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 p-6 pt-0">
            <button
              class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors"
              @click="emit('cancel')"
            >
              Batal
            </button>
            <button
              class="px-5 py-2.5 rounded-xl bg-error text-on-error font-body-base font-semibold shadow-sm transition-all hover:bg-error/90 active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="loading"
              @click="emit('confirm')"
            >
              <span v-if="loading" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
              <span v-else class="material-symbols-outlined text-[18px]">delete</span>
              {{ loading ? 'Menghapus...' : 'Ya, Hapus' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-content { animation: modalSlideIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
@keyframes modalSlideIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
