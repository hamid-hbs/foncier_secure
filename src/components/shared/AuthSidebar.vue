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
  const role = auth.user?.role
  const items = []
  if (role === 'citoyen') {
    items.push(
      { label: 'Tableau de bord', icon: 'fa-gauge', to: '/tableau-de-bord' },
      { label: 'Mes parcelles', icon: 'fa-map', to: '/citoyen/parcelles' },
      { label: 'Vérifications', icon: 'fa-check-circle', to: '/citoyen/verifications' },
      { label: 'Transactions', icon: 'fa-arrows-left-right', to: '/citoyen/transactions' },
      { label: 'Demandes d\'achat', icon: 'fa-cart-shopping', to: '/citoyen/demandes-achat' },
      { label: 'Rendez-vous', icon: 'fa-calendar', to: '/citoyen/rendez-vous' },
      { label: 'Coffre-fort', icon: 'fa-folder', to: '/citoyen/coffre' },
      { label: 'Support', icon: 'fa-ticket', to: '/support/tickets' },
      { label: 'Recherche', icon: 'fa-search', to: '/citoyen/recherche' },
      { label: 'Mon profil', icon: 'fa-user', to: '/mon-profil' },
    )
  } else if (role === 'geometre') {
    items.push(
      { label: 'Dashboard', icon: 'fa-gauge', to: '/geometre/dashboard' },
      { label: 'Missions', icon: 'fa-map', to: '/geometre/missions' },
      { label: 'Mon profil', icon: 'fa-user', to: '/mon-profil' },
    )
  } else if (role === 'notaire') {
    items.push(
      { label: 'Dashboard', icon: 'fa-gauge', to: '/notaire/dashboard' },
      { label: 'Demandes d\'achat', icon: 'fa-cart-shopping', to: '/notaire/demandes-achat' },
      { label: 'Transactions', icon: 'fa-arrows-left-right', to: '/notaire/transactions' },
      { label: 'Rendez-vous', icon: 'fa-calendar', to: '/notaire/rendez-vous' },
      { label: 'Support', icon: 'fa-ticket', to: '/support/tickets' },
      { label: 'Mon profil', icon: 'fa-user', to: '/mon-profil' },
    )
  } else if (role === 'admin') {
    items.push(
      { label: 'Dashboard', icon: 'fa-gauge', to: '/admin/dashboard' },
      { label: 'Utilisateurs', icon: 'fa-users', to: '/admin/users' },
      { label: 'Demandes de rôle', icon: 'fa-clipboard-list', to: '/admin/role-requests' },
      { label: 'Localisation', icon: 'fa-map-pin', to: '/admin/localisation' },
      { label: 'Blockchain', icon: 'fa-shield-halved', to: '/admin/blockchain' },
      { label: 'Support', icon: 'fa-ticket', to: '/admin/support/tickets' },
      { label: 'Mon profil', icon: 'fa-user', to: '/mon-profil' },
    )
  }
  return items
})
function isActive(path) {
  if (path === '/mon-profil') return route.path === path
  return route.path.startsWith(path)
}
function handleLogout() {
  if (!confirmLogout()) return
  auth.logout()
  router.push('/accueil')
}
</script>
<template>
  <Transition name="fade">
    <div v-if="open" @click="props.toggle()" class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm" />
  </Transition>
  <aside
    class="fixed top-0 left-0 z-50 h-full bg-white border-r flex flex-col transition-all duration-300 overflow-y-auto"
    :class="open ? 'translate-x-0' : '-translate-x-full'"
    :style="{ width: 'var(--sidebar-width)', borderColor: 'var(--border)' }"
  >
    <div class="flex items-center justify-between h-16 px-6" :style="{ borderBottom: '1px solid var(--border)' }">
      <router-link to="/" class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center" :style="{ background: 'var(--green-tree)' }">
          <span class="text-white font-bold text-sm">FS</span>
        </div>
        <div>
          <span class="font-bold text-base" :style="{ color: 'var(--text-primary)' }">Foncier</span>
          <span class="font-bold text-base" :style="{ color: 'var(--green-tree)' }">Secure</span>
        </div>
      </router-link>
      <button @click="props.toggle()" class="p-1.5 rounded-lg" :style="{ color: 'var(--text-secondary)' }">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <nav class="flex-1 overflow-y-auto px-3 py-4 flex flex-col gap-1">
      <router-link
        v-for="item in menuItems" :key="item.label" :to="item.to"
        @click="props.toggle()"
        class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium transition-all duration-200 rounded-lg"
        :style="isActive(item.to) ? { background: 'var(--green-tree)', color: '#fff' } : { color: '#64748B', background: 'transparent' }"
      >
        <i :class="['fas', item.icon]" class="w-5 h-5 flex-shrink-0"></i>
        <span>{{ item.label }}</span>
      </router-link>
    </nav>
    <div class="px-3 py-4" :style="{ borderTop: '1px solid var(--border)' }">
      <button @click="handleLogout" class="flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium transition-all duration-200 rounded-lg" :style="{ color: '#64748B' }">
        <i class="fas fa-right-from-bracket w-5 h-5"></i>
        <span>Déconnexion</span>
      </button>
    </div>
  </aside>
</template>
