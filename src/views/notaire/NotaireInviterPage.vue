<script setup>
import { useRoute, useRouter } from 'vue-router'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
</script>

<template>
  <div class="page-wrap max-w-md mx-auto">
    <div class="flex items-center gap-3 mb-8">
      <button @click="goBack(router)" class="btn btn-ghost btn-icon text-stone-500">
        <i class="fas fa-arrow-left"></i>
      </button>
      <div>
        <h1 class="page-title">Inviter un intervenant</h1>
        <p class="page-subtitle">Dossier #{{ route.params.id }}</p>
      </div>
    </div>

    <div class="card">
      <div class="empty-state py-10">
        <div class="empty-icon"><i class="fas fa-user-plus"></i></div>
        <p class="empty-title">Invitation d'intervenants</p>
        <p class="empty-text">Cette fonctionnalité sera disponible dans une prochaine version. Pour ajouter un intervenant, veuillez modifier les participants directement dans le dossier de transaction.</p>
        <button @click="goBack(router)" class="btn btn-primary mt-4">Retour au dossier</button>
      </div>
    </div>
  </div>
</template>
