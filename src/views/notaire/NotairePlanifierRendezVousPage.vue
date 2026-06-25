<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import rendezVousApi from '@/api/rendezVous'
import { goBack } from '@/utils/navigation'

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const error = ref('')
const success = ref(false)
const form = ref({ date_heure: '', lieu: '', notes: '', transaction_id: route.params.id })

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await rendezVousApi.create({ ...form.value, transaction_id: route.params.id })
    success.value = true
    setTimeout(() => router.push(`/notaire/transactions/${route.params.id}`), 1500)
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de la planification'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page-wrap max-w-md mx-auto">
    <div class="flex items-center gap-3 mb-8">
      <button @click="goBack(router)" class="btn btn-ghost btn-icon text-stone-500">
        <i class="fas fa-arrow-left"></i>
      </button>
      <div>
        <h1 class="page-title">Planifier un rendez-vous</h1>
        <p class="page-subtitle">Dossier #{{ route.params.id }}</p>
      </div>
    </div>

    <div v-if="success" class="card">
      <div class="empty-state py-10">
        <div class="empty-icon bg-success/10 text-success"><i class="fas fa-calendar-check"></i></div>
        <p class="empty-title">Rendez-vous planifié !</p>
        <p class="empty-text">Les parties seront notifiées.</p>
      </div>
    </div>

    <form v-else @submit.prevent="submit" class="card space-y-5">
      <div v-if="error" class="alert alert-danger">
        <i class="fas fa-triangle-exclamation shrink-0"></i>
        <span>{{ error }}</span>
      </div>

      <div>
        <label class="form-label">Date et heure <span class="text-red-500">*</span></label>
        <input v-model="form.date_heure" type="datetime-local" class="form-input" required />
      </div>

      <div>
        <label class="form-label">Lieu <span class="text-red-500">*</span></label>
        <div class="relative">
          <i class="fas fa-location-dot absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
          <input v-model="form.lieu" type="text" class="form-input pl-10" required placeholder="Étude notariale, adresse…" />
        </div>
      </div>

      <div>
        <label class="form-label">Notes</label>
        <textarea v-model="form.notes" rows="3" class="form-input resize-none" placeholder="Instructions particulières, documents à apporter…"></textarea>
      </div>

      <div class="flex gap-3">
        <button type="submit" class="btn btn-primary flex-1" :disabled="loading">
          <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
          <i v-else class="fas fa-calendar-plus"></i>
          {{ loading ? 'Planification…' : 'Planifier le rendez-vous' }}
        </button>
        <button type="button" @click="goBack(router)" class="btn btn-ghost">Annuler</button>
      </div>
    </form>
  </div>
</template>
