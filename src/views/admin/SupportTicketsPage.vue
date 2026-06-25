<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import supportTicketApi from '@/api/supportTicket'

const router = useRouter()
const tickets = ref([])
const loading = ref(true)
const statutFilter = ref('')

const statuts = [
  { value: '', label: 'Tous' },
  { value: 'ouvert', label: 'Ouverts' },
  { value: 'en_cours', label: 'En cours' },
  { value: 'ferme', label: 'Fermés' },
]

const filtered = computed(() => {
  if (!statutFilter.value) return tickets.value
  return tickets.value.filter(t => t.statut === statutFilter.value)
})

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
</script>

<template>
  <div class="page-wrap">
    <div class="flex items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Support clients</h1>
        <p class="page-subtitle">Gestion de tous les tickets de support</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="tabs mb-6">
      <button v-for="s in statuts" :key="s.value" class="tab" :class="statutFilter === s.value ? 'active' : ''" @click="statutFilter = s.value">
        {{ s.label }}
        <span class="ml-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full"
          :class="statutFilter === s.value ? 'bg-brand text-white' : 'bg-stone-200 text-stone-500'">
          {{ s.value ? tickets.filter(t => t.statut === s.value).length : tickets.length }}
        </span>
      </button>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 5" :key="i" class="skeleton h-20 rounded-2xl"></div>
    </div>

    <div v-else-if="filtered.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-headset"></i></div>
        <p class="empty-title">Aucun ticket</p>
        <p class="empty-text">Il n'y a pas de ticket pour ce filtre.</p>
      </div>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="t in filtered" :key="t.id"
        @click="router.push({ name: 'AdminSupportTicketDetail', params: { id: t.id } })"
        class="card group hover:border-brand-100 hover:shadow-md cursor-pointer transition-all"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="flex items-center gap-3 flex-1 min-w-0">
            <div class="avatar avatar-sm bg-brand shrink-0">{{ (t.user?.prenom || 'U')[0] }}</div>
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <p class="font-display font-bold text-stone-900 truncate">{{ t.sujet || 'Ticket #' + t.id }}</p>
                <span class="badge" :class="statutBadgeClass(t.statut)">{{ t.statut }}</span>
                <span v-if="t.priorite" class="badge" :class="prioriteBadge(t.priorite)">{{ t.priorite }}</span>
              </div>
              <p class="text-sm text-stone-400">{{ t.user?.prenom }} {{ t.user?.nom }} · {{ t.created_at ? new Date(t.created_at).toLocaleDateString('fr-FR') : '' }}</p>
            </div>
          </div>
          <i class="fas fa-chevron-right text-sm text-stone-300 group-hover:text-brand transition-colors hidden sm:block shrink-0"></i>
        </div>
      </div>
    </div>
  </div>
</template>
