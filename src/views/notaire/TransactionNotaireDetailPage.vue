<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import transactionApi from '@/api/transaction'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const transaction = ref(null)
const loading = ref(true)
const uploading = ref(false)
const uploadError = ref('')
const fileInput = ref(null)

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
function getDocUrl(c) {
  if (!c) return '#'
  return c.startsWith('http') ? c : `${BASE_URL}/storage/${c}`
}

async function handleUpload(event) {
  const file = event.target.files?.[0]
  if (!file || !transaction.value) return
  uploading.value = true
  uploadError.value = ''
  try {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('type_document', 'acte')
    await transactionApi.addDocument(transaction.value.id, formData)
    const res = await transactionApi.show(transaction.value.id)
    transaction.value = res.data
  } catch (e) {
    uploadError.value = 'Erreur lors du téléchargement'
    console.error('Erreur upload document:', e)
  } finally {
    uploading.value = false
  }
}

async function updateStatut(newStatut) {
  try {
    if (newStatut === 'cloturee') {
      await transactionApi.cloturer(transaction.value.id)
    } else if (newStatut === 'annulee') {
      await transactionApi.suspendre(transaction.value.id)
    }
    transaction.value.statut = newStatut
    const res = await transactionApi.show(transaction.value.id)
    transaction.value = res.data
  } catch (e) { console.error('Erreur mise à jour statut:', e) }
}
</script>

<template>
  <div class="page-wrap max-w-3xl mx-auto">
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-40 rounded-2xl"></div>
      <div class="skeleton h-60 rounded-2xl"></div>
    </div>

    <div v-else-if="!transaction" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-file-signature"></i></div>
        <p class="empty-title">Dossier introuvable</p>
        <button @click="goBack(router)" class="btn btn-primary mt-4">Retour</button>
      </div>
    </div>

    <template v-else>
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
        <i class="fas fa-arrow-left text-xs"></i> Mes dossiers
      </button>

      <!-- Header -->
      <div class="card mb-5">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
          <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-stone-100 flex items-center justify-center text-stone-500 shrink-0">
              <i class="fas fa-file-contract text-xl"></i>
            </div>
            <div>
              <h1 class="font-display font-extrabold text-xl text-stone-900 mb-1">{{ transaction.titre || 'Dossier #' + transaction.id }}</h1>
              <span class="badge" :class="statutBadgeClass(transaction.statut)">{{ transaction.statut }}</span>
            </div>
          </div>
          <!-- Status actions -->
          <div v-if="transaction.statut === 'cree'" class="flex gap-2">
            <button @click="updateStatut('en_cours')" class="btn btn-primary btn-sm">
              <i class="fas fa-play"></i> Démarrer
            </button>
          </div>
          <div v-else-if="transaction.statut === 'en_cours'" class="flex gap-2">
            <button @click="updateStatut('cloturee')" class="btn btn-success btn-sm">
              <i class="fas fa-check-double"></i> Clôturer
            </button>
            <button @click="updateStatut('annulee')" class="btn btn-ghost btn-sm text-danger">
              <i class="fas fa-times"></i> Annuler
            </button>
          </div>
        </div>
      </div>

      <!-- Parties -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <div class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Vendeur</p>
          <div class="flex items-center gap-2">
            <div class="avatar avatar-sm bg-brand shrink-0">{{ (transaction.vendeur?.prenom || 'V')[0] }}</div>
            <div>
              <p class="text-sm font-bold text-stone-900">{{ transaction.vendeur?.prenom }} {{ transaction.vendeur?.nom }}</p>
              <p class="text-xs text-stone-400">{{ transaction.vendeur?.email }}</p>
            </div>
          </div>
        </div>
        <div class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Acheteur</p>
          <div class="flex items-center gap-2">
            <div class="avatar avatar-sm bg-gold-dark shrink-0">{{ (transaction.acheteur?.prenom || 'A')[0] }}</div>
            <div>
              <p class="text-sm font-bold text-stone-900">{{ transaction.acheteur?.prenom }} {{ transaction.acheteur?.nom }}</p>
              <p class="text-xs text-stone-400">{{ transaction.acheteur?.email }}</p>
            </div>
          </div>
        </div>
        <div class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Parcelle</p>
          <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center text-brand shrink-0 text-xs">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-stone-900">{{ transaction.parcelle?.titre || '#' + transaction.parcelle_id }}</p>
              <p class="text-xs text-stone-400">{{ transaction.parcelle?.commune?.nom }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions rapides -->
      <div class="card mb-5">
        <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
          <i class="fas fa-bolt text-gold"></i> Actions notariales
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <router-link :to="{ name: 'NotaireInviter', params: { id: transaction.id } }" class="flex items-center gap-3 p-3 rounded-xl border border-stone-100 hover:border-brand-100 hover:bg-brand-50/20 transition-all">
            <div class="w-9 h-9 rounded-lg bg-brand-50 flex items-center justify-center text-brand shrink-0 text-sm"><i class="fas fa-user-plus"></i></div>
            <span class="text-sm font-semibold text-stone-700">Inviter un intervenant</span>
          </router-link>
          <router-link :to="{ name: 'NotaireVerifierIdentite', params: { id: transaction.id } }" class="flex items-center gap-3 p-3 rounded-xl border border-stone-100 hover:border-brand-100 hover:bg-brand-50/20 transition-all">
            <div class="w-9 h-9 rounded-lg bg-success/10 flex items-center justify-center text-success shrink-0 text-sm"><i class="fas fa-id-card"></i></div>
            <span class="text-sm font-semibold text-stone-700">Vérifier identité</span>
          </router-link>
          <router-link :to="{ name: 'NotairePlanifierRendezVous', params: { id: transaction.id } }" class="flex items-center gap-3 p-3 rounded-xl border border-stone-100 hover:border-brand-100 hover:bg-brand-50/20 transition-all">
            <div class="w-9 h-9 rounded-lg bg-gold/10 flex items-center justify-center text-gold-dark shrink-0 text-sm"><i class="fas fa-calendar-plus"></i></div>
            <span class="text-sm font-semibold text-stone-700">Planifier RDV</span>
          </router-link>
          <router-link :to="{ name: 'NotaireFacturesTransaction', params: { id: transaction.id } }" class="flex items-center gap-3 p-3 rounded-xl border border-stone-100 hover:border-brand-100 hover:bg-brand-50/20 transition-all">
            <div class="w-9 h-9 rounded-lg bg-success/10 flex items-center justify-center text-success shrink-0 text-sm"><i class="fas fa-file-invoice-dollar"></i></div>
            <span class="text-sm font-semibold text-stone-700">Factures</span>
          </router-link>
        </div>
      </div>

      <!-- Documents -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-display font-bold text-stone-900 flex items-center gap-2">
            <i class="fas fa-folder-open text-brand text-sm"></i> Documents
          </h3>
          <div>
            <input ref="fileInput" type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png" @change="handleUpload" />
            <button @click="fileInput?.click()" class="btn btn-outline btn-sm" :disabled="uploading">
              <div v-if="uploading" class="spinner spinner-sm border-stone-300 border-t-stone-700"></div>
              <i v-else class="fas fa-upload"></i>
              {{ uploading ? 'Envoi…' : 'Ajouter un document' }}
            </button>
          </div>
        </div>
        <div v-if="uploadError" class="alert alert-danger mb-4">
          <i class="fas fa-triangle-exclamation shrink-0"></i>
          <span>{{ uploadError }}</span>
        </div>
        <div v-if="!transaction.documents?.length" class="text-center py-8 text-stone-400">
          <i class="fas fa-file-plus text-2xl mb-2 block opacity-30"></i>
          <p class="text-sm">Aucun document joint</p>
        </div>
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <a v-for="doc in transaction.documents" :key="doc.id" :href="getDocUrl(doc.chemin_fichier || doc.fichier)" target="_blank"
            class="flex items-center gap-3 p-3 rounded-xl border border-stone-100 hover:border-brand-100 hover:bg-brand-50/20 transition-all">
            <div class="w-9 h-9 rounded-lg bg-stone-100 flex items-center justify-center text-stone-500 text-sm shrink-0">
              <i class="fas fa-file-pdf"></i>
            </div>
            <div class="min-w-0">
              <p class="text-xs font-bold text-stone-900 capitalize truncate">{{ doc.type_document || 'Document' }}</p>
              <p class="text-[10px] text-stone-400">{{ formatDate(doc.created_at) }}</p>
            </div>
          </a>
        </div>
      </div>
    </template>
  </div>
</template>
