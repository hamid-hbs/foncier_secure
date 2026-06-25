<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import serviceGeometreApi from '@/api/serviceGeometre'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const service = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await serviceGeometreApi.show(route.params.id)
    service.value = res.data || null
  } catch (e) { console.error('Erreur chargement service:', e) }
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
  <div class="page-wrap max-w-3xl mx-auto">
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-28 rounded-2xl"></div>
      <div class="skeleton h-48 rounded-2xl"></div>
    </div>

    <div v-else-if="!service" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-ruler-combined"></i></div>
        <p class="empty-title">Service introuvable</p>
        <button @click="goBack(router)" class="btn btn-primary mt-4">Retour</button>
      </div>
    </div>

    <template v-else>
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
        <i class="fas fa-arrow-left text-xs"></i> Mes demandes
      </button>

      <div class="card mb-5">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <h1 class="font-display font-bold text-xl text-stone-900">
                {{ service.type_service || service.libelle || 'Service #' + service.id }}
              </h1>
              <span class="badge" :class="statutBadgeClass(service.statut)">{{ statutLabel(service.statut) }}</span>
            </div>
            <p class="text-sm text-stone-400">
              <i class="fas fa-calendar mr-1.5 text-stone-300"></i>
              Soumis le {{ formatDate(service.created_at) }}
            </p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
        <div class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Parcelle</p>
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center text-brand text-xs shrink-0">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-stone-900">{{ service.parcelle?.titre || '#' + service.parcelle_id }}</p>
              <p class="text-xs text-stone-400">{{ service.parcelle?.commune?.nom || '' }}</p>
            </div>
          </div>
        </div>

        <div class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Type de service</p>
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gold/10 flex items-center justify-center text-gold-dark text-xs shrink-0">
              <i class="fas fa-ruler-combined"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-stone-900 capitalize">{{ (service.type_service || '—').replace(/_/g, ' ') }}</p>
            </div>
          </div>
        </div>

        <div v-if="service.geometre" class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Géomètre assigné</p>
          <div class="flex items-center gap-3">
            <div class="avatar avatar-sm bg-brand shrink-0">{{ (service.geometre?.prenom || 'G')[0] }}</div>
            <div>
              <p class="text-sm font-bold text-stone-900">{{ service.geometre?.prenom }} {{ service.geometre?.nom }}</p>
              <p class="text-xs text-stone-400">{{ service.geometre?.email }}</p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="service.description" class="card">
        <h3 class="font-display font-bold text-stone-900 mb-3">Description</h3>
        <div class="px-4 py-4 bg-stone-50 rounded-xl">
          <p class="text-sm text-stone-700 leading-relaxed whitespace-pre-wrap">{{ service.description }}</p>
        </div>
      </div>
    </template>
  </div>
</template>
