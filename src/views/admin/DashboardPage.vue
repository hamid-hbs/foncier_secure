<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import adminApi from '@/api/admin'

function getRoleCode(role) {
  return typeof role === 'object' ? role?.code : role
}

const router = useRouter()
const stats = ref({
  total_users: 0,
  total_citoyens: 0,
  pending_tickets: 0,
  total_parcelles: 0,
  pending_role_requests: 0,
  total_transactions: 0,
})
const recentUsers = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const [statsRes, usersRes] = await Promise.all([
      adminApi.dashboard ? adminApi.dashboard() : Promise.resolve({ data: {} }),
      adminApi.getUsers ? adminApi.getUsers({ page: 1, per_page: 5 }) : Promise.resolve({ data: [] }),
    ])
    const s = statsRes.data?.data || statsRes.data || {}
    Object.assign(stats.value, s)
    recentUsers.value = (usersRes.data?.data || usersRes.data || []).slice(0, 5)
  } catch (e) { console.error('Erreur chargement dashboard:', e) }
  loading.value = false
})

const statCards = [
  { key: 'total_users', label: 'Utilisateurs', icon: 'fa-users', color: 'bg-brand/10 text-brand', to: '/admin/users' },
  { key: 'total_citoyens', label: 'Citoyens', icon: 'fa-person', color: 'bg-sky-100 text-sky-600', to: '/admin/users' },
  { key: 'total_parcelles', label: 'Parcelles', icon: 'fa-map', color: 'bg-gold/10 text-gold-dark', to: '/admin/localisation' },
  { key: 'total_transactions', label: 'Transactions', icon: 'fa-arrows-left-right', color: 'bg-success/10 text-success', to: '/admin/blockchain' },
  { key: 'pending_role_requests', label: 'Demandes de rôle', icon: 'fa-clipboard-list', color: 'bg-warn/10 text-warn', to: '/admin/role-requests' },
  { key: 'pending_tickets', label: 'Tickets ouverts', icon: 'fa-headset', color: 'bg-danger/10 text-danger', to: '/admin/support/tickets' },
]
</script>

<template>
  <div class="page-wrap">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Administration</h1>
        <p class="page-subtitle">Vue d'ensemble de la plateforme FoncierSecure</p>
      </div>
      <div class="flex items-center gap-2 text-sm text-stone-500 bg-white border border-stone-200 px-4 py-2 rounded-xl shadow-xs">
        <i class="fas fa-calendar text-brand"></i>
        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
      <div v-for="i in 6" :key="i" class="skeleton h-24 rounded-2xl"></div>
    </div>

    <template v-else>
      <!-- Stat Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <router-link
          v-for="c in statCards"
          :key="c.key"
          :to="c.to"
          class="card group hover:border-brand-100 hover:shadow-md transition-all cursor-pointer"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="stat-label">{{ c.label }}</p>
              <p class="stat-value mt-2">{{ stats[c.key] ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all group-hover:scale-110" :class="c.color">
              <i :class="['fas', c.icon]"></i>
            </div>
          </div>
        </router-link>
      </div>

      <!-- Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent users -->
        <div class="lg:col-span-2 card">
          <div class="flex items-center justify-between mb-5">
            <h3 class="font-display font-bold text-stone-900">Utilisateurs récents</h3>
            <router-link to="/admin/users" class="text-xs font-bold text-brand hover:text-brand-light transition-colors">Gérer les utilisateurs <i class="fas fa-arrow-right ml-1 text-[10px]"></i></router-link>
          </div>
          <div v-if="recentUsers.length === 0" class="empty-state py-10">
            <div class="empty-icon"><i class="fas fa-users"></i></div>
            <p class="empty-title">Aucun utilisateur récent</p>
          </div>
          <div v-else class="space-y-1">
            <div v-for="u in recentUsers" :key="u.id"
              @click="router.push('/admin/users')"
              class="flex items-center gap-3 p-3 rounded-xl hover:bg-stone-50 cursor-pointer transition-colors">
              <div class="avatar avatar-sm bg-brand shrink-0">{{ (u.prenom || 'U')[0] }}{{ (u.nom || '')[0] }}</div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-stone-900">{{ u.prenom }} {{ u.nom }}</p>
                <p class="text-xs text-stone-400">{{ u.email }}</p>
              </div>
              <span class="badge badge-info capitalize">{{ getRoleCode(u.role) }}</span>
              <span v-if="!u.is_active" class="badge badge-danger">Inactif</span>
            </div>
          </div>
        </div>

        <!-- Quick actions -->
        <div class="space-y-4">
          <div class="card bg-brand-dark text-white relative overflow-hidden">
            <div class="absolute -right-4 -top-4 text-white/5 pointer-events-none">
              <i class="fas fa-gear text-[100px]"></i>
            </div>
            <h3 class="font-display font-bold mb-4 relative z-10">Actions rapides</h3>
            <div class="space-y-2 relative z-10">
              <router-link to="/admin/users/new" class="flex items-center gap-3 p-3 rounded-xl bg-brand hover:bg-brand-light transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0"><i class="fas fa-user-plus text-sm"></i></div>
                <span class="font-semibold text-sm">Créer un utilisateur</span>
              </router-link>
              <router-link to="/admin/role-requests" class="flex items-center gap-3 p-3 rounded-xl bg-white/8 hover:bg-white/15 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0"><i class="fas fa-clipboard-check text-sm"></i></div>
                <span class="font-semibold text-sm">Demandes de rôle</span>
                <span v-if="stats.pending_role_requests > 0" class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-500 text-white">{{ stats.pending_role_requests }}</span>
              </router-link>
              <router-link to="/admin/blockchain" class="flex items-center gap-3 p-3 rounded-xl bg-white/8 hover:bg-white/15 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0"><i class="fas fa-link text-sm"></i></div>
                <span class="font-semibold text-sm">Explorer blockchain</span>
              </router-link>
            </div>
          </div>

          <div v-if="stats.pending_tickets > 0" class="card border border-warn/30 bg-warn/5">
            <div class="flex items-start gap-3">
              <div class="w-9 h-9 rounded-xl bg-warn/10 flex items-center justify-center text-warn shrink-0">
                <i class="fas fa-triangle-exclamation"></i>
              </div>
              <div>
                <p class="font-bold text-stone-900 text-sm">Tickets en attente</p>
                <p class="text-xs text-stone-500 mt-0.5">{{ stats.pending_tickets }} ticket(s) nécessitent votre attention.</p>
                <router-link to="/admin/support/tickets" class="text-xs font-bold text-brand mt-2 block">Traiter maintenant →</router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
