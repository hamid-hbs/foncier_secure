<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import demandeAchatApi from '@/api/demandeAchat'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const auth = useAuthStore()
const demandes = ref([])
const loading = ref(true)
const isNotaire = computed(() => auth.user?.role === 'notaire')

const statutLabel = { soumise: 'Soumise', acceptee: 'Acceptée', refusee: 'Refusée', annulee: 'Annulée' }
const badgeClass = { soumise: 'badge-warning', acceptee: 'badge-success', refusee: 'badge-danger', annulee: 'badge-secondary' }

onMounted(async () => {
  try {
    const r = await demandeAchatApi.list()
    demandes.value = r.data?.data ?? []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>
<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #D1FAE5;">
        <i class="fas fa-cart-shopping" style="color: var(--green-tree);"></i>
      </div>
      <div>
        <h1 class="section-title">Demandes d'achat</h1>
        <p class="section-subtitle">Initiez un achat depuis la fiche d'une parcelle</p>
      </div>
    </div>
    <div v-if="loading" class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl" style="color: var(--text-secondary);"></i></div>
    <div v-else-if="demandes.length === 0" class="card p-8 text-center" style="color: var(--text-secondary);">
      <i class="fas fa-receipt text-4xl mb-3 opacity-40"></i>
      <p>Aucune demande d'achat.</p>
    </div>
    <div v-else class="space-y-3">
      <div v-for="d in demandes" :key="d.id" class="card p-4 flex items-center justify-between cursor-pointer hover:shadow-md transition" @click="router.push(isNotaire ? `/notaire/demandes-achat/${d.id}` : `/citoyen/demandes-achat/${d.id}`)">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold" style="background: var(--green-tree);">{{ (d.parcelle?.code || '#')[0] }}</div>
          <div>
            <p class="font-medium text-sm" style="color: var(--text-primary);">{{ d.parcelle?.code || 'Parcelle #' + d.parcelle_id }}</p>
            <p class="text-xs" style="color: var(--text-secondary);">{{ d.acheteur?.nom || '' }} — {{ new Date(d.created_at).toLocaleDateString('fr-FR') }}</p>
          </div>
        </div>
        <span :class="['badge', badgeClass[d.statut]]">{{ statutLabel[d.statut] || d.statut }}</span>
      </div>
    </div>
  </div>
</template>
