<script setup>
import { ref } from 'vue'
import blockchainApi from '@/api/blockchain'

const result = ref(null)
const loading = ref(false)
const error = ref('')

async function verifier() {
  loading.value = true
  error.value = ''
  result.value = null
  try {
    const res = await blockchainApi.verify()
    result.value = res.data || null
  } catch (e) {
    error.value = e.response?.data?.message || 'Impossible de vérifier la chaîne blockchain'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <section class="py-20 relative overflow-hidden" style="background: var(--brand-dark);">
      <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute top-0 left-1/3 w-96 h-96 rounded-full" style="background: radial-gradient(circle, #40916c, transparent);"></div>
      </div>
      <div class="max-w-3xl mx-auto px-5 sm:px-8 text-center relative z-10">
        <div class="w-16 h-16 rounded-2xl bg-gold/20 flex items-center justify-center mx-auto mb-6">
          <i class="fas fa-link text-gold-light text-2xl"></i>
        </div>
        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest text-gold bg-gold/15 mb-5">Vérificateur blockchain</span>
        <h1 class="font-display font-extrabold text-4xl sm:text-5xl text-white mb-4">Vérifiez l'intégrité de la chaîne</h1>
        <p class="text-white/60 text-lg mb-10 max-w-xl mx-auto">Vérifiez que l'ensemble de la blockchain FoncierSecure est intègre et n'a subi aucune altération.</p>

        <div class="bg-white/8 backdrop-blur rounded-2xl p-6 border border-white/10">
          <button @click="verifier" class="btn btn-gold btn-lg font-bold w-full sm:w-auto" :disabled="loading">
            <div v-if="loading" class="spinner spinner-sm border-brand/30 border-t-brand"></div>
            <i v-else class="fas fa-shield-halved"></i>
            {{ loading ? 'Vérification en cours…' : 'Lancer la vérification' }}
          </button>
        </div>
      </div>
    </section>

    <section class="py-12 bg-stone-50 min-h-[40vh]">
      <div class="max-w-2xl mx-auto px-5 sm:px-8">
        <div v-if="error" class="alert alert-danger">
          <i class="fas fa-circle-xmark shrink-0 text-lg"></i>
          <div>
            <p class="font-bold">Erreur de vérification</p>
            <p class="text-sm mt-0.5">{{ error }}</p>
          </div>
        </div>

        <div v-else-if="result" class="card" :class="result.valide ? 'border-success/30 bg-success/5' : 'border-danger/30 bg-danger/5'">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white shrink-0" :class="result.valide ? 'bg-success' : 'bg-danger'">
              <i :class="['text-xl', result.valide ? 'fas fa-check-double' : 'fas fa-triangle-exclamation']"></i>
            </div>
            <div>
              <p class="font-display font-bold text-lg text-stone-900">
                {{ result.valide ? 'Chaîne intègre' : 'Chaîne compromise' }}
              </p>
              <p class="text-sm text-stone-500">
                {{ result.valide
                  ? 'La blockchain FoncierSecure est valide et aucune altération n\'a été détectée.'
                  : 'Une anomalie a été détectée dans la chaîne blockchain. Contactez l\'administrateur.' }}
              </p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="rounded-xl bg-stone-50 p-4 text-center">
              <p class="text-xs font-bold text-stone-400 uppercase tracking-wider mb-1">Statut</p>
              <span class="inline-flex items-center gap-1.5 text-sm font-bold" :class="result.valide ? 'text-success' : 'text-danger'">
                <i :class="result.valide ? 'fas fa-circle-check' : 'fas fa-circle-xmark'"></i>
                {{ result.valide ? 'Valide' : 'Invalide' }}
              </span>
            </div>
            <div class="rounded-xl bg-stone-50 p-4 text-center">
              <p class="text-xs font-bold text-stone-400 uppercase tracking-wider mb-1">Entrées vérifiées</p>
              <p class="text-2xl font-bold text-stone-900">{{ result.total_entrees }}</p>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-16 text-stone-400">
          <div class="w-16 h-16 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-link text-3xl text-stone-200"></i>
          </div>
          <p class="text-sm font-medium">Cliquez sur "Lancer la vérification" pour vérifier l'intégrité de la blockchain</p>
        </div>
      </div>
    </section>
  </div>
</template>
