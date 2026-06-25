<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import demandeAchatApi from '@/api/demandeAchat'
import transactionApi from '@/api/transaction'
import supportTicketApi from '@/api/supportTicket'

const router = useRouter()
const auth = useAuthStore()
const conversations = ref([])
const loading = ref(true)
const tab = ref('all')

async function fetchConversations() {
  loading.value = true
  try {
    const [daRes, txRes, stRes] = await Promise.allSettled([
      demandeAchatApi.list(),
      transactionApi.list(),
      supportTicketApi.list()
    ])

    const items = []

    if (daRes.status === 'fulfilled') {
      const data = daRes.value.data?.data || daRes.value.data || []
      for (const d of data) {
        items.push({
          id: d.id,
          type: 'demande_achat',
          label: `Demande d'achat #${d.id}`,
          subtitle: d.parcelle?.titre || `Parcelle #${d.parcelle_id}`,
          statut: d.statut,
          date: d.created_at,
          icon: 'fa-coins',
          color: 'text-amber-600',
          to: auth.userRole === 'notaire'
            ? `/notaire/demandes-achat/${d.id}`
            : `/citoyen/demandes-achat/${d.id}`
        })
      }
    }

    if (txRes.status === 'fulfilled') {
      const data = txRes.value.data?.data || txRes.value.data || []
      for (const t of data) {
        items.push({
          id: t.id,
          type: 'transaction',
          label: `Transaction #${t.id}`,
          subtitle: t.titre || t.parcelle?.titre || '',
          statut: t.statut,
          date: t.created_at,
          icon: 'fa-file-signature',
          color: 'text-blue-600',
          to: auth.userRole === 'notaire'
            ? `/notaire/transactions/${t.id}`
            : `/citoyen/transactions/${t.id}`
        })
      }
    }

    if (stRes.status === 'fulfilled') {
      const data = stRes.value.data?.data || stRes.value.data || []
      for (const s of data) {
        items.push({
          id: s.id,
          type: 'support',
          label: s.sujet || `Ticket #${s.id}`,
          subtitle: s.categorie || 'Support',
          statut: s.statut,
          date: s.created_at,
          icon: 'fa-headset',
          color: 'text-purple-600',
          to: `/support/tickets/${s.id}`
        })
      }
    }

    items.sort((a, b) => new Date(b.date) - new Date(a.date))
    conversations.value = items
  } catch (e) {
    console.error('Erreur chargement conversations:', e)
  }
  loading.value = false
}

const filteredConversations = computed(() => {
  if (tab.value === 'all') return conversations.value
  return conversations.value.filter(c => c.type === tab.value)
})

function statutBadge(statut) {
  const map = {
    en_cours: 'badge-info',
    ouverte: 'badge-info',
    en_attente: 'badge-warning',
    acceptee: 'badge-success',
    terminee: 'badge-neutral',
    validee: 'badge-success',
    rejetee: 'badge-danger',
    fermee: 'badge-neutral',
    resolue: 'badge-success',
  }
  return map[statut] || 'badge-neutral'
}
</script>

<template>
  <div class="page-wrap max-w-3xl mx-auto">
    <div class="flex items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="page-title">Messagerie</h1>
        <p class="page-subtitle">Toutes vos conversations</p>
      </div>
    </div>

    <div class="flex gap-1.5 mb-6 p-1 bg-stone-100 rounded-xl w-fit">
      <button @click="tab = 'all'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="tab === 'all' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700'">Toutes</button>
      <button @click="tab = 'demande_achat'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="tab === 'demande_achat' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700'">Achats</button>
      <button @click="tab = 'transaction'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="tab === 'transaction' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700'">Transactions</button>
      <button @click="tab = 'support'" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="tab === 'support' ? 'bg-white text-stone-900 shadow-sm' : 'text-stone-500 hover:text-stone-700'">Support</button>
    </div>

    <div v-if="loading" class="space-y-2">
      <div v-for="i in 5" :key="i" class="skeleton h-20 rounded-xl"></div>
    </div>

    <div v-else-if="filteredConversations.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-message"></i></div>
        <p class="empty-title">Aucune conversation</p>
        <p class="empty-text">Vous n'avez aucune conversation pour le moment.</p>
      </div>
    </div>

    <div v-else class="space-y-2">
      <div
        v-for="c in filteredConversations"
        :key="c.type + '-' + c.id"
        @click="router.push(c.to)"
        class="card !p-4 flex items-center gap-4 cursor-pointer hover:shadow-md transition-all"
      >
        <div class="w-10 h-10 rounded-xl bg-stone-100 flex items-center justify-center shrink-0">
          <i :class="['fas', c.icon, c.color, 'text-sm']"></i>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-0.5">
            <p class="font-semibold text-stone-900 text-sm truncate">{{ c.label }}</p>
            <span class="badge text-[10px] leading-none py-0.5" :class="statutBadge(c.statut)">{{ c.statut }}</span>
          </div>
          <p class="text-xs text-stone-500 truncate">{{ c.subtitle }}</p>
        </div>
        <div class="text-right shrink-0">
          <p class="text-[10px] text-stone-400 font-medium">{{ c.date ? new Date(c.date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) : '' }}</p>
          <i class="fas fa-chevron-right text-xs text-stone-300 mt-1.5"></i>
        </div>
      </div>
    </div>
  </div>
</template>