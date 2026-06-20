<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import transactionApi from '@/api/transaction'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const transaction = ref(null)
const loading = ref(true)
const advancing = ref(false)
const uploading = ref(false)
const sendingMessage = ref(false)
const messageText = ref('')
const newDocument = ref(null)
const showConfirmAdvance = ref(false)

const steps = ['cree', 'en_verification', 'geometre_assigne', 'rendezvous_planifie', 'valide', 'acte_signe', 'mutation_en_cours', 'cloture']

const etapeLabels = {
  cree: 'Création',
  en_verification: 'En vérification',
  geometre_assigne: 'Géomètre assigné',
  rendezvous_planifie: 'Rendez-vous planifié',
  valide: 'Validé',
  acte_signe: 'Acte signé',
  mutation_en_cours: 'Mutation en cours',
  cloture: 'Clôturé'
}

const currentStepIndex = computed(() => {
  if (!transaction.value?.statut) return 0
  const idx = steps.indexOf(transaction.value.statut)
  return idx >= 0 ? idx : 0
})

const canAdvance = computed(() => {
  return transaction.value && currentStepIndex.value < steps.length - 1
})

const canVerifierIdentite = computed(() => transaction.value?.statut === 'cree' || transaction.value?.statut === 'en_verification')
const canAssignerGeometre = computed(() => transaction.value?.statut === 'cree' || transaction.value?.statut === 'en_verification')
const canRendezVous = computed(() => transaction.value?.statut === 'geometre_assigne')
const canValider = computed(() => transaction.value?.statut === 'rendezvous_planifie')
const canGenererActe = computed(() => transaction.value?.statut === 'valide')
const canAvancerMutation = computed(() => transaction.value?.statut === 'acte_signe')
const canAvancerCloture = computed(() => transaction.value?.statut === 'mutation_en_cours')

onMounted(async () => {
  try {
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data?.data || res.data
  } catch { }
  loading.value = false
})

async function avancerEtape() {
  advancing.value = true
  try {
    await transactionApi.avancerEtape(route.params.id)
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data?.data || res.data
    showConfirmAdvance.value = false
  } catch { alert("Erreur lors de l'avancement") }
  advancing.value = false
}

async function validerDossier() {
  advancing.value = true
  try {
    await transactionApi.validerDossier(route.params.id)
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data?.data || res.data
  } catch { alert("Erreur lors de la validation") }
  advancing.value = false
}

async function genererActe() {
  advancing.value = true
  try {
    await transactionApi.genererActeVente(route.params.id)
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data?.data || res.data
  } catch { alert("Erreur lors de la génération de l'acte") }
  advancing.value = false
}

function onFileChange(e) {
  newDocument.value = e.target.files[0] || null
}

async function uploadDocument() {
  if (!newDocument.value) return
  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('fichier', newDocument.value)
    await transactionApi.addDocument(route.params.id, formData)
    newDocument.value = null
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data?.data || res.data
  } catch { alert("Erreur lors du téléversement") }
  uploading.value = false
}

async function sendMessage() {
  if (!messageText.value.trim()) return
  sendingMessage.value = true
  try {
    await transactionApi.sendMessage(route.params.id, { contenu: messageText.value })
    messageText.value = ''
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data?.data || res.data
  } catch { alert("Erreur lors de l'envoi") }
  sendingMessage.value = false
}

async function exportPdf() {
  try {
    const res = await transactionApi.exportPdf(route.params.id)
    const url = URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }))
    const a = document.createElement('a')
    a.href = url
    a.download = `dossier-${route.params.id}.pdf`
    a.click()
    URL.revokeObjectURL(url)
  } catch { alert("Erreur lors de l'export") }
}
</script>
<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div v-if="loading" class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl" style="color: var(--text-secondary);"></i></div>
    <div v-else-if="!transaction" class="card p-8 text-center" style="color: var(--text-secondary);"><p>Dossier introuvable.</p></div>
    <div v-else>
      <div class="card p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h1 class="section-title mb-1">{{ transaction.titre || 'Dossier #' + transaction.id }}</h1>
            <p class="text-xs" style="color: var(--text-secondary);">Parcelle : {{ transaction.parcelle?.code || 'N/A' }}</p>
          </div>
          <div class="flex gap-2">
            <button @click="exportPdf" class="btn-outline btn-sm"><i class="fas fa-file-pdf mr-1"></i> Export PDF</button>
          </div>
        </div>
        <div class="flex flex-wrap gap-2 mb-4">
          <span v-for="(s, i) in steps" :key="s" class="text-xs px-3 py-1.5 rounded-full font-medium"
            :style="{
              background: i <= currentStepIndex ? 'var(--green-tree)' : 'var(--bg-page)',
              color: i <= currentStepIndex ? '#fff' : 'var(--text-secondary)'
            }">
            {{ etapeLabels[s] }}
          </span>
        </div>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><span class="font-medium">Vendeur :</span> {{ transaction.vendeur?.nom || 'N/A' }}</div>
          <div><span class="font-medium">Acheteur :</span> {{ transaction.acheteur?.nom || 'N/A' }}</div>
          <div><span class="font-medium">Statut :</span> {{ etapeLabels[transaction.statut] || transaction.statut }}</div>
          <div><span class="font-medium">Créé le :</span> {{ new Date(transaction.created_at).toLocaleDateString('fr-FR') }}</div>
        </div>
      </div>
      <div class="flex flex-wrap gap-3 mb-6">
        <button v-if="canVerifierIdentite" @click="router.push(`/notaire/transactions/${route.params.id}/verifier-identite`)" class="btn-outline btn-sm"><i class="fas fa-id-card mr-1"></i> Vérifier identité</button>
        <button v-if="canAssignerGeometre" @click="router.push(`/notaire/transactions/${route.params.id}/intervenants`)" class="btn-outline btn-sm"><i class="fas fa-user-plus mr-1"></i> Inviter participants</button>
        <button v-if="canRendezVous" @click="router.push(`/notaire/transactions/${route.params.id}/planifier-rendezvous`)" class="btn-outline btn-sm"><i class="fas fa-calendar-plus mr-1"></i> Planifier rendez-vous</button>
        <button v-if="canValider" @click="validerDossier" :disabled="advancing" class="btn-green btn-sm"><i class="fas fa-check-circle mr-1"></i> Valider le dossier</button>
        <button v-if="canGenererActe" @click="genererActe" :disabled="advancing" class="btn-gold btn-sm"><i class="fas fa-file-signature mr-1"></i> Générer l'acte de vente</button>
        <button v-if="canAvancerMutation" @click="showConfirmAdvance = true" class="btn-primary btn-sm"><i class="fas fa-forward mr-1"></i> Initier la mutation</button>
        <button v-if="canAvancerCloture" @click="showConfirmAdvance = true" class="btn-green btn-sm"><i class="fas fa-check-double mr-1"></i> Clôturer le dossier</button>
        <button v-if="canAdvance" @click="showConfirmAdvance = true" class="btn-outline btn-sm"><i class="fas fa-forward mr-1"></i> Avancer</button>
      </div>
      <div v-if="showConfirmAdvance" class="card p-4 mb-6" style="border: 2px solid var(--gold);">
        <p class="text-sm mb-3">Faire avancer le dossier à l'étape suivante ?</p>
        <div class="flex gap-2">
          <button @click="avancerEtape" :disabled="advancing" class="btn-green btn-sm"><i class="fas fa-check mr-1"></i> Confirmer</button>
          <button @click="showConfirmAdvance = false" class="btn-outline btn-sm">Annuler</button>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card p-4">
          <h3 class="font-medium mb-3"><i class="fas fa-users mr-2" style="color: var(--green-tree);"></i> Participants</h3>
          <div v-if="transaction.intervenants?.length" class="space-y-2">
            <div v-for="inv in transaction.intervenants" :key="inv.id" class="flex items-center gap-2 text-sm">
              <i class="fas fa-user-circle" style="color: var(--text-secondary);"></i>
              <span>{{ inv.user?.nom || 'Utilisateur' }}</span>
              <span class="text-xs px-1.5 py-0.5 rounded" style="background: var(--bg-page); color: var(--text-secondary);">{{ inv.role_dossier }}</span>
            </div>
          </div>
          <p v-else class="text-sm" style="color: var(--text-secondary);">Aucun participant.</p>
        </div>
        <div class="card p-4">
          <h3 class="font-medium mb-3"><i class="fas fa-file-lines mr-2" style="color: var(--green-tree);"></i> Documents</h3>
          <div v-if="transaction.documents?.length" class="space-y-2">
            <div v-for="doc in transaction.documents" :key="doc.id" class="flex items-center gap-2 text-sm">
              <i class="fas fa-file" style="color: var(--text-secondary);"></i>
              <span class="flex-1 truncate">{{ doc.nom_fichier || 'Document' }}</span>
              <span class="text-xs" style="color: var(--text-secondary);">{{ doc.version ? 'v' + doc.version : '' }}</span>
            </div>
          </div>
          <p v-else class="text-sm" style="color: var(--text-secondary);">Aucun document.</p>
          <div class="mt-3 flex items-center gap-2">
            <input type="file" @change="onFileChange" class="form-input flex-1 text-sm" />
            <button @click="uploadDocument" :disabled="!newDocument || uploading" class="btn-green btn-sm"><i class="fas fa-upload"></i></button>
          </div>
        </div>
      </div>
      <div class="card p-4 mt-6">
        <h3 class="font-medium mb-3"><i class="fas fa-comments mr-2" style="color: var(--green-tree);"></i> Messages</h3>
        <div v-if="transaction.messages?.length" class="space-y-3 mb-4 max-h-60 overflow-y-auto">
          <div v-for="m in transaction.messages" :key="m.id" class="p-3 rounded-lg text-sm" style="background: var(--bg-page);">
            <p class="font-medium mb-1">{{ m.sender?.nom || 'Utilisateur' }}</p>
            <p style="color: var(--text-secondary);">{{ m.contenu }}</p>
            <p class="text-xs mt-1" style="color: var(--text-secondary);">{{ new Date(m.created_at).toLocaleString('fr-FR') }}</p>
          </div>
        </div>
        <div class="flex gap-2">
          <input v-model="messageText" class="form-input flex-1" placeholder="Votre message..." @keyup.enter="sendMessage" />
          <button @click="sendMessage" :disabled="!messageText.trim() || sendingMessage" class="btn-green btn-sm"><i class="fas fa-paper-plane"></i></button>
        </div>
      </div>
    </div>
  </div>
</template>
