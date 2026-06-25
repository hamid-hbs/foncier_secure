<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { goBack } from '@/utils/navigation'
import verificationApi from '@/api/verification'

const route = useRoute()
const router = useRouter()

const verification = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await verificationApi.show(route.params.id)
    verification.value = res.data || null
  } catch (e) { console.error('Erreur chargement vérification:', e) }
  loading.value = false
})

function statutBadge(s) {
  const map = { soumise: 'badge-neutral', en_analyse: 'badge-info', mission_assignee: 'badge-purple', terminee: 'badge-success', validee: 'badge-success', rejetee: 'badge-danger' }
  return map[s] || 'badge-neutral'
}
function statutLabel(s) {
  const map = { soumise: 'Soumise', en_analyse: 'En analyse', mission_assignee: 'Assignée', terminee: 'Terminée', validee: 'Validée', rejetee: 'Rejetée' }
  return map[s] || s
}
function risqueBadge(r) {
  const map = { faible: 'badge-success', moyen: 'badge-warning', eleve: 'badge-danger' }
  return map[r] || 'badge-neutral'
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

const BASE_URL = import.meta.env.VITE_API_URL?.replace('/api', '') || ''
function getDocUrl(c) {
  if (!c) return '#'
  return c.startsWith('http') ? c : `${BASE_URL}/storage/${c}`
}
</script>

<template>
  <div class="page-wrap max-w-3xl mx-auto">
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-28 rounded-2xl"></div>
      <div class="skeleton h-56 rounded-2xl"></div>
    </div>

    <div v-else-if="!verification" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-shield-halved"></i></div>
        <p class="empty-title">Vérification introuvable</p>
        <button @click="goBack(router)" class="btn btn-primary mt-4">Retour</button>
      </div>
    </div>

    <template v-else>
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
        <i class="fas fa-arrow-left text-xs"></i> Mes vérifications
      </button>

      <!-- Header -->
      <div class="card mb-5">
        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
          <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center text-brand shrink-0">
            <i class="fas fa-shield-halved text-xl"></i>
          </div>
          <div class="flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <h1 class="font-display font-bold text-xl text-stone-900">
                {{ verification.parcelle?.titre || 'Parcelle #' + verification.parcelle_id }}
              </h1>
              <span class="badge" :class="statutBadge(verification.statut)">{{ statutLabel(verification.statut) }}</span>
            </div>
            <p class="text-sm text-stone-400">
              <i class="fas fa-location-dot mr-1.5 text-stone-300"></i>
              {{ [verification.parcelle?.commune?.nom, verification.parcelle?.arrondissement?.nom].filter(Boolean).join(' · ') || 'Localisation inconnue' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Details grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
        <!-- Timeline -->
        <div class="card">
          <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
            <i class="fas fa-timeline text-brand text-sm"></i> Progression
          </h3>
          <div class="space-y-3">
            <div v-for="step in [
              { key: 'soumise', label: 'Soumise', icon: 'fa-paper-plane' },
              { key: 'en_analyse', label: 'En analyse', icon: 'fa-magnifying-glass' },
              { key: 'mission_assignee', label: 'Géomètre assigné', icon: 'fa-ruler-combined' },
              { key: 'terminee', label: 'Terminée', icon: 'fa-check-double' },
            ]" :key="step.key"
            class="flex items-center gap-3 transition-opacity"
            :class="['soumise','en_analyse','mission_assignee','terminee','validee'].indexOf(verification.statut) >= ['soumise','en_analyse','mission_assignee','terminee','validee'].indexOf(step.key) ? 'opacity-100' : 'opacity-30'">
              <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-xs transition-colors"
                :class="verification.statut === step.key ? 'bg-brand text-white' : 'bg-stone-100 text-stone-400'">
                <i :class="['fas', step.icon]"></i>
              </div>
              <span class="text-sm font-semibold" :class="verification.statut === step.key ? 'text-stone-900' : 'text-stone-400'">{{ step.label }}</span>
            </div>
          </div>
        </div>

        <!-- Géomètre assigné -->
        <div class="card">
          <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
            <i class="fas fa-user-tie text-brand text-sm"></i> Géomètre assigné
          </h3>
          <div v-if="verification.geometre">
            <div class="flex items-center gap-3 mb-3">
              <div class="avatar bg-brand shrink-0">{{ (verification.geometre.prenom || 'G')[0] }}</div>
              <div>
                <p class="font-bold text-stone-900">{{ verification.geometre.prenom }} {{ verification.geometre.nom }}</p>
                <p class="text-xs text-stone-400">{{ verification.geometre.email }}</p>
              </div>
            </div>
          </div>
          <div v-else class="flex items-center gap-3 py-3 px-4 rounded-xl bg-stone-50">
            <i class="fas fa-clock text-stone-300 text-sm"></i>
            <p class="text-sm text-stone-500">En attente d'assignation</p>
          </div>

          <div class="mt-4 pt-4 border-t border-stone-100">
            <p class="text-xs text-stone-400 mb-1 font-medium">Date de soumission</p>
            <p class="text-sm font-bold text-stone-900">{{ formatDate(verification.created_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Rapport -->
      <div v-if="verification.rapport" class="card mb-5">
        <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
          <i class="fas fa-file-contract text-brand text-sm"></i> Rapport de vérification
        </h3>
        <div class="space-y-4">
          <div v-if="verification.rapport.note_risque" class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-stone-50">
            <i class="fas fa-shield-halved text-stone-400 text-sm shrink-0"></i>
            <div>
              <p class="text-xs text-stone-400 font-medium">Niveau de risque</p>
              <span class="badge mt-1" :class="risqueBadge(verification.rapport.note_risque)">{{ verification.rapport.note_risque }}</span>
            </div>
          </div>

          <div v-if="verification.rapport.superficie_mesuree" class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-stone-50">
            <i class="fas fa-ruler-combined text-stone-400 text-sm shrink-0"></i>
            <div>
              <p class="text-xs text-stone-400 font-medium">Superficie mesurée</p>
              <p class="text-sm font-bold text-stone-900">{{ verification.rapport.superficie_mesuree }} m²</p>
            </div>
          </div>

          <div v-if="verification.rapport.observations" class="px-4 py-4 rounded-xl bg-stone-50">
            <p class="text-xs text-stone-400 font-medium mb-2">Observations</p>
            <p class="text-sm text-stone-700 leading-relaxed whitespace-pre-wrap">{{ verification.rapport.observations }}</p>
          </div>

          <div v-if="verification.rapport.recommendations" class="px-4 py-4 rounded-xl bg-brand-50 border border-brand-100">
            <p class="text-xs text-brand font-medium mb-2 flex items-center gap-1"><i class="fas fa-lightbulb text-xs"></i> Recommandations</p>
            <p class="text-sm text-stone-700 leading-relaxed whitespace-pre-wrap">{{ verification.rapport.recommendations }}</p>
          </div>

          <!-- Rapport document -->
          <div v-if="verification.rapport.fichier_rapport">
            <a :href="getDocUrl(verification.rapport.fichier_rapport)" target="_blank" class="btn btn-outline btn-full">
              <i class="fas fa-file-pdf text-red-500"></i> Télécharger le rapport complet
            </a>
          </div>
        </div>
      </div>

      <!-- No rapport yet -->
      <div v-else-if="verification.statut !== 'terminee' && verification.statut !== 'validee'" class="card bg-stone-50">
        <div class="flex items-center gap-3 py-2">
          <i class="fas fa-hourglass-half text-stone-300 text-lg"></i>
          <p class="text-sm text-stone-500">Le rapport n'est pas encore disponible. La vérification est en cours.</p>
        </div>
      </div>
    </template>
  </div>
</template>
