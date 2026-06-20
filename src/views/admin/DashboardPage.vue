<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import adminApi from '@/api/admin'

const router = useRouter()
const stats = ref({
  total_users: 0,
  total_citoyens: 0,
  pending_tickets: 0,
  total_geometres: 0,
  total_notaires: 0,
  pending_role_requests: 0,
  recent_users: [],
})
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await adminApi.dashboard()
    stats.value = res.data?.data || res.data || stats.value
  } catch { /* ignore */ }
  loading.value = false
})
</script>

<template>
  <div class="page-container">
    <button @click="router.push('/mon-profil')" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="section-title">Administration</h1>
        <p class="section-subtitle">Tableau de bord administrateur</p>
      </div>
      <div class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
        <i class="fas fa-clock"></i>
        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
      </div>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        <div class="stats-card">
          <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center" style="background: var(--info); opacity: 0.15;">
              <i class="fas fa-users" style="color: var(--info);"></i>
            </div>
            <i class="fas fa-chart-line" style="color: var(--success);"></i>
          </div>
          <div class="stat-value">{{ stats.total_users }}</div>
          <div class="stat-label">Utilisateurs</div>
        </div>
        <div class="stats-card">
          <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center" style="background: var(--success); opacity: 0.15;">
              <i class="fas fa-user-check" style="color: var(--success);"></i>
            </div>
            <i class="fas fa-chart-line" style="color: var(--success);"></i>
          </div>
          <div class="stat-value">{{ stats.total_citoyens }}</div>
          <div class="stat-label">Citoyens</div>
        </div>
        <router-link to="/admin/support/tickets" class="stats-card" style="cursor: pointer;">
          <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center" style="background: var(--gold); opacity: 0.15;">
              <i class="fas fa-ticket" style="color: var(--gold);"></i>
            </div>
            <i class="fas fa-arrow-right" style="color: var(--gold);"></i>
          </div>
          <div class="stat-value">{{ stats.pending_tickets || 0 }}</div>
          <div class="stat-label">Tickets support</div>
        </router-link>
        <div class="stats-card">
          <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center" style="background: var(--green-tree); opacity: 0.15;">
              <i class="fas fa-draw-polygon" style="color: var(--green-tree);"></i>
            </div>
            <i class="fas fa-chart-line" style="color: var(--success);"></i>
          </div>
          <div class="stat-value">{{ stats.total_geometres }}</div>
          <div class="stat-label">Géomètres</div>
        </div>
        <div class="stats-card">
          <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center" style="background: var(--text-primary); opacity: 0.1;">
              <i class="fas fa-file-signature" style="color: var(--text-primary);"></i>
            </div>
            <i class="fas fa-chart-line" style="color: var(--success);"></i>
          </div>
          <div class="stat-value">{{ stats.total_notaires }}</div>
          <div class="stat-label">Notaires</div>
        </div>
        <router-link to="/admin/role-requests" class="stats-card" style="cursor: pointer;">
          <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center" style="background: var(--danger); opacity: 0.15;">
              <i class="fas fa-clipboard-list" style="color: var(--danger);"></i>
            </div>
            <i class="fas fa-arrow-right" style="color: var(--danger);"></i>
          </div>
          <div class="stat-value">{{ stats.pending_role_requests }}</div>
          <div class="stat-label">Demandes de rôle</div>
        </router-link>
      </div>

      <div v-if="stats.recent_users?.length" class="card mb-6">
        <h3 class="section-title mb-4" style="font-size: 1rem;">
          <i class="fas fa-user-plus" style="color: var(--green-tree);"></i> Utilisateurs récents
        </h3>
        <div class="table-wrap">
          <table class="w-full">
            <thead>
              <tr>
                <th class="table-header">Nom</th>
                <th class="table-header">Email</th>
                <th class="table-header">Rôle</th>
              </tr>
            </thead>
            <tbody class="divide-y" style="border-color: var(--border);">
              <tr v-for="u in stats.recent_users" :key="u.id">
                <td class="table-cell font-medium" style="color: var(--text-primary);">{{ u.nom }} {{ u.prenom }}</td>
                <td class="table-cell">{{ u.email }}</td>
                <td class="table-cell"><span class="badge" :class="u.role === 'admin' ? 'badge-danger' : u.role === 'geometre' ? 'badge-info' : u.role === 'notaire' ? 'badge-info' : 'badge-success'">{{ u.role }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <h3 class="section-title mb-4" style="font-size: 1rem;">
          <i class="fas fa-bolt" style="color: var(--green-tree);"></i> Liens rapides
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <router-link to="/admin/users" class="flex-center gap-2 p-4 rounded-lg font-medium text-sm" style="background: var(--bg-page); color: var(--green-tree); transition: opacity 0.15s;" @mouseenter="$event.currentTarget.style.opacity = '0.8'" @mouseleave="$event.currentTarget.style.opacity = '1'">
            <i class="fas fa-users"></i> Gérer les utilisateurs
          </router-link>
          <router-link to="/admin/role-requests" class="flex-center gap-2 p-4 rounded-lg font-medium text-sm" style="background: var(--bg-page); color: var(--gold); transition: opacity 0.15s;" @mouseenter="$event.currentTarget.style.opacity = '0.8'" @mouseleave="$event.currentTarget.style.opacity = '1'">
            <i class="fas fa-clipboard-list"></i> Demandes de rôle
          </router-link>
          <router-link to="/admin/blockchain" class="flex-center gap-2 p-4 rounded-lg font-medium text-sm" style="background: var(--bg-page); color: var(--info); transition: opacity 0.15s;" @mouseenter="$event.currentTarget.style.opacity = '0.8'" @mouseleave="$event.currentTarget.style.opacity = '1'">
            <i class="fas fa-shield-alt"></i> Registre blockchain
          </router-link>
        </div>
      </div>
    </template>
  </div>
</template>
