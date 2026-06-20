<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import parcelleApi from '@/api/parcelle'
import { goBack } from '@/utils/navigation'

const router = useRouter()

const query = ref('')
const resultats = ref([])
const loading = ref(false)
const searched = ref(false)

async function rechercher() {
  if (!query.value.trim()) return
  loading.value = true
  searched.value = true
  try {
    const res = await parcelleApi.list({ search: query.value })
    resultats.value = (res.data?.data || []).filter(Boolean)
  } catch {
    resultats.value = []
  }
  loading.value = false
}

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

    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #D1FAE5;">
        <i class="fas fa-search" style="color: var(--green-tree);"></i>
      </div>
      <div>
        <h1 class="section-title">Recherche de parcelles</h1>
        <p class="section-subtitle">Trouvez une parcelle par mot-clé</p>
      </div>
    </div>

    <div class="card mb-6">
      <form @submit.prevent="rechercher" class="flex gap-3">
        <input v-model="query" class="form-input flex-1" placeholder="Rechercher par titre, code, commune..." />
        <button type="submit" class="btn-green flex items-center gap-2" :disabled="loading || !query.trim()">
          <i class="fas fa-search"></i> {{ loading ? '...' : 'Rechercher' }}
        </button>
      </form>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--border); border-top-color: var(--green-tree);"></div>
    </div>

    <div v-else-if="searched && resultats.length === 0" class="card text-center py-12">
      <i class="fas fa-map-pin text-4xl mb-3" style="color: var(--border);"></i>
      <p style="color: var(--text-secondary);">Aucune parcelle trouvée.</p>
    </div>

    <div v-else-if="resultats.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="p in resultats"
        :key="p.id"
        class="card cursor-pointer hover:shadow-lg transition-shadow"
        @click="router.push({ name: 'CitoyenParcelleDetail', params: { id: p.id } })"
      >
        <div class="flex-between mb-3">
          <div class="flex items-center gap-2">
            <i class="fas fa-map-pin" style="color: var(--green-tree);"></i>
            <span class="font-semibold" style="color: var(--text-primary);">{{ p.titre || p.code || 'Parcelle #' + p.id }}</span>
          </div>
          <span class="text-xs px-2 py-1 rounded-full badge" :class="statutClass(p.statut)">{{ p.statut }}</span>
        </div>
        <div class="text-sm mb-2" style="color: var(--text-secondary);">
          <i class="fas fa-ruler-combined mr-1"></i> {{ p.superficie ? p.superficie + ' m²' : '—' }}
        </div>
        <div class="text-sm" style="color: var(--text-secondary);">
          <i class="fas fa-map-marker-alt mr-1"></i>
          {{ p.commune?.nom || '—' }}
          <template v-if="p.arrondissement"> — {{ p.arrondissement.nom }}</template>
        </div>
      </div>
    </div>
  </div>
</template>
