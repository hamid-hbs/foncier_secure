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
  password: '',
  password_confirmation: '',
  telephone: '',
  role: 'citoyen',
})
const submitting = ref(false)
const error = ref('')

async function submitForm() {
  error.value = ''
  if (!formData.value.nom || !formData.value.email || !formData.value.password) {
    error.value = 'Veuillez remplir les champs obligatoires.'
    return
  }
  if (formData.value.password !== formData.value.password_confirmation) {
    error.value = 'Les mots de passe ne correspondent pas.'
    return
  }
  submitting.value = true
  try {
    await api.post('/admin/users', formData.value)
    router.push({ name: 'AdminUsers' })
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors de la création de l'utilisateur"
  }
  submitting.value = false
}
</script>

<template>
  <div class="page-container" style="max-width: 560px;">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="flex items-center gap-3 mb-8">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--green-tree); color: #fff;">
        <i class="fas fa-user-plus"></i>
      </div>
      <div>
        <h1 class="section-title">Nouvel utilisateur</h1>
        <p class="section-subtitle">Créez un compte utilisateur</p>
      </div>
    </div>

    <div class="card">
      <div v-if="error" class="p-3 rounded-lg mb-4 text-sm flex items-center gap-2" style="background: var(--danger); color: #fff; opacity: 0.9;">
        <i class="fas fa-circle-exclamation"></i> {{ error }}
      </div>

      <div class="form-group">
        <label class="form-label">Nom <span style="color: var(--danger);">*</span></label>
        <input v-model="formData.nom" class="form-input w-full" placeholder="Nom" />
      </div>

      <div class="form-group">
        <label class="form-label">Prénom</label>
        <input v-model="formData.prenom" class="form-input w-full" placeholder="Prénom" />
      </div>

      <div class="form-group">
        <label class="form-label">Email <span style="color: var(--danger);">*</span></label>
        <input v-model="formData.email" type="email" class="form-input w-full" placeholder="email@exemple.com" />
      </div>

      <div class="form-group">
        <label class="form-label">Mot de passe <span style="color: var(--danger);">*</span></label>
        <input v-model="formData.password" type="password" class="form-input w-full" placeholder="Mot de passe" />
      </div>

      <div class="form-group">
        <label class="form-label">Confirmer le mot de passe <span style="color: var(--danger);">*</span></label>
        <input v-model="formData.password_confirmation" type="password" class="form-input w-full" placeholder="Confirmer le mot de passe" />
      </div>

      <div class="form-group">
        <label class="form-label">Téléphone</label>
        <input v-model="formData.telephone" class="form-input w-full" placeholder="+226 XX XX XX XX" />
      </div>

      <div class="form-group">
        <label class="form-label">Rôle</label>
        <select v-model="formData.role" class="form-select w-full">
          <option value="citoyen">Citoyen</option>
          <option value="geometre">Géomètre</option>
          <option value="notaire">Notaire</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <div class="flex gap-3 justify-end mt-6">
        <button @click="goBack(router)" class="btn-outline btn-sm">Annuler</button>
        <button @click="submitForm" :disabled="submitting" class="btn-green btn-sm flex items-center gap-1.5">
          <i class="fas fa-plus"></i> {{ submitting ? 'Création...' : "Créer l'utilisateur" }}
        </button>
      </div>
    </div>
  </div>
</template>
