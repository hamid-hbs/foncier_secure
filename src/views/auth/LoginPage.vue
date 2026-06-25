<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const email = ref('')
const password = ref('')
const error = ref('')
const errors = ref({})
const showPassword = ref(false)
const pendingApproval = ref(false)

async function handleSubmit() {
  error.value = ''
  errors.value = {}
  pendingApproval.value = false
  try {
    await auth.login(email.value, password.value)
    router.push(route.query.redirect || '/mon-profil')
  } catch (e) {
    if (e.response?.status === 403 && e.response?.data?.code === 'ACCOUNT_PENDING_APPROVAL') {
      pendingApproval.value = true
      error.value = e.response.data.message || 'Votre compte est en attente d\'approbation par un administrateur.'
    } else if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ') || 'Données invalides'
    } else {
      error.value = e.response?.data?.message || 'Email ou mot de passe incorrect'
    }
  }
}
</script>

<template>
  <div class="min-h-screen flex" style="background: var(--brand-dark);">
    <div class="hidden lg:flex flex-col justify-between w-[420px] shrink-0 p-12 relative overflow-hidden">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full opacity-15" style="background: radial-gradient(circle, #40916c, transparent);"></div>
        <div class="absolute bottom-0 right-0 w-64 h-64 rounded-full opacity-10" style="background: radial-gradient(circle, var(--gold), transparent);"></div>
      </div>
      <router-link to="/" class="flex items-center gap-2.5 relative z-10">
        <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center backdrop-blur">
          <span class="font-display font-extrabold text-white">FS</span>
        </div>
        <span class="font-display font-extrabold text-white text-xl">Foncier<span class="text-gold">Secure</span></span>
      </router-link>
      <div class="relative z-10">
        <blockquote class="text-white/70 text-lg leading-relaxed mb-6 italic">
          "La sécurité foncière est le fondement de la prospérité. FoncierSecure en est la garantie."
        </blockquote>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-gold/20 flex items-center justify-center">
            <i class="fas fa-shield-halved text-gold text-sm"></i>
          </div>
          <div>
            <p class="text-white font-semibold text-sm">Plateforme certifiée</p>
            <p class="text-white/40 text-xs">République du Bénin</p>
          </div>
        </div>
      </div>
    </div>

    <div class="flex-1 flex items-center justify-center p-6 bg-stone-50">
      <div class="w-full max-w-md animate-slide-up">
        <div class="lg:hidden flex items-center gap-2.5 mb-8">
          <div class="w-9 h-9 rounded-xl bg-brand flex items-center justify-center">
            <span class="font-display font-extrabold text-white text-sm">FS</span>
          </div>
          <span class="font-display font-extrabold text-stone-900 text-xl">Foncier<span class="text-brand">Secure</span></span>
        </div>

        <h1 class="font-display font-extrabold text-3xl text-stone-900 mb-1">Bon retour</h1>
        <p class="text-stone-500 mb-8">Connectez-vous à votre espace sécurisé</p>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div v-if="error" class="alert alert-danger" :class="pendingApproval ? 'alert-warning' : ''">
            <i :class="['fas', pendingApproval ? 'fa-hourglass-half' : 'fa-triangle-exclamation', 'shrink-0']"></i>
            <span>{{ error }}</span>
          </div>

          <div class="form-group">
            <label class="form-label">Adresse email</label>
            <div class="relative">
              <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="email" type="email" class="form-input pl-10" :class="errors.email ? 'form-input-error' : ''" placeholder="vous@email.com" required autocomplete="email" />
            </div>
            <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
          </div>

          <div class="form-group">
            <label class="form-label">Mot de passe</label>
            <div class="relative">
              <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="password" :type="showPassword ? 'text' : 'password'" class="form-input pl-10 pr-12" :class="errors.password ? 'form-input-error' : ''" placeholder="••••••••" required autocomplete="current-password" />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700 transition-colors">
                <i :class="['fas', showPassword ? 'fa-eye-slash' : 'fa-eye', 'text-sm']"></i>
              </button>
            </div>
            <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
          </div>

          <div class="flex justify-end">
            <router-link to="/auth/forgot-password" class="text-sm font-semibold text-brand hover:text-brand-light transition-colors">
              Mot de passe oublié ?
            </router-link>
          </div>

          <button type="submit" class="btn btn-primary btn-full btn-lg" :disabled="auth.loading">
            <div v-if="auth.loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
            <i v-else class="fas fa-right-to-bracket"></i>
            {{ auth.loading ? 'Connexion…' : 'Se connecter' }}
          </button>
        </form>

        <p class="text-center text-sm text-stone-500 mt-6">
          Pas encore de compte ?
          <router-link to="/auth/register" class="font-bold text-brand hover:text-brand-light transition-colors">S'inscrire</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
