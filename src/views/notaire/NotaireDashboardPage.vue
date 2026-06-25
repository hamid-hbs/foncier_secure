<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import transactionApi from '@/api/transaction'
import StatutBadge from '@/components/StatutBadge.vue'

const router = useRouter()
const auth = useAuthStore()
const stats = ref({ total: 0, en_cours: 0, cloturees: 0 })
const recentTransactions = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const [allRes, pendingRes] = await Promise.all([
      transactionApi.list({ page: 1, per_page: 5 }),
      transactionApi.list({ statut: 'en_cours', page: 1, per_page: 5 }),
    ])
    const allData = allRes.data?.data || allRes.data || []
    const pendingData = pendingRes.data?.data || pendingRes.data || []
    recentTransactions.value = (Array.isArray(allData) ? allData : []).slice(0, 5)
    const enCoursCount = Array.isArray(pendingData) ? pendingData.length : 0
    const allCount = (allRes.data?.meta?.total || allRes.data?.total || (Array.isArray(allData) ? allData.length : 0))
    stats.value = { total: allCount, en_cours: enCoursCount, cloturees: 0 }
  } catch (e) { console.error('Erreur chargement dashboard:', e) }
  loading.value = false
})
</script>

<template>
  <div class="page-wrap">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Étude Notariale</h1>
        <p class="page-subtitle">Bienvenue, Maître {{ auth.user?.nom || auth.user?.prenom || 'Notaire' }}</p>
      </div>
      <div class="flex items-center gap-2 text-sm text-stone-500 bg-white border border-stone-200 px-4 py-2 rounded-xl shadow-xs">
        <i class="fas fa-calendar text-brand"></i>
        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <div v-for="i in 3" :key="i" class="skeleton h-28 rounded-2xl"></div>
    </div>

    <template v-else>
      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="card">
          <div class="flex items-start justify-between mb-3">
            <p class="stat-label">Total dossiers</p>
            <div class="w-10 h-10 rounded-xl bg-stone-100 flex items-center justify-center text-stone-500 shrink-0">
              <i class="fas fa-folder-open text-sm"></i>
            </div>
          </div>
          <p class="stat-value">{{ stats.total }}</p>
        </div>
        <div class="card">
          <div class="flex items-start justify-between mb-3">
            <p class="stat-label">En cours</p>
            <div class="w-10 h-10 rounded-xl bg-warn/10 flex items-center justify-center text-warn shrink-0">
              <i class="fas fa-hourglass-half text-sm"></i>
            </div>
          </div>
          <p class="stat-value">{{ stats.en_cours }}</p>
        </div>
        <div class="card">
          <div class="flex items-start justify-between mb-3">
            <p class="stat-label">Clôturés</p>
            <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
              <i class="fas fa-check-double text-sm"></i>
            </div>
          </div>
          <p class="stat-value">{{ stats.cloturees }}</p>
        </div>
      </div>

      <!-- Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card">
          <div class="flex items-center justify-between mb-5">
            <h3 class="font-display font-bold text-stone-900">Transactions récentes</h3>
            <router-link to="/notaire/transactions" class="text-xs font-bold text-brand hover:text-brand-light transition-colors">
              Tous les dossiers <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
            </router-link>
          </div>

          <div v-if="recentTransactions.length === 0" class="empty-state py-10">
            <div class="empty-icon"><i class="fas fa-file-signature"></i></div>
            <p class="empty-title">Aucune transaction</p>
            <p class="empty-text">Votre étude n'a pas encore de dossier actif.</p>
            <router-link to="/notaire/transactions/creer" class="btn btn-primary mt-4">
              <i class="fas fa-plus"></i> Créer un dossier
            </router-link>
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="t in recentTransactions" :key="t.id"
              @click="router.push(`/notaire/transactions/${t.id}`)"
              class="group flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl border border-stone-100 hover:border-brand-100 hover:bg-brand-50/20 transition-all cursor-pointer"
            >
              <div class="flex items-start gap-4 mb-3 sm:mb-0">
                <div class="w-11 h-11 rounded-xl bg-stone-100 flex items-center justify-center text-stone-500 group-hover:bg-brand group-hover:text-white transition-colors shrink-0">
                  <i class="fas fa-file-contract text-sm"></i>
                </div>
                <div>
                  <h4 class="font-bold text-stone-900 text-sm group-hover:text-brand transition-colors">
                    {{ t.titre || 'Transaction #' + t.id }}
                  </h4>
                  <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-stone-400 mt-1">
                    <span><i class="fas fa-user-tag mr-1"></i>{{ t.vendeur?.nom || t.vendeur || 'Vendeur' }}</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                    <span><i class="fas fa-user mr-1"></i>{{ t.acheteur?.nom || t.acheteur || 'Acheteur' }}</span>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-3 self-end sm:self-auto">
                <StatutBadge :statut="t.statut" />
                <i class="fas fa-chevron-right text-xs text-stone-300 group-hover:text-brand transition-colors"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div class="card bg-brand-dark text-white relative overflow-hidden">
            <div class="absolute -right-4 -top-4 text-white/5 pointer-events-none">
              <i class="fas fa-scale-balanced text-[100px]"></i>
            </div>
            <h3 class="font-display font-bold mb-4 relative z-10">Actions rapides</h3>
            <div class="space-y-2 relative z-10">
              <router-link to="/notaire/transactions/creer" class="flex items-center gap-3 p-3 rounded-xl bg-brand hover:bg-brand-light transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0"><i class="fas fa-plus text-sm"></i></div>
                <span class="font-semibold text-sm">Ouvrir un dossier</span>
              </router-link>
              <router-link to="/notaire/demandes-achat" class="flex items-center gap-3 p-3 rounded-xl bg-white/8 hover:bg-white/15 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0"><i class="fas fa-cart-shopping text-sm"></i></div>
                <span class="font-semibold text-sm">Demandes d'achat</span>
              </router-link>
              <router-link to="/notaire/transactions" class="flex items-center gap-3 p-3 rounded-xl bg-white/8 hover:bg-white/15 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0"><i class="fas fa-folder-open text-sm"></i></div>
                <span class="font-semibold text-sm">Tous les dossiers</span>
              </router-link>
            </div>
          </div>

          <div class="card border border-warn/30 bg-warn/5">
            <div class="flex items-start gap-3">
              <div class="w-9 h-9 rounded-xl bg-warn/10 flex items-center justify-center text-warn shrink-0">
                <i class="fas fa-bell text-sm"></i>
              </div>
              <div>
                <p class="font-bold text-stone-900 text-sm mb-1">Rappel important</p>
                <p class="text-xs text-stone-500 leading-relaxed">Vérifiez l'identité de chaque intervenant avant la signature de l'acte de vente.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
