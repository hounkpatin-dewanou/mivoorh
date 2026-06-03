<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useToastStore } from '../../stores/toast'
import BrandMark from '../BrandMark.vue'

const props = defineProps({
  links: { type: Array, required: true },
  title: { type: String, default: 'MivooRH' },
})

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const companyName = computed(() => auth.user?.company?.name ?? '')

const toast = useToastStore()

async function logout() {
  await auth.logout()
  toast.info('Vous êtes déconnecté.')
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="flex min-h-screen">
    <aside class="hidden w-64 shrink-0 bg-mivoo-gradient p-6 text-white md:flex md:flex-col">
      <div class="mb-8">
        <BrandMark size="lg" inverted />
        <p v-if="title !== 'MivooRH'" class="mt-1 text-xs text-white/70">{{ title }}</p>
      </div>
      <p v-if="companyName" class="mb-6 text-sm text-white/80">{{ companyName }}</p>
      <nav class="flex flex-1 flex-col gap-1">
        <router-link
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="rounded-lg px-3 py-2 text-sm transition"
          :class="route.path.startsWith(link.to) ? 'bg-white/20 font-semibold' : 'hover:bg-white/10'"
        >
          {{ link.label }}
        </router-link>
      </nav>
      <button type="button" class="mt-4 rounded-lg border border-white/30 px-3 py-2 text-sm hover:bg-white/10" @click="logout">
        Déconnexion
      </button>
    </aside>
    <div class="flex flex-1 flex-col">
      <header class="flex items-center justify-between border-b bg-white px-4 py-3 md:hidden">
        <BrandMark size="md" />
        <button type="button" class="text-sm text-[#4294e3]" @click="logout">Déconnexion</button>
      </header>
      <nav class="flex gap-2 overflow-x-auto border-b bg-white px-4 py-2 md:hidden">
        <router-link
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="whitespace-nowrap rounded-full px-3 py-1 text-xs"
          :class="route.path.startsWith(link.to) ? 'bg-mivoo-gradient text-white' : 'bg-[#f0f6ff] text-[#262b47]'"
        >
          {{ link.label }}
        </router-link>
      </nav>
      <main class="flex-1 p-4 md:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>
