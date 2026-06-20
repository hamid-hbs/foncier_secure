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
const avis = ref([])
const loading = ref(true)
const showModal = ref(false)
const reviewNote = ref(5)
const reviewComment = ref('')
const reviewLoading = ref(false)
const reviewError = ref('')

const averageRating = computed(() => {
  if (!avis.value.length) return 0
  const sum = avis.value.reduce((a, r) => a + (r.note || 0), 0)
  return (sum / avis.value.length).toFixed(1)
})

const fullStars = computed(() => Math.floor(Number(averageRating.value)))
const hasHalfStar = computed(() => Number(averageRating.value) - fullStars.value >= 0.5)

const hoveredStar = ref(0)

const typeLabels = { notaire: 'Notaire', geometre: 'Géomètre', avocat: 'Avocat', expert_foncier: 'Expert foncier' }

async function fetchProfessionnel() {
  loading.value = true
  try {
    const res = await professionnelApi.show(route.params.id)
    professionnel.value = res.data
    avis.value = (res.data.avis || []).filter(Boolean)
  } catch {}
  loading.value = false
}

async function submitReview() {
  reviewError.value = ''
  if (!reviewComment.value.trim()) {
    reviewError.value = 'Veuillez écrire un commentaire'
    return
  }
  reviewLoading.value = true
  try {
    const res = await professionnelApi.giveReview(route.params.id, { note: reviewNote.value, commentaire: reviewComment.value })
    if (res.data) {
      avis.value.unshift(res.data)
    }
    showModal.value = false
    reviewNote.value = 5
    reviewComment.value = ''
    hoveredStar.value = 0
  } catch (e) {
    if (e.response?.status === 422) {
      const errors = e.response.data?.errors
      if (errors) {
        reviewError.value = Object.values(errors).flat().join(', ')
      } else {
        reviewError.value = e.response.data?.message || 'Erreur de validation'
      }
    } else {
      reviewError.value = e.response?.data?.message || 'Erreur lors de l\'envoi de l\'avis'
    }
  } finally {
    reviewLoading.value = false
  }
}

function setReviewNote(n) {
  reviewNote.value = n
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' })
}

onMounted(fetchProfessionnel)
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div v-if="loading" class="text-center py-16">
      <div class="w-8 h-8 border-2 rounded-full animate-spin mx-auto" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
    </div>

    <template v-else-if="professionnel">
      <div class="card mb-6">
        <div class="flex items-start gap-5">
          <div class="w-16 h-16 rounded-xl flex items-center justify-center text-white font-bold text-xl flex-shrink-0" style="background: var(--green-tree);">
            {{ ((professionnel.user?.prenom || professionnel.prenom || '?')[0]) }}{{ ((professionnel.user?.nom || professionnel.nom || '?')[0]) }}
          </div>
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold" style="color: var(--text-primary);">{{ professionnel.user?.prenom || professionnel.prenom }} {{ professionnel.user?.nom || professionnel.nom }}</h1>
            <div class="flex flex-wrap items-center gap-3 mt-2">
              <span class="badge" style="background: #F0F7F4; color: var(--green-tree);">{{ typeLabels[professionnel.type] || professionnel.type }}</span>
              <div v-if="avis.length" class="flex items-center gap-1">
                <template v-for="i in 5" :key="i">
                  <i v-if="i <= fullStars || (i === fullStars + 1 && hasHalfStar)" class="fas fa-star" style="color: var(--gold);"></i>
                  <i v-else class="far fa-star" style="color: #D1D5DB;"></i>
                </template>
                <span class="text-sm font-medium ml-1" style="color: var(--text-primary);">{{ averageRating }}</span>
                <span class="text-sm ml-1" style="color: var(--text-secondary);">({{ avis.length }} avis)</span>
              </div>
            </div>
          </div>
        </div>

        <hr class="my-4" style="border-color: var(--border);" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-if="professionnel.cabinet" class="flex items-start gap-3">
            <i class="fas fa-building mt-0.5" style="color: var(--green-tree);"></i>
            <div>
              <p class="text-xs font-medium" style="color: var(--text-secondary);">Cabinet</p>
              <p class="text-sm" style="color: var(--text-primary);">{{ professionnel.cabinet }}</p>
            </div>
          </div>
          <div v-if="professionnel.zone_intervention" class="flex items-start gap-3">
            <i class="fas fa-map-pin mt-0.5" style="color: var(--green-tree);"></i>
            <div>
              <p class="text-xs font-medium" style="color: var(--text-secondary);">Zone d'intervention</p>
              <p class="text-sm" style="color: var(--text-primary);">{{ professionnel.zone_intervention }}</p>
            </div>
          </div>
          <div v-if="professionnel.specialites && professionnel.specialites.length" class="md:col-span-2">
            <div class="flex items-start gap-3">
              <i class="fas fa-star mt-0.5" style="color: var(--gold);"></i>
              <div>
                <p class="text-xs font-medium" style="color: var(--text-secondary);">Spécialités</p>
                <div class="flex flex-wrap gap-1.5 mt-1">
                  <span v-for="s in professionnel.specialites" :key="s" class="badge" style="background: #F0F7F4; color: var(--green-tree);">{{ s }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-semibold" style="color: var(--text-primary);">Avis clients</h2>
          <button v-if="auth.isAuthenticated" class="btn-gold btn-sm" @click="showModal = true">
            <i class="fas fa-star"></i> Donner un avis
          </button>
        </div>

        <div v-if="avis.length === 0" class="text-center py-8">
          <i class="far fa-star mb-2" style="color: #D1D5DB; font-size: 2.5rem;"></i>
          <p style="color: var(--text-secondary);">Aucun avis pour le moment.</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="r in avis" :key="r.id" class="p-4 rounded-lg" style="background: var(--bg-page);">
            <div class="flex items-center gap-1 mb-2">
              <template v-for="i in 5" :key="i">
                <i v-if="i <= r.note" class="fas fa-star" style="color: var(--gold);"></i>
                <i v-else class="far fa-star" style="color: #D1D5DB;"></i>
              </template>
              <span class="text-xs ml-2" style="color: var(--text-secondary);">{{ formatDate(r.created_at) }}</span>
            </div>
            <p v-if="r.auteur" class="text-xs font-medium" style="color: var(--text-secondary);">{{ r.auteur.prenom || r.auteur.nom || 'Anonyme' }}</p>
            <p v-if="r.commentaire" class="text-sm mt-1" style="color: var(--text-primary);">{{ r.commentaire }}</p>
          </div>
        </div>
      </div>

      <Teleport to="body">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5);" @click.self="showModal = false">
          <div class="card w-full max-w-md">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Donner un avis</h3>
              <button @click="showModal = false" class="p-1 rounded-lg transition-colors" style="color: var(--text-secondary);">
                <i class="fas fa-times"></i>
              </button>
            </div>

            <div v-if="reviewError" class="p-3 rounded-lg text-sm mb-4" style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">{{ reviewError }}</div>

            <form @submit.prevent="submitReview">
              <div class="mb-5">
                <label class="form-label">Note</label>
                <div class="flex items-center gap-1">
                  <button v-for="i in 5" :key="i" type="button" @click="setReviewNote(i)" @mouseenter="hoveredStar = i" @mouseleave="hoveredStar = 0" class="p-0.5 transition-transform hover:scale-110" style="background: none; border: none;">
                    <i v-if="i <= (hoveredStar || reviewNote)" class="fas fa-star" style="color: var(--gold); font-size: 1.75rem;"></i>
                    <i v-else class="far fa-star" style="color: #D1D5DB; font-size: 1.75rem;"></i>
                  </button>
                </div>
              </div>
              <div class="mb-5">
                <label class="form-label">Commentaire</label>
                <textarea v-model="reviewComment" class="form-textarea" rows="3" placeholder="Partagez votre expérience..." required></textarea>
              </div>
              <button type="submit" class="btn-green w-full" :disabled="reviewLoading">
                {{ reviewLoading ? 'Envoi...' : 'Publier l\'avis' }}
              </button>
            </form>
          </div>
        </div>
      </Teleport>
    </template>

    <div v-else class="card text-center py-12">
      <i class="fas fa-user mb-3" style="color: #D1D5DB; font-size: 3rem;"></i>
      <p style="color: var(--text-secondary);">Professionnel introuvable.</p>
    </div>
  </div>
</template>
