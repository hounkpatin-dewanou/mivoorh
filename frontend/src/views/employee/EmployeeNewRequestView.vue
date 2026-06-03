<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { createEmployeeRequest, fetchEmployeeBalance } from '../../api/advanceRequests'

const router = useRouter()
const amount = ref(50000)
const reason = ref('')
const balance = ref(null)
const error = ref('')
const loading = ref(false)

onMounted(async () => {
  const { data } = await fetchEmployeeBalance()
  balance.value = data
})

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await createEmployeeRequest({ amount: amount.value, reason: reason.value })
    router.push('/employee/history')
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de la demande.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-lg">
    <h1 class="text-2xl font-bold text-[#262b47]">Nouvelle demande d'avance</h1>
    <p v-if="balance" class="mt-2 text-sm text-slate-600">
      Solde disponible : <strong>{{ Number(balance.available_balance).toLocaleString('fr-FR') }} FCFA</strong>
    </p>
    <form class="card-mivoo mt-6 space-y-4 p-6" @submit.prevent="submit">
      <div>
        <label class="text-sm font-medium">Montant (FCFA)</label>
        <input v-model.number="amount" type="number" min="1000" required class="mt-1 w-full rounded-lg border px-3 py-2" />
      </div>
      <div>
        <label class="text-sm font-medium">Motif</label>
        <textarea v-model="reason" required rows="4" class="mt-1 w-full rounded-lg border px-3 py-2" placeholder="Ex. frais médicaux, scolarité…" />
      </div>
      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
      <button type="submit" class="btn-mivoo w-full rounded-lg py-2.5 font-semibold" :disabled="loading">
        {{ loading ? 'Envoi…' : 'Soumettre la demande' }}
      </button>
    </form>
  </div>
</template>
