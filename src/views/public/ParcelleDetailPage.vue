<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { goBack } from '@/utils/navigation'
import parcelleApi from '@/api/parcelle'

const auth = useAuthStore()
const isLoggedIn = computed(() => !!auth.user)
const canInitierAchat = computed(() => isLoggedIn.value && parcelle.value && ['libre', 'en_demande'].includes(parcelle.value.statut))

const route = useRoute()
const router = useRouter()
const parcelle = ref(null)
const loading = ref(true)

const statutLabels = { libre: 'Libre', en_demande: 'En demande', en_transaction: 'En transaction', vendue: 'Vendue' }
const statutColors = { libre: 'badge-success', en_demande: 'badge-warning', en_transaction: 'badge-info', vendue: 'badge-danger' }

onMounted(async () => {
  try {
    const res = await parcelleApi.show(route.params.id)
    parcelle.value = res.data
  } catch {}
  loading.value = false
})

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' })
}
</script>

<template>
  <div class="page-container max-w-4xl">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div v-if="loading" class="text-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin mx-auto" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <div v-else-if="!parcelle" class="card text-center py-12">
      <i class="fas fa-map-pin mb-3" style="color: #D1D5DB; font-size: 3rem;"></i>
      <p style="color: var(--text-secondary);">Parcelle introuvable.</p>
    </div>

    <template v-if="parcelle">
      <div class="card mb-6">
        <div class="flex items-start gap-4 mb-6">
          <div class="w-12 h-12 rounded-lg flex items-center justify-center shrink-0" style="background: var(--bg-page);">
            <i class="fas fa-map-pin" style="color: var(--green-tree); font-size: 1.25rem;"></i>
          </div>
          <div>
            <h1 class="section-title">{{ parcelle.titre || 'Parcelle' }}</h1>
            <p class="section-subtitle">
              <template v-if="parcelle.commune">{{ parcelle.commune.nom }}</template>
              <template v-if="parcelle.arrondissement"> — {{ parcelle.arrondissement.nom }}</template>
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <dt class="font-medium" style="color: var(--text-secondary);">Superficie</dt>
            <dd style="color: var(--text-primary);">{{ parcelle.superficie || '-' }} m²</dd>
          </div>
          <div>
            <dt class="font-medium" style="color: var(--text-secondary);">Statut</dt>
            <dd><span :class="['badge', statutColors[parcelle.statut] || 'badge-info']">{{ statutLabels[parcelle.statut] || parcelle.statut }}</span></dd>
          </div>
          <div v-if="parcelle.quartier">
            <dt class="font-medium" style="color: var(--text-secondary);">Quartier</dt>
            <dd style="color: var(--text-primary);">{{ parcelle.quartier.nom }}</dd>
          </div>
        </div>

        <hr class="my-4" style="border-color: var(--border);" />

        <div class="space-y-3">
          <h3 class="font-semibold" style="color: var(--text-primary);">Propriétaire</h3>
          <div v-if="parcelle.proprietaire" class="flex items-center gap-3 p-3 rounded-lg" style="background: var(--bg-page);">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold" style="background: var(--green-tree);">
              {{ (parcelle.proprietaire.prenom || '?')[0] }}{{ (parcelle.proprietaire.nom || '?')[0] }}
            </div>
            <div>
              <p class="font-medium" style="color: var(--text-primary);">{{ parcelle.proprietaire.prenom }} {{ parcelle.proprietaire.nom }}</p>
            </div>
          </div>
          <p v-else class="text-sm" style="color: var(--text-secondary);">Non renseigné</p>
        </div>
      </div>

      <div v-if="canInitierAchat" class="card mb-6">
        <div class="flex items-center justify-between gap-4">
          <div>
            <p class="font-semibold" style="color: var(--text-primary);">Cette parcelle vous intéresse ?</p>
            <p class="text-sm mt-1" style="color: var(--text-secondary);">Initiez une demande d'achat auprès du propriétaire.</p>
          </div>
          <button @click="router.push('/citoyen/demandes-achat/creer?parcelle_id=' + parcelle.id)" class="btn-green">
            <i class="fas fa-cart-plus mr-1"></i> Initier un achat
          </button>
        </div>
      </div>

      <div v-if="parcelle.verifications && parcelle.verifications.length" class="card mb-6">
        <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Vérifications</h3>
        <div v-for="v in parcelle.verifications" :key="v.id" class="flex items-center gap-3 p-3 rounded-lg mb-2" style="background: var(--bg-page);">
          <i class="fas fa-shield-alt" style="color: var(--green-tree);"></i>
          <div class="flex-1">
            <p class="text-sm font-medium" style="color: var(--text-primary);">{{ v.type_verification || v.type }}</p>
            <p class="text-xs" style="color: var(--text-secondary);">{{ v.statut }} — {{ formatDate(v.created_at) }}</p>
          </div>
          <span v-if="v.statut === 'valide'" class="badge badge-success">Validé</span>
          <span v-else-if="v.statut === 'refuse'" class="badge badge-danger">Refusé</span>
          <span v-else class="badge badge-warning">En cours</span>
        </div>
      </div>

      <div v-if="parcelle.documents_visibles && parcelle.documents && parcelle.documents.length" class="card mb-6">
        <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Documents</h3>
        <div v-for="d in parcelle.documents" :key="d.id" class="flex items-center gap-3 p-3 rounded-lg mb-2" style="background: var(--bg-page);">
          <i class="fas fa-file" style="color: var(--green-tree);"></i>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate" style="color: var(--text-primary);">{{ d.nom || d.fichier }}</p>
            <p class="text-xs" style="color: var(--text-secondary);">{{ d.type_document || d.type }}</p>
          </div>
          <a v-if="d.url" :href="d.url" target="_blank" class="btn-outline btn-sm flex items-center gap-1">
            <i class="fas fa-download"></i> Voir
          </a>
        </div>
      </div>
    </template>
  </div>
</template>
