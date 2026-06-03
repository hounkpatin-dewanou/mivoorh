<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()
const title = route.meta.title || route.name
</script>

<template>
  <div class="rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
    <h1 class="text-2xl font-semibold text-slate-800">{{ title }}</h1>
    <p class="mt-2 text-slate-500">Page en construction — {{ route.path }}</p>
  </div>
</template>
