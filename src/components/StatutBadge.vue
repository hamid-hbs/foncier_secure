<script setup>
const props = defineProps({ statut: { type: String, default: 'soumise' } })

const config = {
  soumise:         { cls: 'badge-neutral', label: 'Soumise' },
  en_analyse:      { cls: 'badge-info',    label: 'En analyse' },
  mission_assignee:{ cls: 'badge-purple',  label: 'Mission assignée' },
  terminee:        { cls: 'badge-success', label: 'Terminée' },
  validee:         { cls: 'badge-success', label: 'Validée' },
  rejetee:         { cls: 'badge-danger',  label: 'Rejetée' },
  en_cours:        { cls: 'badge-info',    label: 'En cours' },
  cloturee:        { cls: 'badge-success', label: 'Clôturée' },
  cree:            { cls: 'badge-neutral', label: 'Créée' },
  en_attente:      { cls: 'badge-warning', label: 'En attente' },
  traite:          { cls: 'badge-success', label: 'Traitée' },
  acceptee:        { cls: 'badge-success', label: 'Acceptée' },
  contestee:       { cls: 'badge-danger',  label: 'Contestée' },
  signale:         { cls: 'badge-warning', label: 'Signalé' },
  resolue:         { cls: 'badge-success', label: 'Résolue' },
  archive:         { cls: 'badge-neutral', label: 'Archivée' },
  ouvert:          { cls: 'badge-success', label: 'Ouvert' },
  ferme:           { cls: 'badge-neutral', label: 'Fermé' },
  refusee:         { cls: 'badge-danger',  label: 'Refusée' },
}

const current = config[props.statut] || { cls: 'badge-neutral', label: props.statut }
</script>

<template>
  <span class="badge" :class="current.cls">{{ current.label }}</span>
</template>
