<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import BrandMark from '../components/BrandMark.vue'
import PasswordInput from '../components/ui/PasswordInput.vue'

const router = useRouter()
const auth = useAuthStore()
const toast = useToastStore()
const email = ref('rh1@mivoorh.test')
const password = ref('password')
const loading = ref(false)

async function handleSubmit() {
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    toast.success(`Bienvenue, ${auth.user?.name ?? ''}`)
    router.push({ name: 'dashboard' })
  } catch {
    /* erreur affichée par l'intercepteur Axios */
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-[80vh] items-center justify-center">
    <div class="card-mivoo w-full max-w-md overflow-hidden">
      <div class="bg-mivoo-gradient px-8 py-10 text-center text-white">
        <BrandMark size="xl" inverted link-home class="block" />
        <p class="mt-1 text-sm text-white/90">Bien-être financier de votre équipe</p>
      </div>
      <form class="space-y-4 p-8" @submit.prevent="handleSubmit">
        <div>
          <label class="mb-1 block text-sm font-medium text-[#262b47]" for="email">Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            required
            class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:border-[#4294e3] focus:outline-none focus:ring-2 focus:ring-[#4294e3]/30"
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-[#262b47]" for="password">Mot de passe</label>
          <PasswordInput
            id="password"
            v-model="password"
            required
            input-class="border-slate-200 focus:border-[#4294e3] focus:outline-none focus:ring-2 focus:ring-[#4294e3]/30"
          />
        </div>
        <button type="submit" class="btn-mivoo w-full rounded-lg py-2.5 font-semibold" :disabled="loading">
          {{ loading ? 'Connexion…' : 'Se connecter' }}
        </button>
        <p class="text-center text-sm text-slate-500">
          Pas de compte ?
          <router-link to="/register" class="font-medium text-[#4294e3] hover:underline">S'inscrire</router-link>
        </p>
      </form>
    </div>
  </div>
</template>
