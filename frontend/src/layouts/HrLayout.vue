<script setup>
import AppLayout from '../components/layout/AppLayout.vue'

const links = [
  { to: '/hr/dashboard', label: 'Dashboard' },
  { to: '/hr/employees', label: 'Employés' },
  { to: '/hr/requests', label: 'Demandes' },
  { to: '/hr/export', label: 'Export CSV' },
  { to: '/hr/profile', label: 'Mon profil' },
]
</script>

<template>
  <AppLayout :links="links" title="Espace RH">
    <router-view />
  </AppLayout>
</template>
