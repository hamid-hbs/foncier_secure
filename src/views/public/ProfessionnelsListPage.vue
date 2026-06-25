<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import professionnelApi from '@/api/professionnel'
import localisationApi from '@/api/localisation'

const router = useRouter()
const professionnels = ref([])
const communes = ref([])
const loading = ref(true)
const typeFilter = ref('')
const communeFilter = ref('')

const types = [
  { value: '', label: 'Tous' },
  { value: 'notaire', label: 'Notaires' },
  { value: 'geometre', label: 'Géomètres' },
]

async function fetchPros() {
  loading.value = true
  try {
    const params = {}
    if (typeFilter.value) params.type = typeFilter.value
    if (communeFilter.value) params.commune_id = communeFilter.value
    const res = await professionnelApi.list(params)
    professionnels.value = (res.data.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement professionnels:', e) }
  loading.value = false
}

onMounted(async () => {
  try {
    const c = await localisationApi.getCommunes()
    communes.value = c.data || []
  } catch (e) { console.error('Erreur chargement communes:', e) }
  fetchPros()
})

watch([typeFilter, communeFilter], () => fetchPros())
</script>

<template>
  <div>
    <!-- Header -->
    <section class="py-16 relative overflow-hidden" style="background: var(--brand-dark);">
      <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute -bottom-16 left-1/4 w-72 h-72 rounded-full" style="background: radial-gradient(circle, var(--gold), transparent);"></div>
      </div>
      <div class="max-w-7xl mx-auto px-5 sm:px-8 relative z-10">
        <div class="text-center mb-8">
          <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest text-gold bg-gold/15 mb-4">Annuaire</span>
          <h1 class="font-display font-extrabold text-4xl sm:text-5xl text-white mb-3">Professionnels partenaires</h1>
          <p class="text-white/60 text-lg">Notaires et géomètres certifiés pour accompagner vos démarches foncières</p>
        </div>

        <div class="max-w-xl mx-auto flex flex-col sm:flex-row gap-3">
          <div class="flex gap-2 bg-white/8 backdrop-blur rounded-xl p-1.5 border border-white/10">
            <button v-for="t in types" :key="t.value" @click="typeFilter = t.value"
              class="flex-1 px-4 py-2 rounded-lg text-sm font-bold transition-all"
              :class="typeFilter === t.value ? 'bg-white text-brand shadow-sm' : 'text-white/60 hover:text-white'">
              {{ t.label }}
            </button>
          </div>
          <select v-model="communeFilter" class="form-select">
            <option value="">Toutes communes</option>
            <option v-for="c in communes" :key="c.id" :value="c.id">{{ c.nom }}</option>
          </select>
        </div>
      </div>
    </section>

    <!-- Grid -->
    <section class="py-12 bg-stone-50">
      <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <div v-for="i in 6" :key="i" class="skeleton h-56 rounded-2xl"></div>
        </div>

        <div v-else-if="professionnels.length === 0" class="card max-w-lg mx-auto">
          <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-users"></i></div>
            <p class="empty-title">Aucun professionnel trouvé</p>
            <p class="empty-text">Essayez d'autres filtres pour trouver un professionnel partenaire.</p>
          </div>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <div
            v-for="p in professionnels" :key="p.id"
            @click="router.push(`/professionnels/${p.id}`)"
            class="bg-white rounded-2xl border border-stone-100 p-6 cursor-pointer hover:border-brand-100 hover:shadow-lg hover:-translate-y-1 transition-all group"
          >
            <div class="flex items-center gap-4 mb-4">
              <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-display font-extrabold text-xl text-white shrink-0"
                :style="{ background: p.type === 'notaire' ? 'var(--brand)' : 'var(--gold-dark)' }">
                {{ (p.user?.prenom || p.prenom || 'P')[0] }}
              </div>
              <div>
                <p class="font-display font-bold text-stone-900 group-hover:text-brand transition-colors">
                  {{ p.type === 'notaire' ? 'Maître ' : '' }}{{ p.user?.prenom || p.prenom }} {{ p.user?.nom || p.nom }}
                </p>
                <span class="badge" :class="p.type === 'notaire' ? 'badge-purple' : 'badge-info'" style="font-size: 0.7rem;">
                  {{ p.type === 'notaire' ? 'Notaire' : 'Géomètre' }}
                </span>
              </div>
            </div>

            <div class="space-y-2 text-sm text-stone-500">
              <div v-if="p.cabinet" class="flex items-center gap-2">
                <i class="fas fa-building w-4 text-center text-stone-300 text-xs"></i>
                <span class="truncate">{{ p.cabinet }}</span>
              </div>
              <div v-if="p.zone_intervention || p.commune?.nom" class="flex items-center gap-2">
                <i class="fas fa-location-dot w-4 text-center text-stone-300 text-xs"></i>
                <span class="truncate">{{ p.zone_intervention || p.commune?.nom }}</span>
              </div>
              <div v-if="p.telephone || p.user?.telephone" class="flex items-center gap-2">
                <i class="fas fa-phone w-4 text-center text-stone-300 text-xs"></i>
                <span>{{ p.telephone || p.user?.telephone }}</span>
              </div>
            </div>

            <div class="mt-4 pt-4 border-t border-stone-100 flex items-center justify-between">
              <div class="flex items-center gap-1">
                <i class="fas fa-star text-gold text-xs"></i>
                <span class="text-xs font-bold text-stone-700">{{ p.note_moyenne || '—' }}</span>
              </div>
              <span class="text-xs font-bold text-brand opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                Voir profil <i class="fas fa-arrow-right text-[10px]"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
