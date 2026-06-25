<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import parcelleApi from '@/api/parcelle'

const router = useRouter()

const parcelles = ref([])
const loading = ref(true)

const BASE_URL = import.meta.env.VITE_API_URL?.replace('/api', '') || ''

function getPhotoUrl(p) {
  const docs = p.documents || p.parcelle_documents || []
  const photoDoc = docs.find(d => d.type_document === 'photo')
  if (!photoDoc) return null
  const chemin = photoDoc.chemin_fichier || photoDoc.fichier || ''
  if (!chemin) return null
  if (chemin.startsWith('http')) return chemin
  return `${BASE_URL}/storage/${chemin}`
}

onMounted(async () => {
  try {
    const res = await parcelleApi.list()
    parcelles.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement parcelles:', e) }
  loading.value = false
})

function statutBadgeClass(statut) {
  const map = {
    libre: 'badge-success',
    en_demande: 'badge-warning',
    en_transaction: 'badge-info',
    vendue: 'badge-neutral',
    conteste: 'badge-danger',
    en_verification: 'badge-info',
    sollicite: 'badge-purple',
  }
  return map[statut] || 'badge-neutral'
}
</script>

<template>
  <div class="page-wrap">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Mon patrimoine foncier</h1>
        <p class="page-subtitle">Gérez et consultez vos parcelles sécurisées</p>
      </div>
      <router-link :to="{ name: 'CreerParcelle' }" class="btn btn-primary">
        <i class="fas fa-plus"></i> Déclarer une parcelle
      </router-link>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="i in 6" :key="i" class="skeleton h-72 rounded-2xl"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="parcelles.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-map"></i></div>
        <p class="empty-title">Aucune parcelle enregistrée</p>
        <p class="empty-text">Sécurisez votre patrimoine en déclarant vos parcelles sur la plateforme.</p>
        <router-link :to="{ name: 'CreerParcelle' }" class="btn btn-primary mt-4">
          <i class="fas fa-plus"></i> Déclarer ma première parcelle
        </router-link>
      </div>
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="p in parcelles"
        :key="p.id"
        @click="router.push({ name: 'CitoyenParcelleDetail', params: { id: p.id } })"
        class="bg-white rounded-2xl border border-stone-100 overflow-hidden cursor-pointer hover:border-brand-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group flex flex-col"
      >
        <!-- Image -->
        <div class="relative w-full h-44 overflow-hidden bg-stone-100 shrink-0">
          <img
            v-if="getPhotoUrl(p)"
            :src="getPhotoUrl(p)"
            :alt="p.titre || 'Photo parcelle'"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
          <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-brand-50 to-brand-100/50">
            <i class="fas fa-map-marked-alt text-4xl text-brand-200"></i>
            <span class="text-[10px] font-bold uppercase tracking-widest text-brand-300">Sans aperçu</span>
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>

          <!-- Badge statut -->
          <span class="badge absolute top-3 right-3 backdrop-blur-sm shadow-sm" :class="statutBadgeClass(p.statut)">
            {{ (p.statut || '').replace('_', ' ') }}
          </span>

          <!-- Bottom info overlay -->
          <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
            <span class="font-mono text-xs font-semibold text-white bg-black/40 backdrop-blur-sm px-2 py-1 rounded-lg">
              {{ p.code || '#' + p.id }}
            </span>
            <span v-if="p.superficie" class="text-xs font-bold text-white/90 drop-shadow flex items-center gap-1">
              <i class="fas fa-ruler-combined text-white/60 text-[10px]"></i> {{ p.superficie }} m²
            </span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-4 flex-1 flex flex-col">
          <h3 class="font-display font-bold text-stone-900 mb-2 group-hover:text-brand transition-colors line-clamp-1">
            {{ p.titre || 'Parcelle non nommée' }}
          </h3>

          <div class="space-y-1.5 mb-4 flex-1">
            <div class="flex items-start gap-2.5 text-sm text-stone-500">
              <i class="fas fa-location-dot mt-0.5 text-stone-300 w-3.5 shrink-0 text-center text-xs"></i>
              <span class="truncate-2 text-xs">
                {{ p.commune?.nom || 'Localisation inconnue' }}
                <template v-if="p.arrondissement"> · {{ p.arrondissement.nom }}</template>
                <template v-if="p.quartier"> · {{ p.quartier.nom }}</template>
              </span>
            </div>
            <div v-if="p.prix_estimatif" class="flex items-center gap-2.5 text-sm">
              <i class="fas fa-tag text-gold w-3.5 shrink-0 text-center text-xs"></i>
              <span class="font-semibold text-stone-900 text-xs">{{ Number(p.prix_estimatif).toLocaleString('fr-FR') }} FCFA</span>
            </div>
          </div>

          <div class="pt-3 border-t border-stone-100 flex items-center justify-between text-xs">
            <span class="text-stone-400">
              <i class="fas fa-file-contract mr-1"></i>{{ p.documents?.length || 0 }} doc(s)
            </span>
            <span class="text-brand font-bold group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
              Voir détails <i class="fas fa-arrow-right text-[10px]"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
