<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import notificationApi from '@/api/notification'
const notifications = ref([])
const unreadCount = ref(0)
const open = ref(false)
let interval = null
async function fetchUnread() {
  try { const res = await notificationApi.unreadCount(); unreadCount.value = res.data?.count || 0 } catch { /* ignore */ }
}
async function fetchAll() {
  try { const res = await notificationApi.list(); notifications.value = (res.data || []).filter(Boolean) } catch { /* ignore */ }
}
async function markRead(id) {
  try { await notificationApi.markAsRead(id); await fetchUnread(); await fetchAll() } catch { /* ignore */ }
}
async function markAllRead() {
  try { await notificationApi.markAllAsRead(); unreadCount.value = 0; await fetchAll() } catch { /* ignore */ }
}
function toggle() {
  open.value = !open.value
  if (open.value) fetchAll()
}
onMounted(() => {
  fetchUnread()
  interval = setInterval(fetchUnread, 30000)
})
onBeforeUnmount(() => {
  if (interval) clearInterval(interval)
})
</script>
<template>
  <div class="relative">
    <button @click="toggle" class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors" :style="{ color: 'var(--text-secondary)' }">
      <i class="fas fa-bell text-lg"></i>
      <span v-if="unreadCount > 0" class="absolute -top-0.5 -right-0.5 w-4.5 h-4.5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
    </button>
    <Transition name="fade">
      <div v-if="open" class="absolute right-0 mt-2 w-80 bg-white rounded-lg border overflow-hidden z-50 origin-top-right" :style="{ borderColor: 'var(--border)', boxShadow: 'var(--shadow-lg)' }">
        <div class="flex items-center justify-between px-4 py-3" :style="{ borderBottom: '1px solid var(--border)' }">
          <span class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">Notifications</span>
          <button v-if="unreadCount > 0" @click="markAllRead" class="text-xs flex items-center gap-1" :style="{ color: 'var(--green-tree)' }"><i class="fas fa-check-double"></i> Tout marquer lu</button>
        </div>
        <div class="max-h-72 overflow-y-auto">
          <div v-if="notifications.length === 0" class="p-6 text-center text-sm" :style="{ color: 'var(--text-secondary)' }">Aucune notification</div>
          <div v-for="n in notifications" :key="n.id" @click="!n.lu ? markRead(n.id) : null" :class="['flex items-start gap-3 p-3.5 border-b cursor-pointer transition-colors', n.lu ? 'bg-white' : '']" :style="[n.lu ? {} : { background: 'rgba(45, 106, 79, 0.04)' }, { borderColor: 'var(--border)' }]">
            <div :class="['w-2 h-2 rounded-full mt-1.5 shrink-0', n.lu ? 'bg-transparent' : '']" :style="n.lu ? {} : { background: 'var(--green-tree)' }"></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">{{ n.titre || 'Notification' }}</p>
              <p class="text-xs truncate" :style="{ color: 'var(--text-secondary)' }">{{ n.message || '' }}</p>
              <p class="text-xs mt-0.5" :style="{ color: 'var(--text-tertiary)' }">{{ n.created_at ? new Date(n.created_at).toLocaleString('fr-FR') : '' }}</p>
            </div>
            <button v-if="!n.lu" @click.stop="markRead(n.id)" class="hover:opacity-75" :style="{ color: 'var(--text-secondary)' }"><i class="fas fa-times text-sm"></i></button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
