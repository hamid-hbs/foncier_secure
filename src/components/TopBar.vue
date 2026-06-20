<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NotificationBell from '@/components/NotificationBell.vue'
import { confirmLogout } from '@/utils/navigation'
defineProps({
  toggleSidebar: { type: Function, default: () => {} },
  sidebarOpen: { type: Boolean, default: true }
})
const router = useRouter()
const auth = useAuthStore()
const searchQuery = ref('')
const showProfileMenu = ref(false)
function handleLogout() {
  if (!confirmLogout()) return
  showProfileMenu.value = false
  auth.logout()
  router.push('/accueil')
}
</script>
<template>
  <header class="fixed top-0 right-0 z-30 h-16 bg-white/80 backdrop-blur-xl border-b transition-all duration-300" :style="{ borderColor: 'var(--border)', left: sidebarOpen ? 'var(--sidebar-width)' : '0' }">
    <div class="flex items-center h-full px-4 sm:px-6 lg:px-8">
      <button @click="toggleSidebar" class="p-2 rounded-lg hover:bg-gray-100 transition-all mr-3 shrink-0" :style="{ color: 'var(--text-secondary)' }">
        <i :class="['fas', sidebarOpen ? 'fa-times' : 'fa-bars']" class="w-5 h-5"></i>
      </button>
      <div class="flex-1 max-w-md">
        <div class="relative">
          <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-sm" :style="{ color: 'var(--text-secondary)' }"></i>
          <input v-model="searchQuery" type="text" placeholder="Rechercher..." class="form-input pl-10 py-2" />
        </div>
      </div>
      <div class="ml-auto flex items-center gap-3">
        <NotificationBell />
        <div class="relative">
          <button @click="showProfileMenu = !showProfileMenu" class="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-gray-100 transition-all">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-semibold text-xs" :style="{ background: 'var(--green-tree)' }">
              {{ (auth.user?.prenom || '?')[0] }}{{ (auth.user?.nom || '?')[0] }}
            </div>
            <div class="hidden sm:block text-left">
              <p class="text-sm font-medium leading-tight" :style="{ color: 'var(--text-primary)' }">{{ auth.user?.prenom }} {{ auth.user?.nom }}</p>
              <p class="text-xs capitalize" :style="{ color: 'var(--text-secondary)' }">{{ auth.user?.role || 'Citoyen' }}</p>
            </div>
          </button>
          <Transition name="fade">
            <div v-if="showProfileMenu" @click="showProfileMenu = false" class="fixed inset-0 z-10" />
          </Transition>
          <Transition name="slide">
            <div v-if="showProfileMenu" class="absolute right-0 top-full mt-2 w-56 bg-white rounded-lg border py-2 z-20" :style="{ borderColor: 'var(--border)', boxShadow: 'var(--shadow-lg)' }">
              <router-link to="/mon-profil" @click="showProfileMenu = false" class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors" :style="{ color: 'var(--text-primary)' }" @mouseenter="$event.target.style.background = 'var(--bg-page)'" @mouseleave="$event.target.style.background = 'transparent'">
                <i class="fas fa-user w-4 h-4" :style="{ color: 'var(--text-secondary)' }"></i> Mon profil
              </router-link>
              <hr class="my-1" :style="{ borderColor: 'var(--border)' }" />
              <button @click="handleLogout" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm transition-colors" :style="{ color: 'var(--danger)' }" @mouseenter="$event.target.style.background = '#FEE2E2'" @mouseleave="$event.target.style.background = 'transparent'">
                <i class="fas fa-right-from-bracket w-4 h-4"></i> Déconnexion
              </button>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </header>
</template>
