<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import notificationApi from '@/api/notification'

const router = useRouter()
const notifications = ref([])
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const filter = ref('all')

async function fetchNotifications(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 20 }
    if (filter.value === 'unread') params.lu = 0
    const res = await notificationApi.list(params)
    notifications.value = (res.data.data || []).filter(Boolean)
    totalPages.value = res.data?.meta?.last_page || 1
    currentPage.value = page
  } catch (e) { console.error('Erreur chargement notifications:', e) }
  loading.value = false
}

async function markRead(id) {
  try { await notificationApi.markAsRead(id); await fetchNotifications(currentPage.value) } catch (e) { console.error('Erreur marquage lecture:', e) }
}

function typeIcon(type) {
  const icons = { info: 'fa-circle-info', success: 'fa-circle-check', warning: 'fa-triangle-exclamation', error: 'fa-circle-xmark' }
  return icons[type] || 'fa-bell'
}
function typeColor(type) {
  const colors = { info: 'text-brand bg-brand-50', success: 'text-success bg-success-50', warning: 'text-warn bg-warn-50', error: 'text-danger bg-danger-50' }
  return colors[type] || 'text-brand bg-brand-50'
}

onMounted(() => fetchNotifications())
</script>

<template>
  <div class="page-wrap max-w-3xl mx-auto">
    <!-- Header -->
    <div class="flex items-start justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Toutes vos alertes et mises à jour</p>
      </div>
      <div></div>
    </div>

    <!-- Filters -->
    <div class="tabs mb-6">
      <button class="tab" :class="filter === 'all' ? 'active' : ''" @click="filter = 'all'; fetchNotifications(1)">Toutes</button>
      <button class="tab" :class="filter === 'unread' ? 'active' : ''" @click="filter = 'unread'; fetchNotifications(1)">Non lues</button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 5" :key="i" class="skeleton h-16 rounded-xl"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="notifications.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-bell-slash"></i></div>
        <p class="empty-title">Aucune notification</p>
        <p class="empty-text">Vous n'avez pas de notification {{ filter === 'unread' ? 'non lue' : '' }} pour le moment.</p>
      </div>
    </div>

    <!-- List -->
    <div v-else class="space-y-2">
      <div
        v-for="n in notifications"
        :key="n.id"
        @click="!n.lu ? markRead(n.id) : null"
        class="flex items-start gap-4 p-4 rounded-xl border transition-all cursor-pointer"
        :class="n.lu ? 'bg-white border-stone-100 hover:border-stone-200' : 'bg-brand-50/40 border-brand-100 hover:bg-brand-50'"
      >
        <!-- Icon -->
        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-sm" :class="typeColor(n.type)">
          <i :class="['fas', typeIcon(n.type)]"></i>
        </div>
        <!-- Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-2">
            <p class="text-sm font-semibold text-stone-900">{{ n.titre || 'Notification' }}</p>
            <div class="flex items-center gap-2 shrink-0">
              <span v-if="!n.lu" class="w-2 h-2 rounded-full bg-brand shrink-0"></span>
              <span class="text-[11px] text-stone-400 whitespace-nowrap">{{ n.created_at ? new Date(n.created_at).toLocaleString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '' }}</span>
            </div>
          </div>
          <p class="text-sm text-stone-500 mt-0.5 leading-relaxed">{{ n.message || '' }}</p>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="pagination mt-6">
      <button class="page-btn" :disabled="currentPage <= 1" @click="fetchNotifications(currentPage - 1)">
        <i class="fas fa-chevron-left text-xs"></i>
      </button>
      <button v-for="p in totalPages" :key="p" class="page-btn" :class="p === currentPage ? 'active' : ''" @click="fetchNotifications(p)">{{ p }}</button>
      <button class="page-btn" :disabled="currentPage >= totalPages" @click="fetchNotifications(currentPage + 1)">
        <i class="fas fa-chevron-right text-xs"></i>
      </button>
    </div>
  </div>
</template>
