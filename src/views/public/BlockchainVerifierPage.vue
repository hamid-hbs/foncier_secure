<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import blockchainApi from '@/api/blockchain'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const result = ref(null)
const loading = ref(false)
const error = ref('')

async function verifyBlockchain() {
  error.value = ''
  result.value = null
  loading.value = true
  try {
    const res = await blockchainApi.verify()
    result.value = res.data
  } catch (e) {
    if (e.response?.status === 422) {
      error.value = Object.values(e.response.data?.errors || {}).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || 'Erreur lors de la vérification'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="max-w-2xl mx-auto">
      <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-xl flex items-center justify-center mx-auto mb-4" style="background: #F0F7F4; color: var(--green-tree);">
          <i class="fas fa-shield-alt" style="font-size: 1.75rem;"></i>
        </div>
        <h1 class="section-title">Vérification blockchain</h1>
        <p class="section-subtitle">Vérifiez l'intégrité des enregistrements sur la blockchain</p>
      </div>

      <div class="card mb-6 text-center">
        <p class="mb-4" style="color: var(--text-secondary);">Cliquez sur le bouton ci-dessous pour lancer une vérification blockchain.</p>
        <button type="button" class="btn-green" :disabled="loading" @click="verifyBlockchain">
          <i class="fas fa-shield-alt"></i>
          {{ loading ? 'Vérification...' : 'Lancer la vérification' }}
        </button>
      </div>

      <div v-if="error" class="card mb-6" style="border: 1px solid #FECACA;">
        <div class="flex items-center gap-3">
          <i class="fas fa-circle-xmark" style="color: #D62828; font-size: 1.5rem;"></i>
          <p style="color: #D62828;">{{ error }}</p>
        </div>
      </div>

      <div v-if="result" class="card" :style="result.valid ? { border: '2px solid #A7F3D0' } : { border: '2px solid #FECACA' }">
        <div class="flex items-center gap-4 mb-4">
          <div v-if="result.valid" class="w-14 h-14 rounded-full flex items-center justify-center" style="background: #D1FAE5;">
            <i class="fas fa-circle-check" style="color: #065F46; font-size: 1.75rem;"></i>
          </div>
          <div v-else class="w-14 h-14 rounded-full flex items-center justify-center" style="background: #FEE2E2;">
            <i class="fas fa-circle-xmark" style="color: #991B1B; font-size: 1.75rem;"></i>
          </div>
          <div>
            <h2 class="text-xl font-bold" :style="{ color: result.valid ? '#065F46' : '#991B1B' }">
              {{ result.valid ? 'Blockchain valide' : 'Problème détecté' }}
            </h2>
            <p class="text-sm" style="color: var(--text-secondary);">
              {{ result.valid ? 'Tous les enregistrements sont intègres' : 'Des anomalies ont été détectées' }}
            </p>
          </div>
        </div>

        <div v-if="result.messages && result.messages.length" class="space-y-2">
          <h3 class="text-sm font-semibold" style="color: var(--text-secondary);">Messages</h3>
          <div v-for="(msg, i) in result.messages" :key="i" class="p-3 rounded-lg text-sm" :style="{ background: result.valid ? '#F0F7F4' : '#FEE2E2', color: result.valid ? 'var(--text-primary)' : '#991B1B' }">
            {{ msg }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
