<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { goBack } from '@/utils/navigation'
import parcelleApi from '@/api/parcelle'

const route = useRoute()
const router = useRouter()

const parcelle = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await parcelleApi.show(route.params.id)
    parcelle.value = res.data
  } catch { /* ignore */ }
  loading.value = false
})

function statutClass(statut) {
  const map = {
    libre: 'badge-success',
    en_demande: 'badge-warning',
    en_transaction: 'badge-info',
    vendue: 'badge-info',
    conteste: 'badge-danger',
  }
  return map[statut] || 'badge-success'
}
</script>

<template>
  <div class="page-container max-w-4xl">
    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--border); border-top-color: var(--green-tree);"></div>
    </div>

    <div v-else-if="!parcelle" class="card text-center py-12">
      <i class="fas fa-map-pin text-4xl mb-3" style="color: var(--border);"></i>
      <p style="color: var(--text-secondary);">Parcelle introuvable.</p>
    </div>

    <template v-else>
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
        <i class="fas fa-arrow-left"></i> Retour
      </button>

      <div class="flex-between mb-6">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background: #D1FAE5;">
            <i class="fas fa-map-pin text-xl" style="color: var(--green-tree);"></i>
          </div>
          <div>
            <h1 class="section-title">{{ parcelle.titre || 'Parcelle #' + parcelle.id }}</h1>
            <p class="section-subtitle">{{ parcelle.commune?.nom || '—' }}{{ parcelle.arrondissement ? ' — ' + parcelle.arrondissement.nom : '' }}{{ parcelle.quartier ? ' / ' + parcelle.quartier.nom : '' }}</p>
          </div>
        </div>
        <div class="flex gap-2">
          <router-link :to="{ name: 'ModifierParcelle', params: { id: parcelle.id } }" class="btn-outline btn-sm flex items-center gap-1">
            <i class="fas fa-pen"></i> Modifier
          </router-link>
          <router-link :to="{ name: 'DocumentsParcelle', params: { id: parcelle.id } }" class="btn-outline btn-sm flex items-center gap-1">
            <i class="fas fa-file-lines"></i> Documents
          </router-link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card">
          <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Détails de la parcelle</h3>
          <dl class="divide-y text-sm" style="border-color: var(--border);">
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Titre</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ parcelle.titre || '—' }}</dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Code</dt>
              <dd class="font-medium font-mono" style="color: var(--text-primary);">{{ parcelle.code || '—' }}</dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Superficie</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ parcelle.superficie || '—' }} m²</dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Statut</dt>
              <dd><span class="badge" :class="statutClass(parcelle.statut)">{{ parcelle.statut || 'libre' }}</span></dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Propriétaire</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ parcelle.proprietaire?.nom || parcelle.proprietaire?.prenom || 'Non renseigné' }}</dd>
            </div>
          </dl>
        </div>

        <div class="card">
          <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Localisation</h3>
          <dl class="divide-y text-sm" style="border-color: var(--border);">
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Commune</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ parcelle.commune?.nom || '—' }}</dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Arrondissement</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ parcelle.arrondissement?.nom || '—' }}</dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Quartier</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ parcelle.quartier?.nom || '—' }}</dd>
            </div>
            <div v-if="parcelle.latitude && parcelle.longitude" class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Coordonnées</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ parcelle.latitude }}, {{ parcelle.longitude }}</dd>
            </div>
          </dl>
          <div v-if="parcelle.prix_estimatif" class="mt-4 p-3 rounded-lg flex items-center gap-2" style="background: var(--bg-page);">
            <i class="fas fa-coins" style="color: var(--gold);"></i>
            <span class="font-medium" style="color: var(--text-primary);">{{ Number(parcelle.prix_estimatif).toLocaleString('fr-FR') }} FCFA</span>
          </div>
        </div>
      </div>

      <div v-if="parcelle.documents_visibles && parcelle.documents?.length" class="card mb-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-file-lines" style="color: var(--green-tree);"></i> Documents
        </h3>
        <div class="space-y-2">
          <div v-for="doc in parcelle.documents" :key="doc.id" class="flex items-center gap-3 p-3 rounded-lg" style="border: 1px solid var(--border);">
            <i class="fas fa-file-lines" style="color: var(--green-tree);"></i>
            <span class="flex-1 text-sm font-medium truncate" style="color: var(--text-primary);">{{ doc.nom || 'Document' }}</span>
            <a :href="doc.url || `/storage/${doc.fichier}`" target="_blank" class="btn-outline btn-sm flex items-center gap-1">
              <i class="fas fa-download"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="card">
        <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Description</h3>
        <p class="text-sm" style="color: var(--text-secondary);">{{ parcelle.description || 'Aucune description.' }}</p>
      </div>
    </template>
  </div>
</template>
