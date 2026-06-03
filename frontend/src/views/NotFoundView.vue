<script setup>
import { RouterLink } from 'vue-router'
import BrandMark from '../components/BrandMark.vue'
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-[#f0f6ff] px-4 text-center">
    <BrandMark size="hero" link-home />
    <h1 class="mt-6 text-6xl font-bold text-mivoo-gradient">404</h1>
    <p class="mt-2 text-slate-600">Cette page n'existe pas.</p>
    <RouterLink to="/login" class="btn-mivoo mt-8 inline-block rounded-lg px-6 py-2">Connexion</RouterLink>
  </div>
</template>
