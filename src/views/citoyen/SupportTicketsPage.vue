<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import supportTicketApi from '@/api/supportTicket'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const tickets = ref([])
const loading = ref(true)

const statutLabel = { ouvert: 'Ouvert', en_cours: 'En cours', resolu: 'Résolu', ferme: 'Fermé' }
const badgeClass = { ouvert: 'badge-warning', en_cours: 'badge-info', resolu: 'badge-success', ferme: 'badge-secondary' }
const prioriteLabel = { basse: 'Basse', normale: 'Normale', haute: 'Haute', urgente: 'Urgente' }

onMounted(async () => {
  try {
    const r = await supportTicketApi.list()
    tickets.value = r.data?.data ?? []
  } catch (e) { console.error(e) }
  finally { loading.value = false }
})
</script>
<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center justify-between mb-6">
      <h1 class="section-title">Support</h1>
      <router-link to="/support/tickets/creer" class="btn-green btn-sm"><i class="fas fa-plus mr-1"></i> Nouveau ticket</router-link>
    </div>
    <div v-if="loading" class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl" style="color: var(--text-secondary);"></i></div>
    <div v-else-if="tickets.length === 0" class="card p-8 text-center" style="color: var(--text-secondary);">
      <i class="fas fa-ticket text-4xl mb-3 opacity-40"></i>
      <p>Aucun ticket pour le moment.</p>
    </div>
    <div v-else class="space-y-3">
      <div v-for="t in tickets" :key="t.id" class="card p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition" @click="router.push(`/support/tickets/${t.id}`)">
        <div>
          <p class="font-medium text-sm" style="color: var(--text-primary);">{{ t.sujet }}</p>
          <p class="text-xs mt-1" style="color: var(--text-secondary);">{{ new Date(t.created_at).toLocaleDateString('fr-FR') }}</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs px-2 py-0.5 rounded" style="background: var(--bg-page); color: var(--text-secondary);">{{ prioriteLabel[t.priorite] || t.priorite }}</span>
          <span :class="['badge', badgeClass[t.statut]]">{{ statutLabel[t.statut] || t.statut }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
