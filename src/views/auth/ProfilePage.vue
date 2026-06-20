<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { goBack } from '@/utils/navigation'

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
  if (trustScore.value >= 80) return { label: 'Excellence', bar: 'var(--green-tree)' }
  if (trustScore.value >= 50) return { label: 'Confirmé', bar: '#457B9D' }
  if (trustScore.value >= 20) return { label: 'En construction', bar: 'var(--gold)' }
  return { label: 'Nouveau', bar: '#D1D5DB' }
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

async function handleRequestRole(role) {
  try {
    await auth.requestRole({ role })
    alert('Demande de changement de rôle envoyée avec succès')
  } catch {
    alert('Erreur lors de la demande')
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
</script>

<template>
  <div class="page-container max-w-2xl">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="flex items-center gap-3 mb-8">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--bg-page); color: var(--green-tree);">
        <i class="fas fa-user"></i>
      </div>
      <div>
        <h1 class="section-title">Mon profil</h1>
        <p class="section-subtitle">Gérez vos informations personnelles</p>
      </div>
    </div>

    <div v-if="error" class="p-3.5 rounded-lg text-sm mb-4" style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">{{ error }}</div>

    <div class="card mb-6">
      <div class="flex items-center gap-5 mb-6 pb-6" style="border-bottom: 1px solid var(--border);">
        <div class="w-16 h-16 rounded-xl flex items-center justify-center text-white font-bold text-2xl" style="background: var(--green-tree);">
          {{ (auth.user?.nom || '?')[0] }}{{ (auth.user?.prenom || '?')[0] }}
        </div>
        <div>
          <h2 class="text-xl font-bold" style="color: var(--text-primary);">{{ auth.user?.prenom }} {{ auth.user?.nom }}</h2>
          <span class="badge mt-1" style="background: #F0F7F4; color: var(--green-tree);">{{ auth.user?.role || 'Citoyen' }}</span>
        </div>
        <button v-if="!editing" @click="startEditing" class="btn-outline btn-sm flex items-center gap-2 ml-auto">
          <i class="fas fa-pen"></i> Modifier
        </button>
      </div>

      <div v-if="!editing" class="space-y-4">
        <div class="flex items-center gap-3 py-3 px-4 rounded-lg" style="background: var(--bg-page);">
          <i class="fas fa-envelope" style="color: var(--text-secondary);"></i>
          <div>
            <p class="text-sm" style="color: var(--text-secondary);">Email</p>
            <p class="text-sm font-medium" style="color: var(--text-primary);">{{ auth.user?.email }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3 py-3 px-4 rounded-lg" style="background: var(--bg-page);">
          <i class="fas fa-phone" style="color: var(--text-secondary);"></i>
          <div>
            <p class="text-sm" style="color: var(--text-secondary);">Téléphone</p>
            <p class="text-sm font-medium" style="color: var(--text-primary);">{{ auth.user?.telephone || '-' }}</p>
          </div>
        </div>
        <div class="p-4 rounded-lg" style="background: #F0F7F4;">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <i class="fas fa-shield-alt" style="color: var(--green-tree);"></i>
              <span class="text-sm font-semibold" style="color: var(--green-tree);">Indice de confiance</span>
            </div>
            <span class="text-xs font-medium" style="color: var(--text-secondary);">{{ trustLevel.label }}</span>
          </div>
          <div class="w-full h-2.5 rounded-full overflow-hidden" style="background: rgba(255,255,255,0.6);">
            <div class="h-full rounded-full transition-all duration-500" :style="{ width: trustScore + '%', background: trustLevel.bar }"></div>
          </div>
          <div class="flex justify-between mt-1.5">
            <span class="text-xs" style="color: var(--text-secondary);">0</span>
            <span class="text-sm font-bold" style="color: var(--green-tree);">{{ trustScore }}/100</span>
            <span class="text-xs" style="color: var(--text-secondary);">100</span>
          </div>
        </div>
      </div>

      <div v-else class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">Nom</label>
            <input v-model="form.nom" class="form-input" :class="{ 'border-red-500': errors.nom }" />
            <p v-if="errors.nom" class="text-xs mt-1" style="color: #DC2626;">{{ errors.nom[0] }}</p>
          </div>
          <div>
            <label class="form-label">Prénom</label>
            <input v-model="form.prenom" class="form-input" :class="{ 'border-red-500': errors.prenom }" />
            <p v-if="errors.prenom" class="text-xs mt-1" style="color: #DC2626;">{{ errors.prenom[0] }}</p>
          </div>
        </div>
        <div>
          <label class="form-label">Téléphone</label>
          <input v-model="form.telephone" type="tel" class="form-input" :class="{ 'border-red-500': errors.telephone }" />
          <p v-if="errors.telephone" class="text-xs mt-1" style="color: #DC2626;">{{ errors.telephone[0] }}</p>
        </div>
        <div class="flex gap-3">
          <button @click="save" class="btn-green flex items-center gap-2" :disabled="loading">
            <i class="fas fa-floppy-disk"></i> {{ loading ? 'Sauvegarde...' : 'Enregistrer' }}
          </button>
          <button @click="editing = false" class="btn-outline flex items-center gap-2">
            <i class="fas fa-times"></i> Annuler
          </button>
        </div>
      </div>
    </div>

    <div v-if="auth.user?.type === 'professionnel' && auth.user?.professionnel" class="card mb-6">
      <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Informations professionnelles</h3>
      <div class="space-y-3">
        <div class="flex items-center gap-3 py-2" style="border-bottom: 1px solid var(--border);">
          <span class="text-sm font-medium" style="color: var(--text-secondary); min-width: 120px;">Type</span>
          <span style="color: var(--text-primary);">{{ auth.user.professionnel.type }}</span>
        </div>
        <div v-if="auth.user.professionnel.cabinet" class="flex items-center gap-3 py-2" style="border-bottom: 1px solid var(--border);">
          <span class="text-sm font-medium" style="color: var(--text-secondary); min-width: 120px;">Cabinet</span>
          <span style="color: var(--text-primary);">{{ auth.user.professionnel.cabinet }}</span>
        </div>
        <div v-if="auth.user.professionnel.zone_intervention" class="flex items-center gap-3 py-2" style="border-bottom: 1px solid var(--border);">
          <span class="text-sm font-medium" style="color: var(--text-secondary); min-width: 120px;">Zone</span>
          <span style="color: var(--text-primary);">{{ auth.user.professionnel.zone_intervention }}</span>
        </div>
        <div v-if="auth.user.professionnel.specialites?.length" class="flex items-center gap-3 py-2">
          <span class="text-sm font-medium" style="color: var(--text-secondary); min-width: 120px;">Spécialités</span>
          <div class="flex flex-wrap gap-1">
            <span v-for="s in auth.user.professionnel.specialites" :key="s" class="badge badge-info">{{ s }}</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="auth.user?.role === 'citoyen'" class="card">
      <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Demander un changement de rôle</h3>
      <p class="text-sm mb-5" style="color: var(--text-secondary);">Si vous êtes un professionnel, vous pouvez demander à changer de rôle.</p>
      <div class="flex flex-wrap gap-3">
        <button @click="handleRequestRole('geometre')" class="btn-outline btn-sm">Devenir géomètre</button>
        <button @click="handleRequestRole('notaire')" class="btn-outline btn-sm">Devenir notaire</button>
      </div>
    </div>
  </div>
</template>
