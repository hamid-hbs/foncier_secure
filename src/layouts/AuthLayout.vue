<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AuthSidebar from '@/components/shared/AuthSidebar.vue'
import TopBar from '@/components/TopBar.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const sidebarOpen = ref(true)
function toggleSidebar() { sidebarOpen.value = !sidebarOpen.value }

const isProfileRoute = () => route.name === 'Profil'
</script>

<template>
  <div class="min-h-screen bg-page flex">
    <AuthSidebar :open="sidebarOpen" :toggle="toggleSidebar" />
    <div
      class="flex-1 flex flex-col min-w-0 transition-all duration-300"
      :style="{ marginLeft: sidebarOpen ? 'var(--sidebar-w)' : '0' }"
    >
      <TopBar :toggleSidebar="toggleSidebar" :sidebarOpen="sidebarOpen" />
      <main class="flex-1 pt-16 overflow-x-hidden relative">
        <div v-if="auth.pendingApproval && !isProfileRoute()" class="absolute inset-0 z-30 bg-stone-950/40 backdrop-blur-sm flex items-start justify-center pt-32">
          <div class="card max-w-md mx-4 p-8 text-center shadow-xl">
            <div class="w-16 h-16 rounded-2xl bg-gold/15 flex items-center justify-center mx-auto mb-5">
              <i class="fas fa-hourglass-half text-gold text-2xl"></i>
            </div>
            <h2 class="font-display font-bold text-xl text-stone-900 mb-2">Compte en attente</h2>
            <p class="text-sm text-stone-500 mb-6">Votre compte est en attente de validation par un administrateur. Vous aurez accès à toutes les fonctionnalités dès activation.</p>
            <button @click="router.push('/mon-profil')" class="btn btn-primary">
              <i class="fas fa-user"></i> Voir mon profil
            </button>
          </div>
        </div>
        <router-view />
      </main>
    </div>
  </div>
</template>
