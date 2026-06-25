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
const uploadError = ref('')
const fileInput = ref(null)
const typeDoc = ref('titre')
const showUploadModal = ref(false)
const uploadForm = ref({ type_document: 'titre', fichier: null })

const typeOptions = [
  { value: 'titre', label: 'Titre foncier' },
  { value: 'photo', label: 'Photo du terrain' },
  { value: 'plan', label: 'Plan parcellaire' },
  { value: 'autre', label: 'Autre document' },
]

const BASE_URL = import.meta.env.VITE_API_URL?.replace('/api', '') || ''
function getFileUrl(c) {
  if (!c) return '#'
  return c.startsWith('http') ? c : `${BASE_URL}/storage/${c}`
}
function docIcon(type) {
  const map = { photo: 'fa-image text-sky-500', titre: 'fa-certificate text-gold-dark', plan: 'fa-map text-brand', autre: 'fa-file text-stone-500' }
  return map[type] || 'fa-file'
}

onMounted(async () => {
  try {
    const res = await parcelleApi.show(route.params.id)
    parcelle.value = res.data
    documents.value = parcelle.value?.documents || []
  } catch (e) { console.error('Erreur chargement documents:', e) }
  loading.value = false
})

function onFileChange(e) {
  uploadForm.value.fichier = e.target.files?.[0] || null
}

async function uploadDocument() {
  if (!uploadForm.value.fichier) return
  uploading.value = true
  uploadError.value = ''
  try {
    const fd = new FormData()
    fd.append('fichier', uploadForm.value.fichier)
    fd.append('type_document', uploadForm.value.type_document)
    await parcelleApi.uploadDocument(route.params.id, fd)
    const res = await parcelleApi.show(route.params.id)
    parcelle.value = res.data
    documents.value = parcelle.value?.documents || []
    showUploadModal.value = false
    uploadForm.value = { type_document: 'titre', fichier: null }
  } catch (e) {
    uploadError.value = 'Erreur lors du téléchargement'
    console.error('Erreur upload document:', e)
  } finally {
    uploading.value = false
  }
}

async function deleteDocument(docId) {
  if (!confirm('Supprimer ce document ?')) return
  try {
    await parcelleApi.deleteDocument(route.params.id, docId)
    documents.value = documents.value.filter(d => d.id !== docId)
  } catch (e) { console.error('Erreur suppression document:', e) }
}
</script>

<template>
  <div class="page-wrap max-w-2xl mx-auto">
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-20 rounded-2xl"></div>
      <div class="skeleton h-48 rounded-2xl"></div>
    </div>

    <template v-else>
      <div class="flex items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
          <button @click="router.push({ name: 'CitoyenParcelleDetail', params: { id: route.params.id } })" class="btn btn-ghost btn-icon text-stone-500">
            <i class="fas fa-arrow-left"></i>
          </button>
          <div>
            <h1 class="page-title">Documents</h1>
            <p class="page-subtitle">{{ parcelle?.titre || 'Parcelle #' + route.params.id }}</p>
          </div>
        </div>
        <button @click="showUploadModal = true" class="btn btn-primary btn-sm">
          <i class="fas fa-upload"></i> Ajouter
        </button>
      </div>

      <div v-if="documents.length === 0" class="card">
        <div class="empty-state">
          <div class="empty-icon"><i class="fas fa-file-plus"></i></div>
          <p class="empty-title">Aucun document</p>
          <p class="empty-text">Ajoutez les documents justificatifs de cette parcelle.</p>
          <button @click="showUploadModal = true" class="btn btn-primary mt-4">
            <i class="fas fa-upload"></i> Ajouter un document
          </button>
        </div>
      </div>

      <div v-else class="space-y-2">
        <div v-for="doc in documents" :key="doc.id" class="card flex items-center gap-4">
          <div class="w-11 h-11 rounded-xl bg-stone-100 flex items-center justify-center text-lg shrink-0">
            <i :class="['fas', docIcon(doc.type_document)]"></i>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-stone-900 capitalize">{{ doc.type_document }}</p>
            <p class="text-xs text-stone-400">{{ doc.created_at ? new Date(doc.created_at).toLocaleDateString('fr-FR') : '—' }}</p>
          </div>
          <div class="flex items-center gap-2">
            <a :href="getFileUrl(doc.chemin_fichier || doc.fichier)" target="_blank" class="btn btn-ghost btn-icon text-brand hover:bg-brand-50">
              <i class="fas fa-eye text-sm"></i>
            </a>
            <button @click="deleteDocument(doc.id)" class="btn btn-ghost btn-icon text-stone-400 hover:text-danger hover:bg-red-50">
              <i class="fas fa-trash text-sm"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Upload modal -->
      <Transition name="scale">
        <div v-if="showUploadModal" class="modal-overlay" @click.self="showUploadModal = false">
          <div class="modal max-w-sm">
            <div class="modal-header">
              <h3 class="modal-title">Ajouter un document</h3>
              <button @click="showUploadModal = false" class="btn btn-ghost btn-icon"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body space-y-4">
              <div v-if="uploadError" class="alert alert-danger">
                <i class="fas fa-triangle-exclamation shrink-0"></i>
                <span>{{ uploadError }}</span>
              </div>
              <div>
                <label class="form-label">Type de document</label>
                <select v-model="uploadForm.type_document" class="form-select">
                  <option v-for="t in typeOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
              </div>
              <div>
                <label class="form-label">Fichier</label>
                <input type="file" @change="onFileChange" class="form-input text-sm file:mr-3 file:px-3 file:py-1 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand file:font-semibold file:text-xs" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />
              </div>
            </div>
            <div class="modal-footer">
              <button @click="showUploadModal = false" class="btn btn-ghost">Annuler</button>
              <button @click="uploadDocument" class="btn btn-primary" :disabled="uploading || !uploadForm.fichier">
                <div v-if="uploading" class="spinner spinner-sm border-white/30 border-t-white"></div>
                <i v-else class="fas fa-upload"></i>
                {{ uploading ? 'Envoi…' : 'Télécharger' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </template>
  </div>
</template>
