<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import coffreApi from '@/api/coffre'
import { goBack } from '@/utils/navigation'

const router = useRouter()

const dossiers = ref([])
const loading = ref(true)
const showNew = ref(false)
const newDossier = ref({ titre: '', description: '' })
const creating = ref(false)
const error = ref('')

async function fetchDossiers() {
  try {
    const res = await coffreApi.listDossiers()
    dossiers.value = (res.data || []).filter(Boolean)
  } catch { /* ignore */ }
  loading.value = false
}

onMounted(fetchDossiers)

async function createDossier() {
  error.value = ''
  creating.value = true
  try {
    await coffreApi.createDossier(newDossier.value)
    showNew.value = false
    newDossier.value = { titre: '', description: '' }
    await fetchDossiers()
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de la création'
  }
  creating.value = false
}
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="flex-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #F3E8FF;">
          <i class="fas fa-folder" style="color: #6B21A8;"></i>
        </div>
        <div>
          <h1 class="section-title">Coffre numérique</h1>
          <p class="section-subtitle">Stockez et partagez vos documents</p>
        </div>
      </div>
      <button @click="showNew = !showNew" class="btn-green flex items-center gap-2">
        <i class="fas fa-plus"></i> {{ showNew ? 'Annuler' : 'Nouveau dossier' }}
      </button>
    </div>

    <div v-if="showNew" class="card mb-6">
      <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Nouveau dossier</h3>
      <div v-if="error" class="p-3.5 rounded-lg text-sm mb-3" style="background: #FEE2E2; color: var(--danger); border: 1px solid #FECACA;">{{ error }}</div>
      <div class="space-y-4">
        <div class="form-group">
          <label class="form-label">Titre</label>
          <input v-model="newDossier.titre" class="form-input" placeholder="Titre du dossier" required />
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea v-model="newDossier.description" class="form-textarea" placeholder="Description..."></textarea>
        </div>
        <button @click="createDossier" class="btn-green flex items-center gap-2" :disabled="creating || !newDossier.titre.trim()">
          <i class="fas fa-folder-plus"></i> {{ creating ? 'Création...' : 'Créer le dossier' }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--border); border-top-color: var(--green-tree);"></div>
    </div>

    <div v-else-if="dossiers.length === 0" class="card text-center py-12">
      <i class="fas fa-folder text-4xl mb-3" style="color: var(--border);"></i>
      <p style="color: var(--text-secondary);">Aucun dossier dans votre coffre.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="d in dossiers"
        :key="d.id"
        class="card cursor-pointer hover:shadow-lg transition-shadow"
        @click="router.push({ name: 'CoffreDossierDetail', params: { id: d.id } })"
      >
        <div class="flex items-start gap-3 mb-3">
          <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background: #F3E8FF;">
            <i class="fas fa-folder" style="color: #6B21A8;"></i>
          </div>
          <div class="min-w-0 flex-1">
            <h3 class="font-semibold truncate" style="color: var(--text-primary);">{{ d.titre }}</h3>
            <p class="text-xs" style="color: var(--text-secondary);">{{ d.documents?.length || 0 }} document(s)</p>
          </div>
        </div>
        <p v-if="d.description" class="text-sm line-clamp-2" style="color: var(--text-secondary);">{{ d.description }}</p>
        <div class="text-xs mt-3" style="color: var(--text-secondary);">
          {{ d.created_at ? new Date(d.created_at).toLocaleDateString('fr-FR') : '—' }}
        </div>
      </div>
    </div>
  </div>
</template>
