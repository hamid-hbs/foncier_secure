<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import supportTicketApi from '@/api/supportTicket'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const ticket = ref(null)
const loading = ref(true)
const replying = ref(false)
const replyText = ref('')
const newStatut = ref('')

const statutLabel = { ouvert: 'Ouvert', en_cours: 'En cours', resolu: 'Résolu', ferme: 'Fermé' }
const badgeClass = { ouvert: 'badge-warning', en_cours: 'badge-info', resolu: 'badge-success', ferme: 'badge-secondary' }

onMounted(async () => {
  try {
    const r = await supportTicketApi.show(route.params.id)
    ticket.value = r.data?.data ?? r.data
    newStatut.value = ticket.value?.statut || 'en_cours'
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})

async function repondre() {
  if (!replyText.value.trim()) return
  replying.value = true
  try {
    await supportTicketApi.repondre(route.params.id, { reponse: replyText.value, statut: newStatut.value })
    ticket.value.reponse = replyText.value
    ticket.value.statut = newStatut.value
    replyText.value = ''
  } catch (e) { alert(e.response?.data?.message || "Erreur") }
  finally { replying.value = false }
}
</script>
<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div v-if="loading" class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl" style="color: var(--text-secondary);"></i></div>
    <div v-else-if="!ticket" class="card p-8 text-center" style="color: var(--text-secondary);"><p>Ticket introuvable.</p></div>
    <div v-else>
      <div class="card p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h1 class="section-title mb-1">{{ ticket.sujet }}</h1>
            <p class="text-xs" style="color: var(--text-secondary);">Par {{ ticket.user?.nom || 'Utilisateur' }} — {{ new Date(ticket.created_at).toLocaleString('fr-FR') }}</p>
          </div>
          <span :class="['badge', badgeClass[ticket.statut]]">{{ statutLabel[ticket.statut] || ticket.statut }}</span>
        </div>
        <div class="p-4 rounded-lg text-sm" style="background: var(--bg-page);">
          <p style="color: var(--text-secondary); white-space: pre-wrap;">{{ ticket.message }}</p>
        </div>
        <div v-if="ticket.reponse" class="p-4 rounded-lg text-sm mt-4" style="background: #D1FAE5; border: 1px solid var(--green-tree);">
          <p class="font-medium mb-1" style="color: var(--green-tree);">Réponse envoyée :</p>
          <p style="color: var(--text-primary); white-space: pre-wrap;">{{ ticket.reponse }}</p>
        </div>
      </div>
      <div class="card p-6">
        <h3 class="font-medium mb-4">Répondre</h3>
        <div class="mb-3">
          <label class="form-label">Nouveau statut</label>
          <select v-model="newStatut" class="form-input w-full">
            <option value="en_cours">En cours</option>
            <option value="resolu">Résolu</option>
            <option value="ferme">Fermé</option>
          </select>
        </div>
        <textarea v-model="replyText" class="form-input w-full mb-3" rows="4" placeholder="Votre réponse..."></textarea>
        <button @click="repondre" :disabled="replying || !replyText.trim()" class="btn-green"><i class="fas fa-reply mr-1"></i> {{ replying ? 'Envoi...' : 'Répondre' }}</button>
      </div>
    </div>
  </div>
</template>
