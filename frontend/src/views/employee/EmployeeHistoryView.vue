<script setup>
import { onMounted, ref } from 'vue'
import { fetchEmployeeRequests } from '../../api/advanceRequests'
import { fetchNotifications, markAllNotificationsRead, markNotificationRead } from '../../api/notifications'
import LoadingState from '../../components/ui/LoadingState.vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'

const requests = ref([])
const notifications = ref([])
const loading = ref(true)

async function load() {
  loading.value = true
  try {
    const [req, notif] = await Promise.all([fetchEmployeeRequests(), fetchNotifications()])
    requests.value = req.data
    notifications.value = notif.data
  } finally {
    loading.value = false
  }
}

async function readOne(id) {
  await markNotificationRead(id)
  await load()
}

async function readAll() {
  await markAllNotificationsRead()
  await load()
}

onMounted(load)
</script>

<template>
  <LoadingState v-if="loading" message="Chargement…" />
  <div v-else class="grid gap-6 lg:grid-cols-2">
    <div class="card-mivoo p-5">
      <h2 class="font-bold text-[#262b47]">Historique des demandes</h2>
      <ul class="mt-4 space-y-3">
        <li v-for="r in requests" :key="r.id" class="border-b pb-3 text-sm last:border-0">
          <div class="flex items-center justify-between">
            <span class="font-medium">{{ Number(r.amount).toLocaleString('fr-FR') }} FCFA</span>
            <StatusBadge :status="r.status" />
          </div>
          <p class="mt-1 text-slate-500">{{ r.reason }}</p>
          <p class="text-xs text-slate-400">{{ new Date(r.created_at).toLocaleDateString('fr-FR') }}</p>
        </li>
        <li v-if="!requests.length" class="text-slate-400">Aucune demande</li>
      </ul>
    </div>

    <div class="card-mivoo p-5">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-[#262b47]">Notifications</h2>
        <button v-if="notifications.some((n) => !n.is_read)" type="button" class="text-xs text-[#4294e3]" @click="readAll">Tout marquer lu</button>
      </div>
      <ul class="mt-4 space-y-2">
        <li
          v-for="n in notifications"
          :key="n.id"
          class="cursor-pointer rounded-lg p-2 text-sm"
          :class="n.is_read ? 'text-slate-400' : 'bg-[#f0f6ff] font-medium text-[#262b47]'"
          @click="!n.is_read && readOne(n.id)"
        >
          {{ n.message }}
        </li>
        <li v-if="!notifications.length" class="text-slate-400">Aucune notification</li>
      </ul>
    </div>
  </div>
</template>
