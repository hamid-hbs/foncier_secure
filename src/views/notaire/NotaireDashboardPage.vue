<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import transactionApi from '@/api/transaction'
import StatutBadge from '@/components/StatutBadge.vue'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const auth = useAuthStore()
const stats = ref({ total: 0, en_cours: 0, cloturees: 0 })
const recentTransactions = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const [allRes, pendingRes] = await Promise.all([
      transactionApi.list({ page: 1, per_page: 5 }),
      transactionApi.list({ statut: 'en_cours', page: 1, per_page: 5 }),
    ])
    const allData = allRes.data?.data || allRes.data || []
    const pendingData = pendingRes.data?.data || pendingRes.data || []
    recentTransactions.value = (Array.isArray(allData) ? allData : []).slice(0, 5)
    const enCoursCount = Array.isArray(pendingData) ? pendingData.length : 0
    const allCount = (allRes.data?.meta?.total || allRes.data?.total || (Array.isArray(allData) ? allData.length : 0))
    stats.value = {
      total: allCount,
      en_cours: enCoursCount,
      cloturees: 0,
    }
  } catch { /* ignore */ }
  loading.value = false
})
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="section-title">Espace notaire</h1>
        <p class="section-subtitle">Bienvenue, {{ auth.user?.prenom || 'Notaire' }}</p>
      </div>
      <div class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
        <i class="fas fa-clock"></i>
        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
      <div class="stats-card">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-lg flex items-center justify-center" style="background: var(--green-tree); opacity: 0.15;">
            <i class="fas fa-file-lines" style="color: var(--green-tree);"></i>
          </div>
          <i class="fas fa-chart-line" style="color: var(--success);"></i>
        </div>
        <div class="stat-value">{{ loading ? '...' : stats.total }}</div>
        <div class="stat-label">Total transactions</div>
      </div>
      <div class="stats-card">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-lg flex items-center justify-center" style="background: var(--gold); opacity: 0.15;">
            <i class="fas fa-clock" style="color: var(--gold);"></i>
          </div>
          <i class="fas fa-chart-line" style="color: var(--success);"></i>
        </div>
        <div class="stat-value">{{ loading ? '...' : stats.en_cours }}</div>
        <div class="stat-label">En cours</div>
      </div>
      <div class="stats-card">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-lg flex items-center justify-center" style="background: var(--success); opacity: 0.15;">
            <i class="fas fa-circle-check" style="color: var(--success);"></i>
          </div>
          <i class="fas fa-chart-line" style="color: var(--success);"></i>
        </div>
        <div class="stat-value">{{ loading ? '...' : stats.cloturees }}</div>
        <div class="stat-label">Clôturées</div>
      </div>
    </div>

    <div class="card mb-6">
      <div class="flex-between mb-4">
        <h3 class="section-title" style="font-size: 1rem;">Transactions récentes</h3>
        <router-link to="/notaire/transactions" class="btn-outline btn-sm flex items-center gap-1.5">
          <i class="fas fa-eye"></i> Voir tout
        </router-link>
      </div>
      <div v-if="loading" class="flex-center py-8">
        <div class="w-6 h-6 border-2 rounded-full animate-spin" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
      </div>
      <div v-else-if="recentTransactions.length === 0" class="text-center py-8" style="color: var(--text-secondary);">
        <i class="fas fa-file-lines mb-2" style="font-size: 1.5rem;"></i>
        <p>Aucune transaction récente</p>
      </div>
      <div v-else class="table-wrap">
        <table class="w-full">
          <thead>
            <tr>
              <th class="table-header">Titre</th>
              <th class="table-header">Vendeur</th>
              <th class="table-header">Acheteur</th>
              <th class="table-header">Statut</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color: var(--border);">
            <tr v-for="t in recentTransactions" :key="t.id" @click="router.push(`/notaire/transactions/${t.id}`)" class="cursor-pointer" style="transition: background 0.15s;" @mouseenter="$event.currentTarget.style.background = 'var(--bg-page)'" @mouseleave="$event.currentTarget.style.background = ''">
              <td class="table-cell font-medium" style="color: var(--text-primary);">{{ t.titre || 'Sans titre' }}</td>
              <td class="table-cell">{{ t.vendeur?.nom || t.vendeur || '-' }}</td>
              <td class="table-cell">{{ t.acheteur?.nom || t.acheteur || '-' }}</td>
              <td class="table-cell"><StatutBadge :statut="t.statut" /></td>
              <td class="table-cell"><i class="fas fa-chevron-right" style="color: var(--text-secondary);"></i></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <h3 class="section-title mb-4" style="font-size: 1rem;">
        <i class="fas fa-bolt"></i> Actions rapides
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <router-link to="/notaire/transactions" class="flex-center gap-2 p-4 rounded-lg font-medium text-sm" style="background: var(--bg-page); color: var(--green-tree); transition: opacity 0.15s;" @mouseenter="$event.currentTarget.style.opacity = '0.8'" @mouseleave="$event.currentTarget.style.opacity = '1'">
          <i class="fas fa-arrows-left-right"></i> Voir les transactions
        </router-link>
        <router-link to="/notaire/dossiers" class="flex-center gap-2 p-4 rounded-lg font-medium text-sm" style="background: var(--bg-page); color: var(--text-primary); transition: opacity 0.15s;" @mouseenter="$event.currentTarget.style.opacity = '0.8'" @mouseleave="$event.currentTarget.style.opacity = '1'">
          <i class="fas fa-folder"></i> Voir les dossiers
        </router-link>
      </div>
    </div>
  </div>
</template>
