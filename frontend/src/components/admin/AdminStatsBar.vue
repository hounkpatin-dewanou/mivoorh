<script setup>
import { onMounted, ref } from 'vue'
import { fetchAdminStats } from '../../api/companies'

const stats = ref(null)
const loading = ref(true)

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await fetchAdminStats()
    stats.value = data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div v-if="loading" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div v-for="n in 4" :key="n" class="card-mivoo animate-pulse p-4">
      <div class="h-3 w-24 rounded bg-slate-200" />
      <div class="mt-3 h-8 w-16 rounded bg-slate-200" />
    </div>
  </div>
  <div v-else-if="stats" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="card-mivoo p-4">
      <p class="text-xs text-slate-500">Entreprises actives</p>
      <p class="text-2xl font-bold">{{ stats.companies_active }}/{{ stats.companies_total }}</p>
    </div>
    <div class="card-mivoo p-4">
      <p class="text-xs text-slate-500">Employés</p>
      <p class="text-2xl font-bold">{{ stats.employees_total }}</p>
    </div>
    <div class="card-mivoo p-4">
      <p class="text-xs text-slate-500">Demandes en attente</p>
      <p class="text-2xl font-bold">{{ stats.requests_pending }}</p>
    </div>
    <div class="card-mivoo p-4">
      <p class="text-xs text-slate-500">Décaissé (mois)</p>
      <p class="text-2xl font-bold">{{ Number(stats.requests_month_amount).toLocaleString('fr-FR') }} F</p>
    </div>
  </div>
</template>
