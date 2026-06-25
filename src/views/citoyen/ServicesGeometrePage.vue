<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import serviceGeometreApi from '@/api/serviceGeometre'
import StatutBadge from '@/components/StatutBadge.vue'

const router = useRouter()
const services = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await serviceGeometreApi.list()
    services.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement services:', e) }
  loading.value = false
})

function statutBadgeClass(s) {
  const map = { soumise: 'badge-neutral', acceptee: 'badge-success', refusee: 'badge-danger', terminee: 'badge-info' }
  return map[s] || 'badge-neutral'
}
function statutLabel(s) {
  const map = { soumise: 'Soumise', acceptee: 'Acceptée', refusee: 'Refusée', terminee: 'Terminée' }
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
        <h1 class="page-title">Services géomètre</h1>
        <p class="page-subtitle">Demandes de levé topographique et bornage</p>
      </div>
      <router-link to="/citoyen/services-geometre/creer" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvelle demande
      </router-link>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="skeleton h-24 rounded-2xl"></div>
    </div>

    <div v-else-if="services.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-ruler-combined"></i></div>
        <p class="empty-title">Aucune demande</p>
        <p class="empty-text">Vous n'avez pas encore sollicité de service géomètre.</p>
        <router-link to="/citoyen/services-geometre/creer" class="btn btn-primary mt-4">
          <i class="fas fa-plus"></i> Faire une demande
        </router-link>
      </div>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="s in services" :key="s.id"
        @click="router.push(`/citoyen/services-geometre/${s.id}`)"
        class="card group hover:border-brand-100 hover:shadow-md cursor-pointer transition-all"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-colors"
            :class="s.statut === 'accepte' ? 'bg-success/10 text-success group-hover:bg-success group-hover:text-white' : s.statut === 'refuse' ? 'bg-danger/10 text-danger' : 'bg-brand-50 text-brand group-hover:bg-brand group-hover:text-white'">
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
              <span v-if="s.parcelle"><i class="fas fa-map-marker-alt mr-1.5 text-stone-300"></i>{{ s.parcelle?.titre || s.parcelle?.code || 'Parcelle #' + s.parcelle_id }}</span>
              <span><i class="fas fa-calendar mr-1.5 text-stone-300"></i>{{ formatDate(s.created_at) }}</span>
              <span v-if="s.geometre"><i class="fas fa-user-tie mr-1.5 text-stone-300"></i>{{ s.geometre?.prenom }} {{ s.geometre?.nom }}</span>
            </div>
          </div>
          <i class="fas fa-chevron-right text-sm text-stone-300 group-hover:text-brand transition-colors hidden sm:block"></i>
        </div>
      </div>
    </div>
  </div>
</template>
