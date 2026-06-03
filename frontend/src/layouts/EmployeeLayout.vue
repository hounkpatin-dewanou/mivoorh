<script setup>
import AppLayout from '../components/layout/AppLayout.vue'

const links = [
  { to: '/employee/dashboard', label: 'Mon solde' },
  { to: '/employee/request/new', label: 'Nouvelle demande' },
  { to: '/employee/history', label: 'Historique' },
  { to: '/employee/profile', label: 'Mon profil' },
]
</script>

<template>
  <AppLayout :links="links" title="Espace employé">
    <router-view />
  </AppLayout>
</template>
