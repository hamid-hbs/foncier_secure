<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { goBack } from '@/utils/navigation'
import verificationApi from '@/api/verification'

const route = useRoute()
const router = useRouter()

const verification = ref(null)
const loading = ref(true)
const sollicitant = ref(false)
const downloading = ref(false)

onMounted(async () => {
  try {
    const res = await verificationApi.show(route.params.id)
    verification.value = res.data
  } catch { /* ignore */ }
  loading.value = false
})

async function solliciterGeometre() {
  sollicitant.value = true
  try {
    await verificationApi.solliciterGeometre(route.params.id)
    const res = await verificationApi.show(route.params.id)
    verification.value = res.data
  } catch { /* ignore */ }
  sollicitant.value = false
}

async function downloadRapport() {
  downloading.value = true
  try {
    const res = await verificationApi.rapport(route.params.id)
    const url = URL.createObjectURL(new Blob([res.data]))
    const a = document.createElement('a')
    a.href = url
    a.download = `rapport-verification-${route.params.id}.pdf`
    a.click()
    URL.revokeObjectURL(url)
  } catch { /* ignore */ }
  downloading.value = false
}

function statutClass(s) {
  const map = {
    en_analyse: 'badge-info',
    terminee: 'badge-success',
    contestee: 'badge-danger',
  }
  return map[s] || 'badge-info'
}

function risqueClass(n) {
  const map = {
    faible: 'badge-success',
    moyen: 'badge-warning',
    eleve: 'badge-danger',
    critique: 'badge-danger',
  }
  return map[n] || 'badge-info'
}
</script>

<template>
  <div class="page-container max-w-4xl">
    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--border); border-top-color: var(--green-tree);"></div>
    </div>

    <div v-else-if="!verification" class="card text-center py-12">
      <i class="fas fa-check-circle text-4xl mb-3" style="color: var(--border);"></i>
      <p style="color: var(--text-secondary);">Vérification introuvable.</p>
    </div>

    <template v-else>
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
        <i class="fas fa-arrow-left"></i> Retour
      </button>

      <div class="flex-between mb-6">
        <div>
          <h1 class="section-title">{{ verification.titre || 'Vérification #' + verification.id }}</h1>
          <div class="flex gap-2 mt-2">
            <span class="text-xs px-2 py-1 rounded-full badge" :class="statutClass(verification.statut)">{{ verification.statut }}</span>
            <span v-if="verification.niveau_risque" class="text-xs px-2 py-1 rounded-full badge" :class="risqueClass(verification.niveau_risque)">{{ verification.niveau_risque }}</span>
          </div>
        </div>
        <div class="flex gap-2">
          <button v-if="verification.statut === 'en_analyse'" @click="solliciterGeometre" :disabled="sollicitant" class="btn-gold btn-sm flex items-center gap-1">
            <i class="fas fa-user-gear"></i> {{ sollicitant ? 'Envoi...' : 'Solliciter un géomètre' }}
          </button>
          <button v-if="verification.statut === 'terminee'" @click="downloadRapport" :disabled="downloading" class="btn-green btn-sm flex items-center gap-1">
            <i class="fas fa-download"></i> {{ downloading ? 'Téléchargement...' : 'Rapport' }}
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card">
          <h3 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
            <i class="fas fa-info-circle" style="color: var(--green-tree);"></i> Informations
          </h3>
          <dl class="divide-y text-sm" style="border-color: var(--border);">
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Statut</dt>
              <dd><span class="text-xs px-2 py-1 rounded-full badge" :class="statutClass(verification.statut)">{{ verification.statut }}</span></dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Niveau de risque</dt>
              <dd><span class="text-xs px-2 py-1 rounded-full badge" :class="risqueClass(verification.niveau_risque)">{{ verification.niveau_risque || '—' }}</span></dd>
            </div>
            <div v-if="verification.score_risque !== undefined" class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Score de risque</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ verification.score_risque }}</dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Parcelle</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ verification.parcelle?.titre || verification.parcelle?.code || '—' }}</dd>
            </div>
            <div class="flex justify-between py-3">
              <dt style="color: var(--text-secondary);">Date de création</dt>
              <dd class="font-medium" style="color: var(--text-primary);">{{ verification.created_at ? new Date(verification.created_at).toLocaleDateString('fr-FR') : '—' }}</dd>
            </div>
          </dl>
        </div>

        <div class="card">
          <h3 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
            <i class="fas fa-map-pin" style="color: var(--green-tree);"></i> Parcelle associée
          </h3>
          <div v-if="verification.parcelle">
            <dl class="divide-y text-sm" style="border-color: var(--border);">
              <div class="flex justify-between py-3">
                <dt style="color: var(--text-secondary);">Code</dt>
                <dd class="font-medium" style="color: var(--text-primary);">{{ verification.parcelle.code || '—' }}</dd>
              </div>
              <div class="flex justify-between py-3">
                <dt style="color: var(--text-secondary);">Superficie</dt>
                <dd class="font-medium" style="color: var(--text-primary);">{{ verification.parcelle.superficie || '—' }} m²</dd>
              </div>
              <div class="flex justify-between py-3">
                <dt style="color: var(--text-secondary);">Commune</dt>
                <dd class="font-medium" style="color: var(--text-primary);">{{ verification.parcelle.commune?.nom || '—' }}</dd>
              </div>
            </dl>
          </div>
          <p v-else class="text-sm" style="color: var(--text-secondary);">Aucune parcelle associée.</p>
        </div>
      </div>

      <div v-if="verification.documents?.length" class="card mb-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-file-lines" style="color: var(--green-tree);"></i> Documents
        </h3>
        <div class="space-y-2">
          <div v-for="doc in verification.documents" :key="doc.id" class="flex items-center gap-3 p-3 rounded-lg" style="border: 1px solid var(--border);">
            <i class="fas fa-file-lines" style="color: var(--green-tree);"></i>
            <span class="flex-1 text-sm font-medium truncate" style="color: var(--text-primary);">{{ doc.nom_fichier || doc.nom || 'Document' }}</span>
            <a :href="doc.url || `/storage/${doc.fichier}`" target="_blank" class="btn-outline btn-sm flex items-center gap-1">
              <i class="fas fa-download"></i>
            </a>
          </div>
        </div>
      </div>

      <div v-if="verification.analyses?.length" class="card mb-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-flask" style="color: var(--green-tree);"></i> Analyses
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div v-for="a in verification.analyses" :key="a.id" class="p-4 rounded-lg text-sm" :style="{ background: a.resultat === 'conforme' ? '#D1FAE5' : a.resultat === 'alerte' ? '#FEF3C7' : '#FEE2E2', border: '1px solid ' + (a.resultat === 'conforme' ? 'var(--green-tree)' : a.resultat === 'alerte' ? 'var(--gold)' : 'var(--danger)') }">
            <div class="flex items-center gap-2 mb-1">
              <i v-if="a.resultat === 'conforme'" class="fas fa-check-circle" style="color: var(--green-tree);"></i>
              <i v-else-if="a.resultat === 'alerte'" class="fas fa-exclamation-triangle" style="color: var(--gold);"></i>
              <i v-else class="fas fa-times-circle" style="color: var(--danger);"></i>
              <span class="font-semibold capitalize" style="color: var(--text-primary);">{{ a.type_analyse }}</span>
            </div>
            <p v-if="a.details" class="text-xs mt-1" style="color: var(--text-secondary);">{{ a.details }}</p>
          </div>
        </div>
      </div>

      <div v-if="verification.intervention" class="card mb-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-hard-hat" style="color: var(--gold);"></i> Intervention du géomètre
        </h3>
        <dl class="divide-y text-sm" style="border-color: var(--border);">
          <div class="flex justify-between py-3">
            <dt style="color: var(--text-secondary);">Géomètre</dt>
            <dd class="font-medium" style="color: var(--text-primary);">{{ verification.intervention.geometre?.nom || verification.intervention.geometre?.prenom || '—' }}</dd>
          </div>
          <div class="flex justify-between py-3">
            <dt style="color: var(--text-secondary);">Statut</dt>
            <dd><span class="text-xs px-2 py-1 rounded-full badge" :class="statutClass(verification.intervention.statut)">{{ verification.intervention.statut }}</span></dd>
          </div>
          <div v-if="verification.intervention.avis" class="flex justify-between py-3">
            <dt style="color: var(--text-secondary);">Avis</dt>
            <dd class="font-medium" style="color: var(--text-primary);">{{ verification.intervention.avis }}</dd>
          </div>
        </dl>
        <div v-if="verification.intervention.rapport" class="mt-4">
          <a :href="verification.intervention.rapport" target="_blank" class="btn-outline btn-sm flex items-center gap-1">
            <i class="fas fa-download"></i> Télécharger le rapport
          </a>
        </div>
      </div>
    </template>
  </div>
</template>
