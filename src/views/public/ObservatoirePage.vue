<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import observatoireApi from '@/api/observatoire'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const stats = ref(null)
const loading = ref(true)

const monthNames = [
  'Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin',
  'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre',
]

const evolutionMensuelle = computed(() => stats.value?.evolution_mensuelle || [])
const superficiesParCommune = computed(() => stats.value?.prix_moyen?.par_commune || [])
const maxMonthlyValue = computed(() => {
  const values = evolutionMensuelle.value.flatMap((item) => [
    item.transactions || 0,

    item.verifications || 0,

  ])
  return Math.max(...values, 1)
})

function formatNumber(value) {
  return new Intl.NumberFormat('fr-FR').format(value || 0)
}

function formatArea(value) {
  return `${formatNumber(Math.round(value || 0))} m2`
}

function barWidth(value) {
  return `${Math.max(((value || 0) / maxMonthlyValue.value) * 100, value ? 8 : 0)}%`
}

onMounted(async () => {
  try {
    const res = await observatoireApi.getStats()
    stats.value = res.data
  } catch {
    stats.value = null
  }
  loading.value = false
})
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="mb-8">
      <h1 class="section-title">Observatoire du marche foncier</h1>
      <p class="section-subtitle">Statistiques et tendances du marche immobilier au Benin</p>
    </div>

    <div v-if="loading" class="text-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin mx-auto" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <template v-else-if="stats">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #F0F7F4;">
              <i class="fas fa-arrows-left-right" style="color: var(--green-tree);"></i>
            </div>
            <div>
              <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ formatNumber(stats.transactions?.total) }}</p>
              <p class="text-sm" style="color: var(--text-secondary);">Transactions</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
            <div>
              <p class="font-semibold" style="color: var(--text-primary);">{{ formatNumber(stats.transactions?.en_cours) }}</p>
              <p style="color: var(--text-secondary);">En cours</p>
            </div>
            <div>
              <p class="font-semibold" style="color: var(--text-primary);">{{ formatNumber(stats.transactions?.cloturees) }}</p>
              <p style="color: var(--text-secondary);">Cloturees</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #EBF4F8;">
              <i class="fas fa-shield-alt" style="color: #457B9D;"></i>
            </div>
            <div>
              <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ formatNumber(stats.verifications?.total) }}</p>
              <p class="text-sm" style="color: var(--text-secondary);">Verifications</p>
            </div>
          </div>
          <div class="grid grid-cols-3 gap-2 mt-4 text-sm">
            <div>
              <p class="font-semibold" style="color: var(--green-tree);">{{ formatNumber(stats.verifications?.faible) }}</p>
              <p style="color: var(--text-secondary);">Faible</p>
            </div>
            <div>
              <p class="font-semibold" style="color: var(--gold);">{{ formatNumber(stats.verifications?.moyen) }}</p>
              <p style="color: var(--text-secondary);">Moyen</p>
            </div>
            <div>
              <p class="font-semibold" style="color: var(--danger);">{{ formatNumber(stats.verifications?.eleve) }}</p>
              <p style="color: var(--text-secondary);">Eleve</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #FEF7EF;">
              <i class="fas fa-calendar" style="color: var(--gold);"></i>
            </div>
            <div>
              <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ formatNumber(stats.transactions?.ce_mois) }}</p>
              <p class="text-sm" style="color: var(--text-secondary);">Transactions ce mois</p>
            </div>
          </div>
          <p class="text-sm mt-4" style="color: var(--text-secondary);">Activite mensuelle suivie sur les dossiers et verifications.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-[1.5fr_1fr] gap-6 mt-6">
        <section class="card">
          <div class="card-header">
            <div>
              <h2 class="card-title">Evolution mensuelle</h2>
              <p class="card-subtitle">Volumes par type d'activite</p>
            </div>
          </div>

          <div class="space-y-5">
            <div v-for="item in evolutionMensuelle" :key="item.mois">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold" style="color: var(--text-primary);">{{ monthNames[(item.mois || 1) - 1] }}</p>
                <p class="text-xs" style="color: var(--text-secondary);">{{ formatNumber((item.transactions || 0) + (item.verifications || 0)) }} activites</p>
              </div>
              <div class="space-y-1.5">
                <div class="observatoire-bar-row">
                  <span>Transactions</span>
                  <div><i :style="{ width: barWidth(item.transactions), background: 'var(--green-tree)' }"></i></div>
                  <strong>{{ item.transactions || 0 }}</strong>
                </div>
                <div class="observatoire-bar-row">
                  <span>Verifications</span>
                  <div><i :style="{ width: barWidth(item.verifications), background: 'var(--info)' }"></i></div>
                  <strong>{{ item.verifications || 0 }}</strong>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="card">
          <div class="card-header">
            <div>
              <h2 class="card-title">Superficie moyenne</h2>
              <p class="card-subtitle">Par commune</p>
            </div>
          </div>

          <div v-if="superficiesParCommune.length" class="space-y-3">
            <div v-for="item in superficiesParCommune" :key="item.commune" class="flex items-center justify-between gap-4 py-2" style="border-bottom: 1px solid var(--border);">
              <span class="text-sm font-medium truncate" style="color: var(--text-primary);">{{ item.commune }}</span>
              <span class="text-sm font-semibold whitespace-nowrap" style="color: var(--green-tree);">{{ formatArea(item.superficie_moyenne) }}</span>
            </div>
          </div>
          <p v-else class="text-sm" style="color: var(--text-secondary);">Aucune commune avec parcelles mesurees.</p>
        </section>
      </div>
    </template>

    <div v-else class="card text-center py-12">
      <i class="fas fa-chart-bar mb-3" style="color: #D1D5DB; font-size: 3rem;"></i>
      <p style="color: var(--text-secondary);">Aucune statistique disponible.</p>
    </div>
  </div>
</template>

<style scoped>
.observatoire-bar-row {
  display: grid;
  grid-template-columns: 92px 1fr 32px;
  align-items: center;
  gap: 10px;
  font-size: 12px;
  color: var(--text-secondary);
}

.observatoire-bar-row div {
  height: 8px;
  overflow: hidden;
  border-radius: 999px;
  background: var(--bg-page);
}

.observatoire-bar-row i {
  display: block;
  height: 100%;
  min-width: 0;
  border-radius: 999px;
}

.observatoire-bar-row strong {
  text-align: right;
  color: var(--text-primary);
}
</style>
