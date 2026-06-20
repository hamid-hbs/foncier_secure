<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import coffreApi from '@/api/coffre'

const route = useRoute()
const router = useRouter()

const dossier = ref(null)
const loading = ref(true)
const error = ref('')
const uploading = ref(false)
const selectedFile = ref(null)
const showShareModal = ref(false)
const shareDocId = ref(null)
const shareEmail = ref('')
const shareExpiry = ref('')
const integriteStatus = ref({})

async function fetchDossier() {
  loading.value = true
  try {
    const res = await coffreApi.listDossiers()
    const list = (res.data || res.data?.data || []).filter(Boolean)
    dossier.value = list.find(d => d.id == route.params.id) || null
    if (!dossier.value) throw new Error('not found')
  } catch {
    error.value = 'Impossible de charger le dossier'
  } finally {
    loading.value = false
  }
}

onMounted(fetchDossier)

async function uploadDocument() {
  if (!selectedFile.value) return
  uploading.value = true
  error.value = ''
  try {
    const formData = new FormData()
    formData.append('fichier', selectedFile.value)
    await coffreApi.uploadDocument(dossier.value.id, formData)
    selectedFile.value = null
    await fetchDossier()
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors du téléversement"
  } finally {
    uploading.value = false
  }
}

async function downloadDocument(doc) {
  try {
    const res = await coffreApi.downloadDocument(doc.id)
    const url = URL.createObjectURL(new Blob([res.data]))
    const a = document.createElement('a')
    a.href = url
    a.download = doc.nom_fichier || doc.nom || 'document'
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    error.value = 'Erreur de téléchargement'
  }
}

function openShare(doc) {
  shareDocId.value = doc.id
  shareEmail.value = ''
  shareExpiry.value = ''
  showShareModal.value = true
}

async function submitShare() {
  if (!shareEmail.value) return
  try {
    const payload = { email: shareEmail.value }
    if (shareExpiry.value) payload.expire_le = shareExpiry.value
    await coffreApi.shareDocument(shareDocId.value, payload)
    showShareModal.value = false
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors du partage'
  }
}

async function verifyIntegrite(docId) {
  try {
    const res = await coffreApi.verifyIntegrite(docId)
    integriteStatus.value[docId] = res.data?.integrite === true
  } catch {
    integriteStatus.value[docId] = false
  }
}

function formatSize(bytes) {
  if (!bytes) return '-'
  const sizes = ['o', 'Ko', 'Mo', 'Go']
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i]
}
</script>

<template>
  <div class="page-container max-w-5xl">
    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--border); border-top-color: var(--green-tree);"></div>
    </div>

    <div v-else-if="error && !dossier" class="card text-center py-12">
      <i class="fas fa-exclamation-circle text-4xl mb-3" style="color: var(--danger);"></i>
      <p style="color: var(--text-secondary);">{{ error }}</p>
      <button @click="router.push({ name: 'Coffre' })" class="btn-outline mt-4 flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Retour au coffre
      </button>
    </div>

    <template v-else-if="dossier">
      <button @click="router.push({ name: 'Coffre' })" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
        <i class="fas fa-arrow-left"></i> Retour au coffre
      </button>

      <div v-if="error" class="p-3.5 rounded-lg text-sm mb-4" style="background: #FEE2E2; color: var(--danger); border: 1px solid #FECACA;">
        {{ error }}
      </div>

      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #F3E8FF;">
          <i class="fas fa-folder" style="color: #6B21A8;"></i>
        </div>
        <div>
          <h1 class="section-title">{{ dossier.titre }}</h1>
          <p v-if="dossier.description" class="section-subtitle">{{ dossier.description }}</p>
          <p class="text-xs mt-1" style="color: var(--text-secondary);">
            Créé le {{ dossier.created_at ? new Date(dossier.created_at).toLocaleDateString('fr-FR') : '-' }}
          </p>
        </div>
      </div>

      <div class="card mb-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2" style="color: var(--text-primary);">
          <i class="fas fa-upload" style="color: var(--green-tree);"></i> Ajouter un document
        </h3>
        <div class="flex items-center gap-3">
          <input type="file" @change="selectedFile = $event.target.files[0] || null" class="form-input flex-1" />
          <button @click="uploadDocument" class="btn-green btn-sm" :disabled="!selectedFile || uploading">
            <i class="fas fa-cloud-upload-alt"></i> {{ uploading ? 'Téléversement...' : 'Téléverser' }}
          </button>
        </div>
        <p v-if="selectedFile" class="text-xs mt-2" style="color: var(--text-secondary);">
          Fichier sélectionné : {{ selectedFile.name }}
        </p>
      </div>

      <div class="card">
        <div class="flex-between mb-4">
          <h3 class="font-semibold" style="color: var(--text-primary);">
            <i class="fas fa-file-alt" style="color: var(--green-tree);"></i>
            Documents ({{ dossier.documents?.length || 0 }})
          </h3>
        </div>

        <div v-if="!dossier.documents?.length" class="text-center py-10">
          <i class="fas fa-file-upload text-3xl mb-3" style="color: var(--border);"></i>
          <p style="color: var(--text-secondary);">Aucun document dans ce dossier.</p>
        </div>

        <div v-else class="space-y-2">
          <div v-for="doc in dossier.documents" :key="doc.id" class="flex items-center gap-3 p-3 rounded-lg" style="background: var(--bg-page);">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background: #F3E8FF; color: #6B21A8;">
              <i class="fas fa-file-alt"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-sm truncate" style="color: var(--text-primary);">{{ doc.nom_fichier || doc.nom || 'Document' }}</p>
              <p class="text-xs" style="color: var(--text-secondary);">
                <span v-if="doc.type">{{ doc.type }} — </span>
                {{ doc.created_at ? new Date(doc.created_at).toLocaleDateString('fr-FR') : '-' }}
                <span v-if="doc.taille"> — {{ formatSize(doc.taille) }}</span>
              </p>
            </div>
            <div class="flex items-center gap-1 shrink-0">
              <button @click="verifyIntegrite(doc.id)" class="btn-outline btn-sm" title="Vérifier l'intégrité">
                <i v-if="integriteStatus[doc.id] === undefined" class="fas fa-shield-alt"></i>
                <i v-else-if="integriteStatus[doc.id]" class="fas fa-check-circle" style="color: var(--green-tree);"></i>
                <i v-else class="fas fa-times-circle" style="color: var(--danger);"></i>
              </button>
              <button @click="openShare(doc)" class="btn-outline btn-sm" title="Partager">
                <i class="fas fa-share-alt"></i>
              </button>
              <button @click="downloadDocument(doc)" class="btn-outline btn-sm" title="Télécharger">
                <i class="fas fa-download"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <Teleport to="body">
      <div v-if="showShareModal" class="fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(0,0,0,0.4);">
        <div class="card max-w-md w-full mx-4">
          <h3 class="font-semibold mb-4" style="color: var(--text-primary);">Partager le document</h3>
          <div class="space-y-4">
            <div class="form-group">
              <label class="form-label">Adresse email</label>
              <input v-model="shareEmail" type="email" class="form-input" placeholder="Email du destinataire" />
            </div>
            <div class="form-group">
              <label class="form-label">Date d'expiration (optionnelle)</label>
              <input v-model="shareExpiry" type="date" class="form-input" />
            </div>
          </div>
          <div class="flex items-center justify-end gap-3 mt-6">
            <button @click="showShareModal = false" class="btn-outline btn-sm">Annuler</button>
            <button @click="submitShare" class="btn-green btn-sm flex items-center gap-1" :disabled="!shareEmail">
              <i class="fas fa-share-alt"></i> Partager
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
