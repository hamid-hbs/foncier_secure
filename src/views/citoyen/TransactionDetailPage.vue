<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { goBack } from '@/utils/navigation'
import transactionApi from '@/api/transaction'

const route = useRoute()
const router = useRouter()

const transaction = ref(null)
const loading = ref(true)
const activeTab = ref('info')
const messageText = ref('')
const sending = ref(false)
const inviteEmail = ref('')
const inviteRole = ref('notaire')
const inviting = ref(false)
const uploadFile = ref(null)
const uploading = ref(false)
const error = ref('')
const validating = ref(false)
const showNoterModal = ref(false)
const note = ref(5)
const commentaire = ref('')
const noterLoading = ref(false)

onMounted(async () => {
  try {
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data
  } catch { /* ignore */ }
  loading.value = false
})

async function sendMessage() {
  if (!messageText.value.trim()) return
  sending.value = true
  error.value = ''
  try {
    await transactionApi.sendMessage(route.params.id, { contenu: messageText.value })
    messageText.value = ''
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors de l'envoi"
  }
  sending.value = false
}

async function inviteProfessionnel() {
  if (!inviteEmail.value.trim()) return
  inviting.value = true
  error.value = ''
  try {
    await transactionApi.inviter(route.params.id, { email: inviteEmail.value, role_dossier: inviteRole.value })
    inviteEmail.value = ''
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors de l'invitation"
  }
  inviting.value = false
}

async function uploadDocument() {
  if (!uploadFile.value) return
  uploading.value = true
  error.value = ''
  try {
    const fd = new FormData()
    fd.append('fichier', uploadFile.value)
    await transactionApi.addDocument(route.params.id, fd)
    uploadFile.value = null
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors de l'upload"
  }
  uploading.value = false
}

async function validerPartie() {
  if (!confirm('Confirmez-vous la validation de votre participation ?')) return
  validating.value = true
  error.value = ''
  try {
    await transactionApi.validerPartie(route.params.id)
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors de la validation"
  }
  validating.value = false
}

async function submitNote() {
  noterLoading.value = true
  error.value = ''
  try {
    await transactionApi.noterProfessionnel(route.params.id, { note: note.value, commentaire: commentaire.value })
    showNoterModal.value = false
    alert('Merci pour votre évaluation !')
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors de l'envoi"
  }
  noterLoading.value = false
}

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
  <div class="page-container max-w-4xl">
    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--border); border-top-color: var(--green-tree);"></div>
    </div>

    <div v-else-if="!transaction" class="card text-center py-12">
      <p style="color: var(--text-secondary);">Transaction introuvable.</p>
    </div>

    <template v-else>
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
        <i class="fas fa-arrow-left"></i> Retour
      </button>

      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #DBEAFE;">
          <i class="fas fa-arrows-left-right" style="color: #2563EB;"></i>
        </div>
        <div class="flex-1">
          <h1 class="section-title">{{ transaction.titre || 'Transaction #' + transaction.id }}</h1>
          <span class="text-xs px-2 py-1 rounded-full badge mt-1" :class="statutClass(transaction.statut)">{{ transaction.statut }}</span>
        </div>
      </div>

      <div v-if="error" class="p-3.5 rounded-lg text-sm mb-4" style="background: #FEE2E2; color: var(--danger); border: 1px solid #FECACA;">
        {{ error }}
      </div>

      <div class="flex gap-3 mb-6 flex-wrap">
        <button v-if="transaction.statut !== 'cloture'" @click="validerPartie" :disabled="validating" class="btn-green btn-sm flex items-center gap-1">
          <i class="fas fa-check-circle"></i> {{ validating ? '...' : 'Valider ma participation' }}
        </button>
        <button v-if="transaction.statut === 'cloture'" @click="showNoterModal = true" class="btn-gold btn-sm flex items-center gap-1">
          <i class="fas fa-star"></i> Noter le professionnel
        </button>
      </div>

      <div class="flex gap-1 mb-6 p-1 rounded-lg w-fit" style="background: var(--bg-page);">
        <button @click="activeTab = 'info'" :class="['px-4 py-2 text-sm font-medium rounded-lg transition-all', activeTab === 'info' ? 'card shadow-sm' : '']" :style="activeTab === 'info' ? { color: 'var(--text-primary)' } : { color: 'var(--text-secondary)' }">Infos</button>
        <button @click="activeTab = 'activites'" :class="['px-4 py-2 text-sm font-medium rounded-lg transition-all', activeTab === 'activites' ? 'card shadow-sm' : '']" :style="activeTab === 'activites' ? { color: 'var(--text-primary)' } : { color: 'var(--text-secondary)' }">Activités</button>
        <button @click="activeTab = 'messages'" :class="['px-4 py-2 text-sm font-medium rounded-lg transition-all', activeTab === 'messages' ? 'card shadow-sm' : '']" :style="activeTab === 'messages' ? { color: 'var(--text-primary)' } : { color: 'var(--text-secondary)' }">Messages</button>
        <button @click="activeTab = 'documents'" :class="['px-4 py-2 text-sm font-medium rounded-lg transition-all', activeTab === 'documents' ? 'card shadow-sm' : '']" :style="activeTab === 'documents' ? { color: 'var(--text-primary)' } : { color: 'var(--text-secondary)' }">Documents</button>
        <button @click="activeTab = 'intervenants'" :class="['px-4 py-2 text-sm font-medium rounded-lg transition-all', activeTab === 'intervenants' ? 'card shadow-sm' : '']" :style="activeTab === 'intervenants' ? { color: 'var(--text-primary)' } : { color: 'var(--text-secondary)' }">Intervenants</button>
      </div>

      <div v-if="activeTab === 'info'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
          <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Participants</h3>
          <dl class="divide-y text-sm" style="border-color: var(--border);">
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Vendeur</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ transaction.vendeur?.nom || transaction.vendeur?.prenom || 'Vous' }}</dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Acheteur</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ transaction.acheteur?.nom || transaction.acheteur?.prenom || transaction.acheteur_email || 'En attente' }}</dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Parcelle</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ transaction.parcelle?.titre || transaction.parcelle?.code || '—' }}</dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Statut</dt>
              <dd><span class="text-xs px-2 py-1 rounded-full badge" :class="statutClass(transaction.statut)">{{ transaction.statut }}</span></dd>
            </div>
          </dl>
        </div>
        <div class="card">
          <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Intervenants</h3>
          <div v-if="transaction.intervenants?.length">
            <div v-for="inv in transaction.intervenants" :key="inv.id" class="flex items-center justify-between py-2 text-sm border-b last:border-0" style="border-color: var(--border);">
              <span style="color: var(--text-primary);">{{ inv.user?.nom || inv.user?.prenom || inv.email || '—' }}</span>
              <span class="text-xs px-2 py-0.5 rounded-full" style="background: var(--bg-page); color: var(--text-secondary);">{{ inv.role_dossier || inv.role }}</span>
            </div>
          </div>
          <p v-else class="text-sm" style="color: var(--text-secondary);">Aucun intervenant pour le moment.</p>
        </div>
      </div>

      <div v-if="activeTab === 'activites'" class="card">
        <h3 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-clock" style="color: var(--green-tree);"></i> Fil d'activité
        </h3>
        <div v-if="transaction.activites?.length" class="space-y-3">
          <div v-for="act in transaction.activites" :key="act.id" class="flex gap-3 p-3 rounded-lg" style="background: var(--bg-page);">
            <div class="w-2 h-2 rounded-full mt-1.5 shrink-0" style="background: var(--green-tree);"></div>
            <div>
              <p class="text-sm font-medium" style="color: var(--text-primary);">{{ act.action || act.description || 'Mise à jour' }}</p>
              <p class="text-xs mt-1" style="color: var(--text-secondary);">{{ act.created_at ? new Date(act.created_at).toLocaleString('fr-FR') : '' }}</p>
            </div>
          </div>
        </div>
        <p v-else class="text-sm" style="color: var(--text-secondary);">Aucune activité pour le moment.</p>
      </div>

      <div v-if="activeTab === 'messages'" class="card">
        <h3 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-comments" style="color: var(--green-tree);"></i> Messages
        </h3>
        <div v-if="transaction.messages?.length" class="space-y-3 mb-6 max-h-80 overflow-y-auto">
          <div v-for="m in transaction.messages" :key="m.id" class="p-3 rounded-lg" style="background: var(--bg-page);">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-xs font-medium" style="color: var(--text-primary);">{{ m.expediteur?.nom || m.expediteur?.prenom || 'Inconnu' }}</span>
              <span class="text-xs" style="color: var(--text-secondary);">{{ m.created_at ? new Date(m.created_at).toLocaleString('fr-FR') : '' }}</span>
            </div>
            <p class="text-sm" style="color: var(--text-primary);">{{ m.contenu || m.message }}</p>
          </div>
        </div>
        <div v-else class="text-sm mb-6" style="color: var(--text-secondary);">Aucun message.</div>
        <form @submit.prevent="sendMessage" class="flex gap-3">
          <input v-model="messageText" class="form-input flex-1" placeholder="Votre message..." />
          <button type="submit" :disabled="sending || !messageText.trim()" class="btn-green btn-sm flex items-center gap-1">
            <i class="fas fa-paper-plane"></i> {{ sending ? '...' : 'Envoyer' }}
          </button>
        </form>
      </div>

      <div v-if="activeTab === 'documents'" class="card">
        <h3 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-file-lines" style="color: var(--green-tree);"></i> Documents
        </h3>
        <div v-if="transaction.documents?.length" class="space-y-2 mb-6">
          <div v-for="doc in transaction.documents" :key="doc.id" class="flex items-center justify-between p-3 rounded-lg" style="background: var(--bg-page);">
            <span class="text-sm font-medium" style="color: var(--text-primary);">{{ doc.nom_fichier || doc.nom || 'Document' }}</span>
            <a :href="doc.url || `/storage/${doc.fichier}`" target="_blank" class="btn-outline btn-sm flex items-center gap-1">
              <i class="fas fa-download"></i>
            </a>
          </div>
        </div>
        <div v-else class="text-sm mb-6" style="color: var(--text-secondary);">Aucun document.</div>
        <form @submit.prevent="uploadDocument" class="flex gap-3 items-center">
          <input type="file" @change="uploadFile = $event.target.files[0] || null" class="form-input flex-1" />
          <button type="submit" v-if="uploadFile" :disabled="uploading" class="btn-green btn-sm">
            {{ uploading ? 'Envoi...' : 'Ajouter' }}
          </button>
        </form>
      </div>

      <div v-if="activeTab === 'intervenants'" class="card">
        <h3 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-user-plus" style="color: var(--green-tree);"></i> Inviter un professionnel
        </h3>
        <p class="text-sm mb-5" style="color: var(--text-secondary);">Invitez un notaire ou géomètre.</p>
        <form @submit.prevent="inviteProfessionnel" class="flex gap-3 items-end">
          <div class="flex-1">
            <label class="form-label">Email</label>
            <input v-model="inviteEmail" type="email" class="form-input" placeholder="email@exemple.com" />
          </div>
          <div>
            <label class="form-label">Rôle</label>
            <select v-model="inviteRole" class="form-select">
              <option value="notaire">Notaire</option>
              <option value="geometre">Géomètre</option>

            </select>
          </div>
          <button type="submit" :disabled="inviting || !inviteEmail.trim()" class="btn-green btn-sm flex items-center gap-1">
            <i class="fas fa-paper-plane"></i> {{ inviting ? '...' : 'Inviter' }}
          </button>
        </form>
      </div>
      <!-- Modal Noter -->
      <Transition name="fade">
        <div v-if="showNoterModal" @click="showNoterModal = false" class="fixed inset-0 z-50 bg-black/30 backdrop-blur-sm" />
      </Transition>
      <Transition name="slide">
        <div v-if="showNoterModal" class="fixed inset-0 z-50 flex items-center justify-center">
          <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl" @click.stop>
            <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Noter le professionnel</h3>
            <p class="text-sm mb-4" style="color: var(--text-secondary);">Évaluez le service rendu par le notaire ou géomètre.</p>
            <div class="space-y-4">
              <div>
                <label class="form-label">Note (1-5)</label>
                <div class="flex gap-2">
                  <button v-for="i in 5" :key="i" @click="note = i" class="w-10 h-10 rounded-lg flex items-center justify-center text-lg transition-all" :style="i <= note ? { background: 'var(--gold-light)', color: 'var(--gold)' } : { background: 'var(--bg-page)', color: 'var(--border)' }">
                    <i class="fas fa-star"></i>
                  </button>
                </div>
              </div>
              <div>
                <label class="form-label">Commentaire (optionnel)</label>
                <textarea v-model="commentaire" class="form-input w-full" rows="3" placeholder="Votre avis..."></textarea>
              </div>
              <div class="flex gap-3 pt-2">
                <button @click="submitNote" :disabled="noterLoading" class="btn-green">
                  {{ noterLoading ? 'Envoi...' : 'Envoyer' }}
                </button>
                <button @click="showNoterModal = false" class="btn-outline">Annuler</button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </template>
  </div>
</template>
