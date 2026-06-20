<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import verificationApi from '@/api/verification'

const router = useRouter()
const auth = useAuthStore()
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

const stats = computed(() => ({
  total: missions.value.length,
  en_attente: missions.value.filter(m => m.statut === 'sollicite').length,
  en_cours: missions.value.filter(m => m.statut === 'en_cours').length,
  terminees: missions.value.filter(m => m.statut === 'termine' || m.statut === 'terminee').length
}))
</script>

<template>
  <div class="page-container">
    <button @click="router.push('/mon-profil')" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="section-title">Espace géomètre</h1>
        <p class="section-subtitle">Bienvenue, {{ auth.user?.prenom || 'Géomètre' }}</p>
      </div>
      <div class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
        <i class="fas fa-clock"></i>
        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
      </div>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="spinner"></div>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-5 mb-8">
        <div class="stats-card card flex-center flex-col text-center" style="transition: all 0.2s;">
          <div class="stat-value" style="color: var(--green-tree);">{{ stats.total }}</div>
          <div class="stat-label">Total missions</div>
        </div>
        <div class="stats-card card flex-center flex-col text-center" style="transition: all 0.2s;">
          <div class="stat-value" style="color: var(--gold);">{{ stats.en_attente }}</div>
          <div class="stat-label">En attente</div>
        </div>
        <div class="stats-card card flex-center flex-col text-center" style="transition: all 0.2s;">
          <div class="stat-value" style="color: var(--info);">{{ stats.en_cours }}</div>
          <div class="stat-label">En cours</div>
        </div>
        <div class="stats-card card flex-center flex-col text-center" style="transition: all 0.2s;">
          <div class="stat-value" style="color: var(--green-tree);">{{ stats.terminees }}</div>
          <div class="stat-label">Terminées</div>
        </div>
      </div>

      <div class="card mb-6">
        <h3 class="text-lg font-semibold mb-5 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-bolt"></i> Actions rapides
        </h3>
        <router-link to="/geometre/missions" class="flex-center gap-2 p-4 rounded-lg font-medium text-sm" style="background: var(--bg-page); color: var(--green-tree);">
          <i class="fas fa-map"></i> Voir mes missions
        </router-link>
      </div>

      <div class="card">
        <h4 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-list" style="color: var(--green-tree);"></i> Dernières missions
        </h4>
        <div v-if="missions.length === 0" class="text-sm py-4" style="color: var(--text-secondary);">Aucune mission assignée.</div>
        <div v-for="m in missions.slice(0, 5)" :key="m.id" class="py-3 border-t flex items-center justify-between" style="border-color: var(--border);">
          <div>
            <router-link :to="`/geometre/verifications/${m.verification?.id || m.id}`" class="text-sm font-medium" style="color: var(--text-primary);">
              {{ m.verification?.titre || m.titre || 'Mission #' + m.id }}
            </router-link>
            <p class="text-xs" style="color: var(--text-secondary);">Statut : {{ m.statut }}</p>
          </div>
          <span class="badge" :class="'badge-' + (m.statut || 'sollicite')">{{ m.statut }}</span>
        </div>
      </div>
    </template>
  </div>
</template>
