<script setup>
import { useToastStore } from '../../stores/toast'

const toast = useToastStore()

const styles = {
  success: 'border-emerald-200 bg-emerald-50 text-emerald-900',
  error: 'border-red-200 bg-red-50 text-red-900',
  info: 'border-[#4294e3]/30 bg-[#f0f6ff] text-[#262b47]',
}
</script>

<template>
  <div
    class="pointer-events-none fixed right-4 top-4 z-[100] flex w-full max-w-sm flex-col gap-2"
    aria-live="polite"
    aria-label="Notifications"
  >
    <transition-group name="toast">
      <div
        v-for="t in toast.items"
        :key="t.id"
        class="pointer-events-auto flex items-start justify-between gap-3 rounded-lg border px-4 py-3 text-sm shadow-lg"
        :class="styles[t.type] || styles.info"
        role="alert"
      >
        <span class="flex-1">{{ t.message }}</span>
        <button
          type="button"
          class="shrink-0 opacity-60 hover:opacity-100"
          aria-label="Fermer"
          @click="toast.remove(t.id)"
        >
          ×
        </button>
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(1rem);
}
</style>
