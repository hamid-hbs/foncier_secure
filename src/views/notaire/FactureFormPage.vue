<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import factureApi from '@/api/facture'
import transactionApi from '@/api/transaction'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const route = useRoute()
const form = ref({ libelle: '', montant: '', description: '', destinataire_type: 'acheteur' })
const transaction = ref(null)
const loading = ref(false)
const error = ref('')
const errors = ref({})
const success = ref(false)

onMounted(async () => {
  try {
    const res = await transactionApi.show(route.params.id)
    transaction.value = res.data || null
  } catch (e) { console.error('Erreur chargement transaction:', e) }
})

async function submit() {
  loading.value = true
  error.value = ''
  errors.value = {}
  try {
    const payload = { ...form.value }
    if (payload.montant) payload.montant = parseFloat(payload.montant)
    await factureApi.create({ ...payload, transaction_id: route.params.id })
    success.value = true
    setTimeout(() => router.push({ name: 'NotaireFacturesTransaction', params: { id: route.params.id } }), 1500)
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
        <h1 class="page-title">Nouvelle facture</h1>
        <p class="page-subtitle">{{ transaction?.titre || 'Transaction #' + route.params.id }}</p>
      </div>
    </div>

    <div v-if="success" class="card">
      <div class="empty-state py-12">
        <div class="empty-icon bg-success/10 text-success"><i class="fas fa-check-double"></i></div>
        <p class="empty-title">Facture créée !</p>
        <p class="empty-text">La facture a été enregistrée avec succès.</p>
      </div>
    </div>

    <form v-else @submit.prevent="submit" class="space-y-5">
      <div v-if="error" class="alert alert-danger">
        <i class="fas fa-triangle-exclamation shrink-0"></i>
        <span>{{ error }}</span>
      </div>

      <div class="card p-6 space-y-5">
        <div>
          <label class="form-label">Libellé <span class="text-red-500">*</span></label>
          <input v-model="form.libelle" type="text" class="form-input" :class="errors.libelle ? 'form-input-error' : ''" placeholder="Honoraires de notaire, frais d'acte…" />
          <p v-if="errors.libelle" class="form-error">{{ errors.libelle[0] }}</p>
        </div>

        <div>
          <label class="form-label">Destinataire</label>
          <select v-model="form.destinataire_type" class="form-select">
            <option value="acheteur">Acheteur</option>
            <option value="vendeur">Vendeur</option>
            <option value="les_deux">Acheteur et vendeur</option>
          </select>
        </div>

        <div>
          <label class="form-label">Montant (FCFA) <span class="text-red-500">*</span></label>
          <div class="relative">
            <i class="fas fa-tag absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
            <input v-model="form.montant" type="number" min="0" step="any" class="form-input pl-10" :class="errors.montant ? 'form-input-error' : ''" placeholder="0" />
          </div>
          <p v-if="errors.montant" class="form-error">{{ errors.montant[0] }}</p>
        </div>

        <div>
          <label class="form-label">Description</label>
          <textarea v-model="form.description" rows="4" class="form-input resize-none" placeholder="Détail des prestations facturées…"></textarea>
        </div>
      </div>

      <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-lg" :disabled="loading">
          <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
          <i v-else class="fas fa-file-invoice"></i>
          {{ loading ? 'Création…' : 'Créer la facture' }}
        </button>
        <button type="button" @click="goBack(router)" class="btn btn-ghost">Annuler</button>
      </div>
    </form>
  </div>
</template>
