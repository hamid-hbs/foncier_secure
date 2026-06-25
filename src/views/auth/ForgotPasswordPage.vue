<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import authApi from '@/api/auth'

const router = useRouter()
const email = ref('')
const loading = ref(false)
const error = ref('')
const success = ref(false)

async function handleSubmit() {
  loading.value = true
  error.value = ''
  try {
    await authApi.sendOtp(email.value)
    success.value = true
  } catch (e) {
    error.value = e.response?.data?.message || 'Une erreur est survenue'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-6" style="background: var(--bg);">
    <div class="w-full max-w-md animate-slide-up">
      <!-- Card -->
      <div class="bg-white rounded-2xl border border-stone-200 shadow-lg p-8">
        <!-- Logo -->
        <div class="flex items-center gap-2.5 mb-8">
          <div class="w-9 h-9 rounded-xl bg-brand flex items-center justify-center">
            <span class="font-display font-extrabold text-white text-sm">FS</span>
          </div>
          <span class="font-display font-extrabold text-stone-900 text-xl">Foncier<span class="text-brand">Secure</span></span>
        </div>

        <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center mb-6">
          <i class="fas fa-envelope-open-text text-brand text-xl"></i>
        </div>

        <h1 class="font-display font-extrabold text-2xl text-stone-900 mb-2">Mot de passe oublié ?</h1>
        <p class="text-stone-500 text-sm mb-7 leading-relaxed">Saisissez votre email et nous vous enverrons un code de réinitialisation.</p>

        <div v-if="success" class="alert alert-success mb-6">
          <i class="fas fa-check-circle shrink-0"></i>
          <span>Un email de réinitialisation a été envoyé. Vérifiez votre boîte mail.</span>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-5">
          <div v-if="error" class="alert alert-danger">
            <i class="fas fa-triangle-exclamation shrink-0"></i>
            <span>{{ error }}</span>
          </div>
          <div>
            <label class="form-label">Adresse email</label>
            <div class="relative">
              <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="email" type="email" class="form-input pl-10" placeholder="vous@email.com" required />
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-full btn-lg" :disabled="loading">
            <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
            <i v-else class="fas fa-paper-plane"></i>
            {{ loading ? 'Envoi…' : 'Envoyer le code' }}
          </button>
        </form>

        <div class="mt-6 text-center space-y-2">
          <p class="text-sm text-stone-500">
            <router-link to="/auth/login" class="font-bold text-brand hover:text-brand-light transition-colors flex items-center justify-center gap-2">
              <i class="fas fa-arrow-left text-xs"></i> Retour à la connexion
            </router-link>
          </p>
          <p class="text-sm text-stone-500">
            Vous avez un code ?
            <router-link to="/auth/reset-password" class="font-bold text-brand hover:text-brand-light transition-colors">Réinitialiser</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
