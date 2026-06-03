<script setup>
defineProps({
  message: { type: String, default: 'Chargement…' },
  compact: { type: Boolean, default: false },
})
</script>

<template>
  <div
    class="flex items-center justify-center text-slate-500"
    :class="compact ? 'py-6' : 'py-12'"
    role="status"
    aria-live="polite"
  >
    <svg
      class="h-8 w-8 animate-spin text-[#4294e3]"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
    </svg>
    <span class="ml-3 text-sm font-medium">{{ message }}</span>
  </div>
</template>
