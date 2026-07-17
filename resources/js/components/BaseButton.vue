<script setup lang="ts">
interface Props {
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost' | 'outline'
  size?: 'sm' | 'md' | 'lg'
  icon?: string
  iconPosition?: 'left' | 'right'
  loading?: boolean
  disabled?: boolean
  type?: 'button' | 'submit' | 'reset'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  iconPosition: 'left',
  loading: false,
  disabled: false,
  type: 'button',
})

const emit = defineEmits<{
  click: [event: MouseEvent]
}>()

function handleClick(event: MouseEvent) {
  if (!props.loading && !props.disabled) {
    emit('click', event)
  }
}
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center gap-2 font-medium rounded-xl transition-all active:scale-95',
      'disabled:opacity-50 disabled:cursor-not-allowed',
      // Variants
      variant === 'primary' && 'bg-primary text-on-primary hover:bg-primary/90 shadow-sm',
      variant === 'secondary' && 'bg-surface-container-low text-on-surface border border-outline-variant hover:bg-surface-container',
      variant === 'danger' && 'bg-error text-on-error hover:bg-error/90 shadow-sm',
      variant === 'ghost' && 'text-on-surface-variant hover:bg-surface-container',
      variant === 'outline' && 'bg-white text-on-surface border border-outline-variant hover:bg-surface-container-low',
      // Sizes
      size === 'sm' && 'px-3 py-1.5 text-body-sm',
      size === 'md' && 'px-5 py-2.5 text-body-base',
      size === 'lg' && 'px-6 py-3 text-body-base',
    ]"
    @click="handleClick"
  >
    <!-- Loading Spinner -->
    <span
      v-if="loading"
      class="material-symbols-outlined animate-spin"
      :class="size === 'sm' ? 'text-[14px]' : 'text-[18px]'"
    >
      progress_activity
    </span>

    <!-- Icon Left -->
    <span
      v-else-if="icon && iconPosition === 'left'"
      class="material-symbols-outlined"
      :class="size === 'sm' ? 'text-[16px]' : 'text-[18px]'"
    >
      {{ icon }}
    </span>

    <!-- Slot Content -->
    <slot />

    <!-- Icon Right -->
    <span
      v-if="icon && iconPosition === 'right' && !loading"
      class="material-symbols-outlined"
      :class="size === 'sm' ? 'text-[16px]' : 'text-[18px]'"
    >
      {{ icon }}
    </span>
  </button>
</template>
