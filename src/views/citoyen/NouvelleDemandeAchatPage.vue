<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import demandeAchatApi from '@/api/demandeAchat'
import parcelleApi from '@/api/parcelle'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const route = useRoute()
const form = ref({ parcelle_id: route.query.parcelle_id || '', type: 'vente', message: '' })
const parcelle = ref(null)
const parcelles = ref([])
const errors = ref({})
const loading = ref(false)
const loadingParcelles = ref(true)
const preselected = computed(() => !!route.query.parcelle_id)

onMounted(async () => {
  try {
    if (preselected.value) {
      const r = await parcelleApi.show(route.query.parcelle_id)
      parcelle.value = r.data
    } else {
      const r = await parcelleApi.list()
      parcelles.value = r.data?.data ?? []
    }
  } catch { }
  finally { loadingParcelles.value = false }
})

async function submit() {
  loading.value = true
  errors.value = {}
  try {
    await demandeAchatApi.create(form.value)
    router.push('/citoyen/demandes-achat')
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data?.errors ?? {}
    else alert(e.response?.data?.message || "Erreur lors de la création")
  } finally { loading.value = false }
}
</script>
<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="card p-6 max-w-xl">
      <h1 class="section-title mb-6">Initier un achat</h1>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="form-label">Parcelle</label>
          <div v-if="preselected && parcelle" class="p-3 rounded-lg text-sm" style="background: var(--bg-page);">
            <p class="font-medium" style="color: var(--text-primary);">{{ parcelle.titre || parcelle.code || '#' + parcelle.id }}</p>
            <p class="text-xs mt-1" style="color: var(--text-secondary);">{{ parcelle.commune?.nom || '' }} {{ parcelle.superficie ? '— ' + parcelle.superficie + ' m²' : '' }}</p>
          </div>
          <select v-else v-model="form.parcelle_id" class="form-input w-full" :disabled="loadingParcelles">
            <option value="" disabled>Sélectionner une parcelle</option>
            <option v-for="p in parcelles" :key="p.id" :value="p.id">{{ p.code || '#' + p.id }} — {{ p.commune?.nom || '' }}</option>
          </select>
          <p v-if="errors.parcelle_id" class="text-xs mt-1" style="color: var(--danger);">{{ errors.parcelle_id[0] }}</p>
        </div>
        <div>
          <label class="form-label">Type</label>
          <select v-model="form.type" class="form-input w-full">
            <option value="vente">Vente</option>
            <option value="achat">Achat</option>
          </select>
        </div>
        <div>
          <label class="form-label">Message (optionnel)</label>
          <textarea v-model="form.message" class="form-input w-full" rows="4" placeholder="Expliquez votre intérêt pour cette parcelle..."></textarea>
        </div>
        <div class="flex gap-3 pt-2">
          <button type="submit" :disabled="loading" class="btn-green">
            <i class="fas fa-paper-plane mr-1"></i> {{ loading ? 'Envoi...' : 'Soumettre' }}
          </button>
          <button type="button" @click="router.push('/citoyen/demandes-achat')" class="btn-outline">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</template>
