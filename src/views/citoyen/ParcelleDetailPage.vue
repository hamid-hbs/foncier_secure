<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { goBack } from '@/utils/navigation'
import parcelleApi from '@/api/parcelle'

const route = useRoute()
const router = useRouter()

const parcelle = ref(null)
const loading = ref(true)
const confirmDelete = ref(false)
const deleting = ref(false)

const BASE_URL = import.meta.env.VITE_API_URL?.replace('/api', '') || ''

function getFileUrl(chemin) {
  if (!chemin) return null
  if (chemin.startsWith('http')) return chemin
  return `${BASE_URL}/storage/${chemin}`
}

function getPhotoUrl(p) {
  const docs = p?.documents || []
  const photoDoc = docs.find(d => d.type_document === 'photo')
  return photoDoc ? getFileUrl(photoDoc.chemin_fichier || photoDoc.fichier) : null
}

function statutBadgeClass(s) {
  const map = { libre: 'badge-success', en_demande: 'badge-warning', en_transaction: 'badge-info', vendue: 'badge-neutral', conteste: 'badge-danger', en_verification: 'badge-info', sollicite: 'badge-purple' }
  return map[s] || 'badge-neutral'
}

function docIcon(type) {
  const map = { photo: 'fa-image', titre: 'fa-certificate', plan: 'fa-map', autre: 'fa-file' }
  return map[type] || 'fa-file'
}

onMounted(async () => {
  try {
    const res = await parcelleApi.show(route.params.id)
    parcelle.value = res.data || null
  } catch (e) { console.error('Erreur chargement parcelle:', e) }
  loading.value = false
})

async function deleteParcelle() {
  deleting.value = true
  try {
    await parcelleApi.update(route.params.id, { statut: 'supprime' })
    router.push('/citoyen/parcelles')
  } catch (e) { console.error('Erreur suppression parcelle:', e) }
  deleting.value = false
}
</script>

<template>
  <div class="page-wrap max-w-3xl mx-auto">
    <!-- Header skeleton -->
    <div v-if="loading" class="space-y-4">
      <div class="skeleton h-64 rounded-2xl"></div>
      <div class="skeleton h-6 rounded-lg w-2/3"></div>
      <div class="skeleton h-4 rounded-lg w-1/3"></div>
    </div>

    <template v-else-if="!parcelle">
      <div class="card">
        <div class="empty-state">
          <div class="empty-icon"><i class="fas fa-map"></i></div>
          <p class="empty-title">Parcelle introuvable</p>
          <p class="empty-text">La parcelle demandée n'existe pas ou vous n'avez pas accès.</p>
          <button @click="router.push('/citoyen/parcelles')" class="btn btn-primary mt-4">Retour aux parcelles</button>
        </div>
      </div>
    </template>

    <template v-else>
      <!-- Back -->
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-stone-900 transition-colors mb-6">
        <i class="fas fa-arrow-left text-xs"></i> Retour
      </button>

      <!-- Hero image -->
      <div class="relative h-56 sm:h-72 rounded-2xl overflow-hidden mb-6 bg-stone-100">
        <img v-if="getPhotoUrl(parcelle)" :src="getPhotoUrl(parcelle)" class="w-full h-full object-cover" :alt="parcelle.titre" />
        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand to-brand-light">
          <i class="fas fa-map-marked-alt text-6xl text-white/20"></i>
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
        <div class="absolute bottom-5 left-5 right-5 flex items-center justify-between">
          <div>
            <h1 class="font-display font-extrabold text-xl text-white drop-shadow">{{ parcelle.titre || 'Parcelle non nommée' }}</h1>
            <p class="text-sm text-white/80 font-mono">{{ parcelle.code }}</p>
          </div>
          <span class="badge backdrop-blur-sm shadow" :class="statutBadgeClass(parcelle.statut)">{{ (parcelle.statut || '').replace('_', ' ') }}</span>
        </div>
      </div>

      <!-- Info grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <!-- Infos principales -->
        <div class="card">
          <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
            <i class="fas fa-circle-info text-brand"></i> Informations
          </h3>
          <div class="space-y-3">
            <div class="flex items-start gap-3">
              <i class="fas fa-location-dot w-4 text-center text-stone-400 mt-0.5 text-sm shrink-0"></i>
              <div>
                <p class="text-xs text-stone-400 font-medium">Localisation</p>
                <p class="text-sm font-semibold text-stone-900">{{ [parcelle.commune?.nom, parcelle.arrondissement?.nom, parcelle.quartier?.nom].filter(Boolean).join(' · ') || '—' }}</p>
              </div>
            </div>
            <div v-if="parcelle.superficie" class="flex items-start gap-3">
              <i class="fas fa-ruler-combined w-4 text-center text-stone-400 mt-0.5 text-sm shrink-0"></i>
              <div>
                <p class="text-xs text-stone-400 font-medium">Superficie</p>
                <p class="text-sm font-semibold text-stone-900">{{ parcelle.superficie }} m²</p>
              </div>
            </div>
            <div v-if="parcelle.prix_estimatif" class="flex items-start gap-3">
              <i class="fas fa-tag w-4 text-center text-gold mt-0.5 text-sm shrink-0"></i>
              <div>
                <p class="text-xs text-stone-400 font-medium">Prix estimatif</p>
                <p class="text-sm font-bold text-stone-900">{{ Number(parcelle.prix_estimatif).toLocaleString('fr-FR') }} FCFA</p>
              </div>
            </div>
            <div v-if="parcelle.usage" class="flex items-start gap-3">
              <i class="fas fa-building w-4 text-center text-stone-400 mt-0.5 text-sm shrink-0"></i>
              <div>
                <p class="text-xs text-stone-400 font-medium">Usage prévu</p>
                <p class="text-sm font-semibold text-stone-900 capitalize">{{ parcelle.usage }}</p>
              </div>
            </div>
            <div v-if="parcelle.description" class="flex items-start gap-3">
              <i class="fas fa-align-left w-4 text-center text-stone-400 mt-0.5 text-sm shrink-0"></i>
              <div>
                <p class="text-xs text-stone-400 font-medium">Description</p>
                <p class="text-sm text-stone-700 leading-relaxed">{{ parcelle.description }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Vérification -->
        <div class="card">
          <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
            <i class="fas fa-shield-halved text-brand"></i> Sécurisation
          </h3>
          <div class="space-y-3">
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl" :class="parcelle.hash_blockchain ? 'bg-success/5 border border-success/20' : 'bg-stone-50'">
              <i :class="['fas text-sm', parcelle.hash_blockchain ? 'fa-check-circle text-success' : 'fa-circle-xmark text-stone-400']"></i>
              <div>
                <p class="text-xs font-semibold" :class="parcelle.hash_blockchain ? 'text-success' : 'text-stone-500'">
                  {{ parcelle.hash_blockchain ? 'Enregistré blockchain' : 'Non enregistré' }}
                </p>
                <p v-if="parcelle.hash_blockchain" class="text-xs font-mono text-stone-400 mt-0.5 truncate">{{ parcelle.hash_blockchain }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-stone-50">
              <i class="fas fa-calendar text-stone-400 text-sm"></i>
              <div>
                <p class="text-xs text-stone-500 font-medium">Enregistrée le</p>
                <p class="text-sm font-semibold text-stone-900">{{ parcelle.created_at ? new Date(parcelle.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) : '—' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Documents -->
      <div v-if="parcelle.documents?.length" class="card mb-6">
        <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
          <i class="fas fa-folder-open text-brand"></i> Documents ({{ parcelle.documents.length }})
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <a
            v-for="doc in parcelle.documents" :key="doc.id"
            :href="getFileUrl(doc.chemin_fichier || doc.fichier)"
            target="_blank"
            class="flex items-center gap-3 p-3 rounded-xl border border-stone-100 hover:border-brand-100 hover:bg-brand-50/20 transition-all"
          >
            <div class="w-9 h-9 rounded-lg bg-brand-50 flex items-center justify-center text-brand text-sm shrink-0">
              <i :class="['fas', docIcon(doc.type_document)]"></i>
            </div>
            <div class="min-w-0">
              <p class="text-xs font-bold text-stone-900 capitalize">{{ doc.type_document }}</p>
              <p class="text-[10px] text-stone-400 truncate">{{ doc.nom || 'Document' }}</p>
            </div>
          </a>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex flex-wrap gap-3">
        <router-link :to="{ name: 'ModifierParcelle', params: { id: parcelle.id } }" class="btn btn-outline">
          <i class="fas fa-pen"></i> Modifier
        </router-link>
        <router-link :to="{ name: 'DocumentsParcelle', params: { id: parcelle.id } }" class="btn btn-ghost">
          <i class="fas fa-file-plus"></i> Gérer les documents
        </router-link>
        <button @click="confirmDelete = true" class="btn btn-ghost text-danger hover:bg-red-50 ml-auto">
          <i class="fas fa-trash"></i> Supprimer
        </button>
      </div>

      <!-- Delete confirm modal -->
      <Transition name="scale">
        <div v-if="confirmDelete" class="modal-overlay" @click.self="confirmDelete = false">
          <div class="modal max-w-sm">
            <div class="modal-header">
              <h3 class="modal-title">Supprimer la parcelle</h3>
              <button @click="confirmDelete = false" class="btn btn-ghost btn-icon"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
              <div class="alert alert-danger mb-4">
                <i class="fas fa-triangle-exclamation shrink-0"></i>
                <span>Cette action est irréversible. Toutes les données associées seront supprimées.</span>
              </div>
              <p class="text-sm text-stone-600">Confirmez la suppression de <strong>{{ parcelle.titre || 'cette parcelle' }}</strong> ?</p>
            </div>
            <div class="modal-footer">
              <button @click="confirmDelete = false" class="btn btn-ghost">Annuler</button>
              <button @click="deleteParcelle" class="btn btn-danger" :disabled="deleting">
                <div v-if="deleting" class="spinner spinner-sm border-white/30 border-t-white"></div>
                <i v-else class="fas fa-trash"></i>
                {{ deleting ? 'Suppression…' : 'Supprimer définitivement' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </template>
  </div>
</template>
