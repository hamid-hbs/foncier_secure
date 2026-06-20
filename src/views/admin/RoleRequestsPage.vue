<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import adminApi from '@/api/admin'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const requests = ref([])
const loading = ref(true)
const processingId = ref(null)

onMounted(fetchRequests)

async function fetchRequests() {
  loading.value = true
  try {
    const res = await adminApi.getRoleRequests()
    const data = res.data?.data || res.data || []
    requests.value = (Array.isArray(data) ? data : []).filter(Boolean)
  } catch { /* ignore */ }
  loading.value = false
}

async function approve(id) {
  processingId.value = id
  try {
    await adminApi.approveRoleRequest(id, { action: 'valide' })
    await fetchRequests()
  } catch { alert('Erreur lors de la validation') }
  processingId.value = null
}

async function reject(id) {
  processingId.value = id
  try {
    await adminApi.approveRoleRequest(id, { action: 'rejete' })
    await fetchRequests()
  } catch { alert('Erreur lors du rejet') }
  processingId.value = null
}
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center gap-3 mb-8">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--gold); opacity: 0.15;">
        <i class="fas fa-clipboard-list" style="color: var(--gold);"></i>
      </div>
      <div>
        <h1 class="section-title">Demandes de changement de rôle</h1>
        <p class="section-subtitle">Validez ou rejetez les demandes des professionnels</p>
      </div>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <div v-else-if="requests.length === 0" class="card text-center py-12">
      <i class="fas fa-clipboard-list mb-3" style="font-size: 2.5rem; color: var(--text-secondary); opacity: 0.5;"></i>
      <p style="color: var(--text-secondary);">Aucune demande en attente.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div v-for="r in requests" :key="r.id" class="card" style="transition: box-shadow 0.2s;" @mouseenter="$event.currentTarget.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)'" @mouseleave="$event.currentTarget.style.boxShadow = ''">
        <div class="flex items-start gap-3 mb-4">
          <div class="flex-center shrink-0" style="width: 40px; height: 40px; border-radius: 50%; background: var(--gold); opacity: 0.2; color: var(--gold);">
            <i class="fas fa-user"></i>
          </div>
          <div class="flex-1">
            <h3 class="font-semibold" style="color: var(--text-primary);">{{ r.user?.nom }} {{ r.user?.prenom }}</h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">{{ r.user?.email }}</p>
          </div>
          <span class="badge" :class="r.statut === 'en_attente' ? 'badge-warning' : r.statut === 'valide' ? 'badge-success' : 'badge-danger'">{{ r.statut }}</span>
        </div>

        <div class="p-3 rounded-lg mb-4" style="background: var(--bg-page);">
          <p class="text-sm" style="color: var(--text-secondary);">Rôle demandé :</p>
          <p class="font-medium text-sm" style="color: var(--text-primary);">{{ r.role_demande }}</p>
        </div>

        <div v-if="r.document_justificatif" class="mb-4">
          <a :href="r.document_justificatif" target="_blank" class="flex items-center gap-2 text-sm font-medium" style="color: var(--green-tree);">
            <i class="fas fa-file-lines"></i> Voir le justificatif
          </a>
        </div>

        <div v-if="r.statut === 'en_attente'" class="flex gap-2">
          <button @click="approve(r.id)" :disabled="processingId === r.id" class="btn-green btn-sm flex items-center gap-1.5">
            <i class="fas fa-circle-check"></i> {{ processingId === r.id ? '...' : 'Valider' }}
          </button>
          <button @click="reject(r.id)" :disabled="processingId === r.id" class="btn-danger btn-sm flex items-center gap-1.5">
            <i class="fas fa-circle-xmark"></i> {{ processingId === r.id ? '...' : 'Rejeter' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
