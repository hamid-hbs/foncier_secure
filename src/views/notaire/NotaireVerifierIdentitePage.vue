<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import transactionApi from '@/api/transaction'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const form = ref({ user_id: '', document_verifie: '' })
const loading = ref(false)
const errors = ref({})
const transaction = ref(null)
const participants = ref([])

onMounted(async () => {
  try {
    const r = await transactionApi.show(route.params.id)
    transaction.value = r.data?.data ?? r.data
    participants.value = []
    if (transaction.value?.vendeur) participants.value.push({ id: transaction.value.vendeur.id, nom: transaction.value.vendeur.nom + ' ' + (transaction.value.vendeur.prenom || '') })
    if (transaction.value?.acheteur) participants.value.push({ id: transaction.value.acheteur.id, nom: transaction.value.acheteur.nom + ' ' + (transaction.value.acheteur.prenom || '') })
  } catch {}
})

async function submit() {
  loading.value = true
  errors.value = {}
  try {
    await transactionApi.verifierIdentite(route.params.id, form.value)
    router.push(`/notaire/transactions/${route.params.id}`)
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data?.errors ?? {}
    else alert(e.response?.data?.message || "Erreur")
  }
  finally { loading.value = false }
}
</script>
<template>
  <div class="page-container max-w-lg">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="card p-6">
      <h1 class="section-title mb-6">Vérifier l'identité</h1>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="form-label">Participant <span style="color: var(--danger);">*</span></label>
          <select v-model="form.user_id" class="form-input w-full" required>
            <option value="">Sélectionner</option>
            <option v-for="p in participants" :key="p.id" :value="p.id">{{ p.nom }}</option>
          </select>
          <p v-if="errors.user_id" class="text-xs mt-1" style="color: var(--danger);">{{ errors.user_id[0] }}</p>
        </div>
        <div>
          <label class="form-label">Document vérifié <span style="color: var(--danger);">*</span></label>
          <input v-model="form.document_verifie" class="form-input w-full" placeholder="ex: Passeport, Carte d'identité..." required />
          <p v-if="errors.document_verifie" class="text-xs mt-1" style="color: var(--danger);">{{ errors.document_verifie[0] }}</p>
        </div>
        <button type="submit" :disabled="loading" class="btn-green"><i class="fas fa-check-circle mr-1"></i> {{ loading ? 'Vérification...' : 'Marquer vérifié' }}</button>
      </form>
    </div>
  </div>
</template>
