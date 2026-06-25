<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import supportTicketApi from '@/api/supportTicket'

const router = useRouter()
const tickets = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await supportTicketApi.list()
    tickets.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement tickets:', e) }
  loading.value = false
})

function statutBadgeClass(s) {
  const map = { ouvert: 'badge-success', en_cours: 'badge-info', ferme: 'badge-neutral' }
  return map[s] || 'badge-neutral'
}
function prioriteBadge(p) {
  const map = { haute: 'badge-danger', moyenne: 'badge-warning', basse: 'badge-neutral' }
  return map[p] || 'badge-neutral'
}
function prioriteLabel(p) {
  const map = { haute: 'Haute', moyenne: 'Moyenne', basse: 'Basse' }
  return map[p] || p
}
</script>

<template>
  <div class="page-wrap">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Support</h1>
        <p class="page-subtitle">Vos demandes d'assistance technique</p>
      </div>
      <router-link :to="{ name: 'SupportTicketCreer' }" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouveau ticket
      </router-link>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="skeleton h-20 rounded-2xl"></div>
    </div>

    <div v-else-if="tickets.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-headset"></i></div>
        <p class="empty-title">Aucun ticket de support</p>
        <p class="empty-text">Vous n'avez pas encore soumis de demande d'assistance.</p>
        <router-link :to="{ name: 'SupportTicketCreer' }" class="btn btn-primary mt-4">
          <i class="fas fa-plus"></i> Créer un ticket
        </router-link>
      </div>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="t in tickets" :key="t.id"
        @click="router.push({ name: 'SupportTicketDetail', params: { id: t.id } })"
        class="card group hover:border-brand-100 hover:shadow-md cursor-pointer transition-all"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center text-brand shrink-0 group-hover:bg-brand group-hover:text-white transition-colors">
            <i class="fas fa-headset text-sm"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <p class="font-display font-bold text-stone-900 truncate">{{ t.sujet || 'Ticket #' + t.id }}</p>
              <span class="badge" :class="statutBadgeClass(t.statut)">{{ t.statut }}</span>
              <span v-if="t.priorite" class="badge" :class="prioriteBadge(t.priorite)">{{ prioriteLabel(t.priorite) }}</span>
            </div>
            <p class="text-sm text-stone-400"><i class="fas fa-calendar mr-1.5 text-stone-300"></i>{{ t.created_at ? new Date(t.created_at).toLocaleDateString('fr-FR') : '—' }}</p>
          </div>
          <i class="fas fa-chevron-right text-sm text-stone-300 group-hover:text-brand transition-colors hidden sm:block"></i>
        </div>
      </div>
    </div>
  </div>
</template>
