<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import coffreApi from '@/api/coffre'

const router = useRouter()

const documents = ref([])
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
    const res = await coffreApi.listDossiers()
    documents.value = (res.data.data || res.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement documents:', e) }
  loading.value = false
})
</script>

<template>
  <div class="page-wrap">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Coffre-fort numérique</h1>
        <p class="page-subtitle">Consultez vos documents fonciers en toute sécurité</p>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="i in 6" :key="i" class="skeleton h-28 rounded-2xl"></div>
    </div>

    <div v-else-if="documents.length === 0" class="card">
      <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-lock"></i></div>
        <p class="empty-title">Aucun document</p>
        <p class="empty-text">Vos documents fonciers apparaîtront ici.</p>
      </div>
    </div>

    <div v-else class="space-y-3">
      <div v-for="doc in documents" :key="doc.id"
        @click="router.push({ name: 'CoffreDossierDetail', params: { id: doc.id } })"
        class="card group hover:border-brand-100 hover:shadow-lg hover:-translate-y-1 cursor-pointer transition-all"
      >
        <div class="flex items-center gap-4">
          <div class="w-11 h-11 rounded-xl bg-stone-100 flex items-center justify-center shrink-0 text-lg group-hover:bg-brand group-hover:text-white transition-colors">
            <i :class="['fas', fileIcon(doc.type_document || doc.mime_type)]"></i>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-display font-bold text-stone-900 truncate group-hover:text-brand transition-colors">{{ doc.nom_fichier || doc.nom || 'Document' }}</p>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-0.5">
              <span v-if="doc.type_document" class="text-xs text-stone-400 capitalize">{{ doc.type_document }}</span>
              <span class="text-xs text-stone-400">{{ doc.created_at ? new Date(doc.created_at).toLocaleDateString('fr-FR') : '' }}</span>
              <span v-if="doc.taille" class="text-xs text-stone-400">{{ (doc.taille / 1024).toFixed(1) }} Ko</span>
            </div>
          </div>
          <i class="fas fa-chevron-right text-sm text-stone-300 group-hover:text-brand transition-colors"></i>
        </div>
      </div>
    </div>
  </div>
</template>
