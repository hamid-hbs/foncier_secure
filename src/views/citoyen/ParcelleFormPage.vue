<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import parcelleApi from '@/api/parcelle'
import localisationApi from '@/api/localisation'

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
  } catch { /* ignore */ }

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
    } catch { /* ignore */ }
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
  } catch { /* ignore */ }
}

async function onArrondissementChange() {
  form.value.quartier_id = ''
  quartiers.value = []
  if (!form.value.arrondissement_id) return
  try {
    const res = await localisationApi.getQuartiers(form.value.arrondissement_id)
    quartiers.value = (res.data || []).filter(Boolean)
  } catch { /* ignore */ }
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
    } else {
      await parcelleApi.store(payload)
    }
    router.push({ name: 'MesParcelles' })
  } catch (e) {
    if (e.response?.status === 422) {
      fieldErrors.value = e.response?.data?.errors || {}
      const messages = Object.values(fieldErrors.value).flat()
      error.value = messages.length ? messages.join(' · ') : 'Données invalides'
    } else {
      error.value = e.response?.data?.message || "Erreur lors de l'enregistrement"
    }
  }
  loading.value = false
}
</script>

<template>
  <div class="page-container max-w-3xl">
    <button @click="router.push({ name: 'MesParcelles' })" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #D1FAE5;">
        <i class="fas fa-map-pin" style="color: var(--green-tree);"></i>
      </div>
      <div>
        <h1 class="section-title">{{ isEdit ? 'Modifier la parcelle' : 'Nouvelle parcelle' }}</h1>
        <p class="section-subtitle">{{ isEdit ? 'Modifiez les informations de votre parcelle' : 'Enregistrez une nouvelle parcelle' }}</p>
      </div>
    </div>

    <form @submit.prevent="submit" class="card space-y-6">
      <div v-if="error" class="p-3.5 rounded-lg text-sm" style="background: #FEE2E2; color: var(--danger); border: 1px solid #FECACA;">
        {{ error }}
      </div>

      <div>
        <h3 class="font-semibold mb-4" style="color: var(--green-tree);">Informations générales</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-group">
            <label class="form-label">Titre</label>
            <input v-model="form.titre" class="form-input" placeholder="ex: Parcelle familiale" />
            <p v-if="getError('titre')" class="text-xs mt-1" style="color: var(--danger);">{{ getError('titre') }}</p>
          </div>
          <div class="form-group">
            <label class="form-label">Superficie (m²)</label>
            <input v-model="form.superficie" type="number" step="0.01" class="form-input" :class="getError('superficie') ? 'form-input-error' : ''" placeholder="500" />
            <p v-if="getError('superficie')" class="text-xs mt-1" style="color: var(--danger);">{{ getError('superficie') }}</p>
          </div>
        </div>
        <div class="form-group mt-4">
          <label class="form-label">Description</label>
          <textarea v-model="form.description" class="form-textarea" placeholder="Description de la parcelle..." rows="3"></textarea>
        </div>
      </div>

      <hr class="border-t" style="border-color: var(--border);" />

      <div>
        <h3 class="font-semibold mb-4" style="color: var(--green-tree);">Localisation</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="form-group">
            <label class="form-label">Commune</label>
            <select v-model="form.commune_id" class="form-select" :class="getError('commune_id') ? 'form-input-error' : ''" @change="onCommuneChange">
              <option value="">Sélectionnez</option>
              <option v-for="c in communes" :key="c.id" :value="c.id">{{ c.nom }}</option>
            </select>
            <p v-if="getError('commune_id')" class="text-xs mt-1" style="color: var(--danger);">{{ getError('commune_id') }}</p>
          </div>
          <div class="form-group">
            <label class="form-label">Arrondissement</label>
            <select v-model="form.arrondissement_id" class="form-select" :class="getError('arrondissement_id') ? 'form-input-error' : ''" @change="onArrondissementChange" :disabled="!form.commune_id">
              <option value="">Sélectionnez</option>
              <option v-for="a in arrondissements" :key="a.id" :value="a.id">{{ a.nom }}</option>
            </select>
            <p v-if="getError('arrondissement_id')" class="text-xs mt-1" style="color: var(--danger);">{{ getError('arrondissement_id') }}</p>
          </div>
          <div class="form-group">
            <label class="form-label">Quartier</label>
            <select v-model="form.quartier_id" class="form-select" :class="getError('quartier_id') ? 'form-input-error' : ''" :disabled="!form.arrondissement_id">
              <option value="">Sélectionnez</option>
              <option v-for="q in quartiers" :key="q.id" :value="q.id">{{ q.nom }}</option>
            </select>
            <p v-if="getError('quartier_id')" class="text-xs mt-1" style="color: var(--danger);">{{ getError('quartier_id') }}</p>
          </div>
        </div>
      </div>

      <hr class="border-t" style="border-color: var(--border);" />

      <div>
        <h3 class="font-semibold mb-4" style="color: var(--green-tree);">Coordonnées GPS et prix</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="form-group">
            <label class="form-label">Latitude</label>
            <input v-model="form.latitude" type="number" step="any" class="form-input" placeholder="6.3700" />
          </div>
          <div class="form-group">
            <label class="form-label">Longitude</label>
            <input v-model="form.longitude" type="number" step="any" class="form-input" placeholder="2.4100" />
          </div>
          <div class="form-group">
            <label class="form-label">Prix estimatif (FCFA)</label>
            <input v-model="form.prix_estimatif" type="number" step="any" class="form-input" placeholder="5 000 000" />
          </div>
        </div>
      </div>

      <hr class="border-t" style="border-color: var(--border);" />

      <button type="submit" class="btn-green btn-full flex-center gap-2" :disabled="loading">
        <i class="fas fa-paper-plane"></i> {{ loading ? 'Enregistrement...' : (isEdit ? 'Mettre à jour' : 'Enregistrer la parcelle') }}
      </button>
    </form>
  </div>
</template>
