<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import transactionApi from '@/api/transaction'
import { goBack } from '@/utils/navigation'

const router = useRouter()

const transactions = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await transactionApi.list()
    transactions.value = (res.data?.data || []).filter(Boolean)
  } catch { /* ignore */ }
  loading.value = false
})

function statutClass(s) {
  const map = {
    cree: 'badge-info',
    en_verification: 'badge-warning',
    geometre_assigne: 'badge-warning',
    rendezvous_planifie: 'badge-info',
    valide: 'badge-success',
    acte_signe: 'badge-success',
    mutation_en_cours: 'badge-warning',
    cloture: 'badge-success',
  }
  return map[s] || 'badge-info'
}
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="flex-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #DBEAFE;">
          <i class="fas fa-arrows-left-right" style="color: #2563EB;"></i>
        </div>
        <div>
          <h1 class="section-title">Mes transactions</h1>
          <p class="section-subtitle">Suivez l'avancement de vos transactions</p>
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--border); border-top-color: var(--green-tree);"></div>
    </div>

    <div v-else-if="transactions.length === 0" class="card text-center py-12">
      <i class="fas fa-arrows-left-right text-4xl mb-3" style="color: var(--border);"></i>
      <p style="color: var(--text-secondary);">Aucune transaction pour le moment. Les transactions sont créées par un notaire.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="t in transactions"
        :key="t.id"
        class="card cursor-pointer hover:shadow-lg transition-shadow"
        @click="router.push({ name: 'TransactionDetail', params: { id: t.id } })"
      >
        <div class="flex-between mb-3">
          <span class="font-semibold truncate" style="color: var(--text-primary);">{{ t.titre || 'Transaction #' + t.id }}</span>
          <span class="text-xs px-2 py-1 rounded-full badge" :class="statutClass(t.statut)">{{ t.statut }}</span>
        </div>
        <div v-if="t.parcelle" class="text-sm mb-2" style="color: var(--text-secondary);">
          <i class="fas fa-map-pin mr-1"></i> {{ t.parcelle.titre || t.parcelle.code || 'Parcelle #' + t.parcelle.id }}
        </div>
        <div class="grid grid-cols-2 gap-2 text-xs" style="color: var(--text-secondary);">
          <div>
            <span class="block" style="color: var(--text-secondary);">Vendeur</span>
            <span class="font-medium" style="color: var(--text-primary);">{{ t.vendeur?.nom || t.vendeur?.prenom || 'Vous' }}</span>
          </div>
          <div>
            <span class="block" style="color: var(--text-secondary);">Acheteur</span>
            <span class="font-medium" style="color: var(--text-primary);">{{ t.acheteur?.nom || t.acheteur?.prenom || t.acheteur_email || 'En attente' }}</span>
          </div>
        </div>
        <div class="text-xs mt-3" style="color: var(--text-secondary);">
          {{ t.created_at ? new Date(t.created_at).toLocaleDateString('fr-FR') : '—' }}
        </div>
      </div>
    </div>
  </div>
</template>
