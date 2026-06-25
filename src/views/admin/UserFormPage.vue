<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const formData = ref({
  nom: '',
  prenom: '',
  email: '',
  telephone: '',
  password: '',
  password_confirmation: '',
  role: 'citoyen',
  is_active: true,
})
const loading = ref(false)
const error = ref('')
const errors = ref({})

const roles = ['citoyen', 'notaire', 'geometre', 'admin']

async function handleSubmit() {
  loading.value = true
  error.value = ''
  errors.value = {}
  try {
    await api.post('/admin/users', formData.value)
    router.push('/admin/users')
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || 'Erreur lors de la création'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page-wrap max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-8">
      <button @click="goBack(router)" class="btn btn-ghost btn-icon text-stone-500">
        <i class="fas fa-arrow-left"></i>
      </button>
      <div>
        <h1 class="page-title">Créer un utilisateur</h1>
        <p class="page-subtitle">Ajouter un nouveau compte à la plateforme</p>
      </div>
    </div>

    <div class="card">
      <div v-if="error" class="alert alert-danger mb-6">
        <i class="fas fa-triangle-exclamation shrink-0"></i>
        <span>{{ error }}</span>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-5">
        <div class="form-row">
          <div>
            <label class="form-label">Prénom</label>
            <input v-model="formData.prenom" type="text" class="form-input" :class="errors.prenom ? 'form-input-error' : ''" required />
            <p v-if="errors.prenom" class="form-error">{{ errors.prenom[0] }}</p>
          </div>
          <div>
            <label class="form-label">Nom</label>
            <input v-model="formData.nom" type="text" class="form-input" :class="errors.nom ? 'form-input-error' : ''" required />
            <p v-if="errors.nom" class="form-error">{{ errors.nom[0] }}</p>
          </div>
        </div>

        <div>
          <label class="form-label">Email</label>
          <div class="relative">
            <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
            <input v-model="formData.email" type="email" class="form-input pl-10" :class="errors.email ? 'form-input-error' : ''" required />
          </div>
          <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
        </div>

        <div>
          <label class="form-label">Téléphone</label>
          <div class="relative">
            <i class="fas fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
            <input v-model="formData.telephone" type="tel" class="form-input pl-10" />
          </div>
        </div>

        <div>
          <label class="form-label">Rôle</label>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button v-for="r in roles" :key="r" type="button" @click="formData.role = r"
              class="py-2.5 px-3 rounded-xl border-2 text-sm font-semibold capitalize transition-all"
              :class="formData.role === r ? 'border-brand bg-brand-50 text-brand' : 'border-stone-200 text-stone-500 hover:border-stone-300'">
              {{ r }}
            </button>
          </div>
        </div>

        <div class="form-row">
          <div>
            <label class="form-label">Mot de passe</label>
            <div class="relative">
              <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="formData.password" type="password" class="form-input pl-10" :class="errors.password ? 'form-input-error' : ''" required />
            </div>
            <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
          </div>
          <div>
            <label class="form-label">Confirmation</label>
            <div class="relative">
              <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="formData.password_confirmation" type="password" class="form-input pl-10" required />
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3 pt-2 border-t border-stone-100">
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="formData.is_active" type="checkbox" class="w-4 h-4 accent-brand rounded" />
            <span class="text-sm font-semibold text-stone-700">Compte actif</span>
          </label>
        </div>

        <div class="flex gap-3 pt-2">
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
            <i v-else class="fas fa-user-plus"></i>
            {{ loading ? 'Création…' : 'Créer le compte' }}
          </button>
          <button type="button" @click="goBack(router)" class="btn btn-ghost">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</template>
