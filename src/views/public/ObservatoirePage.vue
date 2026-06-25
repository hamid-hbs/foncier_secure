<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import observatoireApi from '@/api/observatoire'

const router = useRouter()
const stats = ref(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await observatoireApi.getStats()
    stats.value = res.data || null
  } catch (e) { console.error('Erreur chargement stats:', e) }
  loading.value = false
})

const statCards = computed(() => {
  if (!stats.value) return []
  return [
    { label: 'Parcelles totales', value: stats.value.total_parcelles || 0, icon: 'fa-map', color: 'bg-brand/10 text-brand' },
    { label: 'Parcelles libres', value: stats.value.parcelles_libres || 0, icon: 'fa-circle-check', color: 'bg-success/10 text-success' },
    { label: 'Transactions réalisées', value: stats.value.total_transactions || 0, icon: 'fa-arrows-left-right', color: 'bg-gold/10 text-gold-dark' },
    { label: 'Vérifications effectuées', value: stats.value.total_verifications || 0, icon: 'fa-shield-halved', color: 'bg-info-100 text-sky-600', colorBg: '#e0f2fe' },
    { label: 'Professionnels actifs', value: stats.value.total_professionnels || 0, icon: 'fa-users', color: 'bg-purple-100 text-purple-600' },
    { label: 'Utilisateurs inscrits', value: stats.value.total_utilisateurs || 0, icon: 'fa-person', color: 'bg-stone-100 text-stone-500' },
  ]
})
</script>

<template>
  <div>
    <!-- Hero -->
    <section class="relative py-20 overflow-hidden" style="background: var(--brand-dark);">
      <div class="absolute inset-0 pointer-events-none opacity-10">
        <div class="absolute top-0 left-1/4 w-96 h-96 rounded-full" style="background: radial-gradient(circle, #40916c, transparent);"></div>
      </div>
      <div class="max-w-7xl mx-auto px-5 sm:px-8 text-center relative z-10">
        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest text-gold bg-gold/15 mb-5">Observatoire</span>
        <h1 class="font-display font-extrabold text-4xl sm:text-5xl text-white mb-4">Le marché foncier au Bénin</h1>
        <p class="text-white/60 text-lg max-w-xl mx-auto">Données en temps réel sur le patrimoine foncier sécurisé par FoncierSecure.</p>
      </div>
    </section>

    <!-- Stats -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-3 gap-5">
          <div v-for="i in 6" :key="i" class="skeleton h-32 rounded-2xl"></div>
        </div>
        <div v-else-if="!stats" class="text-center py-16 text-stone-400">
          <i class="fas fa-chart-line text-4xl mb-4 block opacity-30"></i>
          <p class="font-medium">Données non disponibles</p>
        </div>
        <div v-else>
          <div class="grid grid-cols-2 lg:grid-cols-3 gap-5 mb-16">
            <div v-for="s in statCards" :key="s.label" class="card hover:shadow-md transition-shadow">
              <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-lg" :class="s.color">
                  <i :class="['fas', s.icon]"></i>
                </div>
              </div>
              <p class="font-display font-extrabold text-3xl text-stone-900 mb-1">{{ Number(s.value).toLocaleString('fr-FR') }}</p>
              <p class="text-sm text-stone-500 font-medium">{{ s.label }}</p>
            </div>
          </div>

          <!-- Répartition par statut -->
          <div v-if="stats.parcelles_par_statut" class="mb-12">
            <h2 class="font-display font-bold text-xl text-stone-900 mb-6">Répartition par statut</h2>
            <div class="card">
              <div class="space-y-4">
                <div v-for="(count, statut) in stats.parcelles_par_statut" :key="statut">
                  <div class="flex items-center justify-between mb-1.5">
                    <span class="text-sm font-semibold text-stone-700 capitalize">{{ statut.replace('_', ' ') }}</span>
                    <span class="text-sm font-bold text-stone-900">{{ Number(count).toLocaleString('fr-FR') }}</span>
                  </div>
                  <div class="w-full h-2 bg-stone-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700" style="background: var(--brand);"
                      :style="{ width: stats.total_parcelles > 0 ? (count / stats.total_parcelles * 100) + '%' : '0%' }"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- By commune -->
          <div v-if="stats.parcelles_par_commune">
            <h2 class="font-display font-bold text-xl text-stone-900 mb-6">Top communes</h2>
            <div class="card p-0 overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead>
                    <tr class="bg-stone-50 border-b border-stone-100">
                      <th class="table-header text-left">Commune</th>
                      <th class="table-header text-right">Parcelles</th>
                      <th class="table-header text-right">Part</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(count, commune) in stats.parcelles_par_commune" :key="commune" class="table-row border-b border-stone-50 last:border-0">
                      <td class="table-cell font-semibold text-stone-900">{{ commune }}</td>
                      <td class="table-cell text-right font-bold text-stone-900">{{ Number(count).toLocaleString('fr-FR') }}</td>
                      <td class="table-cell text-right text-stone-500">{{ stats.total_parcelles > 0 ? ((count / stats.total_parcelles) * 100).toFixed(1) : 0 }}%</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
