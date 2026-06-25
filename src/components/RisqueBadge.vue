<script setup>
const props = defineProps({ niveau: { type: String, default: 'faible' } })

const config = {
  faible:   { cls: 'badge-success', label: 'Faible' },
  moyen:    { cls: 'badge-warning', label: 'Moyen' },
  eleve:    { cls: 'badge-danger',  label: 'Élevé' },
  critique: { cls: 'bg-red-200 text-red-900', label: 'Critique' },
}

const current = config[props.niveau] || config.faible
</script>

<template>
  <span class="badge" :class="current.cls">{{ current.label }}</span>
</template>
