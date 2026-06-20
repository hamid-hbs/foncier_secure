<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import parcelleApi from '@/api/parcelle'

const route = useRoute()
const router = useRouter()

const parcelle = ref(null)
const documents = ref([])
const loading = ref(true)
const uploading = ref(false)
const deleteLoading = ref(null)
const error = ref('')

const uploadType = ref('autre')
const uploadFile = ref(null)

const typeOptions = [
  { value: 'tf', label: 'Titre foncier' },
  { value: 'adc', label: 'Attestation de détention coutumière' },
  { value: 'plan_topo', label: 'Plan topographique' },
  { value: 'photo', label: 'Photo' },
  { value: 'autre', label: 'Autre' },
]

async function load() {
  try {
    const res = await parcelleApi.show(route.params.id)
    parcelle.value = res.data
    documents.value = (res.data.documents || []).filter(Boolean)
  } catch { /* ignore */ }
  loading.value = false
}

onMounted(load)

function formatSize(bytes) {
  if (!bytes) return '—'
  const kb = bytes / 1024
  if (kb < 1024) return kb.toFixed(1) + ' Ko'
  return (kb / 1024).toFixed(1) + ' Mo'
}

function typeLabel(value) {
  return typeOptions.find(t => t.value === value)?.label || value
}

async function upload() {
  if (!uploadFile.value) return
  uploading.value = true
  error.value = ''
  try {
    const formData = new FormData()
    formData.append('type_document', uploadType.value)
    formData.append('fichier', uploadFile.value)
    await parcelleApi.uploadDocument(route.params.id, formData)
    uploadFile.value = null
    await load()
  } catch (e) {
    const data = e.response?.data
    if (data?.errors) {
      error.value = Object.values(data.errors).flat().join(', ')
    } else {
      error.value = data?.message || "Erreur lors de l'upload"
    }
  }
  uploading.value = false
}

async function removeDocument(docId) {
  deleteLoading.value = docId
  try {
    await parcelleApi.deleteDocument(route.params.id, docId)
    documents.value = documents.value.filter(d => d.id !== docId)
  } catch { /* ignore */ }
  deleteLoading.value = null
}
</script>

<template>
  <div class="page-container max-w-4xl">
    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--border); border-top-color: var(--green-tree);"></div>
    </div>

    <template v-else>
      <button @click="router.push({ name: 'CitoyenParcelleDetail', params: { id: route.params.id } })" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
        <i class="fas fa-arrow-left"></i> Retour à la parcelle
      </button>

      <div class="flex items-center gap-3 mb-8">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #D1FAE5;">
          <i class="fas fa-file-lines" style="color: var(--green-tree);"></i>
        </div>
        <div>
          <h1 class="section-title">Documents</h1>
          <p class="section-subtitle">Parcelle : <span class="font-medium" style="color: var(--green-tree);">{{ parcelle?.code || parcelle?.titre || '#' + parcelle?.id }}</span></p>
        </div>
      </div>

      <div v-if="error" class="p-3.5 rounded-lg text-sm mb-4" style="background: #FEE2E2; color: var(--danger); border: 1px solid #FECACA;">
        {{ error }}
      </div>

      <div class="card mb-8">
        <h3 class="font-semibold mb-4" style="color: var(--green-tree);">Ajouter un document</h3>
        <div class="form-group mb-4">
          <label class="form-label">Type de document</label>
          <select v-model="uploadType" class="form-select">
            <option v-for="t in typeOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
          </select>
        </div>
        <div class="flex items-center gap-3">
          <input type="file" @change="uploadFile = $event.target.files[0] || null" class="form-input flex-1" />
          <button @click="upload" class="btn-green btn-sm flex items-center gap-1" :disabled="!uploadFile || uploading">
            <i class="fas fa-upload"></i> {{ uploading ? 'Téléversement...' : 'Téléverser' }}
          </button>
        </div>
        <p v-if="uploadFile" class="text-xs mt-2" style="color: var(--text-secondary);">{{ uploadFile.name }} ({{ formatSize(uploadFile.size) }})</p>
      </div>

      <div class="card">
        <h3 class="font-semibold mb-4" style="color: var(--green-tree);">Documents téléversés ({{ documents.length }})</h3>
        <div v-if="documents.length === 0" class="text-center py-8">
          <i class="fas fa-file-lines text-3xl mb-3" style="color: var(--border);"></i>
          <p class="text-sm" style="color: var(--text-secondary);">Aucun document.</p>
        </div>
        <div v-else class="space-y-3">
          <div v-for="doc in documents" :key="doc.id" class="flex items-center gap-4 p-4 rounded-lg" style="border: 1px solid var(--border);">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background: var(--bg-page);">
              <i class="fas fa-file-lines" style="color: var(--green-tree);"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-sm truncate" style="color: var(--text-primary);">{{ doc.nom_fichier || doc.nom || 'Document' }}</p>
              <div class="flex items-center gap-3 text-xs mt-1" style="color: var(--text-secondary);">
                <span>{{ formatSize(doc.taille) }}</span>
                <span>{{ doc.created_at ? new Date(doc.created_at).toLocaleDateString('fr-FR') : '—' }}</span>
                <span class="px-2 py-0.5 rounded text-xs" style="background: var(--bg-page); color: var(--green-tree);">{{ typeLabel(doc.type_document) }}</span>
              </div>
            </div>
            <div class="flex items-center gap-1 shrink-0">
              <a v-if="doc.url || doc.fichier" :href="doc.url || `/storage/${doc.fichier}`" target="_blank" class="btn-outline btn-sm flex items-center gap-1">
                <i class="fas fa-download"></i>
              </a>
              <button @click="removeDocument(doc.id)" class="btn-outline btn-sm flex items-center gap-1" :disabled="deleteLoading === doc.id" style="color: var(--danger); border-color: var(--danger);">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
