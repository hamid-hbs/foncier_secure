<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import transactionApi from '@/api/transaction'
import rendezVousApi from '@/api/rendezVous'

const router = useRouter()
const auth = useAuthStore()
const rendezVous = ref([])
const loading = ref(true)

function findParticipant(rv, roleCode) {
  return rv.participants?.find(p => p.user?.role?.code === roleCode || p.user?.role === roleCode)?.user || null
}

onMounted(async () => {
  try {
    const res = await rendezVousApi.list()
    rendezVous.value = (res.data?.data || res.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement rendez-vous:', e) }
  loading.value = false
})

function statutBadgeClass(s) {
  const map = { planifie: 'badge-info', confirme: 'badge-success', annule: 'badge-danger', effectue: 'badge-neutral' }
  return map[s] || 'badge-neutral'
}
function statutLabel(s) {
  const map = { planifie: 'Planifié', confirme: 'Confirmé', annule: 'Annulé', effectue: 'Effectué' }
  return map[s] || s
}
function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <div class="page-wrap">
    <div class="flex items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Mes rendez-vous</h1>
        <p class="page-subtitle">Agenda de vos rendez-vous fonciers</p>
      </div>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="skeleton h-24 rounded-2xl"></div>
    </div>

    <div v-else-if="rendezVous.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-calendar-check"></i></div>
        <p class="empty-title">Aucun rendez-vous</p>
        <p class="empty-text">Vos rendez-vous planifiés dans le cadre de vos transactions apparaîtront ici.</p>
      </div>
    </div>

    <div v-else class="space-y-4">
      <div v-for="rv in rendezVous" :key="rv.id" class="card">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <!-- Date bloc -->
          <div class="w-16 h-16 rounded-xl bg-brand-50 flex flex-col items-center justify-center text-brand shrink-0">
            <span class="font-display font-extrabold text-xl leading-none">{{ rv.date_prevue ? new Date(rv.date_prevue).getDate() : '—' }}</span>
            <span class="text-[10px] font-semibold uppercase">{{ rv.date_prevue ? new Date(rv.date_prevue).toLocaleString('fr-FR', { month: 'short' }) : '' }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <p class="font-display font-bold text-stone-900">
                {{ rv.titre || (rv.type === 'signature' ? 'Signature' : rv.type === 'visite_terrain' ? 'Visite terrain' : rv.type) || 'Rendez-vous #' + rv.id }}
              </p>
              <span class="badge" :class="statutBadgeClass(rv.statut)">{{ statutLabel(rv.statut) }}</span>
            </div>
            <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-stone-400">
              <span><i class="fas fa-clock mr-1.5 text-stone-300"></i>{{ formatDate(rv.date_prevue) }}</span>
              <span v-if="rv.lieu"><i class="fas fa-location-dot mr-1.5 text-stone-300"></i>{{ rv.lieu }}</span>
              <span v-if="findParticipant(rv, 'notaire')"><i class="fas fa-scale-balanced mr-1.5 text-stone-300"></i>Maître {{ findParticipant(rv, 'notaire').nom }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
