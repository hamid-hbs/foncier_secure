<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import demandeAchatApi from '@/api/demandeAchat'
const router = useRouter()
const auth = useAuthStore()
const demandes = ref([])
const loading = ref(true)

const isCitoyen = computed(() => auth.userRole === 'citoyen')

onMounted(async () => {
  try {
    const res = await demandeAchatApi.list()
    demandes.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement demandes:', e) }
  loading.value = false
})

function statutBadgeClass(s) {
  const map = { en_attente: 'badge-warning', acceptee: 'badge-success', refusee: 'badge-danger' }
  return map[s] || 'badge-neutral'
}
function statutLabel(s) {
  const map = { en_attente: 'En attente', acceptee: 'Acceptée', refusee: 'Refusée' }
  return map[s] || s
}
</script>

<template>
  <div class="page-wrap">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Demandes d'achat</h1>
        <p class="page-subtitle">Gérez vos propositions d'achat de parcelles</p>
      </div>
      <router-link v-if="isCitoyen" :to="{ name: 'NouvelleDemandeAchat' }" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvelle demande
      </router-link>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="skeleton h-24 rounded-2xl"></div>
    </div>

    <div v-else-if="demandes.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-cart-shopping"></i></div>
        <p class="empty-title">Aucune demande d'achat</p>
        <p class="empty-text">Vous n'avez pas encore de demande d'acquisition de parcelle.</p>
        <router-link v-if="isCitoyen" :to="{ name: 'NouvelleDemandeAchat' }" class="btn btn-primary mt-4">
          <i class="fas fa-plus"></i> Faire une demande
        </router-link>
      </div>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="d in demandes" :key="d.id"
        @click="router.push({ name: 'DemandeAchatDetail', params: { id: d.id } })"
        class="card group hover:border-brand-100 hover:shadow-md cursor-pointer transition-all"
      >
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-colors"
            :class="d.statut === 'acceptee' ? 'bg-success/10 text-success group-hover:bg-success group-hover:text-white' : d.statut === 'refusee' ? 'bg-danger/10 text-danger' : 'bg-warn/10 text-warn group-hover:bg-gold group-hover:text-white'">
            <i class="fas fa-cart-shopping text-sm"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <p class="font-display font-bold text-stone-900">
                {{ d.parcelle?.titre || d.parcelle?.code || 'Parcelle #' + d.parcelle_id }}
              </p>
              <span class="badge" :class="statutBadgeClass(d.statut)">{{ statutLabel(d.statut) }}</span>
            </div>
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-stone-400">
              <span v-if="d.prix_propose"><i class="fas fa-tag mr-1 text-stone-300"></i>{{ Number(d.prix_propose).toLocaleString('fr-FR') }} FCFA proposés</span>
              <span><i class="fas fa-calendar mr-1 text-stone-300"></i>{{ d.created_at ? new Date(d.created_at).toLocaleDateString('fr-FR') : '—' }}</span>
              <span v-if="d.vendeur"><i class="fas fa-user mr-1 text-stone-300"></i>{{ d.vendeur?.prenom }} {{ d.vendeur?.nom }}</span>
            </div>
          </div>
          <i class="fas fa-chevron-right text-sm text-stone-300 group-hover:text-brand transition-colors hidden sm:block"></i>
        </div>
      </div>
    </div>
  </div>
</template>
