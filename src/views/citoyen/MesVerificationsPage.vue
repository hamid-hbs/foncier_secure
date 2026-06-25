<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import verificationApi from '@/api/verification'

const router = useRouter()
const verifications = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await verificationApi.list()
    verifications.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement vérifications:', e) }
  loading.value = false
})

function statutBadgeClass(s) {
  const map = { soumise: 'badge-neutral', en_analyse: 'badge-info', mission_assignee: 'badge-purple', terminee: 'badge-success', validee: 'badge-success', rejetee: 'badge-danger' }
  return map[s] || 'badge-neutral'
}
function statutLabel(s) {
  const map = { soumise: 'Soumise', en_analyse: 'En analyse', mission_assignee: 'Assignée', terminee: 'Terminée', validee: 'Validée', rejetee: 'Rejetée' }
  return map[s] || s
}
</script>

<template>
  <div class="page-wrap">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Mes vérifications</h1>
        <p class="page-subtitle">Suivez l'état des vérifications de vos parcelles</p>
      </div>
    </div>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="skeleton h-20 rounded-2xl"></div>
    </div>

    <div v-else-if="verifications.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-shield-check"></i></div>
        <p class="empty-title">Aucune vérification en cours</p>
        <p class="empty-text">Les vérifications apparaîtront ici une fois vos parcelles soumises à l'analyse.</p>
      </div>
    </div>

    <div v-else class="card p-0 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-stone-50 border-b border-stone-100">
              <th class="table-header text-left">Parcelle</th>
              <th class="table-header text-left">Géomètre</th>
              <th class="table-header text-left">Statut</th>
              <th class="table-header text-left">Date</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="v in verifications" :key="v.id"
              @click="router.push({ name: 'VerificationDetail', params: { id: v.id } })"
              class="table-row cursor-pointer border-b border-stone-50 last:border-0"
            >
              <td class="table-cell">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center text-brand shrink-0">
                    <i class="fas fa-map-marker-alt text-xs"></i>
                  </div>
                  <div>
                    <p class="font-semibold text-stone-900 text-sm">{{ v.parcelle?.titre || v.parcelle?.code || 'Parcelle #' + (v.parcelle_id || v.id) }}</p>
                    <p class="text-xs text-stone-400">{{ v.parcelle?.commune?.nom || '' }}</p>
                  </div>
                </div>
              </td>
              <td class="table-cell text-sm text-stone-600">{{ v.geometre?.prenom || '' }} {{ v.geometre?.nom || '—' }}</td>
              <td class="table-cell"><span class="badge" :class="statutBadgeClass(v.statut)">{{ statutLabel(v.statut) }}</span></td>
              <td class="table-cell text-sm text-stone-400">{{ v.created_at ? new Date(v.created_at).toLocaleDateString('fr-FR') : '—' }}</td>
              <td class="table-cell text-right">
                <i class="fas fa-chevron-right text-xs text-stone-300"></i>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
