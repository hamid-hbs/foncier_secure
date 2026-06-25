<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import adminApi from '@/api/admin'

const route = useRoute()
const router = useRouter()
const user = ref(null)
const loading = ref(true)
const toggling = ref(false)

async function fetchUser() {
  loading.value = true
  try {
    const res = await adminApi.getUsers({ page: 1, per_page: 100 })
    const users = res.data?.data || res.data || []
    user.value = users.find(u => u.id == route.params.id) || null
  } catch (e) { console.error(e) }
  loading.value = false
}

async function toggleActive() {
  if (!user.value) return
  toggling.value = true
  try {
    await adminApi.toggleUserStatus(user.value.id)
    user.value.is_active = !user.value.is_active
  } catch (e) { console.error(e) }
  toggling.value = false
}

function getRoleCode(role) {
  return typeof role === 'object' ? role?.code : role
}

onMounted(fetchUser)
</script>

<template>
  <div class="page-wrap max-w-2xl mx-auto">
    <button @click="router.push('/admin/users')" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
      <i class="fas fa-arrow-left text-xs"></i> Retour aux utilisateurs
    </button>

    <div v-if="loading" class="space-y-3">
      <div class="skeleton h-8 w-48 rounded-lg"></div>
      <div class="skeleton h-40 rounded-xl"></div>
    </div>

    <div v-else-if="!user" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-user-slash"></i></div>
        <p class="empty-title">Utilisateur introuvable</p>
      </div>
    </div>

    <template v-else>
      <div class="card mb-6">
        <div class="flex items-center gap-5 pb-6 mb-6 border-b border-stone-100">
          <div class="w-16 h-16 rounded-2xl bg-brand flex items-center justify-center font-display font-extrabold text-white text-xl shrink-0">
            {{ (user.prenom || '?')[0] }}{{ (user.nom || '?')[0] }}
          </div>
          <div class="flex-1">
            <h1 class="font-display font-bold text-2xl text-stone-900">{{ user.prenom }} {{ user.nom }}</h1>
            <span class="badge capitalize" :class="user.is_active ? 'badge-success' : 'badge-danger'">
              {{ user.is_active ? 'Actif' : 'Inactif' }}
            </span>
            <span class="badge capitalize ml-2" :class="user.is_active ? 'badge-info' : 'badge-neutral'">{{ getRoleCode(user.role) }}</span>
          </div>
          <button @click="toggleActive" class="btn" :class="user.is_active ? 'btn-outline-danger' : 'btn-outline-success'" :disabled="toggling">
            <i :class="['fas', user.is_active ? 'fa-user-slash' : 'fa-user-check']"></i>
            {{ user.is_active ? 'Désactiver' : 'Activer' }}
          </button>
        </div>

        <div class="space-y-3">
          <div class="flex items-center gap-3 py-2.5 border-b border-stone-100">
            <i class="fas fa-envelope w-5 text-center text-stone-400 shrink-0"></i>
            <span class="text-sm text-stone-500 font-medium min-w-[100px]">Email</span>
            <span class="text-sm text-stone-900 font-semibold">{{ user.email }}</span>
          </div>
          <div class="flex items-center gap-3 py-2.5 border-b border-stone-100">
            <i class="fas fa-phone w-5 text-center text-stone-400 shrink-0"></i>
            <span class="text-sm text-stone-500 font-medium min-w-[100px]">Téléphone</span>
            <span class="text-sm text-stone-900 font-semibold">{{ user.telephone || '—' }}</span>
          </div>
          <div class="flex items-center gap-3 py-2.5 border-b border-stone-100">
            <i class="fas fa-tag w-5 text-center text-stone-400 shrink-0"></i>
            <span class="text-sm text-stone-500 font-medium min-w-[100px]">Rôle</span>
            <span class="text-sm text-stone-900 font-semibold capitalize">{{ getRoleCode(user.role) }}</span>
          </div>
          <div class="flex items-center gap-3 py-2.5 border-b border-stone-100">
            <i class="fas fa-shield-halved w-5 text-center text-stone-400 shrink-0"></i>
            <span class="text-sm text-stone-500 font-medium min-w-[100px]">Statut</span>
            <span class="badge" :class="user.is_active ? 'badge-success' : 'badge-danger'">{{ user.is_active ? 'Actif' : 'Inactif' }}</span>
          </div>
          <div class="flex items-center gap-3 py-2.5">
            <i class="fas fa-calendar w-5 text-center text-stone-400 shrink-0"></i>
            <span class="text-sm text-stone-500 font-medium min-w-[100px]">Inscrit le</span>
            <span class="text-sm text-stone-900 font-semibold">{{ user.created_at ? new Date(user.created_at).toLocaleDateString('fr-FR') : '—' }}</span>
          </div>
        </div>
      </div>

      <div v-if="user.professionnel" class="card">
        <h3 class="font-display font-bold text-stone-900 mb-4">Informations professionnelles</h3>
        <div class="space-y-3">
          <div class="flex items-center gap-3 py-2.5 border-b border-stone-100">
            <span class="text-sm text-stone-500 font-medium min-w-[140px]">Type</span>
            <span class="text-sm text-stone-900 font-semibold capitalize">{{ user.professionnel.type }}</span>
          </div>
          <div v-if="user.professionnel.cabinet" class="flex items-center gap-3 py-2.5 border-b border-stone-100">
            <span class="text-sm text-stone-500 font-medium min-w-[140px]">Cabinet</span>
            <span class="text-sm text-stone-900 font-semibold">{{ user.professionnel.cabinet }}</span>
          </div>
          <div v-if="user.professionnel.numero_agrement" class="flex items-center gap-3 py-2.5 border-b border-stone-100">
            <span class="text-sm text-stone-500 font-medium min-w-[140px]">N° d'agrément</span>
            <span class="text-sm text-stone-900 font-semibold">{{ user.professionnel.numero_agrement }}</span>
          </div>
          <div v-if="user.professionnel.zone_intervention" class="flex items-center gap-3 py-2.5">
            <span class="text-sm text-stone-500 font-medium min-w-[140px]">Zone</span>
            <span class="text-sm text-stone-900 font-semibold">{{ user.professionnel.zone_intervention }}</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>