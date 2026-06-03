<script setup>
import AppLayout from '../components/layout/AppLayout.vue'

const links = [
  { to: '/admin/companies', label: 'Entreprises' },
  { to: '/admin/employees', label: 'Employés' },
  { to: '/admin/requests', label: 'Demandes' },
  { to: '/admin/profile', label: 'Mon profil' },
]
</script>

<template>
  <AppLayout :links="links" title="Super Admin">
    <router-view />
  </AppLayout>
</template>
