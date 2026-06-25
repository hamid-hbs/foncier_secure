<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { confirmLogout } from '@/utils/navigation'

const props = defineProps({
  open: { type: Boolean, default: true },
  toggle: { type: Function, default: () => {} }
})
const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const menuItems = computed(() => {
  const role = auth.userRole
  const items = []
  if (role === 'citoyen') {
    items.push(
      { label: 'Tableau de bord', icon: 'fa-gauge-high', to: '/tableau-de-bord' },
      { label: 'Mes parcelles', icon: 'fa-map', to: '/citoyen/parcelles' },
      { label: 'Vérifications', icon: 'fa-file-lines', to: '/citoyen/verifications' },
      { label: 'Services géomètre', icon: 'fa-ruler-combined', to: '/citoyen/services-geometre' },
      { label: 'Transactions', icon: 'fa-arrows-left-right', to: '/citoyen/transactions' },
      { label: 'Demandes d\'achat', icon: 'fa-coins', to: '/citoyen/demandes-achat' },
      { label: 'Rendez-vous', icon: 'fa-calendar-check', to: '/citoyen/rendez-vous' },
      { label: 'Coffre-fort', icon: 'fa-lock', to: '/citoyen/coffre' },
      { label: 'Recherche', icon: 'fa-magnifying-glass', to: '/citoyen/recherche' },
      { label: 'Messagerie', icon: 'fa-message', to: '/messagerie' },
      { label: 'Notifications', icon: 'fa-bell', to: '/notifications' },
      { label: 'Support', icon: 'fa-headset', to: '/support/tickets' },
    )
  } else if (role === 'geometre') {
    items.push(
      { label: 'Dashboard', icon: 'fa-gauge-high', to: '/geometre/dashboard' },
      { label: 'Services', icon: 'fa-ruler-combined', to: '/geometre/services-geometre' },
      { label: 'Missions', icon: 'fa-map-location-dot', to: '/geometre/missions' },
      { label: 'Coffre-fort', icon: 'fa-lock', to: '/citoyen/coffre' },
      { label: 'Messagerie', icon: 'fa-message', to: '/messagerie' },
      { label: 'Notifications', icon: 'fa-bell', to: '/notifications' },
      { label: 'Support', icon: 'fa-headset', to: '/support/tickets' },
    )
  } else if (role === 'notaire') {
    items.push(
      { label: 'Dashboard', icon: 'fa-gauge-high', to: '/notaire/dashboard' },
      { label: 'Demandes d\'achat', icon: 'fa-coins', to: '/notaire/demandes-achat' },
      { label: 'Transactions', icon: 'fa-file-signature', to: '/notaire/transactions' },
      { label: 'Rendez-vous', icon: 'fa-calendar-check', to: '/notaire/rendez-vous' },
      { label: 'Coffre-fort', icon: 'fa-lock', to: '/citoyen/coffre' },
      { label: 'Messagerie', icon: 'fa-message', to: '/messagerie' },
      { label: 'Notifications', icon: 'fa-bell', to: '/notifications' },
      { label: 'Support', icon: 'fa-headset', to: '/support/tickets' },
    )
  } else if (role === 'admin') {
    items.push(
      { label: 'Dashboard', icon: 'fa-gauge-high', to: '/admin/dashboard' },
      { label: 'Utilisateurs', icon: 'fa-users', to: '/admin/users' },
      { label: 'Demandes de rôle', icon: 'fa-clipboard-list', to: '/admin/role-requests' },
      { label: 'Localisation', icon: 'fa-map-pin', to: '/admin/localisation' },
      { label: 'Blockchain', icon: 'fa-link', to: '/admin/blockchain' },
      { label: 'Support', icon: 'fa-headset', to: '/admin/support/tickets' },
    )
  }
  return items
})

function isActive(to) {
  if (to === '/mon-profil') return route.path === to
  return route.path.startsWith(to)
}

function handleLogout() {
  if (!confirmLogout()) return
  auth.logout()
  router.push('/')
}

const roleLabel = computed(() => {
  const r = auth.userRole
  if (r === 'citoyen') return 'Citoyen'
  if (r === 'notaire') return 'Notaire'
  if (r === 'geometre') return 'Géomètre'
  if (r === 'admin') return 'Administrateur'
  return r
})
</script>

<template>
  <!-- Overlay mobile -->
  <Transition name="fade">
    <div
      v-if="open"
      @click="props.toggle()"
      class="lg:hidden fixed inset-0 z-40 bg-stone-900/50 backdrop-blur-sm"
    />
  </Transition>

  <!-- Sidebar -->
  <aside
    class="fixed top-0 left-0 z-50 h-full flex flex-col bg-white border-r border-stone-200 transition-all duration-300 overflow-hidden"
    :class="open ? 'translate-x-0' : '-translate-x-full'"
    :style="{ width: 'var(--sidebar-w)' }"
  >
    <!-- Logo header -->
    <div class="flex items-center justify-between h-16 px-5 shrink-0 border-b border-stone-200">
      <router-link to="/" class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-brand flex items-center justify-center shadow-sm">
          <span class="font-display font-extrabold text-white text-xs tracking-tight">FS</span>
        </div>
        <span class="font-display font-extrabold text-stone-900">Foncier<span class="text-brand">Secure</span></span>
      </router-link>
      <button @click="props.toggle()" class="lg:hidden p-1.5 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors">
        <i class="fas fa-times text-sm"></i>
      </button>
    </div>

    <!-- User info -->
    <div class="px-4 py-4 border-b border-stone-200 shrink-0">
      <router-link to="/mon-profil" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-brand-50 transition-colors">
        <div class="w-9 h-9 rounded-full bg-gold flex items-center justify-center font-display font-bold text-brand-dark text-sm shrink-0">
          {{ (auth.user?.prenom || 'U')[0] }}{{ (auth.user?.nom || '')[0] }}
        </div>
        <div class="min-w-0">
          <p class="text-sm font-semibold text-stone-900 truncate">{{ auth.user?.prenom }} {{ auth.user?.nom }}</p>
          <p class="text-xs text-stone-500 capitalize">{{ roleLabel }}</p>
        </div>
      </router-link>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto scrollbar-none px-3 py-3">
      <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-stone-400">Navigation</p>
      <router-link
        v-for="item in menuItems"
        :key="item.to"
        :to="item.to"
        @click="() => { if (window?.innerWidth < 1024) props.toggle() }"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 mb-0.5"
        :class="isActive(item.to)
          ? 'bg-brand-50 text-brand shadow-sm'
          : 'text-stone-600 hover:text-stone-900 hover:bg-stone-100'"
      >
        <i :class="['fas', item.icon, 'w-4 flex-shrink-0 text-center text-sm']"
           :style="isActive(item.to) ? 'color: var(--brand)' : ''"></i>
        <span>{{ item.label }}</span>
        <span v-if="isActive(item.to)" class="ml-auto w-1.5 h-1.5 rounded-full bg-brand shrink-0"></span>
      </router-link>
    </nav>

    <!-- Logout -->
    <div class="px-3 py-4 border-t border-stone-200 shrink-0">
      <button @click="handleLogout" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-stone-400 hover:text-red-500 hover:bg-red-50 transition-all">
        <i class="fas fa-right-from-bracket w-4 text-center"></i>
        <span>Déconnexion</span>
      </button>
    </div>
  </aside>
</template>
