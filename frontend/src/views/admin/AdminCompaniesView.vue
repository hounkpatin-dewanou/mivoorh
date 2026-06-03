<script setup>
import { onMounted, ref } from 'vue'
import {
  createCompany,
  deleteCompany,
  fetchAdminCompanies,
  toggleCompany,
  updateCompany,
} from '../../api/companies'
import AdminStatsBar from '../../components/admin/AdminStatsBar.vue'
import BaseModal from '../../components/ui/BaseModal.vue'
import LoadingState from '../../components/ui/LoadingState.vue'
import { useToastStore } from '../../stores/toast'

const toast = useToastStore()
const loading = ref(true)
const companies = ref([])
const showAdd = ref(false)
const showEdit = ref(false)
const showDelete = ref(false)
const saving = ref(false)
const selected = ref(null)
const form = ref({ name: '', sector: '', contact_email: '' })

async function load() {
  loading.value = true
  try {
    const { data } = await fetchAdminCompanies()
    companies.value = data
  } finally {
    loading.value = false
  }
}

function openAdd() {
  form.value = { name: '', sector: '', contact_email: '' }
  showAdd.value = true
}

function openEdit(c) {
  selected.value = c
  form.value = {
    name: c.name,
    sector: c.sector || '',
    contact_email: c.contact_email,
  }
  showEdit.value = true
}

function openDelete(c) {
  selected.value = c
  showDelete.value = true
}

async function submitAdd() {
  saving.value = true
  try {
    await createCompany(form.value)
    toast.success('Entreprise créée.')
    showAdd.value = false
    await load()
  } finally {
    saving.value = false
  }
}

async function submitEdit() {
  saving.value = true
  try {
    await updateCompany(selected.value.id, form.value)
    toast.success('Entreprise mise à jour.')
    showEdit.value = false
    await load()
  } finally {
    saving.value = false
  }
}

async function confirmDelete() {
  saving.value = true
  try {
    await deleteCompany(selected.value.id)
    toast.success('Entreprise supprimée.')
    showDelete.value = false
    await load()
  } finally {
    saving.value = false
  }
}

async function toggle(id) {
  await toggleCompany(id)
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center justify-between gap-4">
      <h1 class="text-2xl font-bold text-mivoo-gradient">Entreprises partenaires</h1>
      <button type="button" class="btn-mivoo rounded-lg px-4 py-2 text-sm" @click="openAdd">+ Nouvelle entreprise</button>
    </div>

    <AdminStatsBar />

    <div class="card-mivoo mt-6 min-h-[160px] overflow-x-auto">
      <LoadingState v-if="loading" message="Chargement des entreprises…" compact />
      <table v-else class="w-full text-sm">
        <thead class="border-b bg-[#f0f6ff]">
          <tr>
            <th class="p-3 text-left">Nom</th>
            <th class="p-3">Secteur</th>
            <th class="p-3">Contact</th>
            <th class="p-3">Employés</th>
            <th class="p-3">Statut</th>
            <th class="p-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in companies" :key="c.id" class="border-b">
            <td class="p-3 font-medium">{{ c.name }}</td>
            <td class="p-3">{{ c.sector || '—' }}</td>
            <td class="p-3">{{ c.contact_email }}</td>
            <td class="p-3 text-center">{{ c.employees_count ?? 0 }}</td>
            <td class="p-3">{{ c.is_active ? 'Active' : 'Inactive' }}</td>
            <td class="p-3 text-right">
              <button type="button" class="text-[#4294e3] hover:underline" @click="openEdit(c)">Modifier</button>
              <span class="mx-1 text-slate-300">|</span>
              <button type="button" class="text-[#4294e3] hover:underline" @click="toggle(c.id)">
                {{ c.is_active ? 'Désactiver' : 'Activer' }}
              </button>
              <span class="mx-1 text-slate-300">|</span>
              <button type="button" class="text-red-600 hover:underline" @click="openDelete(c)">Supprimer</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <BaseModal :open="showAdd" title="Nouvelle entreprise" @close="showAdd = false">
      <form class="space-y-3" @submit.prevent="submitAdd">
        <input v-model="form.name" required placeholder="Nom *" class="w-full rounded-lg border px-3 py-2" />
        <input v-model="form.sector" placeholder="Secteur" class="w-full rounded-lg border px-3 py-2" />
        <input v-model="form.contact_email" type="email" required placeholder="Email contact RH *" class="w-full rounded-lg border px-3 py-2" />
        <button type="submit" class="btn-mivoo w-full rounded-lg py-2" :disabled="saving">Créer</button>
      </form>
    </BaseModal>

    <BaseModal :open="showEdit" title="Modifier l'entreprise" @close="showEdit = false">
      <form class="space-y-3" @submit.prevent="submitEdit">
        <input v-model="form.name" required class="w-full rounded-lg border px-3 py-2" />
        <input v-model="form.sector" placeholder="Secteur" class="w-full rounded-lg border px-3 py-2" />
        <input v-model="form.contact_email" type="email" required class="w-full rounded-lg border px-3 py-2" />
        <button type="submit" class="btn-mivoo w-full rounded-lg py-2" :disabled="saving">Enregistrer</button>
      </form>
    </BaseModal>

    <BaseModal :open="showDelete" title="Supprimer l'entreprise" @close="showDelete = false">
      <p class="text-sm text-slate-600">
        Supprimer <strong>{{ selected?.name }}</strong> et tous ses comptes (RH, employés, demandes) ?
      </p>
      <div class="mt-4 flex gap-2">
        <button type="button" class="flex-1 rounded-lg border py-2" @click="showDelete = false">Annuler</button>
        <button type="button" class="flex-1 rounded-lg bg-red-600 py-2 text-white" :disabled="saving" @click="confirmDelete">
          Supprimer
        </button>
      </div>
    </BaseModal>
  </div>
</template>
