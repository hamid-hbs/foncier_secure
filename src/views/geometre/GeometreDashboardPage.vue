<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import verificationApi from '@/api/verification'

const router = useRouter()
const auth = useAuthStore()
const missions = ref([])
const loading = ref(true)

const stats = computed(() => {
  const all = missions.value
  return {
    total: all.length,
    en_attente: all.filter(m => ['soumise','en_analyse'].includes(m.statut)).length,
    assignees: all.filter(m => m.statut === 'mission_assignee').length,
    terminees: all.filter(m => ['terminee','validee'].includes(m.statut)).length,
  }
})

function statutBadgeClass(s) {
  const map = { soumise: 'badge-neutral', en_analyse: 'badge-info', mission_assignee: 'badge-purple', terminee: 'badge-success', validee: 'badge-success', rejetee: 'badge-danger' }
  return map[s] || 'badge-neutral'
}
function statutLabel(s) {
  const map = { soumise: 'Soumise', en_analyse: 'En analyse', mission_assignee: 'Assignée', terminee: 'Terminée', validee: 'Validée', rejetee: 'Rejetée' }
  return map[s] || s
}

onMounted(async () => {
  try {
    const res = await verificationApi.list()
    missions.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement dashboard géomètre:', e) }
  loading.value = false
})
</script>

<template>
  <div class="page-wrap">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Bonjour, {{ auth.user?.prenom || 'Géomètre' }}</h1>
        <p class="page-subtitle">Tableau de bord des missions de vérification</p>
      </div>
      <div class="flex items-center gap-2 text-sm text-stone-500 bg-white border border-stone-200 px-4 py-2 rounded-xl shadow-xs">
        <i class="fas fa-calendar text-brand"></i>
        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' }) }}
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
      <div v-for="i in 4" :key="i" class="skeleton h-24 rounded-2xl"></div>
    </div>

    <template v-else>
      <!-- Stats -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="card">
          <div class="flex items-start justify-between mb-2">
            <p class="stat-label">Total missions</p>
            <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center text-brand shrink-0"><i class="fas fa-map-location-dot text-sm"></i></div>
          </div>
          <p class="stat-value">{{ stats.total }}</p>
        </div>
        <div class="card">
          <div class="flex items-start justify-between mb-2">
            <p class="stat-label">En attente</p>
            <div class="w-9 h-9 rounded-xl bg-warn/10 flex items-center justify-center text-warn shrink-0"><i class="fas fa-hourglass-half text-sm"></i></div>
          </div>
          <p class="stat-value">{{ stats.en_attente }}</p>
        </div>
        <div class="card">
          <div class="flex items-start justify-between mb-2">
            <p class="stat-label">Assignées</p>
            <div class="w-9 h-9 rounded-xl bg-info-50 flex items-center justify-center" style="background:#e0f2fe;color:#0284c7"><i class="fas fa-clipboard-list text-sm"></i></div>
          </div>
          <p class="stat-value">{{ stats.assignees }}</p>
        </div>
        <div class="card">
          <div class="flex items-start justify-between mb-2">
            <p class="stat-label">Terminées</p>
            <div class="w-9 h-9 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0"><i class="fas fa-circle-check text-sm"></i></div>
          </div>
          <p class="stat-value">{{ stats.terminees }}</p>
        </div>
      </div>

      <!-- Missions list -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card">
          <div class="flex items-center justify-between mb-5">
            <h3 class="font-display font-bold text-stone-900">Mes missions récentes</h3>
            <router-link to="/geometre/missions" class="text-xs font-bold text-brand">Voir toutes <i class="fas fa-arrow-right ml-1 text-[10px]"></i></router-link>
          </div>
          <div v-if="missions.length === 0" class="empty-state py-10">
            <div class="empty-icon"><i class="fas fa-map-location-dot"></i></div>
            <p class="empty-title">Aucune mission</p>
            <p class="empty-text">Vous n'avez pas encore de mission assignée.</p>
          </div>
          <div v-else class="space-y-2">
            <div v-for="m in missions.slice(0, 6)" :key="m.id"
              @click="router.push(`/geometre/verifications/${m.id}/rapport`)"
              class="flex items-center gap-4 p-4 rounded-xl border border-stone-100 hover:border-brand-100 hover:bg-brand-50/20 cursor-pointer transition-all group">
              <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand group-hover:bg-brand group-hover:text-white transition-colors shrink-0">
                <i class="fas fa-ruler-combined text-sm"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-stone-900 text-sm">
                  {{ m.parcelle?.titre || m.parcelle?.code || 'Parcelle #' + (m.parcelle_id || m.id) }}
                </p>
                <p class="text-xs text-stone-400">
                  {{ m.parcelle?.commune?.nom || 'Localisation inconnue' }}
                  {{ m.created_at ? ' — ' + new Date(m.created_at).toLocaleDateString('fr-FR') : '' }}
                </p>
              </div>
              <span class="badge shrink-0" :class="statutBadgeClass(m.statut)">{{ statutLabel(m.statut) }}</span>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div class="card bg-brand-dark text-white relative overflow-hidden">
            <div class="absolute -right-4 -top-4 text-white/5 pointer-events-none">
              <i class="fas fa-ruler-combined text-[100px]"></i>
            </div>
            <h3 class="font-display font-bold mb-4 relative z-10">Accès rapide</h3>
            <div class="space-y-2 relative z-10">
              <router-link to="/geometre/missions" class="flex items-center gap-3 p-3 rounded-xl bg-brand hover:bg-brand-light transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0"><i class="fas fa-map-location-dot text-sm"></i></div>
                <span class="font-semibold text-sm">Toutes mes missions</span>
              </router-link>
              <router-link to="/mon-profil" class="flex items-center gap-3 p-3 rounded-xl bg-white/8 hover:bg-white/15 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0"><i class="fas fa-user text-sm"></i></div>
                <span class="font-semibold text-sm">Mon profil</span>
              </router-link>
            </div>
          </div>

          <div v-if="stats.assignees > 0" class="card border border-brand-100 bg-brand-50">
            <div class="flex items-start gap-3">
              <div class="w-9 h-9 rounded-xl bg-brand-100 flex items-center justify-center text-brand shrink-0">
                <i class="fas fa-bell"></i>
              </div>
              <div>
                <p class="font-bold text-stone-900 text-sm">Missions assignées</p>
                <p class="text-xs text-stone-500 mt-0.5">{{ stats.assignees }} mission(s) vous attendent.</p>
                <router-link to="/geometre/missions" class="text-xs font-bold text-brand mt-2 block">Consulter maintenant →</router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
