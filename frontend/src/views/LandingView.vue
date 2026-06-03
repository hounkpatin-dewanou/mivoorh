<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import BrandMark from '../components/BrandMark.vue'
import RoleIcon from '../components/ui/RoleIcon.vue'
import CheckIcon from '../components/ui/CheckIcon.vue'

// Chiffres alignés sur le seed de démo (voir backend/database/seeders/DatabaseSeeder.php)
const stats = [
  { value: '2', label: 'Entreprises de démonstration' },
  { value: '10', label: 'Comptes employés pré-remplis' },
  { value: '40 %', label: 'Plafond d\'avance par défaut' },
  { value: '3', label: 'Profils : RH, employé, admin' },
]

const roles = [
  {
    title: 'Responsable RH',
    icon: 'hr',
    points: ['Dashboard temps réel', 'Gestion des employés', 'Validation des demandes', 'Export CSV paie'],
  },
  {
    title: 'Employé',
    icon: 'employee',
    points: ['Solde disponible', 'Demande d\'avance en ligne', 'Historique & notifications'],
  },
  {
    title: 'Super Admin',
    icon: 'admin',
    points: ['Activation des entreprises', 'Statistiques globales', 'Pilotage du réseau'],
  },
]

const benefits = [
  { title: 'Avances encadrées', desc: 'Le salaire et le plafond sont fixés par le RH ; le solde disponible est recalculé chaque mois.' },
  { title: 'Inscription en deux parcours', desc: 'Le responsable crée son entreprise ; l\'employé rejoint une société déjà active.' },
  { title: 'Suivi des demandes', desc: 'En attente, approuvée ou refusée — avec motif RH en cas de refus.' },
  { title: 'Export paie', desc: 'Le RH télécharge un CSV des avances pour le traitement de paie.' },
]

const steps = [
  { n: '01', title: 'Inscription entreprise', desc: 'Le RH crée son espace et configure l\'équipe.' },
  { n: '02', title: 'Paramétrage salarial', desc: 'Salaire mensuel et plafond d\'avance par employé (ex. 40 %).' },
  { n: '03', title: 'Demande & validation', desc: 'L\'employé demande une avance ; le RH approuve ou refuse.' },
]

const faq = [
  { q: 'Qu\'est-ce que MivooRH ?', a: 'Un portail web pour les entreprises qui proposent l\'accès anticipé au salaire gagné à leurs équipes.' },
  { q: 'Qui peut s\'inscrire ?', a: 'Les responsables RH et les employés d\'entreprises déjà actives sur la plateforme.' },
  { q: 'Comment est calculé le solde ?', a: 'Selon les jours travaillés dans le mois, le salaire et le plafond défini par l\'employeur.' },
]

const visible = ref(false)
const navScrolled = ref(false)

function onScroll() {
  navScrolled.value = window.scrollY > 24
}

onMounted(() => {
  visible.value = true
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
})
</script>

<template>
  <div class="min-h-screen overflow-x-hidden scroll-smooth">
    <!-- Navbar fixe -->
    <nav
      class="fixed inset-x-0 top-0 z-50 border-b transition-all duration-300"
      :class="navScrolled
        ? 'border-slate-200/80 bg-white/95 shadow-md backdrop-blur-md'
        : 'border-white/20 bg-white/10 backdrop-blur-lg'"
    >
      <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
        <RouterLink to="/">
          <BrandMark size="lg" :inverted="!navScrolled" />
        </RouterLink>
        <div class="flex items-center gap-1 text-sm font-medium sm:gap-2">
          <a
            href="#fonctionnalites"
            class="hidden rounded-lg px-3 py-2 transition sm:inline"
            :class="navScrolled ? 'text-[#262b47] hover:bg-[#f0f6ff]' : 'text-white hover:bg-white/10'"
          >Fonctionnalités</a>
          <a
            href="#comment"
            class="hidden rounded-lg px-3 py-2 transition sm:inline"
            :class="navScrolled ? 'text-[#262b47] hover:bg-[#f0f6ff]' : 'text-white hover:bg-white/10'"
          >Comment ça marche</a>
          <RouterLink
            to="/login"
            class="rounded-lg px-4 py-2 transition"
            :class="navScrolled ? 'text-[#4294e3] hover:bg-[#f0f6ff]' : 'text-white hover:bg-white/10'"
          >Connexion</RouterLink>
          <RouterLink
            to="/register"
            class="rounded-lg px-4 py-2 font-semibold shadow-md transition"
            :class="navScrolled
              ? 'bg-mivoo-gradient text-white hover:opacity-90'
              : 'bg-white text-[#4294e3] hover:bg-[#f0f6ff]'"
          >S'inscrire</RouterLink>
        </div>
      </div>
    </nav>

    <!-- Hero -->
    <header class="hero-animated-bg relative overflow-hidden pt-16 text-white">
      <div class="pointer-events-none absolute -left-20 top-28 h-64 w-64 rounded-full bg-white/10 blur-3xl animate-float" />
      <div class="pointer-events-none absolute -right-16 bottom-10 h-48 w-48 rounded-full bg-white/10 blur-2xl animate-float" style="animation-delay: 1.5s" />

      <div class="relative mx-auto max-w-6xl px-4 pb-24 pt-10 text-center md:pb-32 md:pt-16">
        <p class="animate-fade-in-up mb-4 inline-block rounded-full border border-white/30 bg-white/10 px-4 py-1 text-sm backdrop-blur">
          Portail RH — accès anticipé au salaire
        </p>
        <h1 class="animate-fade-in-up-delay-1 text-3xl font-bold leading-tight md:text-5xl lg:text-6xl">
          Gérez les avances sur salaire<br />de vos équipes
        </h1>
        <p class="animate-fade-in-up-delay-2 mx-auto mt-6 max-w-2xl text-lg text-white/90 md:text-xl">
          MivooRH permet à vos équipes RH de gérer les employés, valider les demandes d'avance sur salaire
          et d'exporter les données — en toute autonomie.
        </p>
        <div class="animate-fade-in-up-delay-3 mt-10 flex flex-wrap justify-center gap-4">
          <RouterLink to="/register" class="rounded-xl bg-white px-8 py-3.5 font-semibold text-[#4294e3] shadow-xl transition hover:scale-105 hover:bg-[#f0f6ff]">
            Créer un compte
          </RouterLink>
          <RouterLink to="/login" class="rounded-xl border-2 border-white px-8 py-3.5 font-semibold transition hover:scale-105 hover:bg-white/15">
            Se connecter
          </RouterLink>
        </div>
      </div>
    </header>

    <!-- Stats -->
    <section class="relative -mt-12 mx-auto max-w-5xl px-4">
      <div class="grid grid-cols-2 gap-4 rounded-2xl bg-white p-6 shadow-xl md:grid-cols-4">
        <div v-for="(s, i) in stats" :key="s.label" class="text-center landing-card-hover rounded-xl p-3" :style="{ animationDelay: `${i * 0.1}s` }">
          <p class="stat-counter text-2xl font-bold text-mivoo-gradient md:text-3xl">{{ s.value }}</p>
          <p class="mt-1 text-xs text-slate-500 md:text-sm">{{ s.label }}</p>
        </div>
      </div>
    </section>

    <!-- Roles -->
    <section id="fonctionnalites" class="mx-auto max-w-6xl px-4 py-20">
      <div class="text-center">
        <h2 class="text-3xl font-bold text-[#262b47]">Un espace pour chaque rôle</h2>
        <p class="mt-3 text-slate-600">Trois profils, un seul portail sécurisé</p>
      </div>
      <div class="mt-12 grid gap-8 md:grid-cols-3">
        <article
          v-for="(r, i) in roles"
          :key="r.title"
          class="card-mivoo landing-card-hover p-8"
          :class="visible && 'animate-fade-in-up'"
          :style="{ animationDelay: `${0.2 + i * 0.15}s`, opacity: visible ? undefined : 0 }"
        >
          <RoleIcon :name="r.icon" />
          <h3 class="mt-5 text-xl font-bold text-[#262b47]">{{ r.title }}</h3>
          <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
            <li v-for="p in r.points" :key="p" class="flex gap-2.5">
              <CheckIcon />
              <span>{{ p }}</span>
            </li>
          </ul>
        </article>
      </div>
    </section>

    <!-- Benefits -->
    <section class="bg-[#f0f6ff] py-20">
      <div class="mx-auto max-w-6xl px-4">
        <h2 class="text-center text-3xl font-bold text-mivoo-gradient">Ce que propose le portail</h2>
        <div class="mt-12 grid gap-6 sm:grid-cols-2">
          <div
            v-for="b in benefits"
            :key="b.title"
            class="card-mivoo landing-card-hover border-l-4 border-[#4294e3] p-6"
          >
            <h3 class="font-bold text-[#262b47]">{{ b.title }}</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ b.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Steps -->
    <section id="comment" class="mx-auto max-w-6xl px-4 py-20">
      <h2 class="text-center text-3xl font-bold text-[#262b47]">Comment ça marche</h2>
      <div class="mt-14 grid gap-8 md:grid-cols-3">
        <div v-for="step in steps" :key="step.n" class="relative text-center landing-card-hover rounded-2xl bg-white p-8 shadow-sm">
          <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-mivoo-gradient text-lg font-bold text-white">{{ step.n }}</span>
          <h3 class="mt-4 text-lg font-bold">{{ step.title }}</h3>
          <p class="mt-2 text-sm text-slate-600">{{ step.desc }}</p>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="mx-auto max-w-3xl px-4 py-16">
      <h2 class="text-center text-2xl font-bold text-[#262b47]">Questions fréquentes</h2>
      <dl class="mt-8 space-y-4">
        <div v-for="item in faq" :key="item.q" class="card-mivoo p-5 landing-card-hover">
          <dt class="font-semibold text-[#262b47]">{{ item.q }}</dt>
          <dd class="mt-2 text-sm text-slate-600">{{ item.a }}</dd>
        </div>
      </dl>
    </section>

    <!-- CTA -->
    <section class="mx-auto max-w-6xl px-4 pb-20">
      <div class="rounded-3xl bg-mivoo-gradient px-8 py-14 text-center text-white shadow-2xl">
        <div class="flex justify-center">
          <BrandMark size="hero" inverted />
        </div>
        <p class="mx-auto mt-4 max-w-xl text-lg text-white/90">
          Créez un compte RH pour une nouvelle entreprise, ou inscrivez-vous en tant qu'employé d'une société déjà enregistrée.
        </p>
        <RouterLink to="/register" class="mt-8 inline-block rounded-xl bg-white px-10 py-4 font-bold text-[#4294e3] shadow-lg transition hover:scale-105">
          Créer un compte
        </RouterLink>
      </div>
    </section>

    <footer class="border-t border-slate-200 bg-white py-10 text-center text-sm text-slate-500">
      <p class="font-semibold text-mivoo-gradient">MivooRH</p>
      <p class="mt-2">© {{ new Date().getFullYear() }} — Portail de gestion RH & avances sur salaire</p>
      <div class="mt-4 flex justify-center gap-4">
        <RouterLink to="/login" class="text-[#4294e3] hover:underline">Connexion</RouterLink>
        <RouterLink to="/register" class="text-[#4294e3] hover:underline">Inscription</RouterLink>
      </div>
    </footer>
  </div>
</template>
