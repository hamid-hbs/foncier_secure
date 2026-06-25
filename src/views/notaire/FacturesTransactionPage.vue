<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import factureApi from '@/api/facture'
import transactionApi from '@/api/transaction'
import StatutBadge from '@/components/StatutBadge.vue'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const factures = ref([])
const transaction = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const resTx = await transactionApi.show(route.params.id)
    transaction.value = resTx.data || null
    const res = await factureApi.list({ transaction_id: route.params.id })
    factures.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement factures:', e) }
  loading.value = false
})

function statutBadgeClass(s) {
  const map = { brouillon: 'badge-info', envoye: 'badge-info', envoyee: 'badge-warning', payee: 'badge-success', annulee: 'badge-danger' }
  return map[s] || 'badge-neutral'
}
function statutLabel(s) {
  const map = { brouillon: 'Brouillon', envoye: 'Envoyée', envoyee: 'Envoyée', payee: 'Payée', annulee: 'Annulée' }
  return map[s] || s
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>

<template>
  <div class="page-wrap">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
      <i class="fas fa-arrow-left text-xs"></i> {{ transaction?.titre || 'Dossier' }}
    </button>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Factures</h1>
        <p class="page-subtitle">{{ transaction?.titre || 'Transaction #' + route.params.id }}</p>
      </div>
      <router-link :to="{ name: 'NotaireFactureCreer', params: { id: route.params.id } }" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvelle facture
      </router-link>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="skeleton h-20 rounded-2xl"></div>
    </div>

    <div v-else-if="factures.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <p class="empty-title">Aucune facture</p>
        <p class="empty-text">Aucune facture n'a encore été créée pour ce dossier.</p>
        <router-link :to="{ name: 'NotaireFactureCreer', params: { id: route.params.id } }" class="btn btn-primary mt-4">
          <i class="fas fa-plus"></i> Créer une facture
        </router-link>
      </div>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="f in factures" :key="f.id"
        @click="router.push({ name: 'NotaireFactureDetail', params: { id: f.id } })"
        class="card group hover:border-brand-100 hover:shadow-md cursor-pointer transition-all"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="w-11 h-11 rounded-xl bg-stone-100 flex items-center justify-center text-stone-500 shrink-0 group-hover:bg-brand group-hover:text-white transition-colors">
            <i class="fas fa-file-invoice text-sm"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <p class="font-display font-bold text-stone-900 group-hover:text-brand transition-colors truncate">
                {{ f.reference || f.libelle || 'Facture #' + f.id }}
              </p>
              <span class="badge" :class="statutBadgeClass(f.statut)">{{ statutLabel(f.statut) }}</span>
            </div>
            <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-stone-400">
              <span><i class="fas fa-tag mr-1.5 text-stone-300"></i>{{ Number(f.montant).toLocaleString('fr-FR') }} FCFA</span>
              <span><i class="fas fa-calendar mr-1.5 text-stone-300"></i>{{ formatDate(f.date_emission || f.created_at) }}</span>
              <span v-if="f.emetteur"><i class="fas fa-user mr-1.5 text-stone-300"></i>{{ f.emetteur?.prenom || '' }} {{ f.emetteur?.nom || '' }}</span>
            </div>
          </div>
          <i class="fas fa-chevron-right text-sm text-stone-300 group-hover:text-brand transition-colors hidden sm:block"></i>
        </div>
      </div>
    </div>
  </div>
</template>
