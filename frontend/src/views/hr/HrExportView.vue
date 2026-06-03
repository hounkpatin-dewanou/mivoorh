<script setup>
import { computed, ref } from 'vue'
import { downloadHrExportCsv } from '../../api/advanceRequests'
import { useToastStore } from '../../stores/toast'

const toast = useToastStore()
const month = ref(new Date().getMonth() + 1)
const year = ref(new Date().getFullYear())
const loading = ref(false)

const monthLabels = [
  'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
  'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
]

const periodLabel = computed(() => `${monthLabels[month.value - 1]} ${year.value}`)

async function exportCsv() {
  loading.value = true
  try {
    const { data } = await downloadHrExportCsv(month.value, year.value)
    const blob = new Blob([data], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `mivoorh-demandes-${year.value}-${String(month.value).padStart(2, '0')}.csv`
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
    toast.success(`Export CSV téléchargé — ${periodLabel.value}`)
  } catch {
    /* message via intercepteur Axios */
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-[#262b47]">Export CSV — synchronisation paie</h1>
    <p class="mt-2 max-w-2xl text-sm text-slate-600">
      Exporte toutes les demandes d'avance créées pendant le mois choisi (séparateur <code>;</code>, encodage UTF-8).
      Fichier compatible Excel / logiciels de paie.
    </p>

    <div class="card-mivoo mt-6 max-w-lg p-6">
      <p class="mb-4 rounded-lg bg-[#f0f6ff] px-3 py-2 text-sm text-[#262b47]">
        Période sélectionnée : <strong>{{ periodLabel }}</strong>
      </p>
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="text-sm font-medium text-[#262b47]" for="export-month">Mois</label>
          <select id="export-month" v-model.number="month" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2">
            <option v-for="(label, i) in monthLabels" :key="i" :value="i + 1">{{ label }}</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-medium text-[#262b47]" for="export-year">Année</label>
          <input
            id="export-year"
            v-model.number="year"
            type="number"
            min="2020"
            max="2100"
            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2"
          />
        </div>
      </div>
      <button
        type="button"
        class="btn-mivoo mt-6 w-full rounded-lg py-2.5 font-semibold disabled:opacity-50"
        :disabled="loading"
        @click="exportCsv"
      >
        {{ loading ? 'Génération du fichier…' : 'Télécharger le CSV' }}
      </button>
      <ul class="mt-4 list-inside list-disc text-xs text-slate-500">
        <li>Colonnes : employé, montant, motif, statut, date de traitement, commentaire RH</li>
        <li>Connexion RH requise — le token est envoyé automatiquement</li>
      </ul>
    </div>
  </div>
</template>
