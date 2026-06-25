<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import parcelleApi from '@/api/parcelle'
import localisationApi from '@/api/localisation'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()

const isEdit = !!route.params.id
const communes = ref([])
const arrondissements = ref([])
const quartiers = ref([])
const loading = ref(false)
const error = ref('')
const fieldErrors = ref({})

const form = ref({
  titre: '',
  description: '',
  commune_id: '',
  arrondissement_id: '',
  quartier_id: '',
  superficie: '',
  latitude: '',
  longitude: '',
  prix_estimatif: '',
})

onMounted(async () => {
  try {
    const res = await localisationApi.getCommunes()
    communes.value = (res.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement communes:', e) }

  if (isEdit) {
    try {
      const res = await parcelleApi.show(route.params.id)
      const p = res.data
      form.value.titre = p.titre || ''
      form.value.description = p.description || ''
      form.value.commune_id = p.commune_id || ''
      form.value.arrondissement_id = p.arrondissement_id || ''
      form.value.quartier_id = p.quartier_id || ''
      form.value.superficie = p.superficie || ''
      form.value.latitude = p.latitude || ''
      form.value.longitude = p.longitude || ''
      form.value.prix_estimatif = p.prix_estimatif || ''
      if (form.value.commune_id) await onCommuneChange()
      if (form.value.arrondissement_id) await onArrondissementChange()
    } catch (e) { console.error('Erreur chargement parcelle:', e) }
  }
})

async function onCommuneChange() {
  form.value.arrondissement_id = ''
  form.value.quartier_id = ''
  arrondissements.value = []
  quartiers.value = []
  if (!form.value.commune_id) return
  try {
    const res = await localisationApi.getArrondissements(form.value.commune_id)
    arrondissements.value = (res.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement arrondissements:', e) }
}

async function onArrondissementChange() {
  form.value.quartier_id = ''
  quartiers.value = []
  if (!form.value.arrondissement_id) return
  try {
    const res = await localisationApi.getQuartiers(form.value.arrondissement_id)
    quartiers.value = (res.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement quartiers:', e) }
}

function getError(field) {
  return fieldErrors.value[field]?.[0]
}

async function submit() {
  error.value = ''
  fieldErrors.value = {}
  loading.value = true
  try {
    const payload = { ...form.value }
    if (payload.superficie) payload.superficie = parseFloat(payload.superficie)
    if (payload.latitude) payload.latitude = parseFloat(payload.latitude)
    if (payload.longitude) payload.longitude = parseFloat(payload.longitude)
    if (payload.prix_estimatif) payload.prix_estimatif = parseFloat(payload.prix_estimatif)
    if (!payload.latitude) delete payload.latitude
    if (!payload.longitude) delete payload.longitude
    if (!payload.prix_estimatif) delete payload.prix_estimatif

    if (isEdit) {
      await parcelleApi.update(route.params.id, payload)
      router.push({ name: 'CitoyenParcelleDetail', params: { id: route.params.id } })
    } else {
      const res = await parcelleApi.store(payload)
      const id = res.data?.id
      if (id) router.push({ name: 'CitoyenParcelleDetail', params: { id } })
      else router.push('/citoyen/parcelles')
    }
  } catch (e) {
    if (e.response?.status === 422) {
      fieldErrors.value = e.response.data?.errors || {}
      error.value = Object.values(fieldErrors.value).flat().join(', ') || 'Données invalides'
    } else {
      error.value = e.response?.data?.message || 'Une erreur est survenue'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page-wrap max-w-2xl mx-auto">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-8">
      <button @click="goBack(router)" class="btn btn-ghost btn-icon text-stone-500">
        <i class="fas fa-arrow-left"></i>
      </button>
      <div>
        <h1 class="page-title">{{ isEdit ? 'Modifier la parcelle' : 'Déclarer une parcelle' }}</h1>
        <p class="page-subtitle">{{ isEdit ? 'Mettez à jour les informations de votre bien' : 'Enregistrez un nouveau bien foncier' }}</p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-5">
      <!-- Error alert -->
      <div v-if="error" class="alert alert-danger">
        <i class="fas fa-triangle-exclamation shrink-0"></i>
        <span>{{ error }}</span>
      </div>

      <!-- Titre -->
      <div class="card p-6">
        <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
          <i class="fas fa-circle-info text-brand"></i> Informations générales
        </h3>
        <div class="space-y-4">
          <div>
            <label class="form-label">Titre de la parcelle <span class="text-red-500">*</span></label>
            <input v-model="form.titre" type="text" class="form-input" :class="getError('titre') ? 'form-input-error' : ''" placeholder="Ex : Terrain résidentiel Cotonou" required />
            <p v-if="getError('titre')" class="form-error">{{ getError('titre') }}</p>
          </div>
          <div>
            <label class="form-label">Description</label>
            <textarea v-model="form.description" rows="3" class="form-input resize-none" :class="getError('description') ? 'form-input-error' : ''" placeholder="Description du terrain, caractéristiques, environnement…"></textarea>
            <p v-if="getError('description')" class="form-error">{{ getError('description') }}</p>
          </div>
        </div>
      </div>

      <!-- Localisation -->
      <div class="card p-6">
        <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
          <i class="fas fa-location-dot text-brand"></i> Localisation
        </h3>
        <div class="space-y-4">
          <div>
            <label class="form-label">Commune <span class="text-red-500">*</span></label>
            <select v-model="form.commune_id" class="form-select" :class="getError('commune_id') ? 'form-input-error' : ''" @change="onCommuneChange" required>
              <option value="">Sélectionner une commune</option>
              <option v-for="c in communes" :key="c.id" :value="c.id">{{ c.nom }}</option>
            </select>
            <p v-if="getError('commune_id')" class="form-error">{{ getError('commune_id') }}</p>
          </div>
          <div class="form-row">
            <div>
              <label class="form-label">Arrondissement</label>
              <select v-model="form.arrondissement_id" class="form-select" :disabled="!form.commune_id" @change="onArrondissementChange">
                <option value="">Sélectionner</option>
                <option v-for="a in arrondissements" :key="a.id" :value="a.id">{{ a.nom }}</option>
              </select>
            </div>
            <div>
              <label class="form-label">Quartier</label>
              <select v-model="form.quartier_id" class="form-select" :disabled="!form.arrondissement_id">
                <option value="">Sélectionner</option>
                <option v-for="q in quartiers" :key="q.id" :value="q.id">{{ q.nom }}</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div>
              <label class="form-label">Latitude GPS</label>
              <input v-model="form.latitude" type="number" step="any" class="form-input font-mono" placeholder="6.3654" />
            </div>
            <div>
              <label class="form-label">Longitude GPS</label>
              <input v-model="form.longitude" type="number" step="any" class="form-input font-mono" placeholder="2.4183" />
            </div>
          </div>
        </div>
      </div>

      <!-- Caractéristiques -->
      <div class="card p-6">
        <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
          <i class="fas fa-ruler-combined text-brand"></i> Caractéristiques
        </h3>
        <div class="form-row">
          <div>
            <label class="form-label">Superficie (m²)</label>
            <div class="relative">
              <input v-model="form.superficie" type="number" step="any" min="0" class="form-input pr-10" :class="getError('superficie') ? 'form-input-error' : ''" placeholder="500" />
              <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none">m²</span>
            </div>
            <p v-if="getError('superficie')" class="form-error">{{ getError('superficie') }}</p>
          </div>
          <div>
            <label class="form-label">Prix estimatif (FCFA)</label>
            <div class="relative">
              <input v-model="form.prix_estimatif" type="number" step="any" min="0" class="form-input pr-16" placeholder="5000000" />
              <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs pointer-events-none font-semibold">FCFA</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-lg" :disabled="loading">
          <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
          <i v-else :class="['fas', isEdit ? 'fa-floppy-disk' : 'fa-plus']"></i>
          {{ loading ? (isEdit ? 'Enregistrement…' : 'Déclaration…') : (isEdit ? 'Enregistrer' : 'Déclarer la parcelle') }}
        </button>
        <button type="button" @click="goBack(router)" class="btn btn-ghost">Annuler</button>
      </div>
    </form>
  </div>
</template>
