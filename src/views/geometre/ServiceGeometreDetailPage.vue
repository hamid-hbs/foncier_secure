<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import serviceGeometreApi from '@/api/serviceGeometre'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const service = ref(null)
const loading = ref(true)
const processing = ref(false)
const uploadLoading = ref(false)
const rapportFile = ref(null)

const isGeometre = computed(() => auth.userRole === 'geometre')

onMounted(async () => {
  try {
    const res = await serviceGeometreApi.show(route.params.id)
    service.value = res.data || null
  } catch (e) { console.error('Erreur chargement service:', e) }
  loading.value = false
})

function statutBadgeClass(s) {
  const map = { soumise: 'badge-neutral', acceptee: 'badge-success', refusee: 'badge-danger', terminee: 'badge-info' }
  return map[s] || 'badge-neutral'
}
function statutLabel(s) {
  const map = { soumise: 'Soumise', acceptee: 'Acceptée', refusee: 'Refusée', terminee: 'Terminée' }
  return map[s] || s
}
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

async function accepter() {
  processing.value = true
  try {
    await serviceGeometreApi.accepter(service.value.id)
    service.value.statut = 'accepte'
  } catch (e) { console.error('Erreur acceptation:', e) }
  processing.value = false
}

async function refuser() {
  if (!confirm('Refuser cette mission ?')) return
  processing.value = true
  try {
    await serviceGeometreApi.refuser(service.value.id)
    service.value.statut = 'refuse'
  } catch (e) { console.error('Erreur refus:', e) }
  processing.value = false
}

async function deposerRapport() {
  if (!rapportFile.value) return
  uploadLoading.value = true
  try {
    const formData = new FormData()
    formData.append('rapport', rapportFile.value)
    await serviceGeometreApi.rapport(service.value.id, formData)
    service.value.statut = 'rapport_depose'
    rapportFile.value = null
  } catch (e) { console.error('Erreur dépôt rapport:', e) }
  uploadLoading.value = false
}
</script>

<template>
  <div class="page-wrap max-w-3xl mx-auto">
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-28 rounded-2xl"></div>
      <div class="skeleton h-48 rounded-2xl"></div>
    </div>

    <div v-else-if="!service" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-ruler-combined"></i></div>
        <p class="empty-title">Service introuvable</p>
        <button @click="goBack(router)" class="btn btn-primary mt-4">Retour</button>
      </div>
    </div>

    <template v-else>
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
        <i class="fas fa-arrow-left text-xs"></i> Services géomètre
      </button>

      <!-- Header -->
      <div class="card mb-5">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <h1 class="font-display font-bold text-xl text-stone-900">
                {{ service.type_service || service.libelle || 'Service #' + service.id }}
              </h1>
              <span class="badge" :class="statutBadgeClass(service.statut)">{{ statutLabel(service.statut) }}</span>
            </div>
            <p class="text-sm text-stone-400">
              <i class="fas fa-calendar mr-1.5 text-stone-300"></i>
              Soumis le {{ formatDate(service.created_at) }}
            </p>
          </div>
          <!-- Actions géomètre -->
          <div v-if="isGeometre && service.statut === 'soumis'" class="flex gap-2">
            <button @click="accepter" class="btn btn-success btn-sm" :disabled="processing">
              <div v-if="processing" class="spinner spinner-sm border-white/30 border-t-white"></div>
              <i v-else class="fas fa-check"></i> Accepter
            </button>
            <button @click="refuser" class="btn btn-ghost btn-sm text-danger" :disabled="processing">
              <i class="fas fa-times"></i> Refuser
            </button>
          </div>
        </div>
      </div>

      <!-- Details -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
        <div class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Parcelle</p>
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-brand-50 flex items-center justify-center text-brand text-xs shrink-0">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-stone-900">{{ service.parcelle?.titre || '#' + service.parcelle_id }}</p>
              <p class="text-xs text-stone-400">{{ service.parcelle?.commune?.nom || '' }}</p>
            </div>
          </div>
        </div>

        <div class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Type de service</p>
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gold/10 flex items-center justify-center text-gold-dark text-xs shrink-0">
              <i class="fas fa-ruler-combined"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-stone-900 capitalize">{{ (service.type_service || '—').replace(/_/g, ' ') }}</p>
            </div>
          </div>
        </div>

        <div class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Demandeur</p>
          <div class="flex items-center gap-3">
            <div class="avatar avatar-sm bg-brand shrink-0">{{ (service.demandeur?.prenom || 'D')[0] }}</div>
            <div>
              <p class="text-sm font-bold text-stone-900">{{ service.demandeur?.prenom }} {{ service.demandeur?.nom }}</p>
              <p class="text-xs text-stone-400">{{ service.demandeur?.email }}</p>
            </div>
          </div>
        </div>

        <div v-if="service.geometre" class="card p-4">
          <p class="text-xs text-stone-400 font-medium mb-2">Géomètre assigné</p>
          <div class="flex items-center gap-3">
            <div class="avatar avatar-sm bg-brand shrink-0">{{ (service.geometre?.prenom || 'G')[0] }}</div>
            <div>
              <p class="text-sm font-bold text-stone-900">{{ service.geometre?.prenom }} {{ service.geometre?.nom }}</p>
              <p class="text-xs text-stone-400">{{ service.geometre?.email }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div v-if="service.description" class="card mb-5">
        <h3 class="font-display font-bold text-stone-900 mb-3">Description</h3>
        <div class="px-4 py-4 bg-stone-50 rounded-xl">
          <p class="text-sm text-stone-700 leading-relaxed whitespace-pre-wrap">{{ service.description }}</p>
        </div>
      </div>

      <!-- Déposer rapport (géomètre only) -->
      <div v-if="isGeometre && service.statut === 'accepte'" class="card">
        <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
          <i class="fas fa-file-upload text-brand"></i> Déposer le rapport
        </h3>
        <div class="flex items-center gap-3">
          <input type="file" ref="rapportFile" accept=".pdf,.doc,.docx" class="block w-full text-sm text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand hover:file:bg-brand-100" @change="deposerRapport" />
          <button @click="deposerRapport" class="btn btn-primary btn-sm shrink-0" :disabled="uploadLoading || !rapportFile">
            <div v-if="uploadLoading" class="spinner spinner-sm border-white/30 border-t-white"></div>
            <i v-else class="fas fa-upload"></i>
            {{ uploadLoading ? 'Envoi…' : 'Déposer' }}
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script>

</script>
