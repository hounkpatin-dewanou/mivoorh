<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { createEmployee, deleteEmployee, fetchEmployees } from '../../api/employees'
import BaseModal from '../../components/ui/BaseModal.vue'
import LoadingState from '../../components/ui/LoadingState.vue'
import PasswordInput from '../../components/ui/PasswordInput.vue'
import { useToastStore } from '../../stores/toast'

const toast = useToastStore()
const loading = ref(true)
const employees = ref([])
const search = ref('')
const showAdd = ref(false)
const showDelete = ref(false)
const selected = ref(null)
const saving = ref(false)
const error = ref('')
const form = ref({
  name: '',
  email: '',
  password: 'password',
  monthly_salary: '',
  advance_limit_pct: '',
})

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return employees.value
  return employees.value.filter((e) =>
    e.user?.name?.toLowerCase().includes(q) || e.user?.email?.toLowerCase().includes(q),
  )
})

async function load() {
  loading.value = true
  try {
    const { data } = await fetchEmployees()
    employees.value = data
  } finally {
    loading.value = false
  }
}

async function submitAdd() {
  saving.value = true
  error.value = ''
  try {
    await createEmployee(form.value)
    toast.success('Employé créé avec succès.')
    showAdd.value = false
    form.value = { name: '', email: '', password: 'password', monthly_salary: '', advance_limit_pct: '' }
    await load()
  } catch {
    error.value = ''
  } finally {
    saving.value = false
  }
}

function openDelete(e) {
  selected.value = e
  showDelete.value = true
}

async function confirmDelete() {
  saving.value = true
  try {
    await deleteEmployee(selected.value.id)
    toast.success('Employé supprimé.')
    showDelete.value = false
    await load()
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center justify-between gap-4">
      <h1 class="text-2xl font-bold text-[#262b47]">Employés</h1>
      <button type="button" class="btn-mivoo rounded-lg px-4 py-2 text-sm font-semibold" @click="showAdd = true">+ Ajouter</button>
    </div>

    <input
      v-model="search"
      type="search"
      placeholder="Rechercher par nom ou email…"
      class="mt-4 w-full max-w-md rounded-lg border px-3 py-2 text-sm"
    />

    <div class="card-mivoo mt-4 min-h-[160px] overflow-x-auto">
      <LoadingState v-if="loading" message="Chargement des employés…" compact />
      <table v-else class="w-full text-left text-sm">
        <thead class="border-b bg-[#f0f6ff]">
          <tr>
            <th class="p-3">Nom</th>
            <th class="p-3">Email</th>
            <th class="p-3">Salaire</th>
            <th class="p-3">Plafond</th>
            <th class="p-3">Statut</th>
            <th class="p-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="e in filtered" :key="e.id" class="border-b hover:bg-[#f0f6ff]/50">
            <td class="p-3 font-medium">{{ e.user?.name }}</td>
            <td class="p-3 text-slate-500">{{ e.user?.email }}</td>
            <td class="p-3">{{ Number(e.monthly_salary).toLocaleString('fr-FR') }} F</td>
            <td class="p-3">{{ e.advance_limit_pct }}%</td>
            <td class="p-3">
              <span :class="e.is_active ? 'text-emerald-600' : 'text-slate-400'">{{ e.is_active ? 'Actif' : 'Inactif' }}</span>
            </td>
            <td class="p-3 text-right">
              <RouterLink :to="`/hr/employees/${e.id}`" class="font-medium text-[#4294e3] hover:underline">Modifier</RouterLink>
              <span class="mx-1 text-slate-300">|</span>
              <button type="button" class="text-red-600 hover:underline" @click="openDelete(e)">Supprimer</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <BaseModal :open="showAdd" title="Ajouter un employé" @close="showAdd = false">
      <form class="space-y-3" @submit.prevent="submitAdd">
        <input v-model="form.name" required placeholder="Nom complet" class="w-full rounded-lg border px-3 py-2" />
        <input v-model="form.email" type="email" required placeholder="Email" class="w-full rounded-lg border px-3 py-2" />
        <PasswordInput v-model="form.password" required placeholder="Mot de passe initial" autocomplete="new-password" />
        <input v-model.number="form.monthly_salary" type="number" required placeholder="Salaire mensuel (FCFA)" class="w-full rounded-lg border px-3 py-2" />
        <input v-model.number="form.advance_limit_pct" type="number" min="1" max="100" placeholder="Plafond avance %" class="w-full rounded-lg border px-3 py-2" />
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        <button type="submit" class="btn-mivoo w-full rounded-lg py-2" :disabled="saving">{{ saving ? 'Enregistrement…' : 'Créer' }}</button>
      </form>
    </BaseModal>

    <BaseModal :open="showDelete" title="Supprimer l'employé" @close="showDelete = false">
      <p class="text-sm text-slate-600">
        Supprimer <strong>{{ selected?.user?.name }}</strong> et toutes ses demandes d'avance ? Cette action est irréversible.
      </p>
      <div class="mt-4 flex gap-2">
        <button type="button" class="flex-1 rounded-lg border py-2" @click="showDelete = false">Annuler</button>
        <button type="button" class="flex-1 rounded-lg bg-red-600 py-2 text-white" :disabled="saving" @click="confirmDelete">Supprimer</button>
      </div>
    </BaseModal>
  </div>
</template>
