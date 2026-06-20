<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { confirmLogout } from '@/utils/navigation'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const mobileOpen = ref(false)

const isPublic = computed(() => route.meta?.public)
const navLinks = computed(() => [
  { label: 'Accueil', to: '/' },
  { label: 'Observatoire', to: '/observatoire' },
  { label: 'Cartographie', to: '/cartographie' },
  { label: 'Professionnels', to: '/professionnels' },
])

const userLinks = computed(() => {
  if (!auth.user) return []
  const links = [
    { label: 'Tableau de bord', to: '/tableau-de-bord' },
    { label: 'Profil', to: '/mon-profil' },
  ]
  if (auth.user.role === 'geometre') {
    links.unshift({ label: 'Géomètre', to: '/geometre/dashboard' })
  } else if (auth.user.role === 'notaire') {
    links.unshift({ label: 'Notaire', to: '/notaire/dashboard' })
  } else if (auth.user.role === 'admin') {
    links.unshift({ label: 'Admin', to: '/admin/dashboard' })
  }
  return links
})

function handleLogout() {
  if (!confirmLogout()) return
  mobileOpen.value = false
  auth.logout()
  router.push('/accueil')
}
</script>
<template>
  <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b" style="border-color: var(--border);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <router-link to="/" class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-sm" style="background: var(--green-tree);">FS</div>
          <span class="font-bold text-lg" style="color: var(--text-primary);">FoncierSecure</span>
        </router-link>
        <div class="hidden md:flex items-center gap-6">
          <template v-if="isPublic || !auth.isAuthenticated">
            <router-link v-for="l in navLinks" :key="l.to" :to="l.to"
              class="text-sm font-medium transition-colors"
              :style="{ color: route.path === l.to ? 'var(--green-tree)' : 'var(--text-secondary)' }">
              {{ l.label }}
            </router-link>
          </template>
          <template v-if="auth.isAuthenticated">
            <router-link v-for="l in userLinks" :key="l.to" :to="l.to"
              class="text-sm font-medium transition-colors"
              :style="{ color: route.path.startsWith(l.to) ? 'var(--green-tree)' : 'var(--text-secondary)' }">
              {{ l.label }}
            </router-link>
          </template>
          <div v-if="!auth.isAuthenticated" class="flex items-center gap-3 ml-4">
            <router-link to="/auth/login" class="btn-outline btn-sm">Connexion</router-link>
            <router-link to="/auth/register" class="btn-green btn-sm">Inscription</router-link>
          </div>
          <div v-else class="flex items-center gap-3 ml-4">
            <span class="text-sm" style="color: var(--text-secondary);">{{ auth.user?.nom }} {{ auth.user?.prenom }}</span>
            <button @click="handleLogout" class="btn-outline btn-sm" style="color: var(--danger);">Déconnexion</button>
          </div>
        </div>
        <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2" style="color: var(--text-secondary);">
          <i :class="mobileOpen ? 'fas fa-times' : 'fas fa-bars'"></i>
        </button>
      </div>
    </div>
    <div v-if="mobileOpen" class="md:hidden border-t px-4 py-3 space-y-2" style="border-color: var(--border); background: var(--surface);">
      <template v-if="isPublic || !auth.isAuthenticated">
        <router-link v-for="l in navLinks" :key="l.to" :to="l.to" @click="mobileOpen=false"
          class="block py-2 text-sm" style="color: var(--text-secondary);">{{ l.label }}</router-link>
      </template>
      <template v-if="auth.isAuthenticated">
        <router-link v-for="l in userLinks" :key="l.to" :to="l.to" @click="mobileOpen=false"
          class="block py-2 text-sm" style="color: var(--text-secondary);">{{ l.label }}</router-link>
        <button @click="handleLogout" class="block w-full text-left py-2 text-sm" style="color: var(--danger);">Déconnexion</button>
      </template>
      <div v-if="!auth.isAuthenticated" class="pt-2 space-y-2">
        <router-link to="/auth/login" @click="mobileOpen=false" class="block btn-outline btn-sm text-center">Connexion</router-link>
        <router-link to="/auth/register" @click="mobileOpen=false" class="block btn-green btn-sm text-center">Inscription</router-link>
      </div>
    </div>
  </nav>
</template>
