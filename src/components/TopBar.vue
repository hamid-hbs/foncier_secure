<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NotificationBell from '@/components/NotificationBell.vue'
import { confirmLogout } from '@/utils/navigation'

defineProps({
  toggleSidebar: { type: Function, default: () => {} },
  sidebarOpen: { type: Boolean, default: true }
})

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const showProfileMenu = ref(false)

function handleLogout() {
  if (!confirmLogout()) return
  showProfileMenu.value = false
  auth.logout()
  router.push('/')
}

function getPageTitle() {
  const p = route.path
  if (p === '/tableau-de-bord') return 'Tableau de bord'
  if (p.startsWith('/citoyen/parcelles')) return 'Mes parcelles'
  if (p.startsWith('/citoyen/verifications')) return 'Vérifications'
  if (p.startsWith('/citoyen/transactions')) return 'Transactions'
  if (p.startsWith('/citoyen/demandes-achat')) return "Demandes d'achat"
  if (p.startsWith('/citoyen/rendez-vous')) return 'Rendez-vous'
  if (p.startsWith('/citoyen/coffre')) return 'Coffre-fort'
  if (p.startsWith('/citoyen/recherche')) return 'Recherche'
  if (p.startsWith('/support')) return 'Support'
  if (p.startsWith('/notaire/dashboard')) return 'Étude notariale'
  if (p.startsWith('/notaire/transactions')) return 'Transactions notariales'
  if (p.startsWith('/notaire/demandes-achat')) return "Demandes d'achat"
  if (p.startsWith('/notaire/rendez-vous')) return 'Rendez-vous'
  if (p.startsWith('/geometre/dashboard')) return 'Dashboard géomètre'
  if (p.startsWith('/geometre/missions')) return 'Mes missions'
  if (p.startsWith('/admin/dashboard')) return 'Administration'
  if (p.startsWith('/admin/users')) return 'Utilisateurs'
  if (p.startsWith('/admin/role-requests')) return 'Demandes de rôle'
  if (p.startsWith('/admin/localisation')) return 'Localisation'
  if (p.startsWith('/admin/blockchain')) return 'Explorer blockchain'
  if (p.startsWith('/admin/support')) return 'Support'
  if (p === '/mon-profil') return 'Mon profil'
  if (p === '/notifications') return 'Notifications'
  return 'FoncierSecure'
}
</script>

<template>
  <header
    class="fixed top-0 right-0 z-30 h-16 bg-white border-b border-stone-200 transition-all duration-300"
    :style="{ left: sidebarOpen ? 'var(--sidebar-w)' : '0' }"
  >
    <div class="flex items-center h-full px-5 sm:px-7 gap-4">

      <!-- Sidebar toggle -->
      <button
        @click="toggleSidebar"
        class="p-2 rounded-lg text-stone-500 hover:text-stone-900 hover:bg-stone-100 transition-colors"
      >
        <i :class="sidebarOpen ? 'fas fa-indent' : 'fas fa-outdent'" class="text-sm"></i>
      </button>

      <!-- Page title -->
      <div class="hidden sm:block">
        <h1 class="font-display font-bold text-stone-900 text-base leading-tight">{{ getPageTitle() }}</h1>
      </div>

      <!-- Right actions -->
      <div class="ml-auto flex items-center gap-2">

        <NotificationBell />

        <!-- Profile -->
        <div class="relative">
          <button
            @click="showProfileMenu = !showProfileMenu"
            class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl hover:bg-stone-100 transition-colors"
          >
            <div class="w-8 h-8 rounded-full bg-brand flex items-center justify-center font-display font-bold text-white text-xs shrink-0">
              {{ (auth.user?.prenom || '?')[0] }}{{ (auth.user?.nom || '?')[0] }}
            </div>
            <div class="hidden sm:block text-left">
              <p class="text-sm font-semibold text-stone-900 leading-tight">{{ auth.user?.prenom }} {{ auth.user?.nom }}</p>
              <p class="text-[11px] text-stone-400 capitalize">{{ auth.userRole }}</p>
            </div>
            <i class="fas fa-chevron-down text-[9px] text-stone-400"></i>
          </button>

          <Transition name="fade">
            <div v-if="showProfileMenu" @click.self="showProfileMenu = false" class="fixed inset-0 z-10" />
          </Transition>
          <Transition name="slide">
            <div v-if="showProfileMenu" class="absolute right-0 top-full mt-2 w-52 bg-white rounded-xl border border-stone-100 shadow-xl py-1.5 z-20">
              <router-link to="/mon-profil" @click="showProfileMenu = false" class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-700 hover:bg-stone-50 font-medium">
                <i class="fas fa-user w-4 text-stone-400 text-center"></i> Mon profil
              </router-link>
              <hr class="my-1 border-stone-100">
              <button @click="handleLogout" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm font-medium text-left text-red-500 hover:bg-red-50">
                <i class="fas fa-right-from-bracket w-4 text-center"></i> Déconnexion
              </button>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </header>
</template>
