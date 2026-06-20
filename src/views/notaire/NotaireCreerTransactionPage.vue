<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import transactionApi from '@/api/transaction'
import parcelleApi from '@/api/parcelle'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const route = useRoute()
const form = ref({ parcelle_id: route.query.parcelle || '', vendeur_id: route.query.vendeur || '', acheteur_id: route.query.acheteur || '', titre: '' })
const parcelles = ref([])
const loading = ref(false)
const errors = ref({})

onMounted(async () => {
  try {
    const r = await parcelleApi.list()
    parcelles.value = r.data?.data ?? []
  } catch {}
})

async function submit() {
  loading.value = true
  errors.value = {}
  try {
    await transactionApi.create(form.value)
    router.push('/notaire/transactions')
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data?.errors ?? {}
    else alert(e.response?.data?.message || "Erreur")
  }
  finally { loading.value = false }
}
</script>
<template>
  <div class="page-container max-w-xl">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="card p-6">
      <h1 class="section-title mb-6">Nouveau dossier de transaction</h1>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="form-label">Parcelle <span style="color: var(--danger);">*</span></label>
          <select v-model="form.parcelle_id" class="form-input w-full" required>
            <option value="">Sélectionner</option>
            <option v-for="p in parcelles" :key="p.id" :value="p.id">{{ p.code || '#' + p.id }}</option>
          </select>
          <p v-if="errors.parcelle_id" class="text-xs mt-1" style="color: var(--danger);">{{ errors.parcelle_id[0] }}</p>
        </div>
        <div>
          <label class="form-label">Vendeur <span style="color: var(--danger);">*</span></label>
          <input v-model="form.vendeur_id" class="form-input w-full" placeholder="ID du vendeur" required />
          <p v-if="errors.vendeur_id" class="text-xs mt-1" style="color: var(--danger);">{{ errors.vendeur_id[0] }}</p>
        </div>
        <div>
          <label class="form-label">Acheteur</label>
          <input v-model="form.acheteur_id" class="form-input w-full" placeholder="ID de l'acheteur (optionnel)" />
        </div>
        <div>
          <label class="form-label">Titre (optionnel)</label>
          <input v-model="form.titre" class="form-input w-full" placeholder="ex: Vente parcelle FS-00042" />
        </div>
        <button type="submit" :disabled="loading" class="btn-green"><i class="fas fa-folder-plus mr-1"></i> {{ loading ? 'Création...' : 'Créer le dossier' }}</button>
      </form>
    </div>
  </div>
</template>
