<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'
import { ref, onMounted, onUnmounted } from 'vue'
import notificationApi from '@/api/notification'
import { confirmLogout } from '@/utils/navigation'
const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const modules = [
  { icon: 'fa-shield-alt', title: 'Vérifications', desc: 'Authentifiez vos titres fonciers avec notre système de vérification avancé.', color: 'text-green-500', bg: 'bg-green-50', link: '/parcelles' },
  { icon: 'fa-arrows-left-right', title: 'Transactions', desc: 'Sécurisez chaque étape de vos transactions immobilières.', color: 'text-info', bg: 'bg-blue-50', link: '/parcelles' },
  { icon: 'fa-folder', title: 'Coffre-fort', desc: 'Stockez et partagez vos documents en toute sécurité.', color: 'text-green-500', bg: 'bg-green-50', link: '/parcelles' },
  { icon: 'fa-handshake', title: 'Médiations', desc: 'Résolvez vos conflits à l\'amiable.', color: 'text-green-500', bg: 'bg-green-50', link: '/parcelles' },
]
const notifUnread = ref(0)
const showMenu = ref(false)
let interval = null
function getDashboardRoute() {
  if (!auth.user?.role) return '/mon-profil'
  const role = auth.user.role
  if (role === 'citoyen') return '/tableau-de-bord'

  if (role === 'geometre') return '/geometre/dashboard'
  if (role === 'notaire') return '/notaire/dashboard'
  if (role === 'admin') return '/admin/dashboard'
  return '/mon-profil'
}
function handleLogout() {
  if (!confirmLogout()) return
  showMenu.value = false
  auth.logout()
  router.push('/accueil')
}
function closeMenu(e) {
  if (!e.target.closest('.profile-menu')) showMenu.value = false
}
onMounted(async () => {
  if (auth.isAuthenticated) {
    try { const r = await notificationApi.nonLues(); notifUnread.value = r.data?.count ?? 0 } catch {}
    interval = setInterval(async () => {
      try { const r = await notificationApi.nonLues(); notifUnread.value = r.data?.count ?? 0 } catch {}
    }, 30000)
  }
  document.addEventListener('click', closeMenu)
})
onUnmounted(() => {
  if (interval) clearInterval(interval)
  document.removeEventListener('click', closeMenu)
})
</script>
<template>
  <div class="min-h-screen" style="background: var(--bg-page);">
    <header class="bg-white border-b" style="border-color: var(--border);">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white font-bold text-sm" style="background: var(--green-tree);">FS</div>
          <span class="font-bold text-base" style="color: var(--text-primary);">Foncier<span style="color: var(--green-tree);">Secure</span></span>
        </router-link>
        <div class="flex items-center gap-6 ml-12">
          <router-link to="/parcelles" class="text-sm font-medium" style="color: var(--text-secondary);">Parcelles</router-link>
          <router-link to="/observatoire" class="text-sm font-medium" style="color: var(--text-secondary);">Observatoire</router-link>
          <router-link to="/cartographie" class="text-sm font-medium" style="color: var(--text-secondary);">Carte</router-link>
          <router-link to="/professionnels" class="text-sm font-medium" style="color: var(--text-secondary);">Professionnels</router-link>
        </div>
        <div class="flex items-center gap-3 ml-auto">
          <template v-if="auth.isAuthenticated">
            <router-link to="/notifications" class="relative p-2 rounded-lg hover:bg-gray-100 transition">
              <i class="fas fa-bell" style="color: var(--text-secondary);"></i>
              <span v-if="notifUnread > 0" class="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full text-white text-[10px] flex items-center justify-center font-bold" style="background: var(--danger);">{{ notifUnread > 9 ? '9+' : notifUnread }}</span>
            </router-link>
            <div class="relative profile-menu">
              <button @click.stop="showMenu = !showMenu" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: var(--green-tree);">{{ (auth.user?.nom || auth.user?.name || 'U')[0].toUpperCase() }}</div>
                <span class="text-sm font-medium hidden sm:block" style="color: var(--text-primary);">{{ auth.user?.nom || auth.user?.name || 'Utilisateur' }}</span>
                <i class="fas fa-chevron-down text-xs" style="color: var(--text-secondary);"></i>
              </button>
              <div v-if="showMenu" class="absolute right-0 top-full mt-1 w-56 bg-white rounded-xl shadow-lg border py-1 z-50" style="border-color: var(--border);">
                <router-link @click="showMenu = false" :to="getDashboardRoute()" class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-gray-50" style="color: var(--text-primary);">
                  <i class="fas fa-gauge-high w-4" style="color: var(--text-secondary);"></i> Tableau de bord
                </router-link>
                <router-link @click="showMenu = false" to="/mon-profil" class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-gray-50" style="color: var(--text-primary);">
                  <i class="fas fa-user w-4" style="color: var(--text-secondary);"></i> Mon profil
                </router-link>
                <router-link @click="showMenu = false" to="/notifications" class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-gray-50" style="color: var(--text-primary);">
                  <i class="fas fa-bell w-4" style="color: var(--text-secondary);"></i> Notifications
                  <span v-if="notifUnread > 0" class="ml-auto text-xs font-bold px-1.5 py-0.5 rounded-full text-white" style="background: var(--danger);">{{ notifUnread }}</span>
                </router-link>
                <hr class="my-1" style="border-color: var(--border);">
                <button @click="handleLogout" class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-gray-50 w-full text-left" style="color: var(--danger);">
                  <i class="fas fa-right-from-bracket w-4"></i> Déconnexion
                </button>
              </div>
            </div>
          </template>
          <template v-else>
            <router-link to="/auth/login" class="btn-outline btn-sm">Connexion</router-link>
            <router-link to="/auth/register" class="btn-green btn-sm">S'inscrire</router-link>
          </template>
        </div>
      </div>
    </header>
    <main><router-view /></main>
    <footer class="py-10 text-center text-sm" style="color: var(--text-secondary); background: var(--green-tree-dark); color: rgba(255,255,255,0.7);">
      <div class="max-w-7xl mx-auto px-4">© 2025 FoncierSecure Bénin — Tous droits réservés</div>
    </footer>
  </div>
</template>
