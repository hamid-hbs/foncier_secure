<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const auth = useAuthStore()
const form = ref({ nom: '', prenom: '', email: '', telephone: '', password: '', password_confirmation: '' })
const error = ref('')
const errors = ref({})

async function handleSubmit() {
  error.value = ''
  errors.value = {}
  try {
    await auth.register(form.value)
    router.push('/mon-profil')
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ') || "Erreur de validation"
    } else {
      error.value = e.response?.data?.message || "Erreur lors de l'inscription"
    }
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center px-4 py-8" style="background: var(--bg-page);">
    <div class="w-full max-w-lg">
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
        <i class="fas fa-arrow-left"></i> Retour
      </button>
      <div class="text-center mb-8">
        <router-link to="/" class="inline-flex items-center gap-2.5">
          <div class="w-10 h-10 flex items-center justify-center rounded-xl" style="background: var(--green-tree);">
            <span class="text-white font-bold text-lg">FS</span>
          </div>
          <span class="text-xl font-bold" style="color: var(--text-primary);">Foncier<span style="color: var(--green-tree);">Secure</span></span>
        </router-link>
      </div>
      <div class="card">
        <h1 class="text-2xl font-bold mb-1" style="color: var(--text-primary);">Créer un compte</h1>
        <p class="text-sm mb-6" style="color: var(--text-secondary);">Rejoignez la plateforme de sécurisation foncière</p>
        <form @submit.prevent="handleSubmit">
          <div v-if="error" class="p-3.5 rounded-lg text-sm mb-5" style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">{{ error }}</div>
          <div class="flex gap-4">
            <div class="form-group flex-1">
              <label class="form-label">Nom</label>
              <div class="relative">
                <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
                <input v-model="form.nom" class="form-input pl-10" :class="{ 'border-red-500': errors.nom }" placeholder="Votre nom" required />
              </div>
              <p v-if="errors.nom" class="text-xs mt-1" style="color: #DC2626;">{{ errors.nom[0] }}</p>
            </div>
            <div class="form-group flex-1">
              <label class="form-label">Prénom</label>
              <div class="relative">
                <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
                <input v-model="form.prenom" class="form-input pl-10" :class="{ 'border-red-500': errors.prenom }" placeholder="Votre prénom" required />
              </div>
              <p v-if="errors.prenom" class="text-xs mt-1" style="color: #DC2626;">{{ errors.prenom[0] }}</p>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <div class="relative">
              <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
              <input v-model="form.email" type="email" class="form-input pl-10" :class="{ 'border-red-500': errors.email }" placeholder="vous@email.com" required />
            </div>
            <p v-if="errors.email" class="text-xs mt-1" style="color: #DC2626;">{{ errors.email[0] }}</p>
          </div>
          <div class="form-group">
            <label class="form-label">Téléphone</label>
            <div class="relative">
              <i class="fas fa-phone absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
              <input v-model="form.telephone" type="tel" class="form-input pl-10" :class="{ 'border-red-500': errors.telephone }" placeholder="+229 XX XX XX XX" required />
            </div>
            <p v-if="errors.telephone" class="text-xs mt-1" style="color: #DC2626;">{{ errors.telephone[0] }}</p>
          </div>
          <div class="form-group">
            <label class="form-label">Mot de passe</label>
            <div class="relative">
              <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
              <input v-model="form.password" type="password" class="form-input pl-10" :class="{ 'border-red-500': errors.password }" placeholder="Minimum 8 caractères" required />
            </div>
            <p v-if="errors.password" class="text-xs mt-1" style="color: #DC2626;">{{ errors.password[0] }}</p>
          </div>
          <div class="form-group">
            <label class="form-label">Confirmer le mot de passe</label>
            <div class="relative">
              <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
              <input v-model="form.password_confirmation" type="password" class="form-input pl-10" :class="{ 'border-red-500': errors.password_confirmation }" placeholder="Répétez le mot de passe" required />
            </div>
            <p v-if="errors.password_confirmation" class="text-xs mt-1" style="color: #DC2626;">{{ errors.password_confirmation[0] }}</p>
          </div>
          <button type="submit" class="btn-green w-full" :disabled="auth.loading">
            <i class="fas fa-user-plus"></i> {{ auth.loading ? 'Inscription...' : 'Créer mon compte' }}
          </button>
        </form>
        <p class="text-center text-sm mt-6" style="color: var(--text-secondary);">
          Déjà inscrit ? <router-link to="/auth/login" class="font-medium" style="color: var(--green-tree);">Se connecter</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
