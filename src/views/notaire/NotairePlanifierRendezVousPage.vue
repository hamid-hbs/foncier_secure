<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import rendezVousApi from '@/api/rendezVous'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const error = ref('')

const form = ref({
  type: 'physique',
  date_rendezvous: '',
  heure: '',
  lieu: '',
  description: '',
})

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await rendezVousApi.store(route.params.id, form.value)
    router.push(`/notaire/transactions/${route.params.id}`)
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors de la création"
  }
  finally { loading.value = false }
}
</script>
<template>
  <div class="page-container max-w-2xl">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <h1 class="section-title mb-6">Planifier un rendez-vous</h1>
    <form @submit.prevent="submit" class="card space-y-4">
      <div v-if="error" class="p-3 rounded-lg text-sm" style="background: #FEE2E2; color: var(--danger);">{{ error }}</div>
      <div class="form-group">
        <label class="form-label">Type <span style="color: var(--danger);">*</span></label>
        <select v-model="form.type" class="form-select">
          <option value="physique">Physique</option>
          <option value="visio">Visio</option>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group">
          <label class="form-label">Date <span style="color: var(--danger);">*</span></label>
          <input v-model="form.date_rendezvous" type="date" class="form-input" required />
        </div>
        <div class="form-group">
          <label class="form-label">Heure</label>
          <input v-model="form.heure" type="time" class="form-input" />
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Lieu <span style="color: var(--danger);">*</span></label>
        <input v-model="form.lieu" class="form-input" placeholder="Adresse du rendez-vous..." required />
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea v-model="form.description" class="form-textarea" rows="3"></textarea>
      </div>
      <button type="submit" :disabled="loading" class="btn-green">
        <i class="fas fa-calendar-plus mr-1"></i> {{ loading ? 'Création...' : 'Créer le rendez-vous' }}
      </button>
    </form>
  </div>
</template>
