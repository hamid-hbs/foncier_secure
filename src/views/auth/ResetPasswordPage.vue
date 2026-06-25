<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import authApi from '@/api/auth'

const router = useRouter()
const email = ref('')
const otp = ref('')
const password = ref('')
const passwordConfirm = ref('')
const loading = ref(false)
const error = ref('')
const errors = ref({})
const success = ref(false)
const showPassword = ref(false)

async function handleSubmit() {
  loading.value = true
  error.value = ''
  errors.value = {}
  try {
    await authApi.resetPassword({ email: email.value, otp: otp.value, password: password.value, password_confirmation: passwordConfirm.value })
    success.value = true
    setTimeout(() => router.push('/auth/login'), 2000)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ') || 'Données invalides'
    } else {
      error.value = e.response?.data?.message || 'Code invalide ou expiré'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-6" style="background: var(--bg);">
    <div class="w-full max-w-md animate-slide-up">
      <div class="bg-white rounded-2xl border border-stone-200 shadow-lg p-8">
        <div class="flex items-center gap-2.5 mb-8">
          <div class="w-9 h-9 rounded-xl bg-brand flex items-center justify-center">
            <span class="font-display font-extrabold text-white text-sm">FS</span>
          </div>
          <span class="font-display font-extrabold text-stone-900 text-xl">Foncier<span class="text-brand">Secure</span></span>
        </div>

        <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center mb-6">
          <i class="fas fa-key text-brand text-xl"></i>
        </div>

        <h1 class="font-display font-extrabold text-2xl text-stone-900 mb-2">Réinitialiser le mot de passe</h1>
        <p class="text-stone-500 text-sm mb-7">Saisissez le code reçu par email et choisissez votre nouveau mot de passe.</p>

        <div v-if="success" class="alert alert-success mb-4">
          <i class="fas fa-check-circle shrink-0"></i>
          <span>Mot de passe mis à jour ! Redirection vers la connexion…</span>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="error" class="alert alert-danger">
            <i class="fas fa-triangle-exclamation shrink-0"></i>
            <span>{{ error }}</span>
          </div>

          <div>
            <label class="form-label">Email</label>
            <div class="relative">
              <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="email" type="email" class="form-input pl-10" placeholder="vous@email.com" required />
            </div>
          </div>

          <div>
            <label class="form-label">Code reçu par email</label>
            <div class="relative">
              <i class="fas fa-hashtag absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="otp" type="text" class="form-input pl-10 font-mono tracking-widest" :class="errors.otp ? 'form-input-error' : ''" placeholder="123456" required />
            </div>
            <p v-if="errors.otp" class="form-error">{{ errors.otp[0] }}</p>
          </div>

          <div>
            <label class="form-label">Nouveau mot de passe</label>
            <div class="relative">
              <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="password" :type="showPassword ? 'text' : 'password'" class="form-input pl-10 pr-10" :class="errors.password ? 'form-input-error' : ''" placeholder="8 caractères min." required />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700">
                <i :class="['fas', showPassword ? 'fa-eye-slash' : 'fa-eye', 'text-sm']"></i>
              </button>
            </div>
            <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
          </div>

          <div>
            <label class="form-label">Confirmer le mot de passe</label>
            <div class="relative">
              <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="passwordConfirm" :type="showPassword ? 'text' : 'password'" class="form-input pl-10" :class="errors.password_confirmation ? 'form-input-error' : ''" placeholder="Répéter" required />
            </div>
            <p v-if="errors.password_confirmation" class="form-error">{{ errors.password_confirmation[0] }}</p>
          </div>

          <button type="submit" class="btn btn-primary btn-full btn-lg" :disabled="loading">
            <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
            <i v-else class="fas fa-check"></i>
            {{ loading ? 'Mise à jour…' : 'Réinitialiser' }}
          </button>
        </form>

        <p class="text-center text-sm text-stone-500 mt-6">
          <router-link to="/auth/login" class="font-bold text-brand hover:text-brand-light transition-colors flex items-center justify-center gap-2">
            <i class="fas fa-arrow-left text-xs"></i> Retour à la connexion
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>
