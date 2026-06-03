<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { fetchPublicCompanies } from '../api/companies'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import BrandMark from '../components/BrandMark.vue'
import PasswordInput from '../components/ui/PasswordInput.vue'
import RoleIcon from '../components/ui/RoleIcon.vue'

const router = useRouter()
const auth = useAuthStore()
const toast = useToastStore()
const companies = ref([])
const step = ref('choice')
const loading = ref(false)

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  company_id: '',
  company_name: '',
  company_sector: '',
  company_contact_email: '',
  monthly_salary: '',
  advance_limit_pct: '',
})

onMounted(async () => {
  const { data } = await fetchPublicCompanies()
  companies.value = data
  if (data.length) form.value.company_id = data[0].id
})

function chooseAccountType(type) {
  step.value = type
}

function goBack() {
  step.value = 'choice'
}

async function submitHr() {
  loading.value = true
  try {
    await auth.register({
      name: form.value.name,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
      role: 'hr',
      company_name: form.value.company_name,
      company_sector: form.value.company_sector,
      company_contact_email: form.value.company_contact_email || form.value.email,
    })
    toast.success('Entreprise et compte RH créés.')
    router.push({ name: 'dashboard' })
  } catch {
    /* intercepteur */
  } finally {
    loading.value = false
  }
}

async function submitEmployee() {
  loading.value = true
  try {
    await auth.register({
      name: form.value.name,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
      role: 'employee',
      company_id: form.value.company_id,
      monthly_salary: form.value.monthly_salary,
      advance_limit_pct: form.value.advance_limit_pct,
    })
    toast.success('Compte employé créé.')
    router.push({ name: 'dashboard' })
  } catch {
    /* intercepteur */
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-[80vh] items-center justify-center bg-[#f0f6ff]/50 py-8">
    <div class="card-mivoo w-full max-w-lg overflow-hidden">
      <div class="relative bg-mivoo-gradient px-8 py-6 text-center text-white">
        <button
          v-if="step !== 'choice'"
          type="button"
          class="absolute left-4 top-6 rounded-full p-2 text-white transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/40"
          aria-label="Retour au choix du type de compte"
          @click="goBack"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <BrandMark size="lg" inverted link-home class="block" />
        <h1 class="mt-3 text-xl font-bold">Créer un compte</h1>
        <p class="mt-1 text-sm text-white/80">
          <template v-if="step === 'choice'">Quel type de compte souhaitez-vous ?</template>
          <template v-else-if="step === 'hr'">Inscription responsable & entreprise</template>
          <template v-else>Inscription employé</template>
        </p>
      </div>

      <!-- Étape 1 : choix du type -->
      <div v-if="step === 'choice'" class="space-y-4 p-8">
        <button
          type="button"
          class="flex w-full items-center gap-4 rounded-xl border-2 border-transparent bg-[#f0f6ff] p-4 text-left transition hover:border-[#4294e3] hover:shadow-md"
          @click="chooseAccountType('hr')"
        >
          <RoleIcon name="hr" />
          <div>
            <p class="font-semibold text-[#262b47]">Responsable RH / Entreprise</p>
            <p class="mt-1 text-sm text-slate-600">
              Créez votre entreprise et votre compte administrateur en une seule étape. Une entreprise = un compte responsable.
            </p>
          </div>
        </button>
        <button
          type="button"
          class="flex w-full items-center gap-4 rounded-xl border-2 border-transparent bg-[#f0f6ff] p-4 text-left transition hover:border-[#4294e3] hover:shadow-md"
          @click="chooseAccountType('employee')"
        >
          <RoleIcon name="employee" />
          <div>
            <p class="font-semibold text-[#262b47]">Employé</p>
            <p class="mt-1 text-sm text-slate-600">
              Rejoignez une entreprise déjà inscrite sur MivooRH pour accéder aux avances sur salaire.
            </p>
          </div>
        </button>
        <router-link to="/login" class="block text-center text-sm text-[#4294e3]">Déjà inscrit ? Connexion</router-link>
      </div>

      <!-- Étape 2a : RH + création entreprise -->
      <form v-else-if="step === 'hr'" class="space-y-4 p-8" @submit.prevent="submitHr">
        <input v-model="form.company_name" required placeholder="Nom de l'entreprise *" class="w-full rounded-lg border px-3 py-2" />
        <input v-model="form.company_sector" placeholder="Secteur (ex. Technologie)" class="w-full rounded-lg border px-3 py-2" />
        <input
          v-model="form.company_contact_email"
          type="email"
          placeholder="Email contact RH (optionnel, sinon votre email)"
          class="w-full rounded-lg border px-3 py-2"
        />
        <hr class="border-slate-100" />
        <input v-model="form.name" required placeholder="Votre nom complet *" class="w-full rounded-lg border px-3 py-2" />
        <input v-model="form.email" type="email" required placeholder="Votre email *" class="w-full rounded-lg border px-3 py-2" />
        <div class="grid gap-3 sm:grid-cols-2">
          <div>
            <label class="text-xs font-medium text-slate-600">Mot de passe</label>
            <div class="mt-1">
              <PasswordInput v-model="form.password" required :minlength="8" autocomplete="new-password" />
            </div>
          </div>
          <div>
            <label class="text-xs font-medium text-slate-600">Confirmation</label>
            <div class="mt-1">
              <PasswordInput v-model="form.password_confirmation" required autocomplete="new-password" />
            </div>
          </div>
        </div>
        <button type="submit" class="btn-mivoo w-full rounded-lg py-2.5 font-semibold" :disabled="loading">
          {{ loading ? 'Création…' : 'Créer mon entreprise et mon compte' }}
        </button>
      </form>

      <!-- Étape 2b : employé -->
      <form v-else class="space-y-4 p-8" @submit.prevent="submitEmployee">
        <div>
          <label class="text-xs font-medium text-slate-600">Entreprise *</label>
          <select v-model.number="form.company_id" required class="mt-1 w-full rounded-lg border px-3 py-2">
            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <p v-if="!companies.length" class="mt-1 text-xs text-amber-600">
            Aucune entreprise disponible. Un responsable RH doit d'abord inscrire son entreprise.
          </p>
        </div>
        <input v-model="form.name" required placeholder="Nom complet *" class="w-full rounded-lg border px-3 py-2" />
        <input v-model="form.email" type="email" required placeholder="Email *" class="w-full rounded-lg border px-3 py-2" />
        <div class="grid gap-3 sm:grid-cols-2">
          <div>
            <label class="text-xs font-medium text-slate-600">Mot de passe</label>
            <div class="mt-1">
              <PasswordInput v-model="form.password" required :minlength="8" autocomplete="new-password" />
            </div>
          </div>
          <div>
            <label class="text-xs font-medium text-slate-600">Confirmation</label>
            <div class="mt-1">
              <PasswordInput v-model="form.password_confirmation" required autocomplete="new-password" />
            </div>
          </div>
        </div>
        <input v-model.number="form.monthly_salary" type="number" required placeholder="Salaire mensuel (FCFA) *" class="w-full rounded-lg border px-3 py-2" />
        <input v-model.number="form.advance_limit_pct" type="number" min="1" max="100" placeholder="Plafond avance %" class="w-full rounded-lg border px-3 py-2" />
        <button type="submit" class="btn-mivoo w-full rounded-lg py-2.5 font-semibold" :disabled="loading || !companies.length">
          {{ loading ? 'Création…' : 'Créer mon compte employé' }}
        </button>
        <router-link to="/login" class="block text-center text-sm text-[#4294e3]">Déjà inscrit ? Connexion</router-link>
      </form>
    </div>
  </div>
</template>
