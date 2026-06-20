<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import verificationApi from '@/api/verification'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const missions = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await verificationApi.getMissions()
    const data = res.data?.data || res.data || []
    missions.value = (Array.isArray(data) ? data : []).filter(Boolean)
  } catch { /* ignore */ }
  loading.value = false
})

function statutLabel(s) {
  const map = { sollicite: 'Sollicité', en_cours: 'En cours', termine: 'Terminé', terminee: 'Terminée' }
  return map[s] || s
}

function peutDeposerRapport(statut) {
  return statut === 'sollicite' || statut === 'en_cours'
}
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center gap-3 mb-8">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--bg-page); color: var(--green-tree);">
        <i class="fas fa-map"></i>
      </div>
      <div>
        <h1 class="section-title">Mes missions terrain</h1>
        <p class="section-subtitle">Missions de vérification qui vous sont assignées</p>
      </div>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="spinner"></div>
    </div>

    <div v-else-if="missions.length === 0" class="card text-center py-12">
      <i class="fas fa-map" style="font-size: 48px; color: var(--border); margin-bottom: 16px;"></i>
      <p style="color: var(--text-secondary);">Aucune mission assignée.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div v-for="m in missions" :key="m.id" class="card">
        <div class="flex items-start justify-between mb-4">
          <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: var(--bg-page); color: var(--green-tree);">
            <i class="fas fa-clipboard-list"></i>
          </div>
          <span class="badge" :class="'badge-' + (m.statut || 'sollicite')">{{ statutLabel(m.statut) }}</span>
        </div>
        <h3 class="font-semibold mb-1" style="color: var(--text-primary);">{{ m.verification?.titre || m.titre || 'Mission #' + m.id }}</h3>
        <p v-if="m.verification?.parcelle" class="text-sm mb-2" style="color: var(--text-secondary);">
          <i class="fas fa-map-pin"></i> {{ m.verification.parcelle.code || m.verification.parcelle.nom || 'Parcelle #' + m.verification.parcelle.id }}
        </p>
        <p v-if="m.verification?.parcelle?.commune" class="text-xs mb-4" style="color: var(--text-secondary);">
          <i class="fas fa-location-dot"></i> {{ m.verification.parcelle.commune }}
        </p>
        <div class="flex gap-2">
          <router-link :to="`/geometre/verifications/${m.verification?.id || m.id}`" class="btn-outline btn-sm flex items-center gap-1.5">
            <i class="fas fa-eye"></i> Voir détails
          </router-link>
          <router-link v-if="peutDeposerRapport(m.statut)" :to="`/geometre/verifications/${m.verification?.id || m.id}/rapport`" class="btn-green btn-sm flex items-center gap-1.5">
            <i class="fas fa-upload"></i> Déposer rapport
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
