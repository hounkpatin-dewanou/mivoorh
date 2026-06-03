<script setup>
import { onMounted, ref } from 'vue'
import {
  approveAdminRequest,
  createAdminRequest,
  deleteAdminRequest,
  fetchAdminEmployees,
  fetchAdminRequests,
  refuseAdminRequest,
  updateAdminRequest,
} from '../../api/admin'
import { fetchAdminCompanies } from '../../api/companies'
import AdminStatsBar from '../../components/admin/AdminStatsBar.vue'
import BaseModal from '../../components/ui/BaseModal.vue'
import LoadingState from '../../components/ui/LoadingState.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { useToastStore } from '../../stores/toast'

const toast = useToastStore()
const loading = ref(true)
const requests = ref([])
const companies = ref([])
const employees = ref([])
const statusFilter = ref('')
const companyFilter = ref('')
const actionModal = ref({ open: false, action: '', id: null })
const comment = ref('')
const showForm = ref(false)
const showDelete = ref(false)
const saving = ref(false)
const editing = ref(null)
const selected = ref(null)

const form = ref({
  employee_id: '',
  amount: '',
  reason: '',
  status: 'pending',
})

async function load() {
  loading.value = true
  try {
    const params = {}
    if (statusFilter.value) params.status = statusFilter.value
    if (companyFilter.value) params.company_id = companyFilter.value
    const { data } = await fetchAdminRequests(params)
    requests.value = data
  } finally {
    loading.value = false
  }
}

async function loadEmployees() {
  const { data } = await fetchAdminEmployees()
  employees.value = data.filter((e) => e.is_active)
}

async function init() {
  loading.value = true
  try {
    const { data: comp } = await fetchAdminCompanies()
    companies.value = comp
    await loadEmployees()
    const params = {}
    if (statusFilter.value) params.status = statusFilter.value
    if (companyFilter.value) params.company_id = companyFilter.value
    const { data } = await fetchAdminRequests(params)
    requests.value = data
  } finally {
    loading.value = false
  }
}

function openAction(action, id) {
  actionModal.value = { open: true, action, id }
  comment.value = ''
}

async function confirmAction() {
  if (actionModal.value.action === 'refuse' && !comment.value.trim()) {
    toast.error('Commentaire obligatoire pour un refus.')
    return
  }
  saving.value = true
  try {
    if (actionModal.value.action === 'approve') {
      await approveAdminRequest(actionModal.value.id, comment.value || 'Approuvée par MivooPay.')
      toast.success('Demande approuvée.')
    } else {
      await refuseAdminRequest(actionModal.value.id, comment.value)
      toast.success('Demande refusée.')
    }
    actionModal.value.open = false
    await load()
  } finally {
    saving.value = false
  }
}

function openAdd() {
  editing.value = null
  form.value = { employee_id: employees.value[0]?.id || '', amount: '', reason: '', status: 'pending' }
  showForm.value = true
}

function openEdit(r) {
  editing.value = r
  form.value = {
    employee_id: r.employee_id,
    amount: r.amount,
    reason: r.reason,
    status: r.status,
  }
  showForm.value = true
}

function openDelete(r) {
  selected.value = r
  showDelete.value = true
}

async function submitForm() {
  saving.value = true
  try {
    if (editing.value) {
      await updateAdminRequest(editing.value.id, {
        amount: form.value.amount,
        reason: form.value.reason,
        status: form.value.status,
      })
      toast.success('Demande mise à jour.')
    } else {
      await createAdminRequest(form.value)
      toast.success('Demande créée.')
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
    await deleteAdminRequest(selected.value.id)
    toast.success('Demande supprimée.')
    showDelete.value = false
    await load()
  } finally {
    saving.value = false
  }
}

onMounted(init)
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center justify-between gap-4">
      <h1 class="text-2xl font-bold text-mivoo-gradient">Demandes d'avance</h1>
      <button type="button" class="btn-mivoo rounded-lg px-4 py-2 text-sm" @click="openAdd">+ Nouvelle demande</button>
    </div>

    <AdminStatsBar />

    <div class="mt-6 flex flex-wrap gap-3">
      <select v-model="statusFilter" class="rounded-lg border px-3 py-2 text-sm" @change="load">
        <option value="">Tous les statuts</option>
        <option value="pending">En attente</option>
        <option value="approved">Approuvées</option>
        <option value="refused">Refusées</option>
      </select>
      <select v-model="companyFilter" class="rounded-lg border px-3 py-2 text-sm" @change="load">
        <option value="">Toutes les entreprises</option>
        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>

    <div class="card-mivoo mt-4 min-h-[120px]">
      <LoadingState v-if="loading" message="Chargement des demandes…" />
      <template v-else>
      <div class="divide-y">
      <div v-for="r in requests" :key="r.id" class="flex flex-wrap items-start justify-between gap-3 p-4">
        <div>
          <p class="font-medium">{{ r.employee?.user?.name }}</p>
          <p class="text-xs text-slate-500">{{ r.employee?.company?.name }}</p>
          <p class="text-lg font-bold text-[#4294e3]">{{ Number(r.amount).toLocaleString('fr-FR') }} FCFA</p>
          <p class="text-sm text-slate-500">{{ r.reason }}</p>
          <p v-if="r.review_comment" class="mt-1 text-xs text-slate-400">Commentaire : {{ r.review_comment }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <StatusBadge :status="r.status" />
          <button type="button" class="text-sm text-[#4294e3] hover:underline" @click="openEdit(r)">Modifier</button>
          <button type="button" class="text-sm text-red-600 hover:underline" @click="openDelete(r)">Supprimer</button>
          <template v-if="r.status === 'pending'">
            <button type="button" class="btn-mivoo rounded-lg px-3 py-1 text-sm" @click="openAction('approve', r.id)">Approuver</button>
            <button type="button" class="rounded-lg border border-red-300 px-3 py-1 text-sm text-red-600" @click="openAction('refuse', r.id)">Refuser</button>
          </template>
        </div>
      </div>
      <p v-if="!requests.length" class="p-6 text-center text-slate-400">Aucune demande</p>
      </div>
      </template>
    </div>

    <BaseModal
      :open="actionModal.open"
      :title="actionModal.action === 'approve' ? 'Approuver' : 'Refuser'"
      @close="actionModal.open = false"
    >
      <textarea v-model="comment" rows="3" class="w-full rounded-lg border px-3 py-2" :placeholder="actionModal.action === 'refuse' ? 'Commentaire *' : 'Commentaire (optionnel)'" />
      <button type="button" class="btn-mivoo mt-3 w-full rounded-lg py-2" :disabled="saving" @click="confirmAction">Confirmer</button>
    </BaseModal>

    <BaseModal :open="showForm" :title="editing ? 'Modifier la demande' : 'Nouvelle demande'" @close="showForm = false">
      <form class="space-y-3" @submit.prevent="submitForm">
        <select v-if="!editing" v-model.number="form.employee_id" required class="w-full rounded-lg border px-3 py-2">
          <option v-for="e in employees" :key="e.id" :value="e.id">
            {{ e.user?.name }} — {{ e.company?.name }}
          </option>
        </select>
        <input v-model.number="form.amount" type="number" required placeholder="Montant FCFA *" class="w-full rounded-lg border px-3 py-2" />
        <textarea v-model="form.reason" required rows="3" placeholder="Motif *" class="w-full rounded-lg border px-3 py-2" />
        <select v-if="editing" v-model="form.status" class="w-full rounded-lg border px-3 py-2">
          <option value="pending">En attente</option>
          <option value="approved">Approuvée</option>
          <option value="refused">Refusée</option>
        </select>
        <button type="submit" class="btn-mivoo w-full rounded-lg py-2" :disabled="saving">Enregistrer</button>
      </form>
    </BaseModal>

    <BaseModal :open="showDelete" title="Supprimer la demande" @close="showDelete = false">
      <p class="text-sm">Supprimer définitivement cette demande ?</p>
      <div class="mt-4 flex gap-2">
        <button type="button" class="flex-1 rounded-lg border py-2" @click="showDelete = false">Annuler</button>
        <button type="button" class="flex-1 rounded-lg bg-red-600 py-2 text-white" :disabled="saving" @click="confirmDelete">Supprimer</button>
      </div>
    </BaseModal>
  </div>
</template>
