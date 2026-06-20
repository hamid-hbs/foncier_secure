<script setup>
const props = defineProps({
  niveau: { type: String, default: 'faible' }
})

const config = {
  faible: { class: 'bg-green-100 text-green-800', label: 'Faible' },
  moyen: { class: 'bg-yellow-100 text-yellow-800', label: 'Moyen' },
  eleve: { class: 'bg-red-100 text-red-800', label: 'Élevé' },
  critique: { class: 'bg-red-200 text-red-900', label: 'Critique' },
}

const current = config[props.niveau] || config.faible
</script>

<template>
  <span class="badge" :class="current.class">{{ current.label }}</span>
</template>
