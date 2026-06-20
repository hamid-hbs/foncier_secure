<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import authApi from '@/api/auth'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const email = ref('')
const otp = ref('')
const password = ref('')
const password_confirmation = ref('')
const loading = ref(false)
const error = ref('')
const errors = ref({})
const success = ref('')

async function resetPassword() {
  error.value = ''
  errors.value = {}
  success.value = ''
  if (password.value !== password_confirmation.value) {
    error.value = 'Les mots de passe ne correspondent pas'
    return
  }
  if (password.value.length < 8) {
    error.value = 'Le mot de passe doit contenir au moins 8 caractères'
    return
  }
  loading.value = true
  try {
    const res = await authApi.resetPassword({
      email: email.value,
      otp: otp.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    })
    success.value = res.data?.message || 'Mot de passe réinitialisé avec succès'
    setTimeout(() => router.push('/auth/login'), 2000)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || 'Erreur lors de la réinitialisation'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center px-4" style="background: var(--bg-page);">
    <div class="w-full max-w-md">
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
        <div class="flex items-center gap-3 mb-6">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: #F0F7F4; color: var(--green-tree);">
            <i class="fas fa-key"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold" style="color: var(--text-primary);">Réinitialiser le mot de passe</h1>
            <p class="text-sm" style="color: var(--text-secondary);">Entrez le code OTP et votre nouveau mot de passe</p>
          </div>
        </div>
        <div v-if="error" class="p-3.5 rounded-lg text-sm mb-5" style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">{{ error }}</div>
        <div v-if="success" class="p-3.5 rounded-lg text-sm mb-5" style="background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0;">{{ success }}</div>
        <form @submit.prevent="resetPassword">
          <div class="mb-4">
            <label class="form-label">Email</label>
            <div class="relative">
              <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
              <input v-model="email" type="email" class="form-input pl-10" :class="{ 'border-red-500': errors.email }" placeholder="vous@email.com" required />
            </div>
            <p v-if="errors.email" class="text-xs mt-1" style="color: #DC2626;">{{ errors.email[0] }}</p>
          </div>
          <div class="mb-4">
            <label class="form-label">Code OTP</label>
            <input v-model="otp" type="text" class="form-input text-center tracking-widest text-lg" :class="{ 'border-red-500': errors.otp }" placeholder="• • • • • •" maxlength="6" required />
            <p v-if="errors.otp" class="text-xs mt-1" style="color: #DC2626;">{{ errors.otp[0] }}</p>
          </div>
          <div class="mb-4">
            <label class="form-label">Nouveau mot de passe</label>
            <div class="relative">
              <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
              <input v-model="password" type="password" class="form-input pl-10" :class="{ 'border-red-500': errors.password }" placeholder="Minimum 8 caractères" required />
            </div>
            <p v-if="errors.password" class="text-xs mt-1" style="color: #DC2626;">{{ errors.password[0] }}</p>
          </div>
          <div class="mb-5">
            <label class="form-label">Confirmer le mot de passe</label>
            <div class="relative">
              <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
              <input v-model="password_confirmation" type="password" class="form-input pl-10" :class="{ 'border-red-500': errors.password_confirmation }" placeholder="Retaper le mot de passe" required />
            </div>
            <p v-if="errors.password_confirmation" class="text-xs mt-1" style="color: #DC2626;">{{ errors.password_confirmation[0] }}</p>
          </div>
          <button type="submit" class="btn-green w-full" :disabled="loading">
            <i class="fas fa-check"></i> {{ loading ? 'Réinitialisation...' : 'Réinitialiser' }}
          </button>
        </form>
        <div class="mt-6 text-center">
          <router-link to="/auth/login" class="inline-flex items-center gap-1.5 text-sm font-medium" style="color: var(--green-tree);">
            <i class="fas fa-arrow-left"></i> Retour à la connexion
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
