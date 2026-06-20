<script setup>
const props = defineProps({
  statut: { type: String, default: 'soumise' }
})

const config = {
  soumise: { class: 'bg-slate-100 text-slate-700', label: 'Soumise' },
  en_analyse: { class: 'bg-blue-100 text-blue-700', label: 'En analyse' },
  mission_assignee: { class: 'bg-indigo-100 text-indigo-700', label: 'Mission assignée' },
  terminee: { class: 'bg-green-100 text-green-700', label: 'Terminée' },
  validee: { class: 'bg-emerald-100 text-emerald-700', label: 'Validée' },
  rejetee: { class: 'bg-red-100 text-red-700', label: 'Rejetée' },
  en_cours: { class: 'bg-blue-100 text-blue-700', label: 'En cours' },
  cloturee: { class: 'bg-green-100 text-green-700', label: 'Clôturée' },
  cree: { class: 'bg-slate-100 text-slate-700', label: 'Créée' },
  en_attente: { class: 'bg-yellow-100 text-yellow-700', label: 'En attente' },
  traite: { class: 'bg-green-100 text-green-700', label: 'Traitée' },
  acceptee: { class: 'bg-green-100 text-green-700', label: 'Acceptée' },
  contestee: { class: 'bg-red-100 text-red-700', label: 'Contestée' },
  signale: { class: 'bg-orange-100 text-orange-700', label: 'Signalé' },
  resolue: { class: 'bg-green-100 text-green-700', label: 'Résolue' },
  archive: { class: 'bg-slate-200 text-slate-600', label: 'Archivée' },
}

const current = config[props.statut] || config.soumise
</script>

<template>
  <span class="badge" :class="current.class">{{ current.label }}</span>
</template>
