<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import factureApi from '@/api/facture'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const facture = ref(null)
const loading = ref(true)
const processing = ref(false)
const downloadLoading = ref(false)

onMounted(async () => {
  try {
    const res = await factureApi.show(route.params.id)
    facture.value = res.data || null
  } catch (e) { console.error('Erreur chargement facture:', e) }
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

async function envoyer() {
  processing.value = true
  try {
    await factureApi.envoyer(facture.value.id)
    facture.value.statut = 'envoyee'
  } catch (e) { console.error('Erreur envoi facture:', e) }
  processing.value = false
}

async function payer() {
  processing.value = true
  try {
    await factureApi.payer(facture.value.id)
    facture.value.statut = 'payee'
  } catch (e) { console.error('Erreur paiement facture:', e) }
  processing.value = false
}

async function downloadPdf() {
  downloadLoading.value = true
  try {
    const res = await factureApi.pdf(facture.value.id)
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `facture-${facture.value.id}.pdf`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (e) { console.error('Erreur téléchargement PDF:', e) }
  downloadLoading.value = false
}
</script>

<template>
  <div class="page-wrap max-w-3xl mx-auto">
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-28 rounded-2xl"></div>
      <div class="skeleton h-56 rounded-2xl"></div>
    </div>

    <div v-else-if="!facture" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <p class="empty-title">Facture introuvable</p>
        <button @click="goBack(router)" class="btn btn-primary mt-4">Retour</button>
      </div>
    </div>

    <template v-else>
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
        <i class="fas fa-arrow-left text-xs"></i> Factures
      </button>

      <!-- Header -->
      <div class="card mb-5">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
          <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-stone-100 flex items-center justify-center text-stone-500 shrink-0">
              <i class="fas fa-file-invoice text-xl"></i>
            </div>
            <div>
              <div class="flex flex-wrap items-center gap-2 mb-2">
                <h1 class="font-display font-bold text-xl text-stone-900">{{ facture.reference || facture.libelle || 'Facture #' + facture.id }}</h1>
                <span class="badge" :class="statutBadgeClass(facture.statut)">{{ statutLabel(facture.statut) }}</span>
              </div>
              <p class="text-sm text-stone-400">
                <i class="fas fa-calendar mr-1.5 text-stone-300"></i>
                {{ formatDate(facture.date_emission || facture.created_at) }}
              </p>
            </div>
          </div>
          <!-- Actions -->
          <div class="flex gap-2 flex-wrap">
            <button v-if="facture.statut === 'emise'" @click="envoyer" class="btn btn-primary btn-sm" :disabled="processing">
              <div v-if="processing" class="spinner spinner-sm border-white/30 border-t-white"></div>
              <i v-else class="fas fa-paper-plane"></i> Envoyer
            </button>
            <button v-if="facture.statut === 'envoyee'" @click="payer" class="btn btn-success btn-sm" :disabled="processing">
              <div v-if="processing" class="spinner spinner-sm border-white/30 border-t-white"></div>
              <i v-else class="fas fa-check-double"></i> Marquer payée
            </button>
            <button @click="downloadPdf" class="btn btn-outline btn-sm" :disabled="downloadLoading">
              <div v-if="downloadLoading" class="spinner spinner-sm border-stone-300 border-t-stone-700"></div>
              <i v-else class="fas fa-file-pdf text-red-500"></i> PDF
            </button>
          </div>
        </div>
      </div>

      <!-- Montant -->
      <div class="card mb-5 text-center">
        <p class="text-xs text-stone-400 font-medium mb-1">Montant</p>
        <p class="text-4xl font-display font-extrabold text-brand">{{ Number(facture.montant).toLocaleString('fr-FR') }}</p>
        <p class="text-sm text-stone-500 font-semibold">FCFA</p>
      </div>

      <!-- Details -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
        <div class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Destinataire</p>
          <p class="text-sm font-bold text-stone-900">{{ facture.destinataire_label || facture.destinataire_type || '—' }}</p>
        </div>
        <div class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Transaction</p>
          <p class="text-sm font-bold text-stone-900">{{ facture.transaction?.titre || '#' + facture.transaction_id }}</p>
        </div>
      </div>

      <!-- Description -->
      <div v-if="facture.description" class="card">
        <h3 class="font-display font-bold text-stone-900 mb-3">Description</h3>
        <div class="px-4 py-4 bg-stone-50 rounded-xl">
          <p class="text-sm text-stone-700 leading-relaxed whitespace-pre-wrap">{{ facture.description }}</p>
        </div>
      </div>
    </template>
  </div>
</template>
