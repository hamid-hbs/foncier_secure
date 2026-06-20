<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import notificationApi from '@/api/notification'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const notifications = ref([])
const loading = ref(true)
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)

const iconMap = {
  info: 'fa-bell',
  success: 'fa-check-circle',
  warning: 'fa-exclamation-triangle',
  error: 'fa-times-circle',
}

const colorMap = {
  info: '#457B9D',
  success: 'var(--green-tree)',
  warning: 'var(--gold)',
  error: '#DC2626',
}

const bgMap = {
  info: '#EBF4F8',
  success: '#F0F7F4',
  warning: '#FEF7EF',
  error: '#FEE2E2',
}

function getIcon(n) {
  return iconMap[n.type] || 'fa-bell'
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const now = new Date()
  const diff = now - d
  const mins = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)
  if (mins < 1) return 'À l\'instant'
  if (mins < 60) return `Il y a ${mins} min`
  if (hours < 24) return `Il y a ${hours}h`
  if (days < 7) return `Il y a ${days}j`
  return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
}

const unreadCount = ref(0)

async function fetchNotifications() {
  loading.value = true
  try {
    const res = await notificationApi.list({ page: currentPage.value })
    const data = res.data
    if (data.data) {
      notifications.value = (data.data || []).filter(Boolean)
      currentPage.value = data.current_page || 1
      lastPage.value = data.last_page || 1
      total.value = data.total || 0
    } else if (Array.isArray(data)) {
      notifications.value = data.filter(Boolean)
      lastPage.value = 1
      total.value = notifications.value.length
    }
    unreadCount.value = notifications.value.filter(n => !n.read_at && !n.lu).length
  } catch {}
  loading.value = false
}

async function markAsRead(n) {
  if (n.read_at || n.lu) return
  try {
    await notificationApi.markAsRead(n.id)
    n.read_at = new Date().toISOString()
    n.lu = true
    unreadCount.value = notifications.value.filter(n => !n.read_at && !n.lu).length
  } catch {}
}

function changePage(page) {
  if (page < 1 || page > lastPage.value) return
  currentPage.value = page
  fetchNotifications()
}

onMounted(fetchNotifications)
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #F0F7F4; color: var(--green-tree);">
          <i class="fas fa-bell"></i>
        </div>
        <div>
          <h1 class="section-title">Notifications</h1>
          <p v-if="unreadCount > 0" class="section-subtitle">{{ unreadCount }} non {{ unreadCount > 1 ? 'lues' : 'lue' }}</p>
        </div>
      </div>
    </div>

    <div v-if="loading && notifications.length === 0" class="text-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin mx-auto" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <div v-else-if="notifications.length === 0" class="card text-center py-12">
      <i class="fas fa-bell mb-3" style="color: #D1D5DB; font-size: 3rem;"></i>
      <p style="color: var(--text-secondary);">Aucune notification pour le moment.</p>
    </div>

    <div v-else class="space-y-3">
      <div v-for="n in notifications" :key="n.id"
        class="card transition-all duration-200"
        :style="(!n.read_at && !n.lu) ? { borderLeft: '4px solid var(--green-tree)', cursor: 'pointer' } : {}"
        @click="markAsRead(n)">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" :style="{ background: bgMap[n.type] || '#F0F7F4' }">
            <i :class="['fas', getIcon(n)]" :style="{ color: colorMap[n.type] || 'var(--text-secondary)' }"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <div>
                <p class="text-sm font-semibold" :style="{ color: (!n.read_at && !n.lu) ? 'var(--text-primary)' : 'var(--text-secondary)' }">{{ n.titre || n.title || 'Notification' }}</p>
                <p v-if="n.message" class="text-sm mt-0.5" style="color: var(--text-secondary);">{{ n.message }}</p>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-xs whitespace-nowrap" style="color: #9CA3AF;">{{ formatDate(n.created_at) }}</span>
                <i v-if="n.read_at || n.lu" class="fas fa-envelope-open" style="color: #9CA3AF;"></i>
                <i v-else class="fas fa-envelope" style="color: var(--green-tree);"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="lastPage > 1" class="flex items-center justify-center gap-2 mt-8">
      <button class="btn-outline btn-sm" :disabled="currentPage <= 1" @click="changePage(currentPage - 1)">Précédent</button>
      <template v-for="page in lastPage" :key="page">
        <button v-if="page === currentPage || page === 1 || page === lastPage || Math.abs(page - currentPage) <= 1"
          class="btn-sm font-medium transition-all"
          :style="page === currentPage ? { background: 'var(--green-tree)', color: 'white', border: 'none' } : { background: 'transparent', color: 'var(--text-secondary)', border: '1px solid var(--border)' }"
          @click="changePage(page)">
          {{ page }}
        </button>
        <span v-else-if="page === currentPage - 2 || page === currentPage + 2" key="dots" style="color: var(--text-secondary);">...</span>
      </template>
      <button class="btn-outline btn-sm" :disabled="currentPage >= lastPage" @click="changePage(currentPage + 1)">Suivant</button>
    </div>
  </div>
</template>
