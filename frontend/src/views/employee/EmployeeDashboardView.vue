<script setup>
import { onMounted, ref } from 'vue'
import { fetchEmployeeBalance, fetchEmployeeRequests } from '../../api/advanceRequests'
import LoadingState from '../../components/ui/LoadingState.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'

const balance = ref(null)
const requests = ref([])
const loading = ref(true)

onMounted(async () => {
  loading.value = true
  try {
    const [bal, req] = await Promise.all([fetchEmployeeBalance(), fetchEmployeeRequests()])
    balance.value = bal.data
    requests.value = req.data.slice(0, 5)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-[#262b47]">Mon espace</h1>
    <LoadingState v-if="loading" message="Chargement de votre espace…" />
    <div v-else-if="balance" class="mt-6 overflow-hidden rounded-2xl bg-mivoo-gradient p-8 text-white shadow-lg shadow-[#4294e3]/20">
      <p class="text-sm text-white/90">Solde disponible ce mois</p>
      <p class="mt-1 text-4xl font-bold text-white">{{ Number(balance.available_balance).toLocaleString('fr-FR') }} FCFA</p>
      <p class="mt-2 text-sm text-white/80">
        Plafond {{ balance.advance_limit_pct }}% · Salaire {{ Number(balance.monthly_salary).toLocaleString('fr-FR') }} F/mois
      </p>
    </div>
    <div v-if="!loading" class="card-mivoo mt-6 p-5">
      <div class="flex items-center justify-between">
        <h2 class="font-semibold">Dernières demandes</h2>
        <router-link to="/employee/request/new" class="btn-mivoo rounded-lg px-4 py-2 text-sm">Nouvelle demande</router-link>
      </div>
      <ul class="mt-4 space-y-2">
        <li v-for="r in requests" :key="r.id" class="flex justify-between text-sm">
          <span>{{ Number(r.amount).toLocaleString('fr-FR') }} F — {{ r.reason }}</span>
          <StatusBadge :status="r.status" />
        </li>
        <li v-if="!loading && !requests.length" class="text-slate-400">Aucune demande pour le moment</li>
      </ul>
      <router-link to="/employee/history" class="mt-4 inline-block text-sm text-[#4294e3] hover:underline">Voir tout l'historique →</router-link>
    </div>
  </div>
</template>
