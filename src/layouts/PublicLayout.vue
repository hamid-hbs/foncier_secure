<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'
import notificationApi from '@/api/notification'
import { confirmLogout } from '@/utils/navigation'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const notifUnread = ref(0)
const showMenu = ref(false)
const mobileOpen = ref(false)
const scrolled = ref(false)
let interval = null

const navLinks = [
  { label: 'Parcelles', to: '/parcelles' },
  { label: 'Observatoire', to: '/observatoire' },
  { label: 'Carte', to: '/cartographie' },
  { label: 'Professionnels', to: '/professionnels' },
]

function getDashboardRoute() {
  const role = auth.userRole
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
  router.push('/')
}

function closeMenu(e) {
  if (!e.target.closest('.profile-menu')) showMenu.value = false
}

function onScroll() {
  scrolled.value = window.scrollY > 12
}

function isActive(to) {
  if (to === '/') return route.path === '/'
  return route.path.startsWith(to)
}

const isAuthPage = computed(() => route.path.startsWith('/auth'))

onMounted(async () => {
  window.addEventListener('scroll', onScroll, { passive: true })
  document.addEventListener('click', closeMenu)
  if (auth.isAuthenticated) {
    try { const r = await notificationApi.nonLues(); notifUnread.value = r.data?.count ?? 0 } catch (e) { console.error('Erreur chargement notifications:', e) }
    interval = setInterval(async () => {
      try { const r = await notificationApi.nonLues(); notifUnread.value = r.data?.count ?? 0 } catch (e) { console.error('Erreur chargement notifications:', e) }
    }, 30000)
  }
})
onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  document.removeEventListener('click', closeMenu)
  if (interval) clearInterval(interval)
})
</script>

<template>
  <div class="min-h-screen flex flex-col bg-page">

    <!-- NAVBAR -->
    <header v-if="!isAuthPage"
      class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
      :class="scrolled
        ? 'bg-white/95 backdrop-blur-xl shadow-sm border-b border-stone-200'
        : 'bg-transparent'"
    >
      <div class="max-w-7xl mx-auto px-5 sm:px-8 h-16 flex items-center justify-between gap-6">

        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-2.5 shrink-0">
          <div class="w-9 h-9 rounded-lg bg-brand flex items-center justify-center shadow-brand">
            <span class="font-display font-extrabold text-white text-sm tracking-tight">FS</span>
          </div>
          <span class="font-display font-extrabold text-lg text-stone-900">Foncier<span class="text-brand">Secure</span></span>
        </router-link>

        <!-- Nav links desktop -->
        <nav class="hidden lg:flex items-center gap-1">
          <router-link
            v-for="l in navLinks"
            :key="l.to"
            :to="l.to"
            class="px-3.5 py-2 rounded-lg text-sm font-semibold transition-all duration-150"
            :class="isActive(l.to)
              ? 'text-brand bg-brand-50'
              : scrolled ? 'text-stone-600 hover:text-stone-900 hover:bg-stone-100' : 'text-stone-700 hover:text-stone-900 hover:bg-white/20'"
          >
            {{ l.label }}
          </router-link>
        </nav>

        <!-- Actions -->
        <div class="flex items-center gap-2 ml-auto">
          <template v-if="auth.isAuthenticated">
            <!-- Bell -->
            <router-link to="/notifications" class="relative p-2.5 rounded-lg hover:bg-stone-100 transition-colors">
              <i class="fas fa-bell text-stone-500"></i>
              <span v-if="notifUnread > 0" class="absolute top-1.5 right-1.5 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">
                {{ notifUnread > 9 ? '9+' : notifUnread }}
              </span>
            </router-link>
            <!-- Avatar menu -->
            <div class="relative profile-menu">
              <button @click.stop="showMenu = !showMenu" class="flex items-center gap-2 pl-1 pr-3 py-1 rounded-xl hover:bg-stone-100 transition-colors">
                <div class="avatar avatar-sm bg-brand">{{ (auth.user?.prenom || 'U')[0] }}{{ (auth.user?.nom || '')[0] }}</div>
                <div class="hidden sm:block text-left">
                  <p class="text-sm font-semibold text-stone-900 leading-tight">{{ auth.user?.prenom }} {{ auth.user?.nom }}</p>
                  <p class="text-xs text-stone-400 capitalize leading-tight">{{ auth.userRole }}</p>
                </div>
                <i class="fas fa-chevron-down text-[10px] text-stone-400 ml-1"></i>
              </button>
              <Transition name="slide">
                <div v-if="showMenu" class="absolute right-0 top-full mt-2 w-52 bg-white rounded-xl shadow-xl border border-stone-100 py-1.5 z-50">
                  <router-link @click="showMenu = false" :to="getDashboardRoute()" class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 hover:bg-stone-50 font-medium">
                    <i class="fas fa-gauge-high w-4 text-stone-400"></i> Tableau de bord
                  </router-link>
                  <router-link @click="showMenu = false" to="/mon-profil" class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 hover:bg-stone-50 font-medium">
                    <i class="fas fa-user w-4 text-stone-400"></i> Mon profil
                  </router-link>
                  <router-link @click="showMenu = false" to="/notifications" class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 hover:bg-stone-50 font-medium">
                    <i class="fas fa-bell w-4 text-stone-400"></i> Notifications
                    <span v-if="notifUnread > 0" class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white bg-red-500">{{ notifUnread }}</span>
                  </router-link>
                  <hr class="my-1 border-stone-100">
                  <button @click="handleLogout" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium w-full text-left text-red-500 hover:bg-red-50">
                    <i class="fas fa-right-from-bracket w-4"></i> Déconnexion
                  </button>
                </div>
              </Transition>
            </div>
          </template>

          <template v-else>
            <router-link to="/auth/login" class="btn btn-ghost btn-sm hidden sm:inline-flex" :class="!scrolled ? 'text-stone-700 hover:bg-white/20' : ''">
              Connexion
            </router-link>
            <router-link to="/auth/register" class="btn btn-primary btn-sm shadow-brand">
              S'inscrire
            </router-link>
          </template>

          <!-- Mobile toggle -->
          <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2.5 rounded-lg hover:bg-stone-100 transition-colors">
            <i :class="mobileOpen ? 'fas fa-times' : 'fas fa-bars'" class="text-stone-700"></i>
          </button>
        </div>
      </div>

      <!-- Mobile nav -->
      <Transition name="slide">
        <div v-if="mobileOpen" class="lg:hidden bg-white border-t border-stone-100 px-5 py-4 space-y-1 shadow-lg">
          <router-link
            v-for="l in navLinks" :key="l.to" :to="l.to"
            @click="mobileOpen = false"
            class="block px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors"
            :class="isActive(l.to) ? 'text-brand bg-brand-50' : 'text-stone-700 hover:bg-stone-50'"
          >{{ l.label }}</router-link>
          <div v-if="!auth.isAuthenticated" class="flex gap-2 pt-2">
            <router-link to="/auth/login" @click="mobileOpen = false" class="btn btn-outline btn-sm flex-1 justify-center">Connexion</router-link>
            <router-link to="/auth/register" @click="mobileOpen = false" class="btn btn-primary btn-sm flex-1 justify-center">S'inscrire</router-link>
          </div>
        </div>
      </Transition>
    </header>

    <!-- PAGE CONTENT -->
    <main class="flex-1" :class="isAuthPage ? '' : 'pt-16'">
      <router-view />
    </main>

    <!-- FOOTER -->
    <footer v-if="!isAuthPage" class="bg-white border-t border-stone-200 text-stone-600 mt-auto">
      <div class="max-w-7xl mx-auto px-5 sm:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
          <div class="sm:col-span-2 lg:col-span-1">
            <div class="flex items-center gap-2.5 mb-4">
              <div class="w-9 h-9 rounded-lg bg-brand flex items-center justify-center shadow-sm">
                <span class="font-display font-extrabold text-white text-sm">FS</span>
              </div>
              <span class="font-display font-extrabold text-stone-900 text-lg">Foncier<span class="text-brand">Secure</span></span>
            </div>
            <p class="text-sm leading-relaxed text-stone-400 max-w-xs">
              La plateforme de référence pour la sécurisation foncière au Bénin. Transparence, confiance, légalité.
            </p>
          </div>
          <div>
            <h4 class="font-display font-bold text-brand text-sm mb-4 uppercase tracking-wider">Plateforme</h4>
            <ul class="space-y-2.5 text-sm">
              <li><router-link to="/parcelles" class="text-stone-500 hover:text-brand transition-colors">Parcelles</router-link></li>
              <li><router-link to="/observatoire" class="text-stone-500 hover:text-brand transition-colors">Observatoire</router-link></li>
              <li><router-link to="/cartographie" class="text-stone-500 hover:text-brand transition-colors">Cartographie</router-link></li>
              <li><router-link to="/professionnels" class="text-stone-500 hover:text-brand transition-colors">Professionnels</router-link></li>
            </ul>
          </div>
          <div>
            <h4 class="font-display font-bold text-brand text-sm mb-4 uppercase tracking-wider">Sécurité</h4>
            <ul class="space-y-2.5 text-sm">
              <li><router-link to="/blockchain/verifier" class="text-stone-500 hover:text-brand transition-colors">Vérificateur blockchain</router-link></li>
              <li><router-link to="/auth/register" class="text-stone-500 hover:text-brand transition-colors">Créer un compte</router-link></li>
            </ul>
          </div>
          <div>
            <h4 class="font-display font-bold text-brand text-sm mb-4 uppercase tracking-wider">Contact</h4>
            <ul class="space-y-2.5 text-sm">
              <li class="flex items-center gap-2"><i class="fas fa-map-pin text-brand w-4"></i> Cotonou, Bénin</li>
              <li class="flex items-center gap-2"><i class="fas fa-envelope text-brand w-4"></i> contact@fonciersecure.bj</li>
            </ul>
          </div>
        </div>
        <div class="border-t border-stone-200 pt-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-stone-400">
          <span>&copy; {{ new Date().getFullYear() }} FoncierSecure Bénin — Tous droits réservés</span>
          <span class="flex items-center gap-1"><i class="fas fa-shield-halved text-brand/60"></i> Sécurisé par blockchain</span>
        </div>
      </div>
    </footer>
  </div>
</template>
