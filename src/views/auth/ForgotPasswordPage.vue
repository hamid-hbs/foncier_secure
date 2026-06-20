<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import authApi from '@/api/auth'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const email = ref('')
const loading = ref(false)
const error = ref('')
const errors = ref({})
const success = ref('')

async function sendOtp() {
  error.value = ''
  errors.value = {}
  success.value = ''
  loading.value = true
  try {
    const res = await authApi.sendOtp(email.value)
    success.value = res.data?.message || 'OTP envoyé à ' + email.value
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || "Erreur lors de l'envoi du code"
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
            <h1 class="text-xl font-bold" style="color: var(--text-primary);">Mot de passe oublié</h1>
            <p class="text-sm" style="color: var(--text-secondary);">Recevez un code OTP par email</p>
          </div>
        </div>
        <div v-if="error" class="p-3.5 rounded-lg text-sm mb-5" style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">{{ error }}</div>
        <div v-if="success" class="p-3.5 rounded-lg text-sm mb-5" style="background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0;">{{ success }}</div>
        <form @submit.prevent="sendOtp">
          <div class="mb-5">
            <label class="form-label">Email</label>
            <div class="relative">
              <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
              <input v-model="email" type="email" class="form-input pl-10" :class="{ 'border-red-500': errors.email }" placeholder="vous@email.com" required />
            </div>
            <p v-if="errors.email" class="text-xs mt-1" style="color: #DC2626;">{{ errors.email[0] }}</p>
          </div>
          <button type="submit" class="btn-green w-full" :disabled="loading">
            <i class="fas fa-paper-plane"></i> {{ loading ? 'Envoi...' : 'Envoyer le code OTP' }}
          </button>
        </form>
        <div class="mt-6 text-center">
          <router-link to="/auth/login" class="inline-flex items-center gap-1.5 text-sm font-medium" style="color: var(--green-tree);">
            <i class="fas fa-arrow-left"></i> Retour à la connexion
          </router-link>
          <span class="mx-2" style="color: var(--text-secondary);">|</span>
          <router-link to="/auth/reset-password" class="inline-flex items-center gap-1.5 text-sm font-medium" style="color: var(--green-tree);">
            J'ai déjà un code
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
