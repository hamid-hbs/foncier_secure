<script setup>
import { ref } from 'vue'
import AuthSidebar from '@/components/shared/AuthSidebar.vue'
import TopBar from '@/components/TopBar.vue'
const sidebarOpen = ref(true)
function toggleSidebar() { sidebarOpen.value = !sidebarOpen.value }
</script>
<template>
  <div class="min-h-screen" style="background: var(--bg-page);">
    <AuthSidebar :open="sidebarOpen" :toggle="toggleSidebar" />
    <div
      class="transition-all duration-300"
      :style="{ marginLeft: sidebarOpen ? 'var(--sidebar-width)' : '0' }"
    >
      <TopBar :toggle-sidebar="toggleSidebar" :sidebar-open="sidebarOpen" />
      <main class="pt-20 pb-8 px-4 sm:px-6 lg:px-8 min-h-screen">
        <router-view />
      </main>
    </div>
  </div>
</template>
