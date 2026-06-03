<script setup>
import { onMounted, ref } from 'vue'
import api from '../../api/axios'
import LoadingState from '../../components/ui/LoadingState.vue'

const stats = ref(null)
const loading = ref(true)

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await api.get('/hr/stats')
    stats.value = data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-mivoo-gradient">Dashboard RH</h1>
    <LoadingState v-if="loading" message="Chargement du tableau de bord…" />
    <div v-else-if="stats" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="card-mivoo p-4"><p class="text-sm text-slate-500">Employés</p><p class="text-2xl font-bold">{{ stats.employees_active }}/{{ stats.employees_count }}</p></div>
      <div class="card-mivoo p-4"><p class="text-sm text-slate-500">Demandes en attente</p><p class="text-2xl font-bold">{{ stats.requests_pending }}</p></div>
      <div class="card-mivoo p-4"><p class="text-sm text-slate-500">Demandes ce mois</p><p class="text-2xl font-bold">{{ stats.requests_month_count }}</p></div>
      <div class="card-mivoo p-4"><p class="text-sm text-slate-500">Montant décaissé</p><p class="text-2xl font-bold">{{ Number(stats.disbursed_month).toLocaleString() }} F</p></div>
    </div>
  </div>
</template>
