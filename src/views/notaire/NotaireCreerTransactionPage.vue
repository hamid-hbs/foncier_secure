<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import transactionApi from '@/api/transaction'
import parcelleApi from '@/api/parcelle'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const route = useRoute()
const form = ref({ parcelle_id: route.query.parcelle || '', vendeur_id: route.query.vendeur || '', acheteur_id: route.query.acheteur || '', titre: '' })
const parcelles = ref([])
const loading = ref(false)
const error = ref('')
const errors = ref({})

onMounted(async () => {
  try {
    const res = await parcelleApi.list({ page: 1, per_page: 50 })
    parcelles.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement parcelles:', e) }
})

async function submit() {
  loading.value = true
  error.value = ''
  errors.value = {}
  try {
    const res = await transactionApi.create(form.value)
    const id = res.data?.id
    router.push(id ? `/notaire/transactions/${id}` : '/notaire/transactions')
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
        <h1 class="page-title">Ouvrir un dossier</h1>
        <p class="page-subtitle">Créer une nouvelle transaction notariale</p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-5">
      <div v-if="error" class="alert alert-danger">
        <i class="fas fa-triangle-exclamation shrink-0"></i>
        <span>{{ error }}</span>
      </div>

      <div class="card p-6 space-y-5">
        <div>
          <label class="form-label">Titre du dossier <span class="text-red-500">*</span></label>
          <input v-model="form.titre" type="text" class="form-input" :class="errors.titre ? 'form-input-error' : ''" placeholder="Ex : Vente terrain Cotonou — M. Dupont" required />
          <p v-if="errors.titre" class="form-error">{{ errors.titre[0] }}</p>
        </div>

        <div>
          <label class="form-label">Parcelle concernée <span class="text-red-500">*</span></label>
          <select v-model="form.parcelle_id" class="form-select" :class="errors.parcelle_id ? 'form-input-error' : ''" required>
            <option value="">Sélectionner une parcelle</option>
            <option v-for="p in parcelles" :key="p.id" :value="p.id">
              {{ p.titre || p.code || '#' + p.id }} — {{ p.commune?.nom || '' }}
            </option>
          </select>
          <p v-if="errors.parcelle_id" class="form-error">{{ errors.parcelle_id[0] }}</p>
        </div>

        <div class="form-row">
          <div>
            <label class="form-label">ID Vendeur <span class="text-red-500">*</span></label>
            <div class="relative">
              <i class="fas fa-user-tag absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="form.vendeur_id" type="text" class="form-input pl-10" :class="errors.vendeur_id ? 'form-input-error' : ''" placeholder="ID utilisateur" required />
            </div>
            <p v-if="errors.vendeur_id" class="form-error">{{ errors.vendeur_id[0] }}</p>
          </div>
          <div>
            <label class="form-label">ID Acheteur <span class="text-red-500">*</span></label>
            <div class="relative">
              <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
              <input v-model="form.acheteur_id" type="text" class="form-input pl-10" :class="errors.acheteur_id ? 'form-input-error' : ''" placeholder="ID utilisateur" required />
            </div>
            <p v-if="errors.acheteur_id" class="form-error">{{ errors.acheteur_id[0] }}</p>
          </div>
        </div>
      </div>

      <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-lg" :disabled="loading">
          <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
          <i v-else class="fas fa-folder-plus"></i>
          {{ loading ? 'Création…' : 'Créer le dossier' }}
        </button>
        <button type="button" @click="goBack(router)" class="btn btn-ghost">Annuler</button>
      </div>
    </form>
  </div>
</template>
