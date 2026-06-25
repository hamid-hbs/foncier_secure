<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { goBack } from '@/utils/navigation'
import verificationApi from '@/api/verification'

const route = useRoute()
const router = useRouter()
const verification = ref(null)
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const success = ref(false)

const form = ref({
  note_risque: 'faible',
  observations: '',
  recommendations: '',
  superficie_mesuree: '',
  coordonnees_gps: '',
})

const risques = [
  { value: 'faible', label: 'Faible', color: 'bg-success/10 text-success border-success/30' },
  { value: 'moyen', label: 'Moyen', color: 'bg-warn/10 text-warn border-warn/30' },
  { value: 'eleve', label: 'Élevé', color: 'bg-danger/10 text-danger border-danger/30' },
]

onMounted(async () => {
  try {
    const res = await verificationApi.show(route.params.id)
    verification.value = res.data || null
  } catch (e) { console.error('Erreur chargement vérification:', e) }
  loading.value = false
})

async function submit() {
  submitting.value = true
  error.value = ''
  try {
    await verificationApi.rapport(route.params.id, form.value)
    success.value = true
    setTimeout(() => router.push('/geometre/missions'), 1800)
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de la soumission du rapport'
  } finally {
    submitting.value = false
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
        <h1 class="page-title">Rapport de vérification</h1>
        <p class="page-subtitle">Mission #{{ route.params.id }}</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-24 rounded-2xl"></div>
      <div class="skeleton h-64 rounded-2xl"></div>
    </div>

    <!-- Success -->
    <div v-else-if="success" class="card">
      <div class="empty-state py-12">
        <div class="empty-icon bg-success/10 text-success"><i class="fas fa-check-double"></i></div>
        <p class="empty-title">Rapport soumis !</p>
        <p class="empty-text">Votre rapport de vérification a été transmis et sera examiné par l'administration.</p>
      </div>
    </div>

    <template v-else>
      <!-- Parcelle info -->
      <div v-if="verification" class="card mb-5">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center text-brand shrink-0">
            <i class="fas fa-map-marker-alt"></i>
          </div>
          <div>
            <h3 class="font-display font-bold text-stone-900">{{ verification.parcelle?.titre || 'Parcelle #' + verification.parcelle_id }}</h3>
            <p class="text-sm text-stone-400">{{ [verification.parcelle?.commune?.nom, verification.parcelle?.arrondissement?.nom].filter(Boolean).join(' · ') || 'Localisation inconnue' }}</p>
          </div>
          <div class="ml-auto">
            <span class="badge badge-info">{{ verification.statut }}</span>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-5">
        <div v-if="error" class="alert alert-danger">
          <i class="fas fa-triangle-exclamation shrink-0"></i>
          <span>{{ error }}</span>
        </div>

        <div class="card p-6 space-y-5">
          <!-- Risque -->
          <div>
            <label class="form-label">Niveau de risque <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-3 gap-3">
              <button
                v-for="r in risques" :key="r.value"
                type="button"
                @click="form.note_risque = r.value"
                class="py-3 px-4 rounded-xl border-2 font-bold text-sm transition-all"
                :class="form.note_risque === r.value ? r.color + ' border-2' : 'border-stone-200 text-stone-500 hover:border-stone-300'"
              >
                {{ r.label }}
              </button>
            </div>
          </div>

          <!-- Superficie mesurée -->
          <div>
            <label class="form-label">Superficie mesurée (m²)</label>
            <div class="relative">
              <i class="fas fa-ruler-combined absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="form.superficie_mesuree" type="number" step="any" class="form-input pl-10 pr-10" placeholder="Résultat du mesurage" />
              <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none">m²</span>
            </div>
          </div>

          <!-- Coordonnées GPS -->
          <div>
            <label class="form-label">Coordonnées GPS relevées</label>
            <div class="relative">
              <i class="fas fa-satellite-dish absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="form.coordonnees_gps" type="text" class="form-input pl-10 font-mono" placeholder="6.3654, 2.4183" />
            </div>
          </div>

          <!-- Observations -->
          <div>
            <label class="form-label">Observations terrain <span class="text-red-500">*</span></label>
            <textarea v-model="form.observations" rows="4" class="form-input resize-none" required placeholder="Décrivez l'état du terrain, les éventuels problèmes constatés, les limites exactes…"></textarea>
          </div>

          <!-- Recommandations -->
          <div>
            <label class="form-label">Recommandations</label>
            <textarea v-model="form.recommendations" rows="3" class="form-input resize-none" placeholder="Actions recommandées, précautions particulières…"></textarea>
          </div>
        </div>

        <div class="flex gap-3">
          <button type="submit" class="btn btn-primary btn-lg" :disabled="submitting">
            <div v-if="submitting" class="spinner spinner-sm border-white/30 border-t-white"></div>
            <i v-else class="fas fa-file-check"></i>
            {{ submitting ? 'Soumission…' : 'Soumettre le rapport' }}
          </button>
          <button type="button" @click="goBack(router)" class="btn btn-ghost">Annuler</button>
        </div>
      </form>
    </template>
  </div>
</template>
