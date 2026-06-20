<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import transactionApi from '@/api/transaction'
import StatutBadge from '@/components/StatutBadge.vue'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const transactions = ref([])
const loading = ref(true)
const search = ref('')
const statutFilter = ref('')
const page = ref(1)
const lastPage = ref(1)

onMounted(() => fetchTransactions())

watch([search, statutFilter], () => {
  page.value = 1
  fetchTransactions()
})

async function fetchTransactions() {
  loading.value = true
  try {
    const params = { page: page.value }
    if (search.value) params.search = search.value
    if (statutFilter.value) params.statut = statutFilter.value
    const res = await transactionApi.list(params)
    const data = res.data?.data || res.data || []
    transactions.value = (Array.isArray(data) ? data : []).filter(Boolean)
    lastPage.value = res.data?.meta?.last_page || res.data?.last_page || 1
  } catch { /* ignore */ }
  loading.value = false
}

function goToDetail(id) {
  router.push(`/notaire/transactions/${id}`)
}

function prevPage() {
  if (page.value > 1) { page.value--; fetchTransactions() }
}

function nextPage() {
  if (page.value < lastPage.value) { page.value++; fetchTransactions() }
}
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--green-tree); opacity: 0.15;">
          <i class="fas fa-arrows-left-right" style="color: var(--green-tree);"></i>
        </div>
        <div>
          <h1 class="section-title">Transactions</h1>
          <p class="section-subtitle">Consultez toutes les transactions</p>
        </div>
      </div>
      <router-link to="/notaire/transactions/creer" class="btn-green btn-sm flex items-center gap-1.5"><i class="fas fa-plus"></i> Nouveau dossier</router-link>
    </div>

    <div class="flex flex-wrap gap-4 mb-6">
      <div class="relative flex-1 min-w-[200px]">
        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 0.875rem;"></i>
        <input v-model="search" class="form-input" style="padding-left: 36px; width: 100%;" placeholder="Rechercher..." />
      </div>
      <select v-model="statutFilter" class="form-select" style="width: 180px;">
        <option value="">Tous les statuts</option>
        <option value="cree">Création</option>
        <option value="en_verification">En vérification</option>
        <option value="geometre_assigne">Géomètre assigné</option>
        <option value="rendezvous_planifie">RDV planifié</option>
        <option value="valide">Validé</option>
        <option value="acte_signe">Acte signé</option>
        <option value="mutation_en_cours">Mutation en cours</option>
        <option value="cloture">Clôturé</option>
      </select>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <div v-else-if="transactions.length === 0" class="card text-center py-12">
      <i class="fas fa-arrows-left-right mb-3" style="font-size: 2.5rem; color: var(--text-secondary); opacity: 0.5;"></i>
      <p style="color: var(--text-secondary);">Aucune transaction trouvée.</p>
    </div>

    <template v-else>
      <div class="table-wrap">
        <table class="w-full">
          <thead>
            <tr>
              <th class="table-header">Titre</th>
              <th class="table-header">Parcelle</th>
              <th class="table-header">Vendeur</th>
              <th class="table-header">Acheteur</th>
              <th class="table-header">Statut</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color: var(--border);">
            <tr v-for="t in transactions" :key="t.id" @click="goToDetail(t.id)" class="cursor-pointer" style="transition: background 0.15s;" @mouseenter="$event.currentTarget.style.background = 'var(--bg-page)'" @mouseleave="$event.currentTarget.style.background = ''">
              <td class="table-cell font-medium" style="color: var(--text-primary);">
                <i class="fas fa-file-lines mr-2" style="color: var(--green-tree);"></i>
                {{ t.titre || 'Sans titre' }}
              </td>
              <td class="table-cell">{{ t.parcelle?.titre || t.parcelle || '-' }}</td>
              <td class="table-cell">{{ t.vendeur?.nom || t.vendeur || '-' }}</td>
              <td class="table-cell">{{ t.acheteur?.nom || t.acheteur || '-' }}</td>
              <td class="table-cell"><StatutBadge :statut="t.statut" /></td>
              <td class="table-cell"><i class="fas fa-chevron-right" style="color: var(--text-secondary);"></i></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex-between mt-4">
        <span style="color: var(--text-secondary); font-size: 0.875rem;">Page {{ page }} / {{ lastPage }}</span>
        <div class="flex gap-2">
          <button @click="prevPage" :disabled="page <= 1" class="btn-outline btn-sm flex items-center gap-1.5">
            <i class="fas fa-chevron-left"></i> Précédent
          </button>
          <button @click="nextPage" :disabled="page >= lastPage" class="btn-outline btn-sm flex items-center gap-1.5">
            Suivant <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </template>
  </div>
</template>
