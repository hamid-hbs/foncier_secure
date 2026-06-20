<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import localisationApi from '@/api/localisation'
import adminApi from '@/api/admin'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const activeTab = ref('communes')

const communes = ref([])
const arrondissements = ref([])
const quartiers = ref([])
const loading = ref(true)

const showModal = ref(false)
const editingItem = ref(null)
const modalType = ref('communes')
const formNom = ref('')
const formParentId = ref('')
const formCommuneId = ref('')

const showDeleteConfirm = ref(false)
const deletingId = ref(null)

onMounted(() => fetchCommunes())

async function fetchCommunes() {
  loading.value = true
  try {
    const res = await localisationApi.getCommunes()
    communes.value = (res.data?.data || res.data || []).filter(Boolean)
  } catch { /* ignore */ }
  loading.value = false
}

async function fetchArrondissements(communeId) {
  loading.value = true
  try {
    const res = await localisationApi.getArrondissements(communeId)
    arrondissements.value = (res.data?.data || res.data || []).filter(Boolean)
  } catch { arrondissements.value = [] }
  loading.value = false
}

async function fetchQuartiers(arrondissementId) {
  loading.value = true
  try {
    const res = await localisationApi.getQuartiers(arrondissementId)
    quartiers.value = (res.data?.data || res.data || []).filter(Boolean)
  } catch { quartiers.value = [] }
  loading.value = false
}

const arrondissementsFiltered = computed(() => {
  if (!formCommuneId.value) return []
  return arrondissements.value.filter(a => String(a.commune_id) === String(formCommuneId.value))
})

function getCommuneName(id) {
  const c = communes.value.find(c => c.id === id)
  return c ? c.nom : '-'
}

function getArrondissementName(id) {
  const a = arrondissements.value.find(a => a.id === id)
  return a ? a.nom : '-'
}

async function onTabChange(tab) {
  activeTab.value = tab
  if (tab === 'arrondissements' && communes.value.length && !arrondissements.value.length) {
    await fetchArrondissements(communes.value[0].id)
  }
  if (tab === 'quartiers' && arrondissements.value.length && !quartiers.value.length) {
    await fetchQuartiers(arrondissements.value[0].id)
  }
}

function openCreate(type) {
  modalType.value = type
  editingItem.value = null
  formNom.value = ''
  formParentId.value = ''
  formCommuneId.value = ''
  showModal.value = true
}

function openEdit(item, type) {
  modalType.value = type
  editingItem.value = item
  formNom.value = item.nom || ''
  formParentId.value = item.commune_id || item.arrondissement_id || ''
  formCommuneId.value = item.commune_id || ''
  showModal.value = true
}

async function saveItem() {
  if (!formNom.value.trim()) return
  const data = { nom: formNom.value.trim() }
  if (modalType.value === 'arrondissements') data.commune_id = formParentId.value
  if (modalType.value === 'quartiers') data.arrondissement_id = formParentId.value

  try {
    if (editingItem.value) {
      if (modalType.value === 'communes') await adminApi.updateCommune(editingItem.value.id, data)
      else if (modalType.value === 'arrondissements') await adminApi.updateArrondissement(editingItem.value.id, data)
      else await adminApi.updateQuartier(editingItem.value.id, data)
    } else {
      if (modalType.value === 'communes') await adminApi.createCommune(data)
      else if (modalType.value === 'arrondissements') await adminApi.createArrondissement(data)
      else await adminApi.createQuartier(data)
    }
    showModal.value = false
    await refreshCurrentTab()
  } catch { alert('Erreur lors de l\'enregistrement') }
}

function confirmDelete(id, type) {
  deletingId.value = id
  modalType.value = type
  showDeleteConfirm.value = true
}

async function deleteItem() {
  if (!deletingId.value) return
  try {
    if (modalType.value === 'communes') await adminApi.deleteCommune(deletingId.value)
    else if (modalType.value === 'arrondissements') await adminApi.deleteArrondissement(deletingId.value)
    else await adminApi.deleteQuartier(deletingId.value)
    showDeleteConfirm.value = false
    deletingId.value = null
    await refreshCurrentTab()
  } catch { alert('Erreur lors de la suppression') }
}

async function refreshCurrentTab() {
  if (activeTab.value === 'communes') await fetchCommunes()
  else if (activeTab.value === 'arrondissements') {
    if (communes.value.length) await fetchArrondissements(communes.value[0].id)
  } else if (activeTab.value === 'quartiers') {
    if (arrondissements.value.length) await fetchQuartiers(arrondissements.value[0].id)
  }
}
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>
    <div class="flex items-center gap-3 mb-8">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--green-tree); opacity: 0.15;">
        <i class="fas fa-map-pin" style="color: var(--green-tree);"></i>
      </div>
      <div>
        <h1 class="section-title">Gestion des localisations</h1>
        <p class="section-subtitle">Gérez les communes, arrondissements et quartiers</p>
      </div>
    </div>

    <div class="flex gap-1 mb-6 rounded-lg p-1" style="background: var(--bg-page); width: fit-content;">
      <button @click="onTabChange('communes')" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'communes' ? 'card' : ''" :style="{ color: activeTab === 'communes' ? 'var(--text-primary)' : 'var(--text-secondary)' }">Communes</button>
      <button @click="onTabChange('arrondissements')" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'arrondissements' ? 'card' : ''" :style="{ color: activeTab === 'arrondissements' ? 'var(--text-primary)' : 'var(--text-secondary)' }">Arrondissements</button>
      <button @click="onTabChange('quartiers')" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" :class="activeTab === 'quartiers' ? 'card' : ''" :style="{ color: activeTab === 'quartiers' ? 'var(--text-primary)' : 'var(--text-secondary)' }">Quartiers</button>
    </div>

    <div v-if="loading" class="flex-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <template v-if="!loading">
      <div class="flex-between mb-4">
        <span style="color: var(--text-secondary); font-size: 0.875rem;">
          {{ activeTab === 'communes' ? communes.length : activeTab === 'arrondissements' ? arrondissements.length : quartiers.length }} élément(s)
        </span>
        <button @click="openCreate(activeTab)" class="btn-green btn-sm flex items-center gap-1.5">
          <i class="fas fa-plus"></i> Ajouter
        </button>
      </div>

      <div v-if="activeTab === 'communes'" class="table-wrap">
        <table class="w-full">
          <thead>
            <tr>
              <th class="table-header">ID</th>
              <th class="table-header">Nom</th>
              <th class="table-header"></th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color: var(--border);">
            <tr v-for="c in communes" :key="c.id">
              <td class="table-cell font-mono text-xs" style="color: var(--text-secondary);">{{ c.id }}</td>
              <td class="table-cell font-medium" style="color: var(--text-primary);">{{ c.nom }}</td>
              <td class="table-cell">
                <div class="flex gap-2">
                  <button @click="openEdit(c, 'communes')" class="btn-outline btn-sm flex items-center gap-1"><i class="fas fa-pen"></i> Modifier</button>
                  <button @click="confirmDelete(c.id, 'communes')" class="btn-danger btn-sm flex items-center gap-1"><i class="fas fa-trash"></i> Supprimer</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="activeTab === 'arrondissements'">
        <div class="mb-4">
          <label class="form-label">Commune</label>
          <select v-model="formCommuneId" @change="fetchArrondissements(formCommuneId)" class="form-select" style="max-width: 300px;">
            <option value="">Sélectionner une commune</option>
            <option v-for="c in communes" :key="c.id" :value="c.id">{{ c.nom }}</option>
          </select>
        </div>
        <div v-if="arrondissements.length" class="table-wrap">
          <table class="w-full">
            <thead>
              <tr>
                <th class="table-header">ID</th>
                <th class="table-header">Nom</th>
                <th class="table-header">Commune</th>
                <th class="table-header"></th>
              </tr>
            </thead>
            <tbody class="divide-y" style="border-color: var(--border);">
              <tr v-for="a in arrondissements" :key="a.id">
                <td class="table-cell font-mono text-xs" style="color: var(--text-secondary);">{{ a.id }}</td>
                <td class="table-cell font-medium" style="color: var(--text-primary);">{{ a.nom }}</td>
                <td class="table-cell">{{ getCommuneName(a.commune_id) }}</td>
                <td class="table-cell">
                  <div class="flex gap-2">
                    <button @click="openEdit(a, 'arrondissements')" class="btn-outline btn-sm flex items-center gap-1"><i class="fas fa-pen"></i> Modifier</button>
                    <button @click="confirmDelete(a.id, 'arrondissements')" class="btn-danger btn-sm flex items-center gap-1"><i class="fas fa-trash"></i> Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else-if="formCommuneId" class="text-center py-8" style="color: var(--text-secondary);">Aucun arrondissement pour cette commune.</p>
      </div>

      <div v-if="activeTab === 'quartiers'">
        <div class="mb-4">
          <label class="form-label">Arrondissement</label>
          <select v-model="formParentId" @change="fetchQuartiers(formParentId)" class="form-select" style="max-width: 300px;">
            <option value="">Sélectionner un arrondissement</option>
            <option v-for="a in arrondissements" :key="a.id" :value="a.id">{{ a.nom }} ({{ getCommuneName(a.commune_id) }})</option>
          </select>
        </div>
        <div v-if="quartiers.length" class="table-wrap">
          <table class="w-full">
            <thead>
              <tr>
                <th class="table-header">ID</th>
                <th class="table-header">Nom</th>
                <th class="table-header">Arrondissement</th>
                <th class="table-header"></th>
              </tr>
            </thead>
            <tbody class="divide-y" style="border-color: var(--border);">
              <tr v-for="q in quartiers" :key="q.id">
                <td class="table-cell font-mono text-xs" style="color: var(--text-secondary);">{{ q.id }}</td>
                <td class="table-cell font-medium" style="color: var(--text-primary);">{{ q.nom }}</td>
                <td class="table-cell">{{ getArrondissementName(q.arrondissement_id) }}</td>
                <td class="table-cell">
                  <div class="flex gap-2">
                    <button @click="openEdit(q, 'quartiers')" class="btn-outline btn-sm flex items-center gap-1"><i class="fas fa-pen"></i> Modifier</button>
                    <button @click="confirmDelete(q.id, 'quartiers')" class="btn-danger btn-sm flex items-center gap-1"><i class="fas fa-trash"></i> Supprimer</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else-if="formParentId" class="text-center py-8" style="color: var(--text-secondary);">Aucun quartier pour cet arrondissement.</p>
      </div>
    </template>

    <div v-if="showModal" class="fixed inset-0 flex-center z-50" style="background: rgba(0,0,0,0.3);" @click.self="showModal = false">
      <div class="card w-full" style="max-width: 480px; margin: 0 16px; padding: 24px;">
        <h3 class="section-title mb-4">{{ editingItem ? 'Modifier' : 'Ajouter' }} {{ modalType === 'communes' ? 'une commune' : modalType === 'arrondissements' ? 'un arrondissement' : 'un quartier' }}</h3>

        <div class="form-group">
          <label class="form-label">Nom</label>
          <input v-model="formNom" class="form-input w-full" placeholder="Nom" />
        </div>

        <div v-if="modalType === 'arrondissements'" class="form-group">
          <label class="form-label">Commune parente</label>
          <select v-model="formParentId" class="form-select w-full">
            <option value="">Sélectionner une commune</option>
            <option v-for="c in communes" :key="c.id" :value="c.id">{{ c.nom }}</option>
          </select>
        </div>

        <div v-if="modalType === 'quartiers'" class="form-group">
          <label class="form-label">Arrondissement parent</label>
          <select v-model="formParentId" class="form-select w-full">
            <option value="">Sélectionner un arrondissement</option>
            <option v-for="a in arrondissements" :key="a.id" :value="a.id">{{ a.nom }}</option>
          </select>
        </div>

        <div class="flex gap-3 justify-end mt-4">
          <button @click="showModal = false" class="btn-outline btn-sm">Annuler</button>
          <button @click="saveItem" class="btn-green btn-sm flex items-center gap-1.5">
            <i class="fas fa-plus"></i> {{ editingItem ? 'Enregistrer' : 'Créer' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showDeleteConfirm" class="fixed inset-0 flex-center z-50" style="background: rgba(0,0,0,0.3);" @click.self="showDeleteConfirm = false">
      <div class="card w-full" style="max-width: 400px; margin: 0 16px; padding: 24px;">
        <h3 class="section-title mb-2">Confirmer la suppression</h3>
        <p class="text-sm mb-4" style="color: var(--text-secondary);">Cette action est irréversible. Voulez-vous vraiment supprimer cet élément ?</p>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteConfirm = false" class="btn-outline btn-sm">Annuler</button>
          <button @click="deleteItem" class="btn-danger btn-sm flex items-center gap-1.5">
            <i class="fas fa-trash"></i> Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
