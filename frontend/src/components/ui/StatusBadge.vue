<script setup>
const props = defineProps({ status: String })

const classes = {
  pending: 'bg-amber-100 text-amber-800',
  approved: 'bg-emerald-100 text-emerald-800',
  refused: 'bg-red-100 text-red-800',
}
const labels = { pending: 'En attente', approved: 'Approuvée', refused: 'Refusée' }
</script>

<template>
  <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="classes[status] || 'bg-slate-100 text-slate-600'">
    {{ labels[status] || status }}
  </span>
</template>
