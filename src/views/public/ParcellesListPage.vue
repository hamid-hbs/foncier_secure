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

const featuredIndex = ref(0)
const featuredParcelles = computed(() => parcelles.value.slice(0, 5))

const gradients = [
  ['#2D6A4F', '#40916C', '#52B788'],
  ['#1B4332', '#2D6A4F', '#40916C'],
  ['#D4A373', '#E6B87D', '#F0C99A'],
  ['#457B9D', '#1D3557', '#A8DADC'],
  ['#E76F51', '#F4A261', '#E9C46A'],
]
const avatarColors = ['#2D6A4F', '#457B9D', '#D4A373', '#E76F51', '#1B4332', '#6B7280']
const statutLabels = {
  libre: 'Libre',
  en_demande: 'En demande',
  en_transaction: 'En transaction',
  vendue: 'Vendue',
}
const statutColors = {
  libre: 'badge-success',
  en_demande: 'badge-warning',
  en_transaction: 'badge-info',
  vendue: 'badge-danger',
}

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

function featuredGradient(i) {
  const g = gradients[i % gradients.length]
  return `linear-gradient(135deg, ${g[0]} 0%, ${g[1]} 50%, ${g[2]} 100%)`
}
function cardGradient(p) {
  const g = gradients[(p.id || 0) % gradients.length]
  return `linear-gradient(135deg, ${g[0]} 0%, ${g[1]} 50%, ${g[2]} 100%)`
}
function avatarColor(p) {
  return avatarColors[(p.id || 0) % avatarColors.length]
}

const statuts = ['libre', 'en_demande', 'en_transaction', 'vendue']

async function fetchCommunes() {
  try {
    const res = await localisationApi.getCommunes()
    communes.value = (res.data || []).filter(Boolean)
  } catch {}
}

async function fetchParcelles() {
  loading.value = true
  try {
    const params = { page: currentPage.value }
    if (search.value) params.search = search.value
    if (statutFilter.value) params.statut = statutFilter.value
    if (communeFilter.value) params.commune_id = communeFilter.value
    const res = await parcelleApi.list(params)
    const data = res.data
    parcelles.value = (data.data || []).filter(Boolean)
    currentPage.value = data.current_page || 1
    lastPage.value = data.last_page || 1
    total.value = data.total || 0
  } catch {}
  loading.value = false
}

function changePage(page) {
  if (page < 1 || page > lastPage.value) return
  currentPage.value = page
  fetchParcelles()
}

watch([search, statutFilter, communeFilter], () => {
  currentPage.value = 1
  fetchParcelles()
})

let autoSlide = null
onMounted(() => {
  fetchCommunes()
  fetchParcelles()
  autoSlide = setInterval(() => {
    if (featuredParcelles.value.length > 1) {
      featuredIndex.value = (featuredIndex.value + 1) % featuredParcelles.value.length
    }
  }, 5000)
})
onUnmounted(() => {
  if (autoSlide) clearInterval(autoSlide)
})
</script>

<template>
  <div class="page-container">
    <div class="mb-8">
      <h1 class="section-title">Catalogue des parcelles</h1>
      <p class="section-subtitle">Consultez les parcelles disponibles et leurs informations</p>
    </div>

    <!-- Featured parcelles hero banner -->
    <div v-if="parcelles.length > 0" class="card p-0 overflow-hidden mb-6">
      <div class="relative overflow-hidden" style="height: 280px;">
        <div class="absolute inset-0 flex transition-transform duration-500" :style="{ transform: `translateX(-${featuredIndex * 100}%)` }">
          <div v-for="(p, i) in featuredParcelles" :key="p.id" class="min-w-full h-full relative" @click="router.push('/parcelles/' + p.id)" style="cursor: pointer;">
            <!-- Image réelle ou fond dégradé pour le hero -->
            <div class="w-full h-full relative overflow-hidden">
              <img
                v-if="getPhotoUrl(p)"
                :src="getPhotoUrl(p)"
                :alt="p.titre || 'Photo parcelle'"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full" :style="{ background: featuredGradient(i) }">
                <div class="w-full h-full flex items-center justify-center">
                  <i class="fas fa-image" style="font-size: 4rem; color: rgba(255,255,255,0.25);"></i>
                </div>
              </div>
            </div>
            <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.1) 60%, transparent 100%);">
              <div class="absolute bottom-0 left-0 right-0 p-6">
                <div class="flex items-center gap-2 mb-2">
                  <span :class="['badge', statutColors[p.statut] || 'badge-info']">{{ statutLabels[p.statut] || p.statut }}</span>
                  <span class="badge badge-info" style="background: rgba(255,255,255,0.2); color: white;">{{ p.superficie || '—' }} m²</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-1">{{ p.titre || 'Sans titre' }}</h3>
                <p class="text-sm" style="color: rgba(255,255,255,0.8);">
                  <i class="fas fa-map-pin mr-1"></i>
                  {{ p.commune?.nom || '—' }}{{ p.arrondissement?.nom ? ' — ' + p.arrondissement.nom : '' }}
                </p>
              </div>
            </div>
          </div>
        </div>
        <!-- Navigation dots -->
        <div v-if="featuredParcelles.length > 1" class="absolute bottom-3 right-6 flex items-center gap-2 z-10">
          <button v-for="(_, i) in featuredParcelles" :key="i" @click="featuredIndex = i"
            class="w-2.5 h-2.5 rounded-full transition-all duration-300"
            :style="i === featuredIndex ? { background: 'white', width: '20px' } : { background: 'rgba(255,255,255,0.5)' }">
          </button>
        </div>
        <!-- Arrow nav -->
        <button v-if="featuredParcelles.length > 1" @click="featuredIndex = (featuredIndex - 1 + featuredParcelles.length) % featuredParcelles.length"
          class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center text-white z-10 transition-all"
          style="background: rgba(0,0,0,0.3);" @mouseover="$event.target.style.background = 'rgba(0,0,0,0.5)'" @mouseout="$event.target.style.background = 'rgba(0,0,0,0.3)'">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button v-if="featuredParcelles.length > 1" @click="featuredIndex = (featuredIndex + 1) % featuredParcelles.length"
          class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center text-white z-10 transition-all"
          style="background: rgba(0,0,0,0.3);" @mouseover="$event.target.style.background = 'rgba(0,0,0,0.5)'" @mouseout="$event.target.style.background = 'rgba(0,0,0,0.3)'">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Filtres -->
    <div class="card p-4 mb-6">
      <div class="flex flex-wrap gap-4">
        <div class="relative flex-1 min-w-[200px]">
          <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
          <input v-model="search" class="form-input pl-10" placeholder="Rechercher par titre..." />
        </div>
        <div class="relative w-48">
          <i class="fas fa-filter absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
          <select v-model="statutFilter" class="form-select pl-10">
            <option value="">Tous les statuts</option>
            <option v-for="s in statuts" :key="s" :value="s">{{ statutLabels[s] }}</option>
          </select>
        </div>
        <div class="relative w-56">
          <i class="fas fa-map-pin absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
          <select v-model="communeFilter" class="form-select pl-10">
            <option value="">Toutes les communes</option>
            <option v-for="c in communes" :key="c.id" :value="c.id">{{ c.nom }}</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="loading && parcelles.length === 0" class="text-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin mx-auto" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <div v-else-if="parcelles.length === 0" class="card text-center py-12">
      <i class="fas fa-map-pin mb-3" style="color: #D1D5DB; font-size: 3rem;"></i>
      <p style="color: var(--text-secondary);">Aucune parcelle trouvée.</p>
    </div>

    <!-- Liste des parcelles en style Facebook -->
    <div v-else class="space-y-5">
      <div v-for="p in parcelles" :key="p.id" class="card p-0 overflow-hidden" style="cursor: pointer; border-radius: 14px;" @click="router.push('/parcelles/' + p.id)">
        <!-- Vignette image ou placeholder -->
        <div class="relative w-full overflow-hidden" style="height: 200px;">
          <img
            v-if="getPhotoUrl(p)"
            :src="getPhotoUrl(p)"
            :alt="p.titre || 'Photo parcelle'"
            class="w-full h-full object-cover"
          />
          <div
            v-else
            class="w-full h-full flex flex-col items-center justify-center gap-2"
            :style="{ background: cardGradient(p) }"
          >
            <i class="fas fa-image" style="font-size: 3rem; color: rgba(255,255,255,0.35);"></i>
            <span class="text-xs font-medium" style="color: rgba(255,255,255,0.6);">Aucune photo</span>
          </div>
          <!-- Overlay dégradé bas -->
          <div class="absolute inset-x-0 bottom-0 h-16" style="background: linear-gradient(to top, rgba(0,0,0,0.45), transparent);"></div>
          <!-- Badge statut -->
          <div class="absolute bottom-3 left-4">
            <span :class="['badge', statutColors[p.statut] || 'badge-info']" style="background: rgba(255,255,255,0.9); color: var(--text-primary);">{{ statutLabels[p.statut] || p.statut }}</span>
          </div>
        </div>
        <div class="p-5">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0" :style="{ background: avatarColor(p) }">
              {{ (p.proprietaire?.prenom?.[0] || '') + (p.proprietaire?.nom?.[0] || '') || '?' }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-sm truncate" style="color: var(--text-primary);">{{ p.proprietaire?.prenom || '' }} {{ p.proprietaire?.nom || 'Propriétaire' }}</p>
              <p class="text-xs" style="color: var(--text-secondary);">{{ p.created_at ? new Date(p.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) : '—' }}</p>
            </div>
            <i class="fas fa-ellipsis-h" style="color: var(--text-secondary);"></i>
          </div>
          <h3 class="font-bold text-base mb-2" style="color: var(--text-primary);">{{ p.titre || 'Sans titre' }}</h3>
          <p class="text-sm mb-3" style="color: var(--text-secondary);">
            <i class="fas fa-map-marker-alt mr-1" style="color: var(--green-tree);"></i>
            {{ p.commune?.nom || '—' }}<template v-if="p.arrondissement?.nom">, {{ p.arrondissement.nom }}</template>
          </p>
          <div class="flex items-center gap-4 text-sm" style="color: var(--text-secondary);">
            <span><i class="fas fa-ruler-combined mr-1" style="color: var(--green-tree);"></i>{{ p.superficie || '—' }} m²</span>
            <span v-if="p.prix_estimatif"><i class="fas fa-tag mr-1" style="color: var(--gold);"></i>{{ Number(p.prix_estimatif).toLocaleString('fr-FR') }} FCFA</span>
          </div>
          <div class="flex items-center gap-2 mt-4 pt-3" style="border-top: 1px solid var(--border);">
            <button @click.stop class="flex items-center gap-1.5 text-sm font-medium" style="color: var(--text-secondary);">
              <i class="fas fa-heart"></i> <span>0</span>
            </button>
            <button @click.stop class="flex items-center gap-1.5 text-sm font-medium" style="color: var(--text-secondary);">
              <i class="fas fa-comment"></i> <span>0</span>
            </button>
            <button @click.stop class="flex items-center gap-1.5 text-sm font-medium ml-auto" style="color: var(--green-tree);">
              <i class="fas fa-share-nodes"></i> Partager
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="lastPage > 1" class="flex items-center justify-center gap-2 mt-8">
      <button class="btn-outline btn-sm" :disabled="currentPage <= 1" @click="changePage(currentPage - 1)">Précédent</button>
      <template v-for="page in lastPage" :key="page">
        <button v-if="page === currentPage || page === 1 || page === lastPage || Math.abs(page - currentPage) <= 1"
          class="btn-sm font-medium transition-all"
          :style="page === currentPage ? { background: 'var(--green-tree)', color: 'white', border: 'none' } : { background: 'transparent', color: 'var(--text-secondary)', border: '1px solid var(--border)' }"
          @click="changePage(page)">
          {{ page }}
        </button>
        <span v-else-if="page === currentPage - 2 || page === currentPage + 2" key="dots" style="color: var(--text-secondary);">...</span>
      </template>
      <button class="btn-outline btn-sm" :disabled="currentPage >= lastPage" @click="changePage(currentPage + 1)">Suivant</button>
    </div>
  </div>
</template>
