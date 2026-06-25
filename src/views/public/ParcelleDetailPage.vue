<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import parcelleApi from '@/api/parcelle'

const auth = useAuthStore()
const isLoggedIn = computed(() => !!auth.user)
const route = useRoute()
const router = useRouter()
const parcelle = ref(null)
const loading = ref(true)

const canInitierAchat = computed(() => isLoggedIn.value && parcelle.value && ['libre', 'en_demande'].includes(parcelle.value.statut))

const BASE_URL = import.meta.env.VITE_API_URL?.replace('/api', '') || ''
function getFileUrl(chemin) {
  if (!chemin) return null
  return chemin.startsWith('http') ? chemin : `${BASE_URL}/storage/${chemin}`
}
function getPhotoUrl(p) {
  const docs = p?.documents || []
  const d = docs.find(doc => doc.type_document === 'photo')
  return d ? getFileUrl(d.chemin_fichier || d.fichier) : null
}
function statutBadgeClass(s) {
  const map = { libre: 'badge-success', en_demande: 'badge-warning', en_transaction: 'badge-info', vendue: 'badge-neutral', conteste: 'badge-danger' }
  return map[s] || 'badge-neutral'
}

onMounted(async () => {
  try {
    const res = await parcelleApi.show(route.params.id)
    parcelle.value = res.data || null
  } catch (e) { console.error('Erreur chargement parcelle:', e) }
  loading.value = false
})
</script>

<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" class="max-w-4xl mx-auto px-5 py-12 space-y-4">
      <div class="skeleton h-72 rounded-2xl"></div>
      <div class="skeleton h-32 rounded-2xl"></div>
    </div>

    <!-- Not found -->
    <div v-else-if="!parcelle" class="max-w-lg mx-auto px-5 py-16">
      <div class="card">
        <div class="empty-state">
          <div class="empty-icon"><i class="fas fa-map"></i></div>
          <p class="empty-title">Parcelle introuvable</p>
          <button @click="router.push('/parcelles')" class="btn btn-primary mt-4">Retour à la liste</button>
        </div>
      </div>
    </div>

    <template v-else>
      <!-- Hero image -->
      <div class="relative w-full h-72 sm:h-96 overflow-hidden bg-stone-200">
        <img v-if="getPhotoUrl(parcelle)" :src="getPhotoUrl(parcelle)" class="w-full h-full object-cover" :alt="parcelle.titre" />
        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand to-brand-light">
          <i class="fas fa-map-marked-alt text-[6rem] text-white/10"></i>
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-stone-900/80 via-stone-900/20 to-transparent"></div>
        <div class="absolute bottom-6 left-6 right-6 flex items-end justify-between">
          <div>
            <h1 class="font-display font-extrabold text-2xl sm:text-4xl text-white mb-2 drop-shadow">{{ parcelle.titre || 'Parcelle non nommée' }}</h1>
            <span class="font-mono text-sm bg-black/40 backdrop-blur text-white px-3 py-1 rounded-lg">{{ parcelle.code }}</span>
          </div>
          <span class="badge backdrop-blur-sm shadow-lg" :class="statutBadgeClass(parcelle.statut)">{{ (parcelle.statut || '').replace('_', ' ') }}</span>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-4xl mx-auto px-5 sm:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Main info -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Details -->
            <div class="card">
              <h2 class="font-display font-bold text-stone-900 mb-5 flex items-center gap-2">
                <i class="fas fa-circle-info text-brand text-sm"></i> Détails de la parcelle
              </h2>
              <div class="grid grid-cols-2 gap-4">
                <div class="px-4 py-3.5 rounded-xl bg-stone-50">
                  <p class="text-xs text-stone-400 font-medium mb-1">Localisation</p>
                  <p class="text-sm font-bold text-stone-900">{{ [parcelle.commune?.nom, parcelle.arrondissement?.nom, parcelle.quartier?.nom].filter(Boolean).join(' · ') || '—' }}</p>
                </div>
                <div v-if="parcelle.superficie" class="px-4 py-3.5 rounded-xl bg-stone-50">
                  <p class="text-xs text-stone-400 font-medium mb-1">Superficie</p>
                  <p class="text-sm font-bold text-stone-900">{{ parcelle.superficie }} m²</p>
                </div>
                <div v-if="parcelle.prix_estimatif" class="px-4 py-3.5 rounded-xl bg-brand-50">
                  <p class="text-xs text-brand font-medium mb-1">Prix estimatif</p>
                  <p class="text-base font-extrabold text-brand">{{ Number(parcelle.prix_estimatif).toLocaleString('fr-FR') }} FCFA</p>
                </div>
                <div v-if="parcelle.usage" class="px-4 py-3.5 rounded-xl bg-stone-50">
                  <p class="text-xs text-stone-400 font-medium mb-1">Usage prévu</p>
                  <p class="text-sm font-bold text-stone-900 capitalize">{{ parcelle.usage }}</p>
                </div>
              </div>
              <p v-if="parcelle.description" class="mt-5 text-sm text-stone-600 leading-relaxed border-t border-stone-100 pt-4">{{ parcelle.description }}</p>
            </div>

            <!-- Propriétaire -->
            <div v-if="parcelle.proprietaire" class="card">
              <h2 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
                <i class="fas fa-user text-brand text-sm"></i> Propriétaire
              </h2>
              <div class="flex items-center gap-4">
                <div class="avatar bg-brand">{{ (parcelle.proprietaire.prenom || 'P')[0] }}{{ (parcelle.proprietaire.nom || '')[0] }}</div>
                <div>
                  <p class="font-bold text-stone-900">{{ parcelle.proprietaire.prenom }} {{ parcelle.proprietaire.nom }}</p>
                  <p v-if="parcelle.proprietaire.indice_confiance" class="text-sm text-stone-400">
                    Indice de confiance :
                    <span class="font-bold text-brand">{{ parcelle.proprietaire.indice_confiance }}/100</span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar actions -->
          <div class="space-y-4">
            <!-- CTA -->
            <div class="card border border-brand-100 bg-brand-50">
              <h3 class="font-display font-bold text-stone-900 mb-3">Intéressé par ce terrain ?</h3>
              <div v-if="canInitierAchat">
                <router-link
                  :to="{ name: 'NouvelleDemandeAchat', query: { parcelle_id: parcelle.id } }"
                  class="btn btn-primary btn-full mb-3"
                >
                  <i class="fas fa-cart-shopping"></i> Faire une offre d'achat
                </router-link>
              </div>
              <div v-else-if="!isLoggedIn">
                <router-link to="/auth/register" class="btn btn-primary btn-full mb-2">
                  <i class="fas fa-user-plus"></i> Créer un compte
                </router-link>
                <router-link to="/auth/login" class="btn btn-ghost btn-full btn-sm text-stone-600">Se connecter</router-link>
              </div>
              <div v-else>
                <p class="text-sm text-stone-500 text-center py-2">
                  <span v-if="parcelle.statut === 'vendue'">Cette parcelle a déjà été vendue.</span>
                  <span v-else>Cette parcelle n'est pas disponible à l'achat pour le moment.</span>
                </p>
              </div>
            </div>

            <!-- Blockchain -->
            <div v-if="parcelle.hash_blockchain" class="card">
              <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-success/10 flex items-center justify-center text-success">
                  <i class="fas fa-link text-sm"></i>
                </div>
                <p class="font-bold text-stone-900 text-sm">Blockchain vérifiée</p>
              </div>
              <code class="block text-[10px] font-mono text-stone-400 bg-stone-50 px-3 py-2 rounded-lg break-all">{{ parcelle.hash_blockchain }}</code>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
