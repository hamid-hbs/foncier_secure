<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import transactionApi from '@/api/transaction'
import StatutBadge from '@/components/StatutBadge.vue'

const router = useRouter()
const transactions = ref([])
const loading = ref(true)
const page = ref(1)
const totalPages = ref(1)
const search = ref('')
const statutFilter = ref('')

const statuts = [
  { value: '', label: 'Tous les statuts' },
  { value: 'cree', label: 'Créée' },
  { value: 'en_cours', label: 'En cours' },
  { value: 'cloturee', label: 'Clôturée' },
  { value: 'annulee', label: 'Annulée' },
]

async function fetchTransactions(p = 1) {
  loading.value = true
  try {
    const params = { page: p, per_page: 12 }
    if (search.value) params.search = search.value
    if (statutFilter.value) params.statut = statutFilter.value
    const res = await transactionApi.list(params)
    transactions.value = (res.data.data || []).filter(Boolean)
    totalPages.value = res.data?.meta?.last_page || 1
    page.value = p
  } catch (e) { console.error('Erreur chargement transactions:', e) }
  loading.value = false
}

watch([search, statutFilter], () => fetchTransactions(1))
onMounted(() => fetchTransactions())
</script>

<template>
  <div class="page-wrap">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Transactions notariales</h1>
        <p class="page-subtitle">Gérez tous les dossiers de votre étude</p>
      </div>
      <router-link to="/notaire/transactions/creer" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouveau dossier
      </router-link>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
      <div class="relative flex-1">
        <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
        <input v-model="search" type="text" class="form-input pl-10" placeholder="Rechercher un dossier…" />
      </div>
      <select v-model="statutFilter" class="form-select w-full sm:w-48">
        <option v-for="s in statuts" :key="s.value" :value="s.value">{{ s.label }}</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 6" :key="i" class="skeleton h-24 rounded-2xl"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="transactions.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-file-signature"></i></div>
        <p class="empty-title">Aucun dossier trouvé</p>
        <p class="empty-text">Modifiez vos critères ou ouvrez un nouveau dossier de transaction.</p>
        <router-link to="/notaire/transactions/creer" class="btn btn-primary mt-4">
          <i class="fas fa-plus"></i> Ouvrir un dossier
        </router-link>
      </div>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="t in transactions" :key="t.id"
        @click="router.push(`/notaire/transactions/${t.id}`)"
        class="card group hover:border-brand-100 hover:shadow-md cursor-pointer transition-all"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center text-stone-500 shrink-0 group-hover:bg-brand group-hover:text-white transition-colors">
            <i class="fas fa-file-contract text-sm"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
              <p class="font-display font-bold text-stone-900 group-hover:text-brand transition-colors truncate">
                {{ t.titre || 'Transaction #' + t.id }}
              </p>
              <StatutBadge :statut="t.statut" />
            </div>
            <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-stone-400">
              <span><i class="fas fa-user-tag mr-1.5 text-stone-300"></i>{{ t.vendeur?.nom || t.vendeur || 'Vendeur' }}</span>
              <span><i class="fas fa-arrow-right text-xs text-stone-300 mx-1"></i></span>
              <span><i class="fas fa-user mr-1.5 text-stone-300"></i>{{ t.acheteur?.nom || t.acheteur || 'Acheteur' }}</span>
              <span v-if="t.prix_final"><i class="fas fa-tag mr-1.5 text-stone-300"></i>{{ Number(t.prix_final).toLocaleString('fr-FR') }} FCFA</span>
            </div>
          </div>
          <i class="fas fa-chevron-right text-sm text-stone-300 group-hover:text-brand transition-colors hidden sm:block"></i>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <button class="page-btn" :disabled="page <= 1" @click="fetchTransactions(page - 1)"><i class="fas fa-chevron-left text-xs"></i></button>
        <button v-for="p in Math.min(totalPages, 8)" :key="p" class="page-btn" :class="p === page ? 'active' : ''" @click="fetchTransactions(p)">{{ p }}</button>
        <button class="page-btn" :disabled="page >= totalPages" @click="fetchTransactions(page + 1)"><i class="fas fa-chevron-right text-xs"></i></button>
      </div>
    </div>
  </div>
</template>
