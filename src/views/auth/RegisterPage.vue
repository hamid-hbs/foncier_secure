<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()
const form = ref({ nom: '', prenom: '', email: '', telephone: '', password: '', password_confirmation: '', role: 'citoyen', cabinet: '', numero_agrement: '', adresse_professionnelle: '' })
const error = ref('')
const errors = ref({})
const showPassword = ref(false)
const success = ref(false)

async function handleSubmit() {
  error.value = ''
  errors.value = {}
  try {
    await auth.register({ ...form.value })
    success.value = true
    setTimeout(() => router.push('/mon-profil'), 1500)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ') || 'Données invalides'
    } else {
      error.value = e.response?.data?.message || 'Une erreur est survenue'
    }
  }
}
</script>

<template>
  <div class="min-h-screen flex" style="background: var(--brand-dark);">
    <div class="hidden lg:flex flex-col justify-between w-[380px] shrink-0 p-12 relative overflow-hidden">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-24 -right-16 w-72 h-72 rounded-full opacity-15" style="background: radial-gradient(circle, #40916c, transparent);"></div>
        <div class="absolute bottom-0 left-0 w-56 h-56 rounded-full opacity-10" style="background: radial-gradient(circle, var(--gold), transparent);"></div>
      </div>
      <router-link to="/" class="flex items-center gap-2.5 relative z-10">
        <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
          <span class="font-display font-extrabold text-white">FS</span>
        </div>
        <span class="font-display font-extrabold text-white text-xl">Foncier<span class="text-gold">Secure</span></span>
      </router-link>
      <div class="relative z-10">
        <p class="text-white/70 text-sm leading-relaxed max-w-xs">
          La plateforme de référence pour la sécurisation foncière au Bénin. Transparence, confiance, légalité.
        </p>
      </div>
    </div>

    <div class="flex-1 flex items-center justify-center p-6 bg-stone-50 overflow-y-auto">
      <div class="w-full max-w-md py-8 animate-slide-up">
        <router-link to="/" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-8">
          <i class="fas fa-arrow-left text-xs"></i> Retour
        </router-link>

        <div class="lg:hidden flex items-center gap-2.5 mb-6">
          <div class="w-9 h-9 rounded-xl bg-brand flex items-center justify-center">
            <span class="font-display font-extrabold text-white text-sm">FS</span>
          </div>
          <span class="font-display font-extrabold text-stone-900 text-xl">Foncier<span class="text-brand">Secure</span></span>
        </div>

        <h1 class="font-display font-extrabold text-3xl text-stone-900 mb-1">Créer un compte</h1>
        <p class="text-stone-500 mb-8">Rejoignez la plateforme foncière de référence</p>

        <div v-if="success" class="alert alert-success mb-6">
          <i class="fas fa-check-circle shrink-0"></i>
          <span>Compte créé avec succès ! Redirection…</span>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-5">
          <div v-if="error" class="alert alert-danger">
            <i class="fas fa-triangle-exclamation shrink-0"></i>
            <span>{{ error }}</span>
          </div>

          <!-- Role selector -->
          <div>
            <label class="form-label">Vous êtes&nbsp;?</label>
            <div class="grid grid-cols-3 gap-2.5 mt-1.5">
              <button type="button" @click="form.role = 'citoyen'" class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all text-center" :class="form.role === 'citoyen' ? 'border-brand bg-brand-50' : 'border-stone-200 bg-white hover:border-stone-300'">
                <div class="w-10 h-10 rounded-xl bg-stone-100 flex items-center justify-center" :class="form.role === 'citoyen' ? 'bg-brand text-white' : ''">
                  <i class="fas fa-user text-sm"></i>
                </div>
                <span class="text-xs font-bold" :class="form.role === 'citoyen' ? 'text-brand' : 'text-stone-600'">Citoyen</span>
              </button>
              <button type="button" @click="form.role = 'notaire'" class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all text-center" :class="form.role === 'notaire' ? 'border-brand bg-brand-50' : 'border-stone-200 bg-white hover:border-stone-300'">
                <div class="w-10 h-10 rounded-xl bg-stone-100 flex items-center justify-center" :class="form.role === 'notaire' ? 'bg-brand text-white' : ''">
                  <i class="fas fa-file-signature text-sm"></i>
                </div>
                <span class="text-xs font-bold" :class="form.role === 'notaire' ? 'text-brand' : 'text-stone-600'">Notaire</span>
              </button>
              <button type="button" @click="form.role = 'geometre'" class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all text-center" :class="form.role === 'geometre' ? 'border-brand bg-brand-50' : 'border-stone-200 bg-white hover:border-stone-300'">
                <div class="w-10 h-10 rounded-xl bg-stone-100 flex items-center justify-center" :class="form.role === 'geometre' ? 'bg-brand text-white' : ''">
                  <i class="fas fa-ruler-combined text-sm"></i>
                </div>
                <span class="text-xs font-bold" :class="form.role === 'geometre' ? 'text-brand' : 'text-stone-600'">Géomètre</span>
              </button>
            </div>
            <p v-if="errors.role" class="form-error">{{ errors.role[0] }}</p>
          </div>

          <!-- Professional info -->
          <div v-if="form.role !== 'citoyen'" class="card border-brand/20 !p-5 space-y-4">
            <div class="flex items-center gap-2 text-brand font-semibold text-sm">
              <i class="fas fa-briefcase"></i>
              <span>Informations professionnelles</span>
            </div>
            <div>
              <label class="form-label">Cabinet / Étude <span class="text-danger">*</span></label>
              <input v-model="form.cabinet" type="text" class="form-input" :class="errors.cabinet ? 'form-input-error' : ''" placeholder="Nom de votre cabinet ou étude" required />
              <p v-if="errors.cabinet" class="form-error">{{ errors.cabinet[0] }}</p>
            </div>
            <div class="form-row">
              <div>
                <label class="form-label">Numéro d'agrément <span class="text-danger">*</span></label>
                <input v-model="form.numero_agrement" type="text" class="form-input" :class="errors.numero_agrement ? 'form-input-error' : ''" placeholder="Ex: AG-2024-001" required />
                <p v-if="errors.numero_agrement" class="form-error">{{ errors.numero_agrement[0] }}</p>
              </div>
            </div>
            <div>
              <label class="form-label">Adresse professionnelle <span class="text-danger">*</span></label>
              <input v-model="form.adresse_professionnelle" type="text" class="form-input" :class="errors.adresse_professionnelle ? 'form-input-error' : ''" placeholder="Adresse de votre cabinet" required />
              <p v-if="errors.adresse_professionnelle" class="form-error">{{ errors.adresse_professionnelle[0] }}</p>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label class="form-label">Prénom</label>
              <input v-model="form.prenom" type="text" class="form-input" :class="errors.prenom ? 'form-input-error' : ''" placeholder="Jean" required />
              <p v-if="errors.prenom" class="form-error">{{ errors.prenom[0] }}</p>
            </div>
            <div>
              <label class="form-label">Nom</label>
              <input v-model="form.nom" type="text" class="form-input" :class="errors.nom ? 'form-input-error' : ''" placeholder="Kossou" required />
              <p v-if="errors.nom" class="form-error">{{ errors.nom[0] }}</p>
            </div>
          </div>

          <div>
            <label class="form-label">Email</label>
            <div class="relative">
              <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="form.email" type="email" class="form-input pl-10" :class="errors.email ? 'form-input-error' : ''" placeholder="vous@email.com" required />
            </div>
            <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
          </div>

          <div>
            <label class="form-label">Téléphone <span class="text-stone-400 font-normal">(optionnel)</span></label>
            <div class="relative">
              <i class="fas fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="form.telephone" type="tel" class="form-input pl-10" placeholder="+229 01 …" />
            </div>
          </div>

          <div class="form-row">
            <div>
              <label class="form-label">Mot de passe</label>
              <div class="relative">
                <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="form-input pl-10 pr-10" :class="errors.password ? 'form-input-error' : ''" placeholder="8 car. min." required />
                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-700">
                  <i :class="['fas', showPassword ? 'fa-eye-slash' : 'fa-eye', 'text-sm']"></i>
                </button>
              </div>
              <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
            </div>
            <div>
              <label class="form-label">Confirmation</label>
              <div class="relative">
                <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
                <input v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" class="form-input pl-10" :class="errors.password_confirmation ? 'form-input-error' : ''" placeholder="Répéter" required />
              </div>
              <p v-if="errors.password_confirmation" class="form-error">{{ errors.password_confirmation[0] }}</p>
            </div>
          </div>

          <div v-if="form.role !== 'citoyen'" class="card border-brand/20 bg-brand-50/50 !p-4">
            <p class="text-xs text-brand font-semibold flex items-center gap-2">
              <i class="fas fa-info-circle"></i>
              Après inscription, un administrateur validera votre profil professionnel avant activation complète.
            </p>
          </div>

          <button type="submit" class="btn btn-primary btn-full btn-lg" :disabled="auth.loading">
            <div v-if="auth.loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
            <i v-else class="fas fa-user-plus"></i>
            {{ auth.loading ? 'Création…' : 'Créer mon compte' }}
          </button>
        </form>

        <p class="text-center text-sm text-stone-500 mt-6">
          Déjà inscrit ?
          <router-link to="/auth/login" class="font-bold text-brand hover:text-brand-light transition-colors">Se connecter</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
