<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import supportTicketApi from '@/api/supportTicket'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const tickets = ref([])
const loading = ref(true)
const statutFilter = ref('')

const statutLabel = { ouvert: 'Ouvert', en_cours: 'En cours', resolu: 'Résolu', ferme: 'Fermé' }
const badgeClass = { ouvert: 'badge-warning', en_cours: 'badge-info', resolu: 'badge-success', ferme: 'badge-secondary' }

const filtered = computed(() => {
  if (!statutFilter.value) return tickets.value
  return tickets.value.filter(t => t.statut === statutFilter.value)
})

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
      <h1 class="section-title">Tickets support</h1>
      <select v-model="statutFilter" class="form-select" style="width: 160px;">
        <option value="">Tous</option>
        <option value="ouvert">Ouvert</option>
        <option value="en_cours">En cours</option>
        <option value="resolu">Résolu</option>
        <option value="ferme">Fermé</option>
      </select>
    </div>
    <div v-if="loading" class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl" style="color: var(--text-secondary);"></i></div>
    <div v-else-if="filtered.length === 0" class="card p-8 text-center" style="color: var(--text-secondary);">
      <i class="fas fa-ticket text-4xl mb-3 opacity-40"></i>
      <p>Aucun ticket.</p>
    </div>
    <div v-else class="space-y-3">
      <div v-for="t in filtered" :key="t.id" class="card p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition" @click="router.push(`/admin/support/tickets/${t.id}`)">
        <div>
          <p class="font-medium text-sm" style="color: var(--text-primary);">{{ t.sujet }}</p>
          <p class="text-xs mt-1" style="color: var(--text-secondary);">{{ t.user?.nom || 'Utilisateur' }} — {{ new Date(t.created_at).toLocaleDateString('fr-FR') }}</p>
        </div>
        <span :class="['badge', badgeClass[t.statut]]">{{ statutLabel[t.statut] || t.statut }}</span>
      </div>
    </div>
  </div>
</template>
