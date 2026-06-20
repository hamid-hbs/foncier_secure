<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import transactionApi from '@/api/transaction'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const form = ref({ email: '', role_dossier: 'geometre' })
const loading = ref(false)
const errors = ref({})
const transaction = ref(null)

onMounted(async () => {
  try {
    const r = await transactionApi.show(route.params.id)
    transaction.value = r.data?.data ?? r.data
  } catch {}
})

async function submit() {
  loading.value = true
  errors.value = {}
  try {
    await transactionApi.inviter(route.params.id, form.value)
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
      <h1 class="section-title mb-6">Inviter un participant</h1>
      <p v-if="transaction" class="text-sm mb-4" style="color: var(--text-secondary);">Dossier : {{ transaction.titre || '#' + transaction.id }}</p>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="form-label">Email <span style="color: var(--danger);">*</span></label>
          <input v-model="form.email" type="email" class="form-input w-full" required />
          <p v-if="errors.email" class="text-xs mt-1" style="color: var(--danger);">{{ errors.email[0] }}</p>
        </div>
        <div>
          <label class="form-label">Rôle</label>
          <select v-model="form.role_dossier" class="form-input w-full">
            <option value="notaire">Notaire</option>
            <option value="geometre">Géomètre</option>
          </select>
        </div>
        <button type="submit" :disabled="loading" class="btn-green"><i class="fas fa-user-plus mr-1"></i> {{ loading ? 'Envoi...' : 'Inviter' }}</button>
      </form>
    </div>
  </div>
</template>
