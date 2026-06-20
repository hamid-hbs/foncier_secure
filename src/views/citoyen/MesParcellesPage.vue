<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import parcelleApi from '@/api/parcelle'
import { goBack } from '@/utils/navigation'

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
    parcelles.value = (res.data?.data || res.data || []).filter(Boolean)
  } catch { /* ignore */ }
  loading.value = false
})

function statutClass(statut) {
  const map = {
    libre: 'badge-success',
    en_demande: 'badge-warning',
    en_transaction: 'badge-info',
    vendue: 'badge-info',
    conteste: 'badge-danger',
  }
  return map[statut] || 'badge-success'
}
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="flex-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #D1FAE5;">
          <i class="fas fa-map-pin" style="color: var(--green-tree);"></i>
        </div>
        <div>
          <h1 class="section-title">Mes parcelles</h1>
          <p class="section-subtitle">Gérez vos parcelles enregistrées</p>
        </div>
      </div>
      <router-link :to="{ name: 'CreerParcelle' }" class="btn-green flex items-center gap-2">
        <i class="fas fa-plus"></i> Nouvelle parcelle
      </router-link>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--border); border-top-color: var(--green-tree);"></div>
    </div>

    <div v-else-if="parcelles.length === 0" class="card text-center py-12">
      <i class="fas fa-map-pin text-4xl mb-3" style="color: var(--border);"></i>
      <p class="mb-4" style="color: var(--text-secondary);">Vous n'avez aucune parcelle enregistrée.</p>
      <router-link :to="{ name: 'CreerParcelle' }" class="btn-green">Déclarer une parcelle</router-link>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="p in parcelles"
        :key="p.id"
        class="card p-0 overflow-hidden cursor-pointer hover:shadow-lg transition-all duration-200"
        style="border-radius: 14px;"
        @click="router.push({ name: 'CitoyenParcelleDetail', params: { id: p.id } })"
      >
        <!-- Vignette image ou placeholder -->
        <div class="relative w-full overflow-hidden" style="height: 160px;">
          <img
            v-if="getPhotoUrl(p)"
            :src="getPhotoUrl(p)"
            :alt="p.titre || 'Photo parcelle'"
            class="w-full h-full object-cover"
            style="display: block;"
          />
          <div
            v-else
            class="w-full h-full flex flex-col items-center justify-center gap-2"
            style="background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);"
          >
            <i class="fas fa-image" style="font-size: 2.5rem; color: #34D399; opacity: 0.7;"></i>
            <span class="text-xs font-medium" style="color: #059669;">Aucune photo</span>
          </div>
          <!-- Badge statut superposé -->
          <span
            class="badge absolute top-3 right-3"
            :class="statutClass(p.statut)"
            style="font-size: 0.7rem; box-shadow: 0 1px 4px rgba(0,0,0,0.15);"
          >{{ p.statut }}</span>
        </div>

        <!-- Contenu de la card -->
        <div class="p-4">
          <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-map-pin" style="color: var(--green-tree);"></i>
            <span class="font-semibold truncate" style="color: var(--text-primary);">{{ p.titre || 'Parcelle #' + p.id }}</span>
          </div>
          <div class="text-sm mb-1" style="color: var(--text-secondary);">
            <i class="fas fa-ruler-combined mr-1"></i> {{ p.superficie ? p.superficie + ' m²' : '—' }}
          </div>
          <div class="text-sm" style="color: var(--text-secondary);">
            <i class="fas fa-map-marker-alt mr-1"></i>
            {{ p.commune?.nom || '—' }}
            <template v-if="p.arrondissement"> — {{ p.arrondissement.nom }}</template>
            <template v-if="p.quartier"> / {{ p.quartier.nom }}</template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
