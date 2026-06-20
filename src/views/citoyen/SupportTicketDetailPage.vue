<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import supportTicketApi from '@/api/supportTicket'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const ticket = ref(null)
const loading = ref(true)

const statutLabel = { ouvert: 'Ouvert', en_cours: 'En cours', resolu: 'Résolu', ferme: 'Fermé' }
const badgeClass = { ouvert: 'badge-warning', en_cours: 'badge-info', resolu: 'badge-success', ferme: 'badge-secondary' }
const prioriteLabel = { basse: 'Basse', normale: 'Normale', haute: 'Haute', urgente: 'Urgente' }

onMounted(async () => {
  try {
    const r = await supportTicketApi.show(route.params.id)
    ticket.value = r.data?.data ?? r.data
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})
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
          <h1 class="section-title mb-0">{{ ticket.sujet }}</h1>
          <div class="flex gap-2">
            <span class="text-xs px-2 py-0.5 rounded" style="background: var(--bg-page); color: var(--text-secondary);">{{ prioriteLabel[ticket.priorite] || ticket.priorite }}</span>
            <span :class="['badge', badgeClass[ticket.statut]]">{{ statutLabel[ticket.statut] || ticket.statut }}</span>
          </div>
        </div>
        <div class="p-4 rounded-lg text-sm" style="background: var(--bg-page);">
          <p class="font-medium mb-1" style="color: var(--text-primary);">Votre message :</p>
          <p style="color: var(--text-secondary); white-space: pre-wrap;">{{ ticket.message }}</p>
          <p class="text-xs mt-2" style="color: var(--text-secondary);">{{ new Date(ticket.created_at).toLocaleString('fr-FR') }}</p>
        </div>
        <div v-if="ticket.reponse" class="p-4 rounded-lg text-sm mt-4" style="background: #D1FAE5; border: 1px solid var(--green-tree);">
          <p class="font-medium mb-1" style="color: var(--green-tree);">Réponse :</p>
          <p style="color: var(--text-primary); white-space: pre-wrap;">{{ ticket.reponse }}</p>
          <p class="text-xs mt-2" style="color: var(--text-secondary);">{{ ticket.updated_at ? new Date(ticket.updated_at).toLocaleString('fr-FR') : '' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
