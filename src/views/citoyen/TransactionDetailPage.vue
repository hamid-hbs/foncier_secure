<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { goBack } from '@/utils/navigation'
import transactionApi from '@/api/transaction'

const route = useRoute()
const router = useRouter()

const transaction = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data || null
  } catch (e) { console.error('Erreur chargement transaction:', e) }
  loading.value = false
})

function statutBadgeClass(s) {
  const map = { cree: 'badge-neutral', en_cours: 'badge-info', cloturee: 'badge-success', annulee: 'badge-danger' }
  return map[s] || 'badge-neutral'
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

const BASE_URL = import.meta.env.VITE_API_URL?.replace('/api', '') || ''
function getDocUrl(chemin) {
  if (!chemin) return '#'
  if (chemin.startsWith('http')) return chemin
  return `${BASE_URL}/storage/${chemin}`
}
</script>

<template>
  <div class="page-wrap max-w-3xl mx-auto">
    <!-- Loading -->
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-32 rounded-2xl"></div>
      <div class="skeleton h-48 rounded-2xl"></div>
    </div>

    <!-- Not found -->
    <div v-else-if="!transaction" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-file-signature"></i></div>
        <p class="empty-title">Transaction introuvable</p>
        <button @click="goBack(router)" class="btn btn-primary mt-4">Retour</button>
      </div>
    </div>

    <template v-else>
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
        <i class="fas fa-arrow-left text-xs"></i> Retour
      </button>

      <!-- Header -->
      <div class="card mb-5">
        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
          <div class="w-14 h-14 rounded-2xl bg-gold/10 flex items-center justify-center text-gold-dark shrink-0">
            <i class="fas fa-file-contract text-xl"></i>
          </div>
          <div class="flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <h1 class="font-display font-extrabold text-xl text-stone-900">{{ transaction.titre || 'Transaction #' + transaction.id }}</h1>
              <span class="badge" :class="statutBadgeClass(transaction.statut)">{{ transaction.statut }}</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
              <div class="px-4 py-3 rounded-xl bg-stone-50">
                <p class="text-xs text-stone-400 font-medium">Vendeur</p>
                <p class="text-sm font-bold text-stone-900 mt-0.5">{{ transaction.vendeur?.prenom }} {{ transaction.vendeur?.nom }}</p>
              </div>
              <div class="px-4 py-3 rounded-xl bg-stone-50">
                <p class="text-xs text-stone-400 font-medium">Acheteur</p>
                <p class="text-sm font-bold text-stone-900 mt-0.5">{{ transaction.acheteur?.prenom }} {{ transaction.acheteur?.nom }}</p>
              </div>
              <div v-if="transaction.notaire" class="px-4 py-3 rounded-xl bg-stone-50">
                <p class="text-xs text-stone-400 font-medium">Notaire</p>
                <p class="text-sm font-bold text-stone-900 mt-0.5">Maître {{ transaction.notaire?.nom }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Details -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
        <div class="card">
          <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2"><i class="fas fa-info-circle text-brand text-sm"></i> Détails</h3>
          <div class="space-y-3">
            <div v-if="transaction.parcelle" class="flex items-start gap-3 py-2 border-b border-stone-50">
              <i class="fas fa-map-marker-alt text-stone-400 mt-0.5 w-4 text-center text-xs"></i>
              <div>
                <p class="text-xs text-stone-400">Parcelle</p>
                <p class="text-sm font-bold text-stone-900">{{ transaction.parcelle?.titre || '#' + transaction.parcelle_id }}</p>
              </div>
            </div>
            <div v-if="transaction.prix_final" class="flex items-start gap-3 py-2 border-b border-stone-50">
              <i class="fas fa-tag text-gold mt-0.5 w-4 text-center text-xs"></i>
              <div>
                <p class="text-xs text-stone-400">Prix final</p>
                <p class="text-sm font-bold text-stone-900">{{ Number(transaction.prix_final).toLocaleString('fr-FR') }} FCFA</p>
              </div>
            </div>
            <div class="flex items-start gap-3 py-2">
              <i class="fas fa-calendar text-stone-400 mt-0.5 w-4 text-center text-xs"></i>
              <div>
                <p class="text-xs text-stone-400">Date</p>
                <p class="text-sm font-bold text-stone-900">{{ formatDate(transaction.date_transaction || transaction.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Timeline / étapes -->
        <div class="card">
          <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2"><i class="fas fa-timeline text-brand text-sm"></i> Progression</h3>
          <div class="space-y-3">
            <div v-for="step in [
              { key: 'cree', label: 'Dossier créé', icon: 'fa-plus' },
              { key: 'en_cours', label: 'En cours', icon: 'fa-hourglass-half' },
              { key: 'cloturee', label: 'Clôturée', icon: 'fa-check-double' },
            ]" :key="step.key"
            class="flex items-center gap-3"
            :class="transaction.statut === step.key || (step.key === 'cree') ? 'opacity-100' : transaction.statut === 'annulee' ? 'opacity-30' : 'opacity-40'">
              <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-xs"
                :class="transaction.statut === step.key ? 'bg-brand text-white' : 'bg-stone-100 text-stone-400'">
                <i :class="['fas', step.icon]"></i>
              </div>
              <span class="text-sm font-semibold" :class="transaction.statut === step.key ? 'text-stone-900' : 'text-stone-400'">{{ step.label }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Documents -->
      <div v-if="transaction.documents?.length" class="card">
        <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
          <i class="fas fa-folder-open text-brand text-sm"></i> Documents de la transaction
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <a v-for="doc in transaction.documents" :key="doc.id"
            :href="getDocUrl(doc.chemin_fichier || doc.fichier)" target="_blank"
            class="flex items-center gap-3 p-3 rounded-xl border border-stone-100 hover:border-brand-100 hover:bg-brand-50/20 transition-all">
            <div class="w-9 h-9 rounded-lg bg-stone-100 flex items-center justify-center text-stone-500 text-sm shrink-0">
              <i class="fas fa-file-pdf"></i>
            </div>
            <div class="min-w-0">
              <p class="text-xs font-bold text-stone-900 capitalize truncate">{{ doc.type_document || 'Document' }}</p>
              <p class="text-[10px] text-stone-400">Voir le fichier</p>
            </div>
          </a>
        </div>
      </div>
    </template>
  </div>
</template>
