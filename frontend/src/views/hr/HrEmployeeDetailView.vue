<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { deactivateEmployee, deleteEmployee, fetchEmployee, updateEmployee } from '../../api/employees'
import BaseModal from '../../components/ui/BaseModal.vue'
import LoadingState from '../../components/ui/LoadingState.vue'
import { useToastStore } from '../../stores/toast'

const route = useRoute()
const router = useRouter()
const toast = useToastStore()
const employee = ref(null)
const loading = ref(true)
const saving = ref(false)
const showDelete = ref(false)
const message = ref('')
const form = ref({ name: '', email: '', monthly_salary: '', advance_limit_pct: '', is_active: true })

async function load() {
  loading.value = true
  try {
    const { data } = await fetchEmployee(route.params.id)
    employee.value = data
    form.value = {
      name: data.user?.name ?? '',
      email: data.user?.email ?? '',
      monthly_salary: data.monthly_salary,
      advance_limit_pct: data.advance_limit_pct,
      is_active: data.is_active,
    }
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  message.value = ''
  try {
    const { data } = await updateEmployee(route.params.id, form.value)
    employee.value = data
    message.value = 'Employé mis à jour.'
  } catch {
    message.value = 'Erreur lors de la mise à jour.'
  } finally {
    saving.value = false
  }
}

async function deactivate() {
  if (!confirm('Désactiver cet employé ?')) return
  await deactivateEmployee(route.params.id)
  toast.success('Employé désactivé.')
  await load()
}

async function confirmDelete() {
  saving.value = true
  try {
    await deleteEmployee(route.params.id)
    toast.success('Employé supprimé.')
    router.push('/hr/employees')
  } finally {
    saving.value = false
    showDelete.value = false
  }
}

onMounted(load)
</script>

<template>
  <LoadingState v-if="loading" message="Chargement de l'employé…" />
  <div v-else-if="employee" class="max-w-xl">
    <button type="button" class="mb-4 text-sm text-[#4294e3]" @click="router.push('/hr/employees')">← Retour</button>
    <h1 class="text-2xl font-bold text-[#262b47]">{{ employee.user?.name }}</h1>

    <form class="card-mivoo mt-6 space-y-4 p-6" @submit.prevent="save">
      <div>
        <label class="text-sm font-medium">Nom</label>
        <input v-model="form.name" class="mt-1 w-full rounded-lg border px-3 py-2" />
      </div>
      <div>
        <label class="text-sm font-medium">Email</label>
        <input v-model="form.email" type="email" class="mt-1 w-full rounded-lg border px-3 py-2" />
      </div>
      <div>
        <label class="text-sm font-medium">Salaire mensuel (FCFA)</label>
        <input v-model.number="form.monthly_salary" type="number" class="mt-1 w-full rounded-lg border px-3 py-2" />
      </div>
      <div>
        <label class="text-sm font-medium">Plafond d'avance (%)</label>
        <input v-model.number="form.advance_limit_pct" type="number" min="1" max="100" class="mt-1 w-full rounded-lg border px-3 py-2" />
      </div>
      <label class="flex items-center gap-2 text-sm">
        <input v-model="form.is_active" type="checkbox" /> Compte actif
      </label>

      <p v-if="message" class="text-sm text-emerald-600">{{ message }}</p>

      <div class="flex flex-wrap gap-3">
        <button type="submit" class="btn-mivoo rounded-lg px-4 py-2" :disabled="saving">Enregistrer</button>
        <button v-if="employee.is_active" type="button" class="rounded-lg border border-amber-300 px-4 py-2 text-amber-700" @click="deactivate">Désactiver</button>
        <button type="button" class="rounded-lg border border-red-300 px-4 py-2 text-red-600" @click="showDelete = true">Supprimer</button>
      </div>
    </form>

    <BaseModal :open="showDelete" title="Supprimer l'employé" @close="showDelete = false">
      <p class="text-sm text-slate-600">Supprimer définitivement cet employé et ses demandes ?</p>
      <div class="mt-4 flex gap-2">
        <button type="button" class="flex-1 rounded-lg border py-2" @click="showDelete = false">Annuler</button>
        <button type="button" class="flex-1 rounded-lg bg-red-600 py-2 text-white" :disabled="saving" @click="confirmDelete">Supprimer</button>
      </div>
    </BaseModal>

    <div class="card-mivoo mt-6 p-4">
      <h2 class="font-semibold">Historique des demandes</h2>
      <ul class="mt-2 space-y-2 text-sm">
        <li v-for="r in employee.advance_requests" :key="r.id">
          {{ Number(r.amount).toLocaleString('fr-FR') }} F — {{ r.status }} — {{ r.reason }}
        </li>
        <li v-if="!employee.advance_requests?.length" class="text-slate-400">Aucune demande</li>
      </ul>
    </div>
  </div>
</template>
