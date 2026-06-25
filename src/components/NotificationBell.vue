<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import notificationApi from '@/api/notification'

const notifications = ref([])
const unreadCount = ref(0)
const open = ref(false)
let interval = null

async function fetchUnread() {
  try { const res = await notificationApi.unreadCount(); unreadCount.value = res.data?.count || 0 } catch (e) { console.error('Erreur notif non lues:', e) }
}
async function fetchAll() {
  try { const res = await notificationApi.list(); notifications.value = (res.data || []).filter(Boolean) } catch (e) { console.error('Erreur liste notifications:', e) }
}
async function markRead(id) {
  try { await notificationApi.markAsRead(id); await fetchUnread(); await fetchAll() } catch (e) { console.error('Erreur marquage lu:', e) }
}
function toggle() {
  open.value = !open.value
  if (open.value) fetchAll()
}

onMounted(() => {
  fetchUnread()
  interval = setInterval(fetchUnread, 30000)
})
onBeforeUnmount(() => { if (interval) clearInterval(interval) })
</script>

<template>
  <div class="relative">
    <button @click="toggle" class="relative p-2.5 rounded-xl text-stone-500 hover:text-stone-900 hover:bg-stone-100 transition-colors">
      <i class="fas fa-bell text-sm"></i>
      <span v-if="unreadCount > 0"
        class="absolute top-1 right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center px-0.5">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <Transition name="slide">
      <div v-if="open" class="absolute right-0 top-full mt-2 w-80 bg-white rounded-2xl border border-stone-100 shadow-xl overflow-hidden z-50 origin-top-right animate-slide-up">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-stone-100">
          <div class="flex items-center gap-2">
            <span class="font-display font-bold text-stone-900 text-sm">Notifications</span>
            <span v-if="unreadCount > 0" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-brand text-white">{{ unreadCount }}</span>
          </div>
          <div></div>
        </div>

        <!-- List -->
        <div class="max-h-72 overflow-y-auto scrollbar-thin">
          <div v-if="notifications.length === 0" class="py-10 text-center">
            <div class="w-10 h-10 rounded-full bg-stone-100 flex items-center justify-center mx-auto mb-2">
              <i class="fas fa-bell text-stone-300"></i>
            </div>
            <p class="text-sm text-stone-400">Aucune notification</p>
          </div>

          <div
            v-for="n in notifications"
            :key="n.id"
            @click="!n.lu ? markRead(n.id) : null"
            class="flex items-start gap-3 px-4 py-3 border-b border-stone-50 cursor-pointer transition-colors last:border-0"
            :class="n.lu ? 'hover:bg-stone-50' : 'bg-brand-50/40 hover:bg-brand-50'"
          >
            <div class="mt-1.5 w-2 h-2 rounded-full shrink-0" :class="n.lu ? 'bg-transparent' : 'bg-brand'"></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-stone-900 leading-tight">{{ n.titre || 'Notification' }}</p>
              <p class="text-xs text-stone-500 mt-0.5 truncate-2">{{ n.message || '' }}</p>
              <p class="text-[10px] text-stone-400 mt-1">{{ n.created_at ? new Date(n.created_at).toLocaleString('fr-FR') : '' }}</p>
            </div>
            <button v-if="!n.lu" @click.stop="markRead(n.id)" class="p-1 rounded text-stone-300 hover:text-stone-500 transition-colors">
              <i class="fas fa-times text-xs"></i>
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Backdrop -->
    <div v-if="open" @click="open = false" class="fixed inset-0 z-40"></div>
  </div>
</template>
