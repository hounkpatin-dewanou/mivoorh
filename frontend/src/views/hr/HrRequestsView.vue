<script setup>
import { onMounted, ref } from 'vue'
import { approveRequest, fetchHrRequests, refuseRequest } from '../../api/advanceRequests'
import BaseModal from '../../components/ui/BaseModal.vue'
import LoadingState from '../../components/ui/LoadingState.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import { useToastStore } from '../../stores/toast'

const toast = useToastStore()
const requests = ref([])
const filter = ref('')
const modal = ref({ open: false, action: '', id: null })
const comment = ref('')
const loading = ref(true)
const actionLoading = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await fetchHrRequests(filter.value)
    requests.value = data
  } finally {
    loading.value = false
  }
}

function openModal(action, id) {
  modal.value = { open: true, action, id }
  comment.value = action === 'approve' ? '' : ''
}

async function confirm() {
  if (modal.value.action === 'refuse' && !comment.value.trim()) {
    toast.error('Le commentaire est obligatoire pour un refus.')
    return
  }
  actionLoading.value = true
  try {
    if (modal.value.action === 'approve') {
      await approveRequest(modal.value.id, comment.value || 'Demande approuvée.')
      toast.success('Demande approuvée.')
    } else {
      await refuseRequest(modal.value.id, comment.value)
      toast.success('Demande refusée.')
    }
    modal.value.open = false
    await load()
  } catch {
    /* intercepteur */
  } finally {
    actionLoading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-[#262b47]">Demandes d'avance</h1>
    <select v-model="filter" class="mt-4 rounded-lg border px-3 py-2" @change="load">
      <option value="">Toutes</option>
      <option value="pending">En attente</option>
      <option value="approved">Approuvées</option>
      <option value="refused">Refusées</option>
    </select>

    <div class="card-mivoo mt-4 min-h-[120px]">
      <LoadingState v-if="loading" message="Chargement des demandes…" />
      <template v-else>
      <div class="divide-y">
      <div v-for="r in requests" :key="r.id" class="flex flex-wrap items-center justify-between gap-3 p-4">
        <div>
          <p class="font-medium">{{ r.employee?.user?.name }}</p>
          <p class="text-lg font-bold text-[#4294e3]">{{ Number(r.amount).toLocaleString('fr-FR') }} FCFA</p>
          <p class="text-sm text-slate-500">{{ r.reason }}</p>
          <p v-if="r.review_comment" class="mt-1 text-xs text-slate-400">RH : {{ r.review_comment }}</p>
        </div>
        <div class="flex items-center gap-2">
          <StatusBadge :status="r.status" />
          <template v-if="r.status === 'pending'">
            <button type="button" class="btn-mivoo rounded-lg px-3 py-1 text-sm" @click="openModal('approve', r.id)">Approuver</button>
            <button type="button" class="rounded-lg border border-red-300 px-3 py-1 text-sm text-red-600" @click="openModal('refuse', r.id)">Refuser</button>
          </template>
        </div>
      </div>
      <p v-if="!requests.length" class="p-6 text-center text-slate-400">Aucune demande</p>
      </div>
      </template>
    </div>

    <BaseModal
      :open="modal.open"
      :title="modal.action === 'approve' ? 'Approuver la demande' : 'Refuser la demande'"
      @close="modal.open = false"
    >
      <textarea
        v-model="comment"
        rows="3"
        class="w-full rounded-lg border px-3 py-2"
        :placeholder="modal.action === 'refuse' ? 'Commentaire obligatoire…' : 'Commentaire optionnel…'"
      />
      <button type="button" class="btn-mivoo mt-4 w-full rounded-lg py-2" :disabled="actionLoading" @click="confirm">
        {{ actionLoading ? 'Traitement…' : 'Confirmer' }}
      </button>
    </BaseModal>
  </div>
</template>
