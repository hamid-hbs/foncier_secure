<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import supportTicketApi from '@/api/supportTicket'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const form = ref({ sujet: '', message: '', priorite: 'normale' })
const loading = ref(false)
const errors = ref({})

async function submit() {
  loading.value = true
  errors.value = {}
  try {
    await supportTicketApi.create(form.value)
    router.push('/support/tickets')
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data?.errors ?? {}
    else alert(e.response?.data?.message || "Erreur")
  }
  finally { loading.value = false }
}
</script>
<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="card p-6 max-w-xl">
      <h1 class="section-title mb-6">Nouveau ticket</h1>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="form-label">Sujet <span style="color: var(--danger);">*</span></label>
          <input v-model="form.sujet" class="form-input w-full" maxlength="200" required />
          <p v-if="errors.sujet" class="text-xs mt-1" style="color: var(--danger);">{{ errors.sujet[0] }}</p>
        </div>
        <div>
          <label class="form-label">Priorité</label>
          <select v-model="form.priorite" class="form-input w-full">
            <option value="basse">Basse</option>
            <option value="normale">Normale</option>
            <option value="haute">Haute</option>
            <option value="urgente">Urgente</option>
          </select>
        </div>
        <div>
          <label class="form-label">Message <span style="color: var(--danger);">*</span></label>
          <textarea v-model="form.message" class="form-input w-full" rows="5" required></textarea>
          <p v-if="errors.message" class="text-xs mt-1" style="color: var(--danger);">{{ errors.message[0] }}</p>
        </div>
        <div class="flex gap-3">
          <button type="submit" :disabled="loading" class="btn-green"><i class="fas fa-paper-plane mr-1"></i> {{ loading ? 'Envoi...' : 'Envoyer' }}</button>
          <button type="button" @click="router.push('/support/tickets')" class="btn-outline">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</template>
