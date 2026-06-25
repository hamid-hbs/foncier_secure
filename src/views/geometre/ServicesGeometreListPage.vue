<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import serviceGeometreApi from '@/api/serviceGeometre'

const router = useRouter()
const services = ref([])
const loading = ref(true)
const filter = ref('')

const filtres = [
  { value: '', label: 'Tous' },
  { value: 'soumis', label: 'Soumis' },
  { value: 'accepte', label: 'Accepté' },
  { value: 'refuse', label: 'Refusé' },
  { value: 'rapport_depose', label: 'Rapport déposé' },
]

onMounted(async () => {
  try {
    const params = {}
    if (filter.value) params.statut = filter.value
    const res = await serviceGeometreApi.list(params)
    services.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement services:', e) }
  loading.value = false
})

function statutBadgeClass(s) {
  const map = { soumis: 'badge-neutral', accepte: 'badge-success', refuse: 'badge-danger', rapport_depose: 'badge-info' }
  return map[s] || 'badge-neutral'
}
function statutLabel(s) {
  const map = { soumis: 'Soumis', accepte: 'Accepté', refuse: 'Refusé', rapport_depose: 'Rapport déposé' }
  return map[s] || s
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>

<template>
  <div class="page-wrap">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Demandes de service</h1>
        <p class="page-subtitle">Interventions géomètre sollicitées</p>
      </div>
      <select v-model="filter" class="form-select w-full sm:w-44" @change="loading = true; onMounted()">
        <option v-for="f in filtres" :key="f.value" :value="f.value">{{ f.label }}</option>
      </select>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="skeleton h-24 rounded-2xl"></div>
    </div>

    <div v-else-if="services.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-ruler-combined"></i></div>
        <p class="empty-title">Aucune demande</p>
        <p class="empty-text">Aucune intervention géomètre trouvée.</p>
      </div>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="s in services" :key="s.id"
        @click="router.push(`/geometre/services-geometre/${s.id}`)"
        class="card group hover:border-brand-100 hover:shadow-md cursor-pointer transition-all"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center text-brand shrink-0 group-hover:bg-brand group-hover:text-white transition-colors">
            <i class="fas fa-ruler-combined text-sm"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <p class="font-display font-bold text-stone-900 group-hover:text-brand transition-colors truncate">
                {{ s.type_service || s.libelle || 'Service #' + s.id }}
              </p>
              <span class="badge" :class="statutBadgeClass(s.statut)">{{ statutLabel(s.statut) }}</span>
            </div>
            <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-stone-400">
              <span v-if="s.parcelle"><i class="fas fa-map-marker-alt mr-1.5 text-stone-300"></i>{{ s.parcelle?.titre || 'Parcelle #' + s.parcelle_id }}</span>
              <span><i class="fas fa-calendar mr-1.5 text-stone-300"></i>{{ formatDate(s.created_at) }}</span>
              <span v-if="s.demandeur"><i class="fas fa-user mr-1.5 text-stone-300"></i>{{ s.demandeur?.prenom }} {{ s.demandeur?.nom }}</span>
            </div>
          </div>
          <i class="fas fa-chevron-right text-sm text-stone-300 group-hover:text-brand transition-colors hidden sm:block"></i>
        </div>
      </div>
    </div>
  </div>
</template>
