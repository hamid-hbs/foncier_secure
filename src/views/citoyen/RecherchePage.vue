<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import parcelleApi from '@/api/parcelle'

const router = useRouter()

const query = ref('')
const resultats = ref([])
const loading = ref(false)
const searched = ref(false)

const BASE_URL = import.meta.env.VITE_API_URL?.replace('/api', '') || ''

function getPhotoUrl(p) {
  const docs = p.documents || []
  const photoDoc = docs.find(d => d.type_document === 'photo')
  if (!photoDoc) return null
  const chemin = photoDoc.chemin_fichier || photoDoc.fichier || ''
  if (!chemin) return null
  return chemin.startsWith('http') ? chemin : `${BASE_URL}/storage/${chemin}`
}

function statutBadgeClass(s) {
  const map = { libre: 'badge-success', en_demande: 'badge-warning', en_transaction: 'badge-info', vendue: 'badge-neutral' }
  return map[s] || 'badge-neutral'
}

async function search() {
  if (!query.value.trim()) return
  loading.value = true
  searched.value = true
  try {
    const res = await parcelleApi.list({ search: query.value, page: 1, per_page: 20 })
    resultats.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur recherche:', e) }
  loading.value = false
}
</script>

<template>
  <div class="page-wrap max-w-3xl mx-auto">
    <div class="mb-8">
      <h1 class="page-title">Recherche de parcelles</h1>
      <p class="page-subtitle">Recherchez des parcelles par titre, code, commune ou localisation</p>
    </div>

    <!-- Search bar -->
    <form @submit.prevent="search" class="flex gap-3 mb-8">
      <div class="relative flex-1">
        <i class="fas fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
        <input
          v-model="query"
          type="text"
          class="form-input pl-11 py-3.5 text-base"
          placeholder="Titre, code, commune, quartier…"
          required
        />
      </div>
      <button type="submit" class="btn btn-primary btn-lg" :disabled="loading">
        <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
        <i v-else class="fas fa-search"></i>
        Rechercher
      </button>
    </form>

    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 5" :key="i" class="skeleton h-24 rounded-2xl"></div>
    </div>

    <!-- No results -->
    <div v-else-if="searched && resultats.length === 0" class="card">
      <div class="empty-state py-12">
        <div class="empty-icon"><i class="fas fa-magnifying-glass"></i></div>
        <p class="empty-title">Aucun résultat</p>
        <p class="empty-text">Aucune parcelle ne correspond à "{{ query }}". Essayez un autre terme.</p>
      </div>
    </div>

    <!-- Results -->
    <div v-else-if="resultats.length > 0" class="space-y-3">
      <p class="text-sm font-medium text-stone-500 mb-4">{{ resultats.length }} résultat(s) trouvé(s) pour "<strong class="text-stone-900">{{ query }}</strong>"</p>

      <div
        v-for="p in resultats" :key="p.id"
        @click="router.push(`/parcelles/${p.id}`)"
        class="card group hover:border-brand-100 hover:shadow-md cursor-pointer transition-all"
      >
        <div class="flex items-start gap-4">
          <!-- Thumbnail -->
          <div class="w-16 h-16 rounded-xl overflow-hidden shrink-0 bg-stone-100">
            <img v-if="getPhotoUrl(p)" :src="getPhotoUrl(p)" class="w-full h-full object-cover" :alt="p.titre" />
            <div v-else class="w-full h-full flex items-center justify-center bg-brand-50">
              <i class="fas fa-map-marker-alt text-brand text-lg"></i>
            </div>
          </div>
          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <p class="font-display font-bold text-stone-900 group-hover:text-brand transition-colors truncate">{{ p.titre || 'Parcelle #' + p.id }}</p>
              <span class="badge" :class="statutBadgeClass(p.statut)">{{ p.statut }}</span>
            </div>
            <div class="flex flex-wrap gap-x-4 text-xs text-stone-400">
              <span><i class="fas fa-location-dot mr-1 text-stone-300"></i>{{ [p.commune?.nom, p.arrondissement?.nom].filter(Boolean).join(' · ') || 'Localisation inconnue' }}</span>
              <span v-if="p.superficie"><i class="fas fa-ruler-combined mr-1 text-stone-300"></i>{{ p.superficie }} m²</span>
              <span v-if="p.prix_estimatif"><i class="fas fa-tag mr-1 text-gold"></i>{{ Number(p.prix_estimatif).toLocaleString('fr-FR') }} FCFA</span>
            </div>
          </div>
          <i class="fas fa-chevron-right text-xs text-stone-300 group-hover:text-brand transition-colors mt-1 hidden sm:block"></i>
        </div>
      </div>
    </div>

    <!-- Initial state -->
    <div v-else class="text-center py-16 text-stone-400">
      <div class="w-16 h-16 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-magnifying-glass text-2xl text-stone-300"></i>
      </div>
      <p class="text-sm font-medium">Saisissez des termes de recherche pour trouver des parcelles</p>
    </div>
  </div>
</template>
