<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import verificationApi from '@/api/verification'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const verifications = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await verificationApi.list()
    verifications.value = (res.data?.data || []).filter(Boolean)
  } catch { }
  loading.value = false
})

function statutBadge(s) {
  const map = { en_attente: 'badge-warning', en_cours: 'badge-info', terminee: 'badge-success', rejetee: 'badge-danger' }
  return map[s] || 'badge-info'
}
</script>
<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #D1FAE5;">
        <i class="fas fa-check-circle" style="color: var(--green-tree);"></i>
      </div>
      <div>
        <h1 class="section-title">Mes vérifications</h1>
        <p class="section-subtitle">Consultez l'avancement des vérifications sur vos parcelles</p>
      </div>
    </div>
    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--border); border-top-color: var(--green-tree);"></div>
    </div>
    <div v-else-if="verifications.length === 0" class="card text-center py-12">
      <i class="fas fa-check-circle text-4xl mb-3" style="color: var(--border);"></i>
      <p style="color: var(--text-secondary);">Aucune vérification pour le moment. Les vérifications sont initiées par le notaire lors d'une transaction.</p>
    </div>
    <div v-else class="space-y-3">
      <div v-for="v in verifications" :key="v.id" class="card p-4 cursor-pointer hover:shadow-md transition" @click="router.push({ name: 'VerificationDetail', params: { id: v.id } })">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium text-sm" style="color: var(--text-primary);">{{ v.parcelle?.titre || v.parcelle?.code || 'Parcelle #' + v.parcelle_id }}</p>
            <p v-if="v.type_verification" class="text-xs mt-1" style="color: var(--text-secondary);">{{ v.type_verification }}</p>
            <p class="text-xs" style="color: var(--text-secondary);">{{ new Date(v.created_at).toLocaleDateString('fr-FR') }}</p>
          </div>
          <span class="badge" :class="statutBadge(v.statut)">{{ v.statut }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
