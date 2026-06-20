<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import adminApi from '@/api/admin'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const users = ref([])
const loading = ref(true)
const page = ref(1)
const lastPage = ref(1)
const search = ref('')
const roleFilter = ref('')
const togglingId = ref(null)

onMounted(() => fetchUsers())

watch([search, roleFilter], () => {
  page.value = 1
  fetchUsers()
})

async function fetchUsers() {
  loading.value = true
  try {
    const params = { page: page.value }
    if (search.value) params.search = search.value
    if (roleFilter.value) params.role = roleFilter.value
    const res = await adminApi.getUsers(params)
    const data = res.data?.data || res.data || []
    users.value = (Array.isArray(data) ? data : []).filter(Boolean)
    lastPage.value = res.data?.meta?.last_page || res.data?.last_page || 1
  } catch { /* ignore */ }
  loading.value = false
}

async function toggleUserStatus(id) {
  togglingId.value = id
  try {
    await adminApi.toggleUserStatus(id)
    await fetchUsers()
  } catch { alert('Erreur lors de la mise à jour du statut') }
  togglingId.value = null
}

function formatRole(role) {
  const labels = { citoyen: 'Citoyen', geometre: 'Géomètre', notaire: 'Notaire', admin: 'Admin' }
  return labels[role] || role
}

function prevPage() {
  if (page.value > 1) { page.value--; fetchUsers() }
}

function nextPage() {
  if (page.value < lastPage.value) { page.value++; fetchUsers() }
}
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center gap-3 mb-8">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--green-tree); opacity: 0.15;">
        <i class="fas fa-users" style="color: var(--green-tree);"></i>
      </div>
      <div>
        <h1 class="section-title">Gestion des utilisateurs</h1>
        <p class="section-subtitle">Gérez les comptes et les rôles</p>
      </div>
    </div>

    <div class="flex flex-wrap gap-4 mb-6">
      <div class="relative flex-1 min-w-[200px]">
        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 0.875rem;"></i>
        <input v-model="search" class="form-input" style="padding-left: 36px; width: 100%;" placeholder="Rechercher par nom, email..." />
      </div>
      <select v-model="roleFilter" class="form-select" style="width: 160px;">
        <option value="">Tous les rôles</option>
        <option value="citoyen">Citoyen</option>
        <option value="geometre">Géomètre</option>
        <option value="notaire">Notaire</option>
        <option value="admin">Admin</option>
      </select>
      <router-link to="/admin/users/new" class="btn-green btn-sm flex items-center gap-1.5">
        <i class="fas fa-plus"></i> Nouvel utilisateur
      </router-link>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <div v-else-if="users.length === 0" class="card text-center py-12">
      <i class="fas fa-users mb-3" style="font-size: 2.5rem; color: var(--text-secondary); opacity: 0.5;"></i>
      <p style="color: var(--text-secondary);">Aucun utilisateur trouvé.</p>
    </div>

    <template v-else>
      <div class="table-wrap">
        <table class="w-full">
          <thead>
            <tr>
              <th class="table-header">Nom</th>
              <th class="table-header">Prénom</th>
              <th class="table-header">Email</th>
              <th class="table-header">Téléphone</th>
              <th class="table-header">Rôle</th>
              <th class="table-header">Statut</th>
              <th class="table-header">Confiance</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color: var(--border);">
            <tr v-for="u in users" :key="u.id" style="transition: background 0.15s;" @mouseenter="$event.currentTarget.style.background = 'var(--bg-page)'" @mouseleave="$event.currentTarget.style.background = ''">
              <td class="table-cell font-medium" style="color: var(--text-primary);">{{ u.nom }}</td>
              <td class="table-cell">{{ u.prenom }}</td>
              <td class="table-cell" style="color: var(--text-secondary);">{{ u.email }}</td>
              <td class="table-cell">{{ u.telephone || '-' }}</td>
              <td class="table-cell">
                <span class="badge" :class="u.role === 'admin' ? 'badge-danger' : u.role === 'geometre' ? 'badge-info' : u.role === 'notaire' ? 'badge-info' : 'badge-success'">{{ formatRole(u.role) }}</span>
              </td>
              <td class="table-cell">
                <span class="badge" :class="u.is_active ? 'badge-success' : 'badge-danger'">
                  <i :class="u.is_active ? 'fas fa-user-check' : 'fas fa-user-xmark'" class="mr-1"></i>
                  {{ u.is_active ? 'Actif' : 'Inactif' }}
                </span>
              </td>
              <td class="table-cell">
                <span class="badge" :class="u.indice_confiance >= 80 ? 'badge-success' : u.indice_confiance >= 50 ? 'badge-warning' : 'badge-danger'">{{ u.indice_confiance ?? '-' }}</span>
              </td>
              <td class="table-cell">
                <button @click="toggleUserStatus(u.id)" :disabled="togglingId === u.id" class="btn-sm flex items-center gap-1.5 font-medium" :class="u.is_active ? 'btn-danger' : 'btn-green'">
                  <i :class="u.is_active ? 'fas fa-toggle-on' : 'fas fa-toggle-off'"></i>
                  {{ u.is_active ? 'Désactiver' : 'Activer' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex-between mt-4">
        <span style="color: var(--text-secondary); font-size: 0.875rem;">Page {{ page }} / {{ lastPage }}</span>
        <div class="flex gap-2">
          <button @click="prevPage" :disabled="page <= 1" class="btn-outline btn-sm flex items-center gap-1.5">
            <i class="fas fa-chevron-left"></i> Précédent
          </button>
          <button @click="nextPage" :disabled="page >= lastPage" class="btn-outline btn-sm flex items-center gap-1.5">
            Suivant <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </template>
  </div>
</template>
