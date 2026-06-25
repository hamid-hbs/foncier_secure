<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import transactionApi from '@/api/transaction'

const router = useRouter()
const transactions = ref([])
const loading = ref(true)
const page = ref(1)
const totalPages = ref(1)

async function fetchTransactions(p = 1) {
  loading.value = true
  try {
    const res = await transactionApi.list({ page: p, per_page: 10 })
    transactions.value = (res.data.data || []).filter(Boolean)
    totalPages.value = res.data?.meta?.last_page || 1
    page.value = p
  } catch (e) { console.error('Erreur chargement transactions:', e) }
  loading.value = false
}

function statutBadgeClass(s) {
  const map = { cree: 'badge-neutral', en_cours: 'badge-info', cloturee: 'badge-success', annulee: 'badge-danger' }
  return map[s] || 'badge-neutral'
}

onMounted(() => fetchTransactions())
</script>

<template>
  <div class="page-wrap">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Mes transactions</h1>
        <p class="page-subtitle">Historique de vos transactions immobilières</p>
      </div>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 5" :key="i" class="skeleton h-20 rounded-2xl"></div>
    </div>

    <div v-else-if="transactions.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-arrows-left-right"></i></div>
        <p class="empty-title">Aucune transaction</p>
        <p class="empty-text">Vous n'avez pas encore de transaction immobilière enregistrée.</p>
      </div>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="t in transactions" :key="t.id"
        @click="router.push({ name: 'TransactionDetail', params: { id: t.id } })"
        class="card group hover:border-brand-100 hover:shadow-md cursor-pointer transition-all"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-gold/10 flex items-center justify-center text-gold-dark shrink-0 group-hover:bg-gold group-hover:text-white transition-colors">
            <i class="fas fa-file-signature text-lg"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <p class="font-display font-bold text-stone-900">{{ t.titre || 'Transaction #' + t.id }}</p>
              <span class="badge" :class="statutBadgeClass(t.statut)">{{ (t.statut || '').replace('_', ' ') }}</span>
            </div>
            <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-stone-400">
              <span v-if="t.parcelle"><i class="fas fa-map-marker-alt mr-1.5 text-stone-300"></i>{{ t.parcelle?.titre || t.parcelle?.code }}</span>
              <span v-if="t.prix_final"><i class="fas fa-tag mr-1.5 text-stone-300"></i>{{ Number(t.prix_final).toLocaleString('fr-FR') }} FCFA</span>
              <span v-if="t.date_transaction"><i class="fas fa-calendar mr-1.5 text-stone-300"></i>{{ new Date(t.date_transaction).toLocaleDateString('fr-FR') }}</span>
            </div>
          </div>
          <i class="fas fa-chevron-right text-sm text-stone-300 group-hover:text-brand transition-colors hidden sm:block"></i>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <button class="page-btn" :disabled="page <= 1" @click="fetchTransactions(page - 1)"><i class="fas fa-chevron-left text-xs"></i></button>
        <button v-for="p in totalPages" :key="p" class="page-btn" :class="p === page ? 'active' : ''" @click="fetchTransactions(p)">{{ p }}</button>
        <button class="page-btn" :disabled="page >= totalPages" @click="fetchTransactions(page + 1)"><i class="fas fa-chevron-right text-xs"></i></button>
      </div>
    </div>
  </div>
</template>
