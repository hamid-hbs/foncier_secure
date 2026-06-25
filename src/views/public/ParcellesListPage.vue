<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import parcelleApi from '@/api/parcelle'
import localisationApi from '@/api/localisation'

const router = useRouter()
const parcelles = ref([])
const communes = ref([])
const loading = ref(true)
const search = ref('')
const statutFilter = ref('')
const communeFilter = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)

const gradients = [
  ['#2d6a4f','#40916c','#6bb99e'],
  ['#1b4332','#2d6a4f','#40916c'],
  ['#e8a020','#c97c10','#a55c0e'],
  ['#0284c7','#0ea5e9','#38bdf8'],
  ['#059669','#10b981','#34d399'],
]

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

function cardGradient(p) {
  const g = gradients[(p.id || 0) % gradients.length]
  return `linear-gradient(135deg, ${g[0]} 0%, ${g[1]} 60%, ${g[2]} 100%)`
}

function statutBadgeClass(s) {
  const map = { libre: 'badge-success', en_demande: 'badge-warning', en_transaction: 'badge-info', vendue: 'badge-neutral', conteste: 'badge-danger' }
  return map[s] || 'badge-neutral'
}

const statuts = [
  { value: '', label: 'Tous les statuts' },
  { value: 'libre', label: 'Libre' },
  { value: 'en_demande', label: 'En demande' },
  { value: 'en_transaction', label: 'En transaction' },
  { value: 'vendue', label: 'Vendue' },
]

async function fetchCommunes() {
  try {
    const res = await localisationApi.getCommunes()
    communes.value = (res.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement communes:', e) }
}

async function fetchParcelles() {
  loading.value = true
  try {
    const params = { page: currentPage.value }
    if (search.value) params.search = search.value
    if (statutFilter.value) params.statut = statutFilter.value
    if (communeFilter.value) params.commune_id = communeFilter.value
    const res = await parcelleApi.list(params)
    parcelles.value = (res.data.data || []).filter(Boolean)
    lastPage.value = res.data?.meta?.last_page || 1
    total.value = res.data?.meta?.total || parcelles.value.length
  } catch (e) { console.error('Erreur chargement parcelles:', e) }
  loading.value = false
}

watch([search, statutFilter, communeFilter], () => { currentPage.value = 1; fetchParcelles() })

onMounted(() => {
  fetchCommunes()
  fetchParcelles()
})
</script>

<template>
  <div>
    <!-- Hero header -->
    <section class="relative py-16 overflow-hidden" style="background: var(--brand-dark);">
      <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute -top-16 left-1/3 w-96 h-96 rounded-full" style="background: radial-gradient(circle, #40916c, transparent);"></div>
        <div class="absolute bottom-0 right-1/4 w-64 h-64 rounded-full" style="background: radial-gradient(circle, var(--gold), transparent);"></div>
      </div>
      <div class="max-w-7xl mx-auto px-5 sm:px-8 relative z-10">
        <div class="text-center mb-10">
          <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest text-gold bg-gold/15 mb-4">Annonces foncières</span>
          <h1 class="font-display font-extrabold text-4xl sm:text-5xl text-white mb-3">Parcelles disponibles</h1>
          <p class="text-white/60 text-lg">{{ total.toLocaleString('fr-FR') }} parcelles enregistrées sur la plateforme</p>
        </div>

        <!-- Filters -->
        <div class="max-w-3xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="relative sm:col-span-1">
            <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none z-10"></i>
            <input v-model="search" type="text" class="form-input pl-10" placeholder="Rechercher…" />
          </div>
          <select v-model="statutFilter" class="form-select">
            <option v-for="s in statuts" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
          <select v-model="communeFilter" class="form-select">
            <option value="">Toutes communes</option>
            <option v-for="c in communes" :key="c.id" :value="c.id">{{ c.nom }}</option>
          </select>
        </div>
      </div>
    </section>

    <!-- Results -->
    <section class="py-12 bg-stone-50">
      <div class="max-w-7xl mx-auto px-5 sm:px-8">

        <!-- Loading -->
        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          <div v-for="i in 8" :key="i" class="skeleton h-72 rounded-2xl"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="parcelles.length === 0" class="card max-w-lg mx-auto">
          <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-map"></i></div>
            <p class="empty-title">Aucune parcelle trouvée</p>
            <p class="empty-text">Modifiez vos critères de recherche pour voir plus de résultats.</p>
          </div>
        </div>

        <!-- Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          <div
            v-for="p in parcelles" :key="p.id"
            @click="router.push(`/parcelles/${p.id}`)"
            class="bg-white rounded-2xl border border-stone-100 overflow-hidden cursor-pointer hover:border-brand-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group flex flex-col"
          >
            <!-- Image/gradient -->
            <div class="relative w-full h-44 overflow-hidden shrink-0">
              <img
                v-if="getPhotoUrl(p)"
                :src="getPhotoUrl(p)"
                :alt="p.titre"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              />
              <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2 transition-transform duration-500 group-hover:scale-105"
                :style="{ background: cardGradient(p) }">
                <i class="fas fa-map-marked-alt text-4xl text-white/40"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest text-white/50">Sans aperçu</span>
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-stone-900/50 via-transparent to-transparent"></div>
              <span class="badge absolute top-3 right-3 backdrop-blur-sm" :class="statutBadgeClass(p.statut)">
                {{ (p.statut || '').replace('_', ' ') }}
              </span>
              <span class="absolute bottom-3 left-3 font-mono text-xs font-semibold text-white bg-black/40 backdrop-blur px-2 py-1 rounded-lg">
                {{ p.code || '#' + p.id }}
              </span>
            </div>

            <!-- Info -->
            <div class="p-4 flex-1 flex flex-col">
              <h3 class="font-display font-bold text-stone-900 mb-2 group-hover:text-brand transition-colors line-clamp-1">
                {{ p.titre || 'Parcelle non nommée' }}
              </h3>
              <div class="space-y-1.5 flex-1 mb-3">
                <div class="flex items-start gap-2 text-xs text-stone-400">
                  <i class="fas fa-location-dot mt-0.5 w-3.5 shrink-0 text-center text-stone-300"></i>
                  <span class="truncate-2">{{ [p.commune?.nom, p.arrondissement?.nom, p.quartier?.nom].filter(Boolean).join(' · ') || 'Localisation inconnue' }}</span>
                </div>
                <div v-if="p.superficie" class="flex items-center gap-2 text-xs">
                  <i class="fas fa-ruler-combined w-3.5 text-center text-stone-300"></i>
                  <span class="font-semibold text-stone-700">{{ p.superficie }} m²</span>
                </div>
                <div v-if="p.prix_estimatif" class="flex items-center gap-2 text-xs">
                  <i class="fas fa-tag w-3.5 text-center text-gold"></i>
                  <span class="font-bold text-stone-900">{{ Number(p.prix_estimatif).toLocaleString('fr-FR') }} FCFA</span>
                </div>
              </div>
              <div class="pt-3 border-t border-stone-100 flex items-center justify-between text-xs">
                <span class="text-stone-400"><i class="fas fa-user mr-1"></i>{{ p.proprietaire?.nom || p.proprietaire?.prenom || 'Propriétaire' }}</span>
                <span class="text-brand font-bold group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                  Détails <i class="fas fa-arrow-right text-[10px]"></i>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="pagination mt-10">
          <button class="page-btn" :disabled="currentPage <= 1" @click="currentPage--; fetchParcelles()"><i class="fas fa-chevron-left text-xs"></i></button>
          <button v-for="p in Math.min(lastPage, 8)" :key="p" class="page-btn" :class="p === currentPage ? 'active' : ''" @click="currentPage = p; fetchParcelles()">{{ p }}</button>
          <button class="page-btn" :disabled="currentPage >= lastPage" @click="currentPage++; fetchParcelles()"><i class="fas fa-chevron-right text-xs"></i></button>
        </div>
      </div>
    </section>
  </div>
</template>
