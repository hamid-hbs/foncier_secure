<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import transactionApi from '@/api/transaction'
import rendezVousApi from '@/api/rendezVous'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const auth = useAuthStore()
const rendezVous = ref([])
const loading = ref(true)
const actionLoading = ref(false)

onMounted(async () => {
  try {
    const res = await transactionApi.list()
    const transactions = res.data?.data ?? []
    const all = []
    for (const t of transactions) {
      try {
        const r = await rendezVousApi.index(t.id)
        const items = r.data?.data ?? []
        all.push(...items.map(item => ({ ...item, transaction: t })))
      } catch { }
    }
    rendezVous.value = all.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  } catch { }
  finally { loading.value = false }
})

async function confirmer(id) {
  if (!confirm('Confirmer votre présence à ce rendez-vous ?')) return
  actionLoading.value = true
  try {
    await rendezVousApi.confirmer(id, { user_id: auth.user.id })
    rendezVous.value = rendezVous.value.map(r => r.id === id ? { ...r, statut: 'confirme' } : r)
  } catch { alert("Erreur lors de la confirmation") }
  finally { actionLoading.value = false }
}

async function annuler(id) {
  if (!confirm('Annuler ce rendez-vous ?')) return
  actionLoading.value = true
  try {
    await rendezVousApi.updateStatut(id, { statut: 'annule' })
    rendezVous.value = rendezVous.value.filter(r => r.id !== id)
  } catch { alert("Erreur lors de l'annulation") }
  finally { actionLoading.value = false }
}
</script>
<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <h1 class="section-title mb-6">Mes rendez-vous</h1>
    <div v-if="loading" class="flex-center py-8"><i class="fas fa-spinner fa-spin text-2xl" style="color: var(--text-secondary);"></i></div>
    <div v-else-if="rendezVous.length === 0" class="card p-8 text-center" style="color: var(--text-secondary);">
      <i class="fas fa-calendar text-4xl mb-3 opacity-40"></i>
      <p>Aucun rendez-vous pour le moment.</p>
    </div>
    <div v-else class="space-y-3">
      <div v-for="r in rendezVous" :key="r.id" class="card p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium text-sm" style="color: var(--text-primary);">{{ r.type || 'Rendez-vous' }} — {{ new Date(r.date_rendezvous || r.date).toLocaleDateString('fr-FR') }}</p>
            <p v-if="r.lieu" class="text-xs mt-1" style="color: var(--text-secondary);">{{ r.lieu }}</p>
            <p v-if="r.transaction" class="text-xs mt-1" style="color: var(--text-secondary);">Transaction #{{ r.transaction.id }}</p>
          </div>
          <div class="flex items-center gap-2">
            <span v-if="r.statut === 'confirme'" class="badge badge-success">Confirmé</span>
            <span v-else-if="r.statut === 'annule'" class="badge badge-danger">Annulé</span>
            <span v-else class="badge badge-warning">En attente</span>
            <button v-if="r.statut !== 'confirme' && r.statut !== 'annule'" @click="confirmer(r.id)" :disabled="actionLoading" class="btn-sm" style="background: var(--green-tree); color: #fff; border: none; border-radius: 0.375rem; padding: 0.25rem 0.5rem;">
              <i class="fas fa-check mr-1"></i> Confirmer
            </button>
            <button v-if="r.statut !== 'annule'" @click="annuler(r.id)" :disabled="actionLoading" class="btn-sm" style="background: var(--danger); color: #fff; border: none; border-radius: 0.375rem; padding: 0.25rem 0.5rem;">
              <i class="fas fa-times mr-1"></i> Annuler
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
