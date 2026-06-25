<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()
const editing = ref(false)
const form = ref({ nom: '', prenom: '', telephone: '' })
const error = ref('')
const errors = ref({})
const loading = ref(false)

onMounted(() => {
  if (auth.user) {
    form.value = {
      nom: auth.user.nom || '',
      prenom: auth.user.prenom || '',
      telephone: auth.user.telephone || '',
    }
  }
})

const trustScore = computed(() => auth.user?.indice_confiance || 0)

const trustLevel = computed(() => {
  if (trustScore.value >= 80) return { label: 'Excellence', color: 'var(--success)' }
  if (trustScore.value >= 50) return { label: 'Confirmé', color: 'var(--brand)' }
  if (trustScore.value >= 20) return { label: 'En construction', color: 'var(--gold)' }
  return { label: 'Nouveau', color: 'var(--text-3)' }
})

async function save() {
  error.value = ''
  errors.value = {}
  loading.value = true
  try {
    await auth.updateProfile(form.value)
    editing.value = false
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || 'Erreur lors de la sauvegarde'
    }
  } finally {
    loading.value = false
  }
}

function startEditing() {
  form.value = {
    nom: auth.user?.nom || '',
    prenom: auth.user?.prenom || '',
    telephone: auth.user?.telephone || '',
  }
  editing.value = true
}

const roleLabel = computed(() => {
  const r = auth.userRole
  if (r === 'citoyen') return 'Citoyen'
  if (r === 'notaire') return 'Notaire'
  if (r === 'geometre') return 'Géomètre'
  if (r === 'admin') return 'Administrateur'
  return r
})
</script>

<template>
  <div class="page-wrap max-w-2xl mx-auto">
    <!-- Pending approval banner -->
    <div v-if="auth.pendingApproval" class="alert alert-warning mb-6 !border-gold !bg-gold/10">
      <div class="flex items-start gap-3 w-full">
        <i class="fas fa-hourglass-half text-gold shrink-0 mt-0.5"></i>
        <div class="flex-1">
          <p class="font-bold text-stone-900">Compte en attente de validation</p>
          <p class="text-sm text-stone-600 mt-0.5">Votre compte est en attente d'approbation par un administrateur. Vous pourrez utiliser toutes les fonctionnalités dès que votre compte sera activé.</p>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Mon profil</h1>
        <p class="page-subtitle">Gérez vos informations personnelles</p>
      </div>
    </div>

    <div v-if="error" class="alert alert-danger mb-6">
      <i class="fas fa-triangle-exclamation shrink-0"></i>
      <span>{{ error }}</span>
    </div>

    <div class="card mb-5">
      <div class="flex flex-col sm:flex-row sm:items-center gap-5 pb-6 mb-6 border-b border-stone-100">
        <div class="w-20 h-20 rounded-2xl bg-brand flex items-center justify-center font-display font-extrabold text-white text-2xl shrink-0 shadow-brand">
          {{ (auth.user?.prenom || '?')[0] }}{{ (auth.user?.nom || '?')[0] }}
        </div>
        <div class="flex-1">
          <h2 class="font-display font-bold text-2xl text-stone-900">{{ auth.user?.prenom }} {{ auth.user?.nom }}</h2>
          <span class="badge badge-info mt-1">{{ roleLabel }}</span>
          <p class="text-sm text-stone-400 mt-1">{{ auth.user?.email }}</p>
        </div>
        <button v-if="!editing" @click="startEditing" class="btn btn-outline btn-sm self-start sm:self-auto">
          <i class="fas fa-pen"></i> Modifier
        </button>
      </div>

      <div v-if="!editing" class="space-y-3">
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-stone-50">
          <i class="fas fa-envelope w-5 text-center text-stone-400 shrink-0"></i>
          <div>
            <p class="text-xs text-stone-400 font-medium">Email</p>
            <p class="text-sm font-semibold text-stone-900">{{ auth.user?.email }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-stone-50">
          <i class="fas fa-phone w-5 text-center text-stone-400 shrink-0"></i>
          <div>
            <p class="text-xs text-stone-400 font-medium">Téléphone</p>
            <p class="text-sm font-semibold text-stone-900">{{ auth.user?.telephone || '—' }}</p>
          </div>
        </div>

        <div class="px-4 py-4 rounded-xl bg-brand-50 border border-brand-100">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <i class="fas fa-shield-halved text-brand text-sm"></i>
              <span class="text-sm font-bold text-brand">Indice de confiance</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-sm font-bold text-stone-900">{{ trustScore }}/100</span>
              <span class="badge badge-info">{{ trustLevel.label }}</span>
            </div>
          </div>
          <div class="w-full h-2 rounded-full bg-white overflow-hidden">
            <div class="h-full rounded-full transition-all duration-700" :style="{ width: trustScore + '%', background: trustLevel.color }"></div>
          </div>
        </div>
      </div>

      <form v-else @submit.prevent="save" class="space-y-4">
        <div class="form-row">
          <div>
            <label class="form-label">Nom</label>
            <input v-model="form.nom" type="text" class="form-input" :class="errors.nom ? 'form-input-error' : ''" required />
            <p v-if="errors.nom" class="form-error">{{ errors.nom[0] }}</p>
          </div>
          <div>
            <label class="form-label">Prénom</label>
            <input v-model="form.prenom" type="text" class="form-input" :class="errors.prenom ? 'form-input-error' : ''" required />
            <p v-if="errors.prenom" class="form-error">{{ errors.prenom[0] }}</p>
          </div>
        </div>
        <div>
          <label class="form-label">Téléphone</label>
          <div class="relative">
            <i class="fas fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
            <input v-model="form.telephone" type="tel" class="form-input pl-10" :class="errors.telephone ? 'form-input-error' : ''" />
          </div>
          <p v-if="errors.telephone" class="form-error">{{ errors.telephone[0] }}</p>
        </div>
        <div class="flex gap-3 pt-2">
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
            <i v-else class="fas fa-floppy-disk"></i>
            {{ loading ? 'Enregistrement…' : 'Enregistrer' }}
          </button>
          <button type="button" @click="editing = false" class="btn btn-ghost">
            <i class="fas fa-times"></i> Annuler
          </button>
        </div>
      </form>
    </div>

    <div class="card mb-5">
      <h3 class="font-display font-bold text-stone-900 mb-4">
        <i class="fas fa-briefcase text-stone-400 mr-2"></i>
        Informations professionnelles
      </h3>
      <div v-if="auth.user?.professionnel" class="space-y-3">
        <div class="flex items-center gap-3 py-2.5 border-b border-stone-100">
          <span class="text-sm font-semibold text-stone-500 min-w-[120px]">Type</span>
          <span class="text-sm text-stone-900 font-medium capitalize">{{ auth.user.professionnel.type }}</span>
        </div>
        <div v-if="auth.user.professionnel.cabinet" class="flex items-center gap-3 py-2.5 border-b border-stone-100">
          <span class="text-sm font-semibold text-stone-500 min-w-[120px]">Cabinet</span>
          <span class="text-sm text-stone-900 font-medium">{{ auth.user.professionnel.cabinet }}</span>
        </div>
        <div v-if="auth.user.professionnel.numero_agrement" class="flex items-center gap-3 py-2.5 border-b border-stone-100">
          <span class="text-sm font-semibold text-stone-500 min-w-[120px]">N° d'agrément</span>
          <span class="text-sm text-stone-900 font-medium">{{ auth.user.professionnel.numero_agrement }}</span>
        </div>
        <div v-if="auth.user.professionnel.zone_intervention" class="flex items-center gap-3 py-2.5 border-b border-stone-100">
          <span class="text-sm font-semibold text-stone-500 min-w-[120px]">Zone</span>
          <span class="text-sm text-stone-900 font-medium">{{ auth.user.professionnel.zone_intervention }}</span>
        </div>
        <div v-if="auth.user.professionnel.specialites?.length" class="flex items-start gap-3 py-2.5">
          <span class="text-sm font-semibold text-stone-500 min-w-[120px]">Spécialités</span>
          <div class="flex flex-wrap gap-1.5">
            <span v-for="s in auth.user.professionnel.specialites" :key="s" class="badge badge-info">{{ s }}</span>
          </div>
        </div>
      </div>
      <div v-else-if="auth.userRole === 'notaire' || auth.userRole === 'geometre'" class="flex items-center gap-3 p-4 rounded-xl bg-stone-50">
        <i class="fas fa-clock text-stone-400 shrink-0"></i>
        <p class="text-sm text-stone-500">Votre profil professionnel sera configuré par un administrateur après validation de votre compte.</p>
      </div>
      <div v-else class="text-sm text-stone-400">Aucune information professionnelle.</div>
    </div>
  </div>
</template>
