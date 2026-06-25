<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import supportTicketApi from '@/api/supportTicket'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const form = ref({ sujet: '', message: '', priorite: 'moyenne' })
const loading = ref(false)
const errors = ref({})
const error = ref('')

const priorites = [
  { value: 'basse', label: 'Basse', icon: 'fa-angle-down', color: 'border-stone-300 text-stone-500' },
  { value: 'moyenne', label: 'Moyenne', icon: 'fa-minus', color: 'border-warn/50 text-warn' },
  { value: 'haute', label: 'Haute', icon: 'fa-angle-up', color: 'border-danger/50 text-danger' },
]

async function submit() {
  loading.value = true
  errors.value = {}
  error.value = ''
  try {
    await supportTicketApi.create(form.value)
    router.push({ name: 'SupportTickets' })
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || 'Erreur lors de la création du ticket'
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
        <h1 class="page-title">Nouveau ticket de support</h1>
        <p class="page-subtitle">Décrivez votre problème et nous vous répondrons rapidement</p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-5">
      <div v-if="error" class="alert alert-danger">
        <i class="fas fa-triangle-exclamation shrink-0"></i>
        <span>{{ error }}</span>
      </div>

      <div class="card p-6 space-y-5">
        <div>
          <label class="form-label">Sujet <span class="text-red-500">*</span></label>
          <input v-model="form.sujet" type="text" class="form-input" :class="errors.sujet ? 'form-input-error' : ''" required placeholder="Décrivez brièvement votre problème" />
          <p v-if="errors.sujet" class="form-error">{{ errors.sujet[0] }}</p>
        </div>

        <div>
          <label class="form-label">Priorité</label>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="p in priorites" :key="p.value"
              type="button"
              @click="form.priorite = p.value"
              class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border-2 text-sm font-semibold transition-all"
              :class="form.priorite === p.value
                ? (p.value === 'haute' ? 'border-danger bg-danger/5 text-danger' : p.value === 'moyenne' ? 'border-warn bg-warn/5 text-warn' : 'border-stone-300 bg-stone-50 text-stone-600')
                : 'border-stone-200 text-stone-400 hover:border-stone-300'"
            >
              <i :class="['fas', p.icon, 'text-xs']"></i>
              {{ p.label }}
            </button>
          </div>
        </div>

        <div>
          <label class="form-label">Description <span class="text-red-500">*</span></label>
          <textarea v-model="form.message" rows="5" class="form-input resize-none" :class="errors.message ? 'form-input-error' : ''" required placeholder="Décrivez votre problème en détail. Incluez les étapes pour le reproduire, les messages d'erreur, etc."></textarea>
          <p v-if="errors.message" class="form-error">{{ errors.message[0] }}</p>
        </div>
      </div>

      <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-lg" :disabled="loading">
          <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
          <i v-else class="fas fa-paper-plane"></i>
          {{ loading ? 'Envoi…' : 'Envoyer le ticket' }}
        </button>
        <button type="button" @click="goBack(router)" class="btn btn-ghost">Annuler</button>
      </div>
    </form>
  </div>
</template>
