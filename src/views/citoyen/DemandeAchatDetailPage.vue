<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import demandeAchatApi from '@/api/demandeAchat'
import professionnelApi from '@/api/professionnel'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const demande = ref(null)
const loading = ref(true)
const processing = ref(false)

const isVendeur = computed(() => auth.user?.id === demande.value?.vendeur_id || auth.user?.id === demande.value?.vendeur?.id)

onMounted(async () => {
  try {
    const res = await demandeAchatApi.show(route.params.id)
    demande.value = res.data || null
  } catch (e) { console.error('Erreur chargement demande:', e) }
  loading.value = false
})

function statutBadge(s) {
  const map = { en_attente: 'badge-warning', acceptee: 'badge-success', refusee: 'badge-danger' }
  return map[s] || 'badge-neutral'
}

async function accept() {
  processing.value = true
  try {
    await demandeAchatApi.accepter(route.params.id)
    demande.value.statut = 'acceptee'
  } catch (e) { console.error('Erreur acceptation demande:', e) }
  processing.value = false
}

async function reject() {
  if (!confirm('Refuser cette demande ?')) return
  processing.value = true
  try {
    await demandeAchatApi.refuser(route.params.id)
    demande.value.statut = 'refusee'
  } catch (e) { console.error('Erreur refus demande:', e) }
  processing.value = false
}
</script>

<template>
  <div class="page-wrap max-w-2xl mx-auto">
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-28 rounded-2xl"></div>
      <div class="skeleton h-48 rounded-2xl"></div>
    </div>

    <div v-else-if="!demande" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-cart-shopping"></i></div>
        <p class="empty-title">Demande introuvable</p>
        <button @click="goBack(router)" class="btn btn-primary mt-4">Retour</button>
      </div>
    </div>

    <template v-else>
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
        <i class="fas fa-arrow-left text-xs"></i> Mes demandes
      </button>

      <!-- Header -->
      <div class="card mb-5">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <h1 class="font-display font-bold text-xl text-stone-900">
                {{ demande.parcelle?.titre || 'Parcelle #' + demande.parcelle_id }}
              </h1>
              <span class="badge" :class="statutBadge(demande.statut)">{{ demande.statut }}</span>
            </div>
            <p class="text-sm text-stone-400">
              <i class="fas fa-calendar mr-1.5 text-stone-300"></i>
              Soumise le {{ demande.created_at ? new Date(demande.created_at).toLocaleDateString('fr-FR') : '' }}
            </p>
          </div>
          <!-- Actions vendeur -->
          <div v-if="isVendeur && demande.statut === 'en_attente'" class="flex gap-2">
            <button @click="accept" class="btn btn-success btn-sm" :disabled="processing">
              <div v-if="processing" class="spinner spinner-sm border-white/30 border-t-white"></div>
              <i v-else class="fas fa-check"></i> Accepter
            </button>
            <button @click="reject" class="btn btn-ghost btn-sm text-danger" :disabled="processing">
              <i class="fas fa-times"></i> Refuser
            </button>
          </div>
        </div>
      </div>

      <!-- Details -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
        <div class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Acheteur</p>
          <div class="flex items-center gap-3">
            <div class="avatar avatar-sm bg-brand shrink-0">{{ (demande.acheteur?.prenom || 'A')[0] }}</div>
            <div>
              <p class="text-sm font-bold text-stone-900">{{ demande.acheteur?.prenom }} {{ demande.acheteur?.nom }}</p>
              <p class="text-xs text-stone-400">{{ demande.acheteur?.email }}</p>
            </div>
          </div>
        </div>

        <div v-if="demande.prix_propose" class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Prix proposé</p>
          <p class="text-2xl font-display font-extrabold text-brand">{{ Number(demande.prix_propose).toLocaleString('fr-FR') }}</p>
          <p class="text-xs text-stone-400 font-semibold">FCFA</p>
        </div>
      </div>

      <!-- Message -->
      <div v-if="demande.message" class="card">
        <h3 class="font-display font-bold text-stone-900 mb-3">Message de l'acheteur</h3>
        <div class="px-4 py-4 bg-stone-50 rounded-xl">
          <p class="text-sm text-stone-700 leading-relaxed whitespace-pre-wrap italic">"{{ demande.message }}"</p>
        </div>
      </div>
    </template>
  </div>
</template>
