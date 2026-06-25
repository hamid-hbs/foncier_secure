<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import verificationApi from '@/api/verification'

const router = useRouter()
const missions = ref([])
const loading = ref(true)
const filter = ref('all')

const filteredMissions = computed(() => {
  if (filter.value === 'all') return missions.value
  if (filter.value === 'en_cours') return missions.value.filter(m => ['soumise', 'en_analyse', 'mission_assignee'].includes(m.statut))
  if (filter.value === 'terminee') return missions.value.filter(m => ['terminee', 'validee'].includes(m.statut))
  return missions.value
})

onMounted(async () => {
  try {
    const res = await verificationApi.list()
    missions.value = (res.data.data || res.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement missions:', e) }
  loading.value = false
})

function statutBadgeClass(s) {
  const map = { soumise: 'badge-neutral', acceptee: 'badge-success', refusee: 'badge-danger', terminee: 'badge-success' }
  return map[s] || 'badge-neutral'
}
function statutLabel(s) {
  const map = { soumise: 'Soumise', acceptee: 'Acceptée', refusee: 'Refusée', terminee: 'Terminée' }
  return map[s] || s
}

function canSubmitRapport(m) {
  return ['acceptee'].includes(m.statut)
}
</script>

<template>
  <div class="page-wrap">
    <div class="flex items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Mes missions</h1>
        <p class="page-subtitle">Toutes vos missions de vérification foncière</p>
      </div>
    </div>

    <div class="tabs mb-6">
      <button class="tab" :class="filter === 'all' ? 'active' : ''" @click="filter = 'all'">Toutes ({{ missions.length }})</button>
      <button class="tab" :class="filter === 'en_cours' ? 'active' : ''" @click="filter = 'en_cours'">
        En cours ({{ missions.filter(m => ['soumise','en_analyse','mission_assignee'].includes(m.statut)).length }})
      </button>
      <button class="tab" :class="filter === 'terminee' ? 'active' : ''" @click="filter = 'terminee'">
        Terminées ({{ missions.filter(m => ['terminee','validee'].includes(m.statut)).length }})
      </button>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 5" :key="i" class="skeleton h-24 rounded-2xl"></div>
    </div>

    <div v-else-if="filteredMissions.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-map-location-dot"></i></div>
        <p class="empty-title">Aucune mission</p>
        <p class="empty-text">Vous n'avez pas encore de mission dans cette catégorie.</p>
      </div>
    </div>

    <div v-else class="space-y-3">
      <div v-for="m in filteredMissions" :key="m.id"
        class="card group hover:border-brand-100 hover:shadow-md transition-all"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center text-brand shrink-0 group-hover:bg-brand group-hover:text-white transition-colors">
            <i class="fas fa-ruler-combined text-sm"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <p class="font-display font-bold text-stone-900">
                {{ m.parcelle?.titre || m.parcelle?.code || 'Parcelle #' + (m.parcelle_id || m.id) }}
              </p>
              <span class="badge" :class="statutBadgeClass(m.statut)">{{ statutLabel(m.statut) }}</span>
            </div>
            <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-stone-400">
              <span v-if="m.parcelle?.commune"><i class="fas fa-location-dot mr-1.5 text-stone-300"></i>{{ m.parcelle.commune?.nom }}</span>
              <span><i class="fas fa-calendar mr-1.5 text-stone-300"></i>{{ m.created_at ? new Date(m.created_at).toLocaleDateString('fr-FR') : '—' }}</span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <router-link v-if="canSubmitRapport(m)" :to="{ name: 'DepotRapport', params: { id: m.id } }"
              class="btn btn-primary btn-sm" @click.stop>
              <i class="fas fa-file-pen"></i> Soumettre rapport
            </router-link>
            <router-link v-else :to="{ name: 'DepotRapport', params: { id: m.id } }"
              class="btn btn-ghost btn-sm" @click.stop>
              <i class="fas fa-eye"></i> Voir
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
