<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const email = ref('')
const password = ref('')
const error = ref('')
const errors = ref({})
const showPassword = ref(false)

async function handleSubmit() {
  error.value = ''
  errors.value = {}
  try {
    await auth.login(email.value, password.value)
    router.push(route.query.redirect || '/mon-profil')
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ') || 'Données invalides'
    } else {
      error.value = e.response?.data?.message || 'Email ou mot de passe incorrect'
    }
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
        <h1 class="text-2xl font-bold mb-1" style="color: var(--text-primary);">Bienvenue</h1>
        <p class="text-sm mb-6" style="color: var(--text-secondary);">Connectez-vous à votre espace</p>
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div v-if="error" class="p-3.5 rounded-lg text-sm" style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">{{ error }}</div>
          <div>
            <label class="form-label">Email</label>
            <div class="relative">
              <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
              <input v-model="email" type="email" class="form-input pl-10" :class="{ 'border-red-500': errors.email }" placeholder="vous@email.com" required />
            </div>
            <p v-if="errors.email" class="text-xs mt-1" style="color: #DC2626;">{{ errors.email[0] }}</p>
          </div>
          <div>
            <label class="form-label">Mot de passe</label>
            <div class="relative">
              <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary);"></i>
              <input v-model="password" :type="showPassword ? 'text' : 'password'" class="form-input pl-10 pr-10" :class="{ 'border-red-500': errors.password }" placeholder="••••••••" required />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-1/2 -translate-y-1/2" style="color: var(--text-secondary); background: none; border: none;">
                <i :class="['fas', showPassword ? 'fa-eye-slash' : 'fa-eye']"></i>
              </button>
            </div>
            <p v-if="errors.password" class="text-xs mt-1" style="color: #DC2626;">{{ errors.password[0] }}</p>
          </div>
          <div class="flex items-center justify-end">
            <router-link to="/auth/forgot-password" class="text-sm font-medium" style="color: var(--green-tree);">Mot de passe oublié ?</router-link>
          </div>
          <button type="submit" class="btn-green w-full" :disabled="auth.loading">
            <i class="fas fa-right-to-bracket"></i>
            {{ auth.loading ? 'Connexion...' : 'Se connecter' }}
          </button>
        </form>
        <p class="text-center text-sm mt-6" style="color: var(--text-secondary);">
          Pas encore de compte ?
          <router-link to="/auth/register" class="font-medium" style="color: var(--green-tree);">S'inscrire</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
