<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import professionnelApi from '@/api/professionnel'
import localisationApi from '@/api/localisation'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const professionnels = ref([])
const communes = ref([])
const loading = ref(true)
const search = ref('')
const typeFilter = ref('')
const roleFilter = ref('')
const specialiteFilter = ref('')
const communeFilter = ref('')
const zoneFilter = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)

const types = ['notaire', 'geometre', 'avocat', 'expert_foncier']
const typeLabels = { notaire: 'Notaire', geometre: 'Géomètre', avocat: 'Avocat', expert_foncier: 'Expert foncier' }
const typeIcons = { notaire: 'fa-file-signature', geometre: 'fa-draw-polygon', avocat: 'fa-gavel', expert_foncier: 'fa-map' }

async function fetchCommunes() {
  try {
    const res = await localisationApi.getCommunes()
    communes.value = (res.data || []).filter(Boolean)
  } catch {}
}

async function fetchProfessionnels() {
  loading.value = true
  try {
    const params = { page: currentPage.value }
    if (search.value) params.search = search.value
    if (typeFilter.value) params.type = typeFilter.value
    if (roleFilter.value) params.role = roleFilter.value
    if (specialiteFilter.value) params.specialite = specialiteFilter.value
    if (communeFilter.value) params.commune_id = communeFilter.value
    if (zoneFilter.value) params.zone = zoneFilter.value
    const res = await professionnelApi.list(params)
    const data = res.data
    if (Array.isArray(data)) {
      professionnels.value = data.filter(Boolean)
      lastPage.value = 1
      total.value = professionnels.value.length
    } else if (data.data) {
      professionnels.value = (data.data || []).filter(Boolean)
      currentPage.value = data.current_page || 1
      lastPage.value = data.last_page || 1
      total.value = data.total || 0
    } else {
      professionnels.value = []
      lastPage.value = 1
      total.value = 0
    }
  } catch {}
  loading.value = false
}

function changePage(page) {
  if (page < 1 || page > lastPage.value) return
  currentPage.value = page
  fetchProfessionnels()
}

watch([search, typeFilter, roleFilter, specialiteFilter, communeFilter, zoneFilter], () => {
  currentPage.value = 1
  fetchProfessionnels()
})

onMounted(() => {
  fetchCommunes()
  fetchProfessionnels()
})
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="mb-8">
      <h1 class="section-title">Annuaire des professionnels</h1>
      <p class="section-subtitle">Trouvez un notaire, géomètre, avocat ou expert foncier</p>
    </div>

    <div class="card p-4 mb-6">
      <div class="flex flex-wrap gap-4">
        <div class="relative flex-1 min-w-[200px]">
          <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
          <input v-model="search" class="form-input pl-10" placeholder="Rechercher par nom..." />
        </div>
        <div class="relative w-44">
          <i class="fas fa-filter absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
          <select v-model="typeFilter" class="form-select pl-10">
            <option value="">Tous les types</option>
            <option v-for="t in types" :key="t" :value="t">{{ typeLabels[t] || t }}</option>
          </select>
        </div>
        <div class="relative w-44">
          <i class="fas fa-map-pin absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
          <select v-model="communeFilter" class="form-select pl-10">
            <option value="">Toutes les communes</option>
            <option v-for="c in communes" :key="c.id" :value="c.id">{{ c.nom }}</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="loading && professionnels.length === 0" class="text-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin mx-auto" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <div v-else-if="professionnels.length === 0" class="card text-center py-12">
      <i class="fas fa-building mb-3" style="color: #D1D5DB; font-size: 3rem;"></i>
      <p style="color: var(--text-secondary);">Aucun professionnel trouvé.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="p in professionnels" :key="p.id" class="card" style="cursor: pointer;" @click="$router.push('/professionnels/' + p.id)">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white font-bold" style="background: var(--green-tree);">
            {{ ((p.user?.prenom || p.prenom || '?')[0]) }}{{ ((p.user?.nom || p.nom || '?')[0]) }}
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold" style="color: var(--text-primary);">{{ p.user?.prenom || p.prenom }} {{ p.user?.nom || p.nom }}</h3>
            <span class="badge mt-1" style="background: #F0F7F4; color: var(--green-tree);">{{ typeLabels[p.type] || p.type }}</span>
            <div class="mt-3 space-y-1.5">
              <p v-if="p.cabinet" class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
                <i :class="['fas', typeIcons[p.type] || 'fa-building']"></i> {{ p.cabinet }}
              </p>
              <p v-if="p.zone_intervention" class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
                <i class="fas fa-map-pin"></i> {{ p.zone_intervention }}
              </p>
              <div v-if="p.specialites && p.specialites.length" class="flex flex-wrap gap-1 mt-2">
                <span v-for="s in p.specialites.slice(0, 3)" :key="s" class="badge badge-info">{{ s }}</span>
                <span v-if="p.specialites.length > 3" class="badge badge-info">+{{ p.specialites.length - 3 }}</span>
              </div>
              <div v-if="p.note_moyenne" class="flex items-center gap-1 text-sm">
                <i class="fas fa-star" style="color: var(--gold);"></i>
                <span class="font-medium" style="color: var(--text-primary);">{{ p.note_moyenne }}</span>
              </div>
            </div>
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
