<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import adminApi from '@/api/admin'

const router = useRouter()
const logs = ref([])
const loading = ref(true)
const moduleFilter = ref('')
const page = ref(1)
const totalPages = ref(1)

const modules = [
  { value: '', label: 'Tous les modules' },
  { value: 'parcelle', label: 'Parcelle' },
  { value: 'transaction', label: 'Transaction' },
  { value: 'verification', label: 'Vérification' },
  { value: 'document', label: 'Document' },
]

async function fetchLogs(p = 1) {
  loading.value = true
  try {
    const params = { page: p, per_page: 15 }
    if (moduleFilter.value) params.module = moduleFilter.value
    const res = await adminApi.blockchainLogs(params)
    logs.value = (res.data.data || []).filter(Boolean)
    totalPages.value = res.data?.meta?.last_page || 1
    page.value = p
  } catch (e) { console.error('Erreur chargement logs blockchain:', e) }
  loading.value = false
}

watch([moduleFilter], () => fetchLogs(1))
onMounted(() => fetchLogs())

function shortHash(h) {
  if (!h) return '—'
  return h.length > 16 ? h.slice(0, 8) + '…' + h.slice(-8) : h
}
</script>

<template>
  <div class="page-wrap">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Explorer blockchain</h1>
        <p class="page-subtitle">Journal immuable de toutes les actions sur la plateforme</p>
      </div>
    </div>

    <!-- Filter -->
    <div class="flex gap-3 mb-6">
      <select v-model="moduleFilter" class="form-select w-full sm:w-56">
        <option v-for="m in modules" :key="m.value" :value="m.value">{{ m.label }}</option>
      </select>
    </div>

    <div v-if="loading" class="space-y-2">
      <div v-for="i in 8" :key="i" class="skeleton h-16 rounded-xl"></div>
    </div>

    <div v-else-if="logs.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-link"></i></div>
        <p class="empty-title">Aucun enregistrement</p>
        <p class="empty-text">Le journal blockchain est vide pour ce filtre.</p>
      </div>
    </div>

    <div v-else class="card p-0 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-stone-50 border-b border-stone-100">
              <th class="table-header text-left">Action</th>
              <th class="table-header text-left">Hash</th>
              <th class="table-header text-left">Module</th>
              <th class="table-header text-left">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id" class="table-row border-b border-stone-50 last:border-0">
              <td class="table-cell">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-brand/10 flex items-center justify-center text-brand shrink-0">
                    <i class="fas fa-link text-xs"></i>
                  </div>
                  <p class="font-semibold text-stone-900 text-sm">{{ log.action }}</p>
                </div>
              </td>
              <td class="table-cell">
                <code class="text-xs font-mono bg-stone-100 px-2 py-1 rounded text-stone-600">{{ shortHash(log.hash) }}</code>
              </td>
              <td class="table-cell">
                <span class="badge badge-info capitalize">{{ log.module || log.entite || '—' }}</span>
              </td>
              <td class="table-cell text-sm text-stone-400">{{ log.created_at ? new Date(log.created_at).toLocaleString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="totalPages > 1" class="pagination mt-4">
      <button class="page-btn" :disabled="page <= 1" @click="fetchLogs(page - 1)"><i class="fas fa-chevron-left text-xs"></i></button>
      <button v-for="p in Math.min(totalPages, 8)" :key="p" class="page-btn" :class="p === page ? 'active' : ''" @click="fetchLogs(p)">{{ p }}</button>
      <button class="page-btn" :disabled="page >= totalPages" @click="fetchLogs(page + 1)"><i class="fas fa-chevron-right text-xs"></i></button>
    </div>
  </div>
</template>
