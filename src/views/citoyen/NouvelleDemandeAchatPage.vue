<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import demandeAchatApi from '@/api/demandeAchat'
import parcelleApi from '@/api/parcelle'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const route = useRoute()
const form = ref({ parcelle_id: route.query.parcelle_id || '', type: 'vente', message: '', prix_propose: '' })
const parcellesLibres = ref([])
const loading = ref(false)
const error = ref('')
const errors = ref({})
const success = ref(false)

const selectedParcelle = computed(() => parcellesLibres.value.find(p => p.id == form.value.parcelle_id))

onMounted(async () => {
  try {
    const res = await parcelleApi.list({ statut: 'libre', per_page: 50 })
    parcellesLibres.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement parcelles:', e) }
})

async function submit() {
  loading.value = true
  error.value = ''
  errors.value = {}
  try {
    const payload = { ...form.value }
    if (payload.prix_propose) payload.prix_propose = parseFloat(payload.prix_propose)
    await demandeAchatApi.create(payload)
    success.value = true
    setTimeout(() => router.push('/citoyen/demandes-achat'), 1500)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || 'Erreur lors de la soumission'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page-wrap max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-8">
      <button @click="goBack(router)" class="btn btn-ghost btn-icon text-stone-500">
        <i class="fas fa-arrow-left"></i>
      </button>
      <div>
        <h1 class="page-title">Demande d'achat</h1>
        <p class="page-subtitle">Faites une offre d'acquisition pour une parcelle</p>
      </div>
    </div>

    <div v-if="success" class="card">
      <div class="empty-state py-12">
        <div class="empty-icon bg-success/10 text-success"><i class="fas fa-check-double"></i></div>
        <p class="empty-title">Demande envoyée !</p>
        <p class="empty-text">Votre demande d'achat a été soumise. Le propriétaire sera notifié.</p>
      </div>
    </div>

    <form v-else @submit.prevent="submit" class="space-y-5">
      <div v-if="error" class="alert alert-danger">
        <i class="fas fa-triangle-exclamation shrink-0"></i>
        <span>{{ error }}</span>
      </div>

      <div class="card p-6 space-y-5">
        <!-- Parcelle -->
        <div>
          <label class="form-label">Parcelle concernée <span class="text-red-500">*</span></label>
          <select v-model="form.parcelle_id" class="form-select" :class="errors.parcelle_id ? 'form-input-error' : ''" required>
            <option value="">Sélectionner une parcelle</option>
            <option v-for="p in parcellesLibres" :key="p.id" :value="p.id">
              {{ p.titre || p.code || '#' + p.id }} — {{ p.commune?.nom || '' }}
            </option>
          </select>
          <p v-if="errors.parcelle_id" class="form-error">{{ errors.parcelle_id[0] }}</p>
        </div>

        <!-- Selected parcelle info -->
        <div v-if="selectedParcelle" class="flex items-center gap-3 p-3 rounded-xl bg-brand-50 border border-brand-100">
          <div class="w-9 h-9 rounded-xl bg-brand-100 flex items-center justify-center text-brand shrink-0">
            <i class="fas fa-map-marker-alt text-sm"></i>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-bold text-stone-900 text-sm truncate">{{ selectedParcelle.titre || 'Parcelle #' + selectedParcelle.id }}</p>
            <p class="text-xs text-stone-500">{{ [selectedParcelle.commune?.nom, selectedParcelle.arrondissement?.nom].filter(Boolean).join(' · ') }}</p>
          </div>
          <div v-if="selectedParcelle.prix_estimatif" class="text-right shrink-0">
            <p class="text-xs text-stone-400">Prix affiché</p>
            <p class="font-bold text-brand text-sm">{{ Number(selectedParcelle.prix_estimatif).toLocaleString('fr-FR') }} FCFA</p>
          </div>
        </div>

        <!-- Prix proposé -->
        <div>
          <label class="form-label">Prix proposé (FCFA)</label>
          <div class="relative">
            <i class="fas fa-tag absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
            <input v-model="form.prix_propose" type="number" min="0" step="any" class="form-input pl-10 pr-16" placeholder="Votre offre" />
            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-xs font-semibold pointer-events-none">FCFA</span>
          </div>
        </div>

        <!-- Message -->
        <div>
          <label class="form-label">Message au vendeur</label>
          <textarea v-model="form.message" rows="4" class="form-input resize-none" placeholder="Présentez-vous et expliquez votre projet d'acquisition…"></textarea>
        </div>
      </div>

      <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-lg" :disabled="loading">
          <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
          <i v-else class="fas fa-paper-plane"></i>
          {{ loading ? 'Envoi…' : 'Envoyer la demande' }}
        </button>
        <button type="button" @click="goBack(router)" class="btn btn-ghost">Annuler</button>
      </div>
    </form>
  </div>
</template>
