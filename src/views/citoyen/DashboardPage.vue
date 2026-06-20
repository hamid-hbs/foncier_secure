<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import parcelleApi from '@/api/parcelle'
import transactionApi from '@/api/transaction'



const router = useRouter()
const auth = useAuthStore()

const parcellesCount = ref(0)
const transactionsCount = ref(0)

onMounted(async () => {
  try {
    const res = await parcelleApi.list()
    parcellesCount.value = Array.isArray(res.data?.data) ? res.data.data.length : 0
  } catch { /* ignore */ }
  try {
    const res = await transactionApi.list()
    transactionsCount.value = Array.isArray(res.data?.data) ? res.data.data.length : 0
  } catch { /* ignore */ }
})
</script>

<template>
  <div class="page-container">
    <button @click="router.push({ name: 'Profil' })" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="flex-between mb-8">
      <div>
        <h1 class="section-title">Bonjour, {{ auth.user?.prenom || 'Utilisateur' }}</h1>
        <p class="section-subtitle">Voici un résumé de votre activité</p>
      </div>
      <div class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
        <i class="fas fa-clock"></i>
        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
      <div class="stats-card">
        <div class="flex-between mb-3">
          <div class="w-11 h-11 rounded-lg flex items-center justify-center" style="background: #D1FAE5;">
            <i class="fas fa-map-pin" style="color: var(--green-tree);"></i>
          </div>
          <i class="fas fa-chart-line" style="color: var(--gold);"></i>
        </div>
        <div class="text-2xl font-bold" style="color: var(--text-primary);">{{ parcellesCount }}</div>
        <div class="text-sm" style="color: var(--text-secondary);">Mes parcelles</div>
      </div>
      <div class="stats-card">
        <div class="flex-between mb-3">
          <div class="w-11 h-11 rounded-lg flex items-center justify-center" style="background: #DBEAFE;">
            <i class="fas fa-arrows-left-right" style="color: #2563EB;"></i>
          </div>
          <i class="fas fa-chart-line" style="color: var(--gold);"></i>
        </div>
        <div class="text-2xl font-bold" style="color: var(--text-primary);">{{ transactionsCount }}</div>
        <div class="text-sm" style="color: var(--text-secondary);">Transactions</div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="card">
        <h3 class="font-semibold mb-5 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-heart-pulse" style="color: var(--green-tree);"></i>
          Actions rapides
        </h3>
        <div class="space-y-3">
          <router-link :to="{ name: 'CreerParcelle' }" class="flex items-center gap-3 p-3.5 rounded-lg transition-colors" style="background: #D1FAE5; color: var(--green-tree);">
            <i class="fas fa-plus"></i> Déclarer une parcelle
          </router-link>
          <router-link :to="{ name: 'Transactions' }" class="flex items-center gap-3 p-3.5 rounded-lg transition-colors" style="background: #DBEAFE; color: #2563EB;">
            <i class="fas fa-arrows-left-right"></i> Voir mes transactions
          </router-link>
        </div>
      </div>

      <div class="card">
        <h3 class="font-semibold mb-5 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-clock" style="color: var(--green-tree);"></i>
          Activités récentes
        </h3>
        <div class="text-center py-8" style="color: var(--text-secondary);">
          <i class="fas fa-user text-4xl mb-2" style="color: var(--border);"></i>
          <p class="text-sm">Aucune activité récente</p>
        </div>
      </div>
    </div>
  </div>
</template>
