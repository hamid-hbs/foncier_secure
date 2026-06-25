<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import adminApi from '@/api/admin'

function getRoleCode(role) {
  return typeof role === 'object' ? role?.code : role
}

const router = useRouter()
const pendingUsers = ref([])
const loading = ref(true)
const processingId = ref(null)

onMounted(async () => {
  try {
    const res = await adminApi.getPendingUsers()
    pendingUsers.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement utilisateurs en attente:', e) }
  loading.value = false
})

async function approve(id) {
  processingId.value = id
  try {
    await adminApi.approveUser(id)
    pendingUsers.value = pendingUsers.value.filter(u => u.id !== id)
  } catch (e) { console.error('Erreur approbation:', e) }
  processingId.value = null
}

async function reject(id) {
  if (!confirm('Désactiver cet utilisateur ?')) return
  processingId.value = id
  try {
    await adminApi.toggleUserStatus(id)
    pendingUsers.value = pendingUsers.value.filter(u => u.id !== id)
  } catch (e) { console.error('Erreur désactivation:', e) }
  processingId.value = null
}
</script>

<template>
  <div class="page-wrap">
    <div class="flex items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Utilisateurs en attente</h1>
        <p class="page-subtitle">Approuvez ou refusez les nouveaux comptes utilisateurs</p>
      </div>
      <span v-if="pendingUsers.length > 0" class="badge badge-warning text-sm py-1.5 px-3">{{ pendingUsers.length }} en attente</span>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="skeleton h-24 rounded-2xl"></div>
    </div>

    <div v-else-if="pendingUsers.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-clipboard-check"></i></div>
        <p class="empty-title">Aucun utilisateur en attente</p>
        <p class="empty-text">Tous les comptes ont été vérifiés.</p>
      </div>
    </div>

    <div v-else class="space-y-4">
      <div v-for="user in pendingUsers" :key="user.id" class="card">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
          <!-- Avatar -->
          <div class="flex items-center gap-3">
            <div class="avatar bg-brand shrink-0">{{ (user.prenom || 'U')[0] }}{{ (user.nom || '')[0] }}</div>
            <div>
              <p class="font-display font-bold text-stone-900">{{ user.prenom }} {{ user.nom }}</p>
              <p class="text-sm text-stone-400">{{ user.email }}</p>
            </div>
          </div>

          <!-- Info -->
          <div class="flex-1 sm:px-5">
            <div class="flex flex-wrap items-center gap-3 mb-2">
              <span class="badge badge-neutral">{{ getRoleCode(user.role) || 'citoyen' }}</span>
            </div>
            <p class="text-xs text-stone-400 mt-1">
              <i class="fas fa-calendar mr-1"></i>{{ user.created_at ? new Date(user.created_at).toLocaleDateString('fr-FR') : '' }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 shrink-0">
            <button
              @click="approve(user.id)"
              class="btn btn-success btn-sm"
              :disabled="processingId === user.id"
            >
              <div v-if="processingId === user.id" class="spinner spinner-sm border-white/30 border-t-white"></div>
              <i v-else class="fas fa-check"></i>
              Approuver
            </button>
            <button
              @click="reject(user.id)"
              class="btn btn-ghost btn-sm text-danger hover:bg-danger/10"
              :disabled="processingId === user.id"
            >
              <i class="fas fa-times"></i> Refuser
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
