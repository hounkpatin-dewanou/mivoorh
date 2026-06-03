<script setup>
defineProps({
  open: Boolean,
  title: String,
})
const emit = defineEmits(['close'])
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="emit('close')">
    <div class="card-mivoo max-h-[90vh] w-full max-w-md overflow-y-auto p-6">
      <h2 v-if="title" class="mb-4 text-lg font-bold text-[#262b47]">{{ title }}</h2>
      <slot />
      <div v-if="$slots.footer" class="mt-4 flex justify-end gap-2 border-t pt-4">
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>
