<script setup>
defineProps({
  name: {
    type: String,
    required: true,
    validator: (v) => ['hr', 'employee', 'admin'].includes(v),
  },
})
</script>

<template>
  <div
    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-mivoo-gradient text-white shadow-md shadow-[#4294e3]/25"
    aria-hidden="true"
  >
    <!-- Responsable RH — bâtiment / organisation -->
    <svg v-if="name === 'hr'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 9h.01M15 9h.01" />
    </svg>
    <!-- Employé — utilisateur -->
    <svg v-else-if="name === 'employee'" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
      <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    </svg>
    <!-- Super Admin — bouclier -->
    <svg v-else class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
    </svg>
  </div>
</template>
