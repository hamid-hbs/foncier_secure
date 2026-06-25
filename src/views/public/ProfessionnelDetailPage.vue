<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { goBack } from '@/utils/navigation'
import { useAuthStore } from '@/stores/auth'
import professionnelApi from '@/api/professionnel'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const professionnel = ref(null)
const loading = ref(true)
const isLoggedIn = computed(() => !!auth.user)

onMounted(async () => {
  try {
    const res = await professionnelApi.show(route.params.id)
    professionnel.value = res.data || null
  } catch (e) { console.error('Erreur chargement professionnel:', e) }
  loading.value = false
})

const isNotaire = computed(() => professionnel.value?.type === 'notaire' || professionnel.value?.role === 'notaire')
</script>

<template>
  <div>
    <div v-if="loading" class="max-w-3xl mx-auto px-5 py-16 space-y-4">
      <div class="skeleton h-32 rounded-2xl"></div>
      <div class="skeleton h-48 rounded-2xl"></div>
    </div>

    <div v-else-if="!professionnel" class="max-w-lg mx-auto px-5 py-16">
      <div class="card">
        <div class="empty-state">
          <div class="empty-icon"><i class="fas fa-users"></i></div>
          <p class="empty-title">Professionnel introuvable</p>
          <button @click="router.push('/professionnels')" class="btn btn-primary mt-4">Retour à l'annuaire</button>
        </div>
      </div>
    </div>

    <template v-else>
      <!-- Hero header -->
      <section class="py-16 relative overflow-hidden" style="background: var(--brand-dark);">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
          <div class="absolute -top-16 -right-16 w-72 h-72 rounded-full" style="background: radial-gradient(circle, #40916c, transparent);"></div>
        </div>
        <div class="max-w-4xl mx-auto px-5 sm:px-8 relative z-10">
          <button @click="goBack(router)" class="flex items-center gap-2 text-sm text-white/60 hover:text-white transition-colors mb-8">
            <i class="fas fa-arrow-left text-xs"></i> Retour à l'annuaire
          </button>
          <div class="flex flex-col sm:flex-row items-start gap-6">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center font-display font-extrabold text-3xl text-white shrink-0 shadow-xl"
              :style="{ background: isNotaire ? 'var(--brand)' : 'var(--gold-dark)' }">
              {{ (professionnel.user?.prenom || professionnel.prenom || 'P')[0] }}
            </div>
            <div>
              <div class="flex flex-wrap items-center gap-3 mb-2">
                <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-white">
                  {{ isNotaire ? 'Maître ' : '' }}{{ professionnel.user?.prenom || professionnel.prenom }} {{ professionnel.user?.nom || professionnel.nom }}
                </h1>
                <span class="badge" :class="isNotaire ? 'badge-purple' : 'badge-info'" style="font-size:0.7rem;">
                  {{ isNotaire ? 'Notaire' : 'Géomètre' }}
                </span>
              </div>
              <p v-if="professionnel.cabinet" class="text-white/70 text-lg mb-2">{{ professionnel.cabinet }}</p>
              <div class="flex flex-wrap gap-4 text-white/60 text-sm">
                <span v-if="professionnel.zone_intervention">
                  <i class="fas fa-location-dot mr-1.5"></i>{{ professionnel.zone_intervention }}
                </span>
                <span v-if="professionnel.telephone || professionnel.user?.telephone">
                  <i class="fas fa-phone mr-1.5"></i>{{ professionnel.telephone || professionnel.user?.telephone }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Content -->
      <section class="py-10 bg-stone-50">
        <div class="max-w-4xl mx-auto px-5 sm:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Bio & specialites -->
            <div class="lg:col-span-2 space-y-5">
              <div v-if="professionnel.biographie || professionnel.description" class="card">
                <h3 class="font-display font-bold text-stone-900 mb-3">À propos</h3>
                <p class="text-stone-600 leading-relaxed text-sm">{{ professionnel.biographie || professionnel.description }}</p>
              </div>

              <div v-if="professionnel.specialites?.length" class="card">
                <h3 class="font-display font-bold text-stone-900 mb-3">Spécialités</h3>
                <div class="flex flex-wrap gap-2">
                  <span v-for="s in professionnel.specialites" :key="s" class="badge badge-info">{{ s }}</span>
                </div>
              </div>

              <!-- Avis -->
              <div v-if="professionnel.avis?.length" class="card">
                <h3 class="font-display font-bold text-stone-900 mb-4">Avis clients</h3>
                <div class="space-y-4">
                  <div v-for="a in professionnel.avis" :key="a.id" class="border-b border-stone-100 pb-4 last:border-0 last:pb-0">
                    <div class="flex items-center gap-2 mb-2">
                      <div class="flex">
                        <i v-for="i in 5" :key="i" class="fas fa-star text-xs" :class="i <= a.note ? 'text-gold' : 'text-stone-200'"></i>
                      </div>
                      <span class="text-xs text-stone-400">{{ a.created_at ? new Date(a.created_at).toLocaleDateString('fr-FR') : '' }}</span>
                    </div>
                    <p v-if="a.commentaire" class="text-sm text-stone-600 italic">"{{ a.commentaire }}"</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact card -->
            <div class="space-y-4">
              <div class="card">
                <div class="flex items-center gap-2 mb-4">
                  <i class="fas fa-star text-gold"></i>
                  <span class="font-bold text-stone-900">{{ professionnel.note_moyenne || '—' }}/5</span>
                  <span class="text-stone-400 text-sm">({{ professionnel.avis?.length || 0 }} avis)</span>
                </div>
                <div class="space-y-3 mb-5">
                  <div v-if="professionnel.user?.email || professionnel.email" class="flex items-center gap-3 text-sm text-stone-600">
                    <i class="fas fa-envelope text-stone-400 w-4 text-center"></i>
                    <span class="truncate">{{ professionnel.user?.email || professionnel.email }}</span>
                  </div>
                  <div v-if="professionnel.telephone || professionnel.user?.telephone" class="flex items-center gap-3 text-sm text-stone-600">
                    <i class="fas fa-phone text-stone-400 w-4 text-center"></i>
                    <span>{{ professionnel.telephone || professionnel.user?.telephone }}</span>
                  </div>
                </div>
                <div v-if="isLoggedIn && isNotaire">
                  <router-link to="/citoyen/transactions" class="btn btn-primary btn-full">
                    <i class="fas fa-file-signature"></i> Consulter mes transactions
                  </router-link>
                </div>
                <div v-else-if="!isLoggedIn">
                  <router-link to="/auth/login" class="btn btn-primary btn-full">
                    <i class="fas fa-right-to-bracket"></i> Se connecter pour contacter
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>
