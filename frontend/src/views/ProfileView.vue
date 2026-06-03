<script setup>
import { computed, onMounted, ref } from 'vue'
import { fetchPublicCompanies } from '../api/companies'
import { fetchProfile, updateProfile } from '../api/profile'
import LoadingState from '../components/ui/LoadingState.vue'
import PasswordInput from '../components/ui/PasswordInput.vue'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

const auth = useAuthStore()
const toast = useToastStore()
const loading = ref(true)
const saving = ref(false)
const profile = ref(null)
const companies = ref([])

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  company_id: '',
  monthly_salary: '',
  advance_limit_pct: '',
  is_active: true,
  company_name: '',
  company_sector: '',
  company_contact_email: '',
})

const roleLabel = computed(() => {
  const map = { superadmin: 'Super Admin MivooPay', hr: 'Responsable RH', employee: 'Employé' }
  return map[auth.role] ?? auth.role
})

const isEmployee = computed(() => auth.role === 'employee')
const isHr = computed(() => auth.role === 'hr')

function fillForm(data) {
  form.value.name = data.name
  form.value.email = data.email
  if (data.employee) {
    form.value.company_id = data.company_id
    form.value.monthly_salary = data.employee.monthly_salary
    form.value.advance_limit_pct = data.employee.advance_limit_pct
    form.value.is_active = data.employee.is_active
  }
  if (data.company && data.role === 'hr') {
    form.value.company_name = data.company.name
    form.value.company_sector = data.company.sector || ''
    form.value.company_contact_email = data.company.contact_email
  }
}

async function load() {
  loading.value = true
  try {
    const tasks = [fetchProfile()]
    if (auth.role === 'employee') tasks.push(fetchPublicCompanies())
    const results = await Promise.all(tasks)
    profile.value = results[0].data
    if (results[1]) companies.value = results[1].data
    fillForm(profile.value)
    auth.user = profile.value
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  saving.value = true
  try {
    const payload = {
      name: form.value.name,
      email: form.value.email,
    }
    if (form.value.password) {
      payload.password = form.value.password
      payload.password_confirmation = form.value.password_confirmation
    }
    if (isEmployee.value) {
      payload.company_id = form.value.company_id
      payload.monthly_salary = form.value.monthly_salary
      payload.advance_limit_pct = form.value.advance_limit_pct
      payload.is_active = form.value.is_active
    }
    if (isHr.value) {
      payload.company_name = form.value.company_name
      payload.company_sector = form.value.company_sector
      payload.company_contact_email = form.value.company_contact_email
    }
    const { data } = await updateProfile(payload)
    profile.value = data
    fillForm(data)
    auth.user = data
    form.value.password = ''
    form.value.password_confirmation = ''
    toast.success('Profil mis à jour.')
  } catch {
    /* intercepteur */
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="mx-auto max-w-lg">
    <h1 class="text-2xl font-bold text-[#262b47]">Mon profil</h1>
    <p class="mt-1 text-sm text-slate-500">{{ roleLabel }}</p>

    <LoadingState v-if="loading" class="mt-6" message="Chargement du profil…" compact />

    <form v-else-if="profile" class="card-mivoo mt-6 space-y-4 p-6" @submit.prevent="handleSubmit">
      <div>
        <label class="text-xs font-medium text-slate-600">Nom complet</label>
        <input v-model="form.name" required class="mt-1 w-full rounded-lg border px-3 py-2 text-[#262b47]" />
      </div>
      <div>
        <label class="text-xs font-medium text-slate-600">Email</label>
        <input v-model="form.email" type="email" required class="mt-1 w-full rounded-lg border px-3 py-2 text-[#262b47]" />
      </div>

      <template v-if="isHr">
        <hr class="border-slate-100" />
        <p class="text-sm font-semibold text-[#262b47]">Mon entreprise</p>
        <div>
          <label class="text-xs font-medium text-slate-600">Nom de l'entreprise</label>
          <input v-model="form.company_name" required class="mt-1 w-full rounded-lg border px-3 py-2 text-[#262b47]" />
        </div>
        <div>
          <label class="text-xs font-medium text-slate-600">Secteur</label>
          <input v-model="form.company_sector" class="mt-1 w-full rounded-lg border px-3 py-2 text-[#262b47]" />
        </div>
        <div>
          <label class="text-xs font-medium text-slate-600">Email contact RH</label>
          <input v-model="form.company_contact_email" type="email" required class="mt-1 w-full rounded-lg border px-3 py-2 text-[#262b47]" />
        </div>
      </template>

      <template v-if="isEmployee">
        <hr class="border-slate-100" />
        <p class="text-sm font-semibold text-[#262b47]">Informations employé</p>
        <div>
          <label class="text-xs font-medium text-slate-600">Entreprise</label>
          <select v-model.number="form.company_id" required class="mt-1 w-full rounded-lg border px-3 py-2 text-[#262b47]">
            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="text-xs font-medium text-slate-600">Salaire mensuel (FCFA)</label>
          <input v-model.number="form.monthly_salary" type="number" required min="0" class="mt-1 w-full rounded-lg border px-3 py-2 text-[#262b47]" />
        </div>
        <div>
          <label class="text-xs font-medium text-slate-600">Plafond avance (%)</label>
          <input v-model.number="form.advance_limit_pct" type="number" required min="1" max="100" class="mt-1 w-full rounded-lg border px-3 py-2 text-[#262b47]" />
        </div>
        <label class="flex items-center gap-2 text-sm text-[#262b47]">
          <input v-model="form.is_active" type="checkbox" />
          Compte actif
        </label>
      </template>

      <hr class="border-slate-100" />
      <p class="text-xs text-slate-500">Laissez vide pour ne pas changer le mot de passe.</p>
      <div>
        <label class="text-xs font-medium text-slate-600">Nouveau mot de passe</label>
        <div class="mt-1">
          <PasswordInput v-model="form.password" autocomplete="new-password" />
        </div>
      </div>
      <div>
        <label class="text-xs font-medium text-slate-600">Confirmation</label>
        <div class="mt-1">
          <PasswordInput v-model="form.password_confirmation" autocomplete="new-password" />
        </div>
      </div>
      <button type="submit" class="btn-mivoo w-full rounded-lg py-2.5 font-semibold" :disabled="saving">
        {{ saving ? 'Enregistrement…' : 'Enregistrer' }}
      </button>
    </form>
  </div>
</template>
