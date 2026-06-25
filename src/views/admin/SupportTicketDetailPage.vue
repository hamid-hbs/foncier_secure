<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import supportTicketApi from '@/api/supportTicket'
import { goBack } from '@/utils/navigation'

function getRoleCode(role) {
  return typeof role === 'object' ? role?.code : role
}

const route = useRoute()
const router = useRouter()
const ticket = ref(null)
const loading = ref(true)
const replyMessage = ref('')
const replying = ref(false)
const updatingStatut = ref(false)

onMounted(async () => {
  try {
    const res = await supportTicketApi.show(route.params.id)
    ticket.value = res.data || null
  } catch (e) { console.error('Erreur chargement ticket:', e) }
  loading.value = false
})

function statutBadge(s) {
  const map = { ouvert: 'badge-success', en_cours: 'badge-info', ferme: 'badge-neutral' }
  return map[s] || 'badge-neutral'
}
function prioriteBadge(p) {
  const map = { haute: 'badge-danger', moyenne: 'badge-warning', basse: 'badge-neutral' }
  return map[p] || 'badge-neutral'
}

const reponses = computed(() => {
  if (ticket.value?.reponses?.length) return ticket.value.reponses
  if (ticket.value?.reponse) return [{ id: 1, user: ticket.value.assigne || ticket.value.user, message: ticket.value.reponse, created_at: ticket.value.updated_at }]
  return []
})

async function updateStatut(statut) {
  updatingStatut.value = true
  try {
    await supportTicketApi.updateStatut(route.params.id, { statut })
    ticket.value.statut = statut
  } catch (e) { console.error('Erreur mise à jour statut:', e) }
  updatingStatut.value = false
}

async function sendReply() {
  if (!replyMessage.value.trim()) return
  replying.value = true
  try {
    const payload = { reponse: replyMessage.value }
    await supportTicketApi.repondre(route.params.id, payload)
    const res = await supportTicketApi.show(route.params.id)
    ticket.value = res.data
    replyMessage.value = ''
  } catch (e) { console.error('Erreur envoi réponse:', e) }
  replying.value = false
}
</script>

<template>
  <div class="page-wrap max-w-2xl mx-auto">
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-32 rounded-2xl"></div>
      <div class="skeleton h-48 rounded-2xl"></div>
    </div>

    <div v-else-if="!ticket" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-headset"></i></div>
        <p class="empty-title">Ticket introuvable</p>
        <button @click="goBack(router)" class="btn btn-primary mt-4">Retour</button>
      </div>
    </div>

    <template v-else>
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
        <i class="fas fa-arrow-left text-xs"></i> Tous les tickets
      </button>

      <!-- Header -->
      <div class="card mb-5">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
          <div class="flex-1">
            <h1 class="font-display font-bold text-xl text-stone-900 mb-2">{{ ticket.sujet }}</h1>
            <div class="flex flex-wrap gap-2 mb-3">
              <span class="badge" :class="statutBadge(ticket.statut)">{{ ticket.statut }}</span>
              <span v-if="ticket.priorite" class="badge" :class="prioriteBadge(ticket.priorite)">{{ ticket.priorite }}</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="avatar avatar-sm bg-brand shrink-0">{{ (ticket.user?.prenom || 'U')[0] }}</div>
              <div>
                <p class="text-sm font-bold text-stone-900">{{ ticket.user?.prenom }} {{ ticket.user?.nom }}</p>
                <p class="text-xs text-stone-400">{{ ticket.user?.email }}</p>
              </div>
            </div>
          </div>
          <!-- Actions statut -->
          <div class="flex flex-col gap-2 shrink-0">
            <button v-if="ticket.statut === 'ouvert'" @click="updateStatut('en_cours')" class="btn btn-primary btn-sm" :disabled="updatingStatut">
              <i class="fas fa-headset"></i> Prendre en charge
            </button>
            <button v-if="ticket.statut !== 'ferme'" @click="updateStatut('ferme')" class="btn btn-ghost btn-sm text-stone-500" :disabled="updatingStatut">
              <i class="fas fa-check"></i> Fermer le ticket
            </button>
            <button v-else @click="updateStatut('ouvert')" class="btn btn-ghost btn-sm text-brand" :disabled="updatingStatut">
              <i class="fas fa-rotate-left"></i> Rouvrir
            </button>
          </div>
        </div>
        <div class="px-4 py-4 bg-stone-50 rounded-xl border border-stone-100">
          <p class="text-sm text-stone-700 leading-relaxed whitespace-pre-wrap">{{ ticket.message }}</p>
        </div>
        <p class="text-xs text-stone-400 mt-2">Créé le {{ ticket.created_at ? new Date(ticket.created_at).toLocaleString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '' }}</p>
      </div>

      <!-- Fil de discussion -->
      <div v-if="reponses.length" class="card mb-5">
        <h3 class="font-display font-bold text-stone-900 mb-4">Discussion ({{ reponses.length }})</h3>
        <div class="space-y-4">
          <div v-for="r in reponses" :key="r.id" class="flex gap-3">
            <div class="avatar avatar-sm shrink-0" :class="getRoleCode(r.user?.role) === 'admin' ? 'bg-brand' : 'bg-stone-200 text-stone-600'">
              {{ (r.user?.prenom || 'U')[0] }}
            </div>
            <div class="flex-1">
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="text-sm font-bold text-stone-900">{{ r.user?.prenom }} {{ r.user?.nom }}</span>
                <span v-if="getRoleCode(r.user?.role) === 'admin'" class="badge badge-info" style="font-size:0.65rem;">Support</span>
                <span class="text-xs text-stone-400 ml-auto">{{ r.created_at ? new Date(r.created_at).toLocaleString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '' }}</span>
              </div>
              <div class="px-4 py-3 rounded-xl bg-stone-50 border border-stone-100">
                <p class="text-sm text-stone-700 leading-relaxed whitespace-pre-wrap">{{ r.message }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reply -->
      <div class="card">
        <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
          <i class="fas fa-reply text-brand text-sm"></i> Répondre au client
        </h3>
        <div class="space-y-3">
          <textarea v-model="replyMessage" rows="4" class="form-input resize-none" placeholder="Votre réponse au client…"></textarea>
          <div class="flex gap-2">
            <button @click="sendReply" class="btn btn-primary" :disabled="replying || !replyMessage.trim()">
              <div v-if="replying" class="spinner spinner-sm border-white/30 border-t-white"></div>
              <i v-else class="fas fa-paper-plane"></i>
              {{ replying ? 'Envoi…' : 'Envoyer la réponse' }}
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
