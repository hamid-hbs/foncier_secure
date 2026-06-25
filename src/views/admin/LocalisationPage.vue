<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import localisationApi from '@/api/localisation'
import adminApi from '@/api/admin'

const router = useRouter()
const activeTab = ref('communes')
const communes = ref([])
const arrondissements = ref([])
const quartiers = ref([])
const loading = ref(true)
const showModal = ref(false)
const modalType = ref('')
const form = ref({ nom: '', commune_id: '', arrondissement_id: '' })
const saving = ref(false)

onMounted(async () => {
  try {
    const c = await localisationApi.getCommunes()
    communes.value = c.data?.data || c.data || []
    arrondissements.value = communes.value.flatMap(c => c.arrondissements || [])
    quartiers.value = arrondissements.value.flatMap(a => a.quartiers || [])
  } catch (e) { console.error('Erreur chargement localisation:', e) }
  loading.value = false
})

function openModal(type) {
  modalType.value = type
  form.value = { nom: '', commune_id: '', arrondissement_id: '' }
  showModal.value = true
}

async function save() {
  saving.value = true
  try {
    if (modalType.value === 'commune') await adminApi.createCommune?.(form.value)
    else if (modalType.value === 'arrondissement') await adminApi.createArrondissement?.(form.value)
    else await adminApi.createQuartier?.(form.value)
    showModal.value = false
    const c = await localisationApi.getCommunes()
    communes.value = c.data?.data || c.data || []
    arrondissements.value = communes.value.flatMap(c => c.arrondissements || [])
    quartiers.value = arrondissements.value.flatMap(a => a.quartiers || [])
  } catch (e) { console.error('Erreur création localisation:', e) }
  saving.value = false
}
</script>

<template>
  <div class="page-wrap">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="page-title">Localisation</h1>
        <p class="page-subtitle">Gérez les communes, arrondissements et quartiers du Bénin</p>
      </div>
    </div>

    <div class="tabs mb-6">
      <button class="tab" :class="activeTab === 'communes' ? 'active' : ''" @click="activeTab = 'communes'">
        Communes ({{ communes.length }})
      </button>
      <button class="tab" :class="activeTab === 'arrondissements' ? 'active' : ''" @click="activeTab = 'arrondissements'">
        Arrondissements ({{ arrondissements.length }})
      </button>
      <button class="tab" :class="activeTab === 'quartiers' ? 'active' : ''" @click="activeTab = 'quartiers'">
        Quartiers ({{ quartiers.length }})
      </button>
    </div>

    <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
      <div v-for="i in 12" :key="i" class="skeleton h-12 rounded-xl"></div>
    </div>

    <!-- Communes -->
    <div v-else-if="activeTab === 'communes'">
      <div class="flex justify-end mb-4">
        <button @click="openModal('commune')" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Ajouter</button>
      </div>
      <div v-if="communes.length === 0" class="card"><div class="empty-state"><div class="empty-icon"><i class="fas fa-map-pin"></i></div><p class="empty-title">Aucune commune</p></div></div>
      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        <div v-for="c in communes" :key="c.id" class="flex items-center gap-3 px-4 py-3 bg-white border border-stone-100 rounded-xl shadow-xs">
          <div class="w-7 h-7 rounded-lg bg-brand-50 flex items-center justify-center text-brand shrink-0 text-xs"><i class="fas fa-map-pin"></i></div>
          <span class="font-semibold text-stone-900 text-sm truncate">{{ c.nom }}</span>
        </div>
      </div>
    </div>

    <!-- Arrondissements -->
    <div v-else-if="activeTab === 'arrondissements'">
      <div class="flex justify-end mb-4">
        <button @click="openModal('arrondissement')" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Ajouter</button>
      </div>
      <div v-if="arrondissements.length === 0" class="card"><div class="empty-state"><div class="empty-icon"><i class="fas fa-map-marker-alt"></i></div><p class="empty-title">Aucun arrondissement</p></div></div>
      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        <div v-for="a in arrondissements" :key="a.id" class="flex items-center gap-3 px-4 py-3 bg-white border border-stone-100 rounded-xl shadow-xs">
          <div class="w-7 h-7 rounded-lg bg-gold/10 flex items-center justify-center text-gold-dark shrink-0 text-xs"><i class="fas fa-location-dot"></i></div>
          <div class="min-w-0">
            <p class="font-semibold text-stone-900 text-sm truncate">{{ a.nom }}</p>
            <p class="text-xs text-stone-400 truncate">{{ a.commune?.nom }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quartiers -->
    <div v-else>
      <div class="flex justify-end mb-4">
        <button @click="openModal('quartier')" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Ajouter</button>
      </div>
      <div v-if="quartiers.length === 0" class="card"><div class="empty-state"><div class="empty-icon"><i class="fas fa-house"></i></div><p class="empty-title">Aucun quartier</p></div></div>
      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        <div v-for="q in quartiers" :key="q.id" class="flex items-center gap-3 px-4 py-3 bg-white border border-stone-100 rounded-xl shadow-xs">
          <div class="w-7 h-7 rounded-lg bg-success/10 flex items-center justify-center text-success shrink-0 text-xs"><i class="fas fa-house"></i></div>
          <div class="min-w-0">
            <p class="font-semibold text-stone-900 text-sm truncate">{{ q.nom }}</p>
            <p class="text-xs text-stone-400 truncate">{{ q.arrondissement?.nom }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <Transition name="scale">
      <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
        <div class="modal max-w-sm">
          <div class="modal-header">
            <h3 class="modal-title">Ajouter {{ modalType === 'commune' ? 'une commune' : modalType === 'arrondissement' ? 'un arrondissement' : 'un quartier' }}</h3>
            <button @click="showModal = false" class="btn btn-ghost btn-icon"><i class="fas fa-times"></i></button>
          </div>
          <form @submit.prevent="save">
            <div class="modal-body space-y-4">
              <div>
                <label class="form-label">Nom</label>
                <input v-model="form.nom" type="text" class="form-input" required placeholder="Nom de la localité" />
              </div>
              <div v-if="modalType !== 'commune'">
                <label class="form-label">Commune</label>
                <select v-model="form.commune_id" class="form-select" :required="modalType !== 'commune'">
                  <option value="">Sélectionner</option>
                  <option v-for="c in communes" :key="c.id" :value="c.id">{{ c.nom }}</option>
                </select>
              </div>
              <div v-if="modalType === 'quartier'">
                <label class="form-label">Arrondissement</label>
                <select v-model="form.arrondissement_id" class="form-select">
                  <option value="">Sélectionner</option>
                  <option v-for="a in arrondissements.filter(a => a.commune_id == form.commune_id)" :key="a.id" :value="a.id">{{ a.nom }}</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" @click="showModal = false" class="btn btn-ghost">Annuler</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <div v-if="saving" class="spinner spinner-sm border-white/30 border-t-white"></div>
                <i v-else class="fas fa-check"></i>
                {{ saving ? 'Enregistrement…' : 'Enregistrer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </div>
</template>
