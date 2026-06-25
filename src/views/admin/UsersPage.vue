<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import adminApi from '@/api/admin'

const router = useRouter()
const users = ref([])
const loading = ref(true)
const page = ref(1)
const totalPages = ref(1)
const search = ref('')
const roleFilter = ref('')

async function fetchUsers(p = 1) {
  loading.value = true
  try {
    const params = { page: p, per_page: 15 }
    if (search.value) params.search = search.value
    if (roleFilter.value) params.role = roleFilter.value
    const res = await adminApi.getUsers(params)
    users.value = (res.data.data || []).filter(Boolean)
    totalPages.value = res.data?.meta?.last_page || 1
    page.value = p
  } catch (e) { console.error('Erreur chargement utilisateurs:', e) }
  loading.value = false
}

async function toggleActive(user) {
  try {
    await adminApi.toggleUserStatus(user.id)
    user.is_active = !user.is_active
  } catch (e) { console.error('Erreur toggle statut:', e) }
}

function getRoleCode(role) {
  return typeof role === 'object' ? role?.code : role
}

watch([search, roleFilter], () => fetchUsers(1))
onMounted(() => fetchUsers())

const roles = [
  { value: '', label: 'Tous les rôles' },
  { value: 'citoyen', label: 'Citoyen' },
  { value: 'notaire', label: 'Notaire' },
  { value: 'geometre', label: 'Géomètre' },
  { value: 'admin', label: 'Admin' },
]

function roleBadge(role) {
  const code = getRoleCode(role)
  const map = { admin: 'badge-danger', notaire: 'badge-purple', geometre: 'badge-info', citoyen: 'badge-neutral' }
  return map[code] || 'badge-neutral'
}
</script>

<template>
  <div class="page-wrap">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Utilisateurs</h1>
        <p class="page-subtitle">Gérez les comptes et les rôles des utilisateurs</p>
      </div>
      <router-link to="/admin/users/new" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Nouvel utilisateur
      </router-link>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
      <div class="relative flex-1">
        <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
        <input v-model="search" type="text" class="form-input pl-10" placeholder="Rechercher un utilisateur…" />
      </div>
      <select v-model="roleFilter" class="form-select w-full sm:w-48">
        <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-2">
      <div v-for="i in 8" :key="i" class="skeleton h-16 rounded-xl"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="users.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-users"></i></div>
        <p class="empty-title">Aucun utilisateur trouvé</p>
        <p class="empty-text">Modifiez vos critères de recherche ou créez un nouvel utilisateur.</p>
      </div>
    </div>

    <!-- Table -->
    <div v-else class="card p-0 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-stone-50 border-b border-stone-100">
              <th class="table-header text-left">Utilisateur</th>
              <th class="table-header text-left">Rôle</th>
              <th class="table-header text-left">Statut</th>
              <th class="table-header text-left">Inscrit le</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users" :key="u.id" @click="router.push(`/admin/users/${u.id}`)" class="table-row border-b border-stone-50 last:border-0 cursor-pointer hover:bg-stone-50 transition-colors">
              <td class="table-cell">
                <div class="flex items-center gap-3">
                  <div class="avatar avatar-sm bg-brand shrink-0">{{ (u.prenom || 'U')[0] }}{{ (u.nom || '')[0] }}</div>
                  <div>
                    <p class="font-semibold text-stone-900 text-sm">{{ u.prenom }} {{ u.nom }}</p>
                    <p class="text-xs text-stone-400">{{ u.email }}</p>
                  </div>
                </div>
              </td>
              <td class="table-cell">
                <span class="badge capitalize" :class="roleBadge(u.role)">{{ getRoleCode(u.role) }}</span>
              </td>
              <td class="table-cell">
                <span class="badge" :class="u.is_active ? 'badge-success' : 'badge-danger'">
                  {{ u.is_active ? 'Actif' : 'Inactif' }}
                </span>
              </td>
              <td class="table-cell text-sm text-stone-400">{{ u.created_at ? new Date(u.created_at).toLocaleDateString('fr-FR') : '—' }}</td>
              <td class="table-cell">
                <div class="flex items-center justify-end gap-2">
                  <button @click.stop="toggleActive(u)" class="btn btn-ghost btn-xs" :title="u.is_active ? 'Désactiver' : 'Activer'">
                    <i :class="['fas', u.is_active ? 'fa-user-slash text-danger' : 'fa-user-check text-success']"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="pagination">
      <button class="page-btn" :disabled="page <= 1" @click="fetchUsers(page - 1)"><i class="fas fa-chevron-left text-xs"></i></button>
      <button v-for="p in Math.min(totalPages, 8)" :key="p" class="page-btn" :class="p === page ? 'active' : ''" @click="fetchUsers(p)">{{ p }}</button>
      <button class="page-btn" :disabled="page >= totalPages" @click="fetchUsers(page + 1)"><i class="fas fa-chevron-right text-xs"></i></button>
    </div>
  </div>
</template>
