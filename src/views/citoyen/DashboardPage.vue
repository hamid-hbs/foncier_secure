<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import parcelleApi from '@/api/parcelle'
import transactionApi from '@/api/transaction'

const router = useRouter()
const auth = useAuthStore()
const loading = ref(true)
const stats = ref({ parcelles: 0, transactions: 0, verifications: 0 })
const recentParcelles = ref([])
const recentTransactions = ref([])

function statutBadgeClass(statut) {
  const map = { libre: 'badge-success', en_demande: 'badge-warning', en_transaction: 'badge-info', vendue: 'badge-neutral', conteste: 'badge-danger', en_verification: 'badge-info', sollicite: 'badge-purple' }
  return map[statut] || 'badge-neutral'
}

onMounted(async () => {
  try {
    const [pRes, tRes] = await Promise.all([
      parcelleApi.list(),
      transactionApi.list({ page: 1, per_page: 5 }),
    ])
    const parcelles = (pRes.data?.data || pRes.data || []).filter(Boolean)
    const transactions = (tRes.data?.data || tRes.data || []).filter(Boolean)
    recentParcelles.value = parcelles.slice(0, 4)
    recentTransactions.value = transactions.slice(0, 4)
    stats.value = { parcelles: parcelles.length, transactions: transactions.length, verifications: 0 }
  } catch (e) { console.error('Erreur chargement dashboard:', e) }
  loading.value = false
})
</script>

<template>
  <div class="page-wrap">
    <!-- Welcome header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Bonjour, {{ auth.user?.prenom || 'Citoyen' }} 👋</h1>
        <p class="page-subtitle">Voici un aperçu de votre patrimoine foncier</p>
      </div>
      <div class="flex items-center gap-2 text-sm text-stone-500 bg-white border border-stone-200 px-4 py-2 rounded-xl shadow-xs">
        <i class="fas fa-calendar text-brand"></i>
        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
      <div v-for="i in 3" :key="i" class="skeleton h-28 rounded-2xl"></div>
    </div>

    <template v-else>
      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <router-link to="/citoyen/parcelles" class="card group hover:border-brand-100 hover:shadow-md transition-all cursor-pointer">
          <div class="flex items-start justify-between mb-4">
            <div>
              <p class="stat-label">Mes parcelles</p>
              <p class="stat-value mt-1">{{ stats.parcelles }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center group-hover:bg-brand group-hover:text-white transition-colors text-brand">
              <i class="fas fa-map text-lg"></i>
            </div>
          </div>
          <span class="text-xs text-stone-400 font-medium group-hover:text-brand transition-colors">Voir toutes <i class="fas fa-arrow-right ml-1 text-[10px]"></i></span>
        </router-link>

        <router-link to="/citoyen/transactions" class="card group hover:border-brand-100 hover:shadow-md transition-all cursor-pointer">
          <div class="flex items-start justify-between mb-4">
            <div>
              <p class="stat-label">Transactions</p>
              <p class="stat-value mt-1">{{ stats.transactions }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-gold/10 flex items-center justify-center text-gold-dark group-hover:bg-gold group-hover:text-white transition-colors">
              <i class="fas fa-arrows-left-right text-lg"></i>
            </div>
          </div>
          <span class="text-xs text-stone-400 font-medium group-hover:text-brand transition-colors">Voir tout <i class="fas fa-arrow-right ml-1 text-[10px]"></i></span>
        </router-link>

        <router-link to="/citoyen/coffre" class="card group hover:border-brand-100 hover:shadow-md transition-all cursor-pointer">
          <div class="flex items-start justify-between mb-4">
            <div>
              <p class="stat-label">Coffre-fort</p>
              <p class="stat-value mt-1"><i class="fas fa-lock text-xl text-stone-200"></i></p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-success/10 flex items-center justify-center text-success group-hover:bg-success group-hover:text-white transition-colors">
              <i class="fas fa-lock text-lg"></i>
            </div>
          </div>
          <span class="text-xs text-stone-400 font-medium group-hover:text-brand transition-colors">Accéder <i class="fas fa-arrow-right ml-1 text-[10px]"></i></span>
        </router-link>
      </div>

      <!-- Main content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Parcelles récentes -->
        <div class="lg:col-span-2 card">
          <div class="flex items-center justify-between mb-5">
            <h3 class="font-display font-bold text-stone-900">Mes parcelles récentes</h3>
            <router-link to="/citoyen/parcelles" class="text-xs font-bold text-brand hover:text-brand-light transition-colors">Tout voir <i class="fas fa-arrow-right ml-1 text-[10px]"></i></router-link>
          </div>

          <div v-if="recentParcelles.length === 0" class="empty-state py-10">
            <div class="empty-icon"><i class="fas fa-map"></i></div>
            <p class="empty-title">Aucune parcelle</p>
            <p class="empty-text">Déclarez votre premier bien foncier pour commencer.</p>
            <router-link to="/citoyen/parcelles/creer" class="btn btn-primary mt-4">
              <i class="fas fa-plus"></i> Déclarer une parcelle
            </router-link>
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="p in recentParcelles"
              :key="p.id"
              @click="router.push(`/citoyen/parcelles/${p.id}`)"
              class="flex items-center gap-4 p-3.5 rounded-xl border border-stone-100 hover:border-brand-100 hover:bg-brand-50/20 cursor-pointer transition-all group"
            >
              <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand shrink-0 group-hover:bg-brand group-hover:text-white transition-colors">
                <i class="fas fa-map-marker-alt text-sm"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-stone-900 text-sm truncate">{{ p.titre || 'Parcelle #' + p.id }}</p>
                <p class="text-xs text-stone-400 truncate">{{ p.commune?.nom || 'Localisation inconnue' }}</p>
              </div>
              <div class="flex items-center gap-3">
                <span class="badge" :class="statutBadgeClass(p.statut)">{{ (p.statut || '').replace('_', ' ') }}</span>
                <i class="fas fa-chevron-right text-xs text-stone-300 group-hover:text-brand transition-colors"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick actions -->
        <div class="space-y-4">
          <!-- Actions card -->
          <div class="card bg-brand-dark text-white relative overflow-hidden">
            <div class="absolute -right-4 -top-4 text-white/5 pointer-events-none">
              <i class="fas fa-map text-[100px]"></i>
            </div>
            <h3 class="font-display font-bold mb-4 relative z-10">Actions rapides</h3>
            <div class="space-y-2 relative z-10">
              <router-link to="/citoyen/parcelles/creer" class="flex items-center gap-3 p-3 rounded-xl bg-brand hover:bg-brand-light transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0">
                  <i class="fas fa-plus text-sm"></i>
                </div>
                <span class="font-semibold text-sm">Déclarer une parcelle</span>
              </router-link>
              <router-link to="/citoyen/recherche" class="flex items-center gap-3 p-3 rounded-xl bg-white/8 hover:bg-white/15 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0">
                  <i class="fas fa-magnifying-glass text-sm"></i>
                </div>
                <span class="font-semibold text-sm">Rechercher un bien</span>
              </router-link>
              <router-link to="/citoyen/demandes-achat/creer" class="flex items-center gap-3 p-3 rounded-xl bg-white/8 hover:bg-white/15 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0">
                  <i class="fas fa-cart-shopping text-sm"></i>
                </div>
                <span class="font-semibold text-sm">Faire une demande d'achat</span>
              </router-link>
            </div>
          </div>

          <!-- Transactions récentes -->
          <div class="card">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-display font-bold text-stone-900 text-sm">Transactions récentes</h3>
              <router-link to="/citoyen/transactions" class="text-xs font-bold text-brand">Tout voir</router-link>
            </div>
            <div v-if="recentTransactions.length === 0" class="text-center py-6">
              <p class="text-sm text-stone-400">Aucune transaction</p>
            </div>
            <div v-else class="space-y-2">
              <div v-for="t in recentTransactions.slice(0,3)" :key="t.id"
                @click="router.push(`/citoyen/transactions/${t.id}`)"
                class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-stone-50 cursor-pointer transition-colors">
                <div class="w-8 h-8 rounded-lg bg-gold/10 flex items-center justify-center text-gold-dark shrink-0">
                  <i class="fas fa-file-signature text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-stone-900 truncate">#{{ t.id }}</p>
                  <p class="text-xs text-stone-400 capitalize">{{ (t.statut || '').replace('_', ' ') }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
