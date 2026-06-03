<script setup>
import { computed, onMounted, ref } from 'vue'
import {
  createAdminEmployee,
  deleteAdminEmployee,
  fetchAdminEmployees,
  updateAdminEmployee,
} from '../../api/admin'
import { fetchAdminCompanies } from '../../api/companies'
import AdminStatsBar from '../../components/admin/AdminStatsBar.vue'
import BaseModal from '../../components/ui/BaseModal.vue'
import LoadingState from '../../components/ui/LoadingState.vue'
import PasswordInput from '../../components/ui/PasswordInput.vue'
import { useToastStore } from '../../stores/toast'

const toast = useToastStore()
const loading = ref(true)
const employees = ref([])
const companies = ref([])
const companyFilter = ref('')
const search = ref('')
const showForm = ref(false)
const showDelete = ref(false)
const saving = ref(false)
const editing = ref(null)
const selected = ref(null)

const form = ref({
  company_id: '',
  name: '',
  email: '',
  password: '',
  monthly_salary: '',
  advance_limit_pct: '',
  is_active: true,
})

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return employees.value
  return employees.value.filter(
    (e) =>
      e.user?.name?.toLowerCase().includes(q) ||
      e.user?.email?.toLowerCase().includes(q) ||
      e.company?.name?.toLowerCase().includes(q),
  )
})

async function load() {
  loading.value = true
  try {
    const params = companyFilter.value ? { company_id: companyFilter.value } : {}
    const [emp, comp] = await Promise.all([fetchAdminEmployees(params), fetchAdminCompanies()])
    employees.value = emp.data
    companies.value = comp.data
  } finally {
    loading.value = false
  }
}

function openAdd() {
  editing.value = null
  form.value = {
    company_id: companies.value[0]?.id || '',
    name: '',
    email: '',
    password: '',
    monthly_salary: '',
    advance_limit_pct: '',
    is_active: true,
  }
  showForm.value = true
}

function openEdit(e) {
  editing.value = e
  form.value = {
    company_id: e.company_id,
    name: e.user?.name || '',
    email: e.user?.email || '',
    password: '',
    monthly_salary: e.monthly_salary,
    advance_limit_pct: e.advance_limit_pct,
    is_active: e.is_active,
  }
  showForm.value = true
}

function openDelete(e) {
  selected.value = e
  showDelete.value = true
}

async function submitForm() {
  saving.value = true
  try {
    const payload = {
      company_id: form.value.company_id,
      name: form.value.name,
      email: form.value.email,
      monthly_salary: form.value.monthly_salary,
      advance_limit_pct: form.value.advance_limit_pct || 40,
      is_active: form.value.is_active,
    }
    if (editing.value) {
      await updateAdminEmployee(editing.value.id, payload)
      toast.success('Employé mis à jour.')
    } else {
      await createAdminEmployee({ ...payload, password: form.value.password })
      toast.success('Employé créé.')
    }
    showForm.value = false
    await load()
  } finally {
    saving.value = false
  }
}

async function confirmDelete() {
  saving.value = true
  try {
    await deleteAdminEmployee(selected.value.id)
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
      <h1 class="text-2xl font-bold text-mivoo-gradient">Employés</h1>
      <button type="button" class="btn-mivoo rounded-lg px-4 py-2 text-sm" @click="openAdd">+ Nouvel employé</button>
    </div>

    <AdminStatsBar />

    <div class="mt-6 flex flex-wrap gap-3">
      <input v-model="search" placeholder="Rechercher…" class="rounded-lg border px-3 py-2 text-sm" />
      <select v-model="companyFilter" class="rounded-lg border px-3 py-2 text-sm" @change="load">
        <option value="">Toutes les entreprises</option>
        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>

    <div class="card-mivoo mt-4 min-h-[160px] overflow-x-auto">
      <LoadingState v-if="loading" message="Chargement des employés…" compact />
      <table v-else class="w-full text-sm">
        <thead class="border-b bg-[#f0f6ff]">
          <tr>
            <th class="p-3 text-left">Nom</th>
            <th class="p-3 text-left">Email</th>
            <th class="p-3 text-left">Entreprise</th>
            <th class="p-3">Salaire</th>
            <th class="p-3">Plafond</th>
            <th class="p-3">Statut</th>
            <th class="p-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="e in filtered" :key="e.id" class="border-b">
            <td class="p-3 font-medium">{{ e.user?.name }}</td>
            <td class="p-3">{{ e.user?.email }}</td>
            <td class="p-3">{{ e.company?.name }}</td>
            <td class="p-3">{{ Number(e.monthly_salary).toLocaleString('fr-FR') }} F</td>
            <td class="p-3 text-center">{{ e.advance_limit_pct }}%</td>
            <td class="p-3">{{ e.is_active ? 'Actif' : 'Inactif' }}</td>
            <td class="p-3 text-right">
              <button type="button" class="text-[#4294e3] hover:underline" @click="openEdit(e)">Modifier</button>
              <span class="mx-1 text-slate-300">|</span>
              <button type="button" class="text-red-600 hover:underline" @click="openDelete(e)">Supprimer</button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="!filtered.length" class="p-6 text-center text-slate-400">Aucun employé</p>
    </div>

    <BaseModal :open="showForm" :title="editing ? 'Modifier l\'employé' : 'Nouvel employé'" @close="showForm = false">
      <form class="space-y-3" @submit.prevent="submitForm">
        <select v-model.number="form.company_id" required class="w-full rounded-lg border px-3 py-2">
          <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <input v-model="form.name" required placeholder="Nom *" class="w-full rounded-lg border px-3 py-2" />
        <input v-model="form.email" type="email" required placeholder="Email *" class="w-full rounded-lg border px-3 py-2" />
        <PasswordInput v-if="!editing" v-model="form.password" required autocomplete="new-password" />
        <input v-model.number="form.monthly_salary" type="number" required placeholder="Salaire mensuel *" class="w-full rounded-lg border px-3 py-2" />
        <input v-model.number="form.advance_limit_pct" type="number" min="1" max="100" placeholder="Plafond avance %" class="w-full rounded-lg border px-3 py-2" />
        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.is_active" type="checkbox" />
          Compte actif
        </label>
        <button type="submit" class="btn-mivoo w-full rounded-lg py-2" :disabled="saving">Enregistrer</button>
      </form>
    </BaseModal>

    <BaseModal :open="showDelete" title="Supprimer l'employé" @close="showDelete = false">
      <p class="text-sm">Supprimer <strong>{{ selected?.user?.name }}</strong> et toutes ses demandes ?</p>
      <div class="mt-4 flex gap-2">
        <button type="button" class="flex-1 rounded-lg border py-2" @click="showDelete = false">Annuler</button>
        <button type="button" class="flex-1 rounded-lg bg-red-600 py-2 text-white" :disabled="saving" @click="confirmDelete">Supprimer</button>
      </div>
    </BaseModal>
  </div>
</template>
