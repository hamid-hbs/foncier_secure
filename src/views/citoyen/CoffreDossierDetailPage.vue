<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import coffreApi from '@/api/coffre'

const route = useRoute()
const router = useRouter()

const document = ref(null)
const loading = ref(true)

function fileIcon(type) {
  if (!type) return 'fa-file'
  if (type.includes('pdf')) return 'fa-file-pdf text-red-500'
  if (type.includes('image') || type.includes('jpg') || type.includes('png')) return 'fa-file-image text-sky-500'
  if (type.includes('word') || type.includes('doc')) return 'fa-file-word text-blue-500'
  return 'fa-file text-stone-400'
}

onMounted(async () => {
  try {
    const res = await coffreApi.showDossier(route.params.id)
    document.value = res.data || null
  } catch (e) { console.error('Erreur chargement document:', e) }
  loading.value = false
})

async function deleteDocument(id) {
  if (!confirm('Supprimer ce document ?')) return
  try {
    await coffreApi.deleteDocument(id)
    router.push('/citoyen/coffre')
  } catch (e) { console.error('Erreur suppression document:', e) }
}

const BASE_URL = import.meta.env.VITE_API_URL?.replace('/api', '') || ''
function getFileUrl(chemin) {
  if (!chemin) return '#'
  if (chemin.startsWith('http')) return chemin
  return `${BASE_URL}/storage/${chemin}`
}
</script>

<template>
  <div class="page-wrap max-w-2xl mx-auto">
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-24 rounded-2xl"></div>
      <div class="skeleton h-48 rounded-2xl"></div>
    </div>

    <div v-else-if="!document" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-lock"></i></div>
        <p class="empty-title">Document introuvable</p>
        <button @click="router.push('/citoyen/coffre')" class="btn btn-primary mt-4">Retour au coffre-fort</button>
      </div>
    </div>

    <template v-else>
      <button @click="router.push('/citoyen/coffre')" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
        <i class="fas fa-arrow-left text-xs"></i> Coffre-fort
      </button>

      <div class="card mb-5">
        <div class="flex items-start gap-4">
          <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center text-brand shrink-0 text-xl">
            <i :class="['fas', fileIcon(document.type_document || document.mime_type)]"></i>
          </div>
          <div class="flex-1 min-w-0">
            <h1 class="font-display font-bold text-xl text-stone-900">{{ document.nom_fichier || document.nom || 'Document' }}</h1>
            <div class="flex flex-wrap gap-x-5 gap-y-1 mt-2 text-sm text-stone-400">
              <span v-if="document.type_document"><i class="fas fa-tag mr-1.5 text-stone-300"></i>{{ document.type_document }}</span>
              <span v-if="document.taille"><i class="fas fa-weight-hanging mr-1.5 text-stone-300"></i>{{ (document.taille / 1024).toFixed(1) }} Ko</span>
              <span><i class="fas fa-calendar mr-1.5 text-stone-300"></i>{{ document.created_at ? new Date(document.created_at).toLocaleDateString('fr-FR') : '—' }}</span>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <a :href="getFileUrl(document.chemin_fichier || document.fichier)" target="_blank" class="btn btn-primary btn-sm">
              <i class="fas fa-download"></i> Télécharger
            </a>
            <button @click="deleteDocument(document.id)" class="btn btn-ghost btn-icon text-stone-400 hover:text-danger hover:bg-red-50">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="card">
        <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
          <i class="fas fa-info-circle text-brand text-sm"></i> Informations
        </h3>
        <div class="space-y-3">
          <div class="flex items-center gap-3 py-2.5 border-b border-stone-50">
            <span class="text-sm font-semibold text-stone-500 min-w-[120px]">Type</span>
            <span class="text-sm text-stone-900">{{ document.type_document || 'Document' }}</span>
          </div>
          <div class="flex items-center gap-3 py-2.5 border-b border-stone-50">
            <span class="text-sm font-semibold text-stone-500 min-w-[120px]">Hash SHA-256</span>
            <span class="text-xs font-mono text-stone-600 break-all">{{ document.hash_sha256 || '—' }}</span>
          </div>
          <div v-if="document.uploader" class="flex items-center gap-3 py-2.5 border-b border-stone-50">
            <span class="text-sm font-semibold text-stone-500 min-w-[120px]">Ajouté par</span>
            <span class="text-sm text-stone-900">{{ document.uploader.prenom }} {{ document.uploader.nom }}</span>
          </div>
          <div class="flex items-center gap-3 py-2.5">
            <span class="text-sm font-semibold text-stone-500 min-w-[120px]">Date d'ajout</span>
            <span class="text-sm text-stone-900">{{ document.created_at ? new Date(document.created_at).toLocaleString('fr-FR') : '—' }}</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
