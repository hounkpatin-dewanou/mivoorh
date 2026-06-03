<script setup>
import { RouterLink } from 'vue-router'

defineOptions({ inheritAttrs: false })

defineProps({
  size: { type: String, default: 'md' },
  inverted: { type: Boolean, default: false },
  linkHome: { type: Boolean, default: false },
})

const sizes = {
  sm: 'text-lg',
  md: 'text-xl',
  lg: 'text-2xl',
  xl: 'text-3xl',
  hero: 'text-4xl md:text-5xl',
}
</script>

<template>
  <RouterLink
    v-if="linkHome"
    to="/"
    class="inline-block rounded font-bold tracking-tight transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#4294e3]/40"
    :class="[$attrs.class, sizes[size] || sizes.md, inverted ? 'text-white' : 'text-mivoo-gradient']"
    title="Retour à l'accueil"
  >
    MivooRH
  </RouterLink>
  <span
    v-else
    class="inline-block font-bold tracking-tight"
    :class="[
      sizes[size] || sizes.md,
      inverted ? 'text-white' : 'text-mivoo-gradient',
      $attrs.class,
    ]"
  >
    MivooRH
  </span>
</template>
