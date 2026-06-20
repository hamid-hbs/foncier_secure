<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import demandeAchatApi from '@/api/demandeAchat'
import professionnelApi from '@/api/professionnel'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const demande = ref(null)
const loading = ref(true)
const actionLoading = ref(false)
const notaires = ref([])
const selectedNotaire = ref('')
const messages = ref([])
const messageText = ref('')
const sending = ref(false)

const isVendeur = computed(() => auth.user?.id === demande.value?.parcelle?.proprietaire_id)
const isNotaire = computed(() => auth.user?.role === 'notaire')

const statutLabel = { soumise: 'Soumise', acceptee: 'Acceptée', refusee: 'Refusée', annulee: 'Annulée' }
const badgeClass = { soumise: 'badge-warning', acceptee: 'badge-success', refusee: 'badge-danger', annulee: 'badge-secondary' }

onMounted(async () => {
  try {
    const r = await demandeAchatApi.show(route.params.id)
    demande.value = r.data?.data ?? r.data
    const nr = await professionnelApi.list({ type: 'notaire' })
    notaires.value = nr.data?.data ?? []
    const mr = await demandeAchatApi.messages(route.params.id)
    messages.value = mr.data?.data ?? mr.data ?? []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

async function envoyerMessage() {
  if (!messageText.value.trim()) return
  sending.value = true
  try {
    await demandeAchatApi.envoyerMessage(route.params.id, { contenu: messageText.value })
    messageText.value = ''
    const mr = await demandeAchatApi.messages(route.params.id)
    messages.value = mr.data?.data ?? mr.data ?? []
  } catch (e) {
    alert(e.response?.data?.message || "Erreur")
  }
  sending.value = false
}

async function notaireRepondre(action) {
  if (!confirm(`Confirmer ${action === 'accepter' ? "l'acceptation" : 'le refus'} de cette demande ?`)) return
  actionLoading.value = true
  try {
    if (action === 'accepter') {
      await demandeAchatApi.accepterDemande(route.params.id)
    } else {
      await demandeAchatApi.refuserDemande(route.params.id)
    }
    const r = await demandeAchatApi.show(route.params.id)
    demande.value = r.data?.data ?? r.data
  } catch (e) {
    alert(e.response?.data?.message || "Erreur")
  }
  actionLoading.value = false
}

async function repondre(statut) {
  if (!confirm(`Confirmer la réponse "${statutLabel[statut]}" ?`)) return
  if (statut === 'acceptee' && !selectedNotaire.value) {
    alert('Veuillez sélectionner un notaire.')
    return
  }
  actionLoading.value = true
  try {
    await demandeAchatApi.repondre(route.params.id, { statut, notaire_id: selectedNotaire.value || undefined })
    router.push('/citoyen/demandes-achat')
  } catch (e) {
    alert(e.response?.data?.message || "Erreur")
  } finally {
    actionLoading.value = false
  }
}
</script>
<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div v-if="loading" class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl" style="color: var(--text-secondary);"></i></div>
    <div v-else-if="!demande" class="card p-8 text-center" style="color: var(--text-secondary);"><p>Demande introuvable.</p></div>
    <div v-else>
      <div class="card p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h1 class="section-title mb-0">Demande d'achat</h1>
          <span :class="['badge', badgeClass[demande.statut]]">{{ statutLabel[demande.statut] || demande.statut }}</span>
        </div>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><span class="font-medium">Parcelle :</span> {{ demande.parcelle?.code || 'N/A' }}</div>
          <div><span class="font-medium">Acheteur :</span> {{ demande.acheteur?.nom || demande.acheteur?.prenom || 'N/A' }}</div>
          <div><span class="font-medium">Date :</span> {{ new Date(demande.created_at).toLocaleDateString('fr-FR') }}</div>
          <div v-if="demande.notaire"><span class="font-medium">Notaire :</span> {{ demande.notaire.nom }}</div>
        </div>
        <div v-if="demande.message" class="mt-4 p-3 rounded-lg text-sm" style="background: var(--bg-page);">
          <p class="font-medium mb-1">Message :</p>
          <p>{{ demande.message }}</p>
        </div>
      </div>
      <div v-if="isVendeur && demande.statut === 'soumise'" class="card p-6">
        <h3 class="font-medium mb-4">Répondre à la demande</h3>
        <div class="mb-4">
          <label class="form-label">Notaire (obligatoire si acceptation)</label>
          <select v-model="selectedNotaire" class="form-input w-full">
            <option value="">Sélectionner un notaire</option>
            <option v-for="n in notaires" :key="n.id" :value="n.user_id || n.id">{{ n.user?.nom || 'Notaire' }}</option>
          </select>
        </div>
        <div class="flex gap-3">
          <button @click="repondre('acceptee')" :disabled="actionLoading" class="btn-green"><i class="fas fa-check mr-1"></i> Accepter</button>
          <button @click="repondre('refusee')" :disabled="actionLoading" class="btn-outline" style="color: var(--danger); border-color: var(--danger);"><i class="fas fa-times mr-1"></i> Refuser</button>
        </div>
      </div>
      <div v-if="isNotaire && demande.statut === 'acceptee' && !demande.notaire_id" class="card p-6 mt-4">
        <h3 class="font-medium mb-4">Réponse du notaire</h3>
        <p class="text-sm mb-4" style="color: var(--text-secondary);">Acceptez-vous de prendre en charge cette demande ?</p>
        <div class="flex gap-3">
          <button @click="notaireRepondre('accepter')" :disabled="actionLoading" class="btn-green"><i class="fas fa-check mr-1"></i> Accepter</button>
          <button @click="notaireRepondre('refuser')" :disabled="actionLoading" class="btn-outline" style="color: var(--danger); border-color: var(--danger);"><i class="fas fa-times mr-1"></i> Refuser</button>
        </div>
      </div>
      <div v-if="isNotaire && demande.statut === 'acceptee' && demande.notaire_id" class="card p-6 mt-4">
        <h3 class="font-medium mb-4">Créer le dossier de transaction</h3>
        <p class="text-sm mb-4" style="color: var(--text-secondary);">Vous avez accepté cette demande. Créez le dossier pour démarrer la transaction.</p>
        <button @click="router.push(`/notaire/transactions/creer?demande=${demande.id}&parcelle=${demande.parcelle_id}&vendeur=${demande.parcelle?.proprietaire_id}&acheteur=${demande.acheteur_id}`)" class="btn-green"><i class="fas fa-folder-plus mr-1"></i> Créer le dossier de transaction</button>
      </div>

      <!-- Messages -->
      <div class="card p-6 mt-4">
        <h3 class="font-medium mb-4 flex items-center gap-2"><i class="fas fa-comments" style="color: var(--green-tree);"></i> Messages</h3>
        <div v-if="messages.length" class="space-y-3 mb-4 max-h-60 overflow-y-auto">
          <div v-for="m in messages" :key="m.id" class="p-3 rounded-lg" style="background: var(--bg-page);">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-xs font-medium" style="color: var(--text-primary);">{{ m.expediteur?.nom || m.expediteur?.prenom || 'Inconnu' }}</span>
              <span class="text-xs" style="color: var(--text-secondary);">{{ m.created_at ? new Date(m.created_at).toLocaleString('fr-FR') : '' }}</span>
            </div>
            <p class="text-sm" style="color: var(--text-primary);">{{ m.contenu || m.message }}</p>
          </div>
        </div>
        <div v-else class="text-sm mb-4" style="color: var(--text-secondary);">Aucun message.</div>
        <form @submit.prevent="envoyerMessage" class="flex gap-3">
          <input v-model="messageText" class="form-input flex-1" placeholder="Votre message..." />
          <button type="submit" :disabled="sending || !messageText.trim()" class="btn-green btn-sm flex items-center gap-1">
            <i class="fas fa-paper-plane"></i> {{ sending ? '...' : 'Envoyer' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
