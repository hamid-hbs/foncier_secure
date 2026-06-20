<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import adminApi from '@/api/admin'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const logs = ref([])
const loading = ref(true)
const moduleFilter = ref('')
const referenceId = ref('')
const page = ref(1)
const lastPage = ref(1)

onMounted(() => fetchLogs())

watch([moduleFilter, referenceId], () => {
  page.value = 1
  fetchLogs()
})

async function fetchLogs() {
  loading.value = true
  try {
    let res
    if (moduleFilter.value) {
      res = await adminApi.blockchainModule(moduleFilter.value, referenceId.value || undefined)
    } else {
      res = await adminApi.blockchainLogs()
    }
    const data = res.data?.data || res.data || []
    logs.value = (Array.isArray(data) ? data : []).filter(Boolean)
    lastPage.value = res.data?.meta?.last_page || res.data?.last_page || 1
  } catch { logs.value = [] }
  loading.value = false
}

function formatDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center gap-3 mb-8">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--green-tree); opacity: 0.15;">
        <i class="fas fa-shield-alt" style="color: var(--green-tree);"></i>
      </div>
      <div>
        <h1 class="section-title">Registre blockchain</h1>
        <p class="section-subtitle">Consultez les logs et vérifiez l'intégrité de la chaîne</p>
      </div>
    </div>

    <div class="card mb-6">
      <h3 class="section-title mb-4" style="font-size: 1rem;">
        <i class="fas fa-filter" style="color: var(--green-tree);"></i> Filtres
      </h3>
      <div class="flex flex-wrap gap-4">
        <div>
          <label class="form-label">Module</label>
          <select v-model="moduleFilter" class="form-select">
            <option value="">Tous les modules</option>
            <option value="transaction">Transaction</option>
            <option value="parcelle">Parcelle</option>
            <option value="verification">Vérification</option>
            <option value="utilisateur">Utilisateur</option>
          </select>
        </div>
        <div v-if="moduleFilter">
          <label class="form-label">ID de référence</label>
          <input v-model="referenceId" class="form-input" style="width: 160px;" placeholder="ID..." />
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <div v-else-if="logs.length === 0" class="card text-center py-12">
      <i class="fas fa-shield-alt mb-3" style="font-size: 2.5rem; color: var(--text-secondary); opacity: 0.5;"></i>
      <p style="color: var(--text-secondary);">Aucun log blockchain disponible.</p>
    </div>

    <div v-else class="table-wrap">
      <table class="w-full">
        <thead>
          <tr>
            <th class="table-header">ID</th>
            <th class="table-header">Action</th>
            <th class="table-header">Utilisateur</th>
            <th class="table-header">Module</th>
            <th class="table-header">Référence</th>
            <th class="table-header">Current Hash</th>
            <th class="table-header">Previous Hash</th>
            <th class="table-header">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y" style="border-color: var(--border);">
          <tr v-for="l in logs" :key="l.id" style="transition: background 0.15s;" @mouseenter="$event.currentTarget.style.background = 'var(--bg-page)'" @mouseleave="$event.currentTarget.style.background = ''">
            <td class="table-cell font-mono text-xs" style="color: var(--text-secondary);">{{ l.id }}</td>
            <td class="table-cell font-medium" style="color: var(--text-primary);">{{ l.action }}</td>
            <td class="table-cell">{{ l.user?.nom || l.user || '-' }}</td>
            <td class="table-cell">
              <span class="badge badge-info">{{ l.module }}</span>
            </td>
            <td class="table-cell font-mono text-xs" style="color: var(--text-secondary);">{{ l.reference_id || '-' }}</td>
            <td class="table-cell font-mono text-xs" style="max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-secondary);" :title="l.current_hash">{{ l.current_hash ? l.current_hash.substring(0, 16) + '...' : '-' }}</td>
            <td class="table-cell font-mono text-xs" style="max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-secondary);" :title="l.previous_hash">{{ l.previous_hash ? l.previous_hash.substring(0, 16) + '...' : '-' }}</td>
            <td class="table-cell" style="color: var(--text-secondary); white-space: nowrap;">{{ formatDate(l.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
