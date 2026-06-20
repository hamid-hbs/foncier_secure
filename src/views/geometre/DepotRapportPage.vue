<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { goBack } from '@/utils/navigation'
import verificationApi from '@/api/verification'

const route = useRoute()
const router = useRouter()
const verification = ref(null)
const loading = ref(true)
const fichier = ref(null)
const avis = ref('favorable')
const commentaire = ref('')
const submitting = ref(false)

onMounted(async () => {
  try {
    const res = await verificationApi.show(route.params.id)
    verification.value = res.data?.data || res.data
  } catch { /* ignore */ }
  loading.value = false
})

function onFileChange(e) {
  fichier.value = e.target.files[0] || null
}

async function deposerRapport() {
  if (!fichier.value) {
    alert('Veuillez sélectionner un fichier.')
    return
  }
  submitting.value = true
  try {
    const formData = new FormData()
    formData.append('fichier', fichier.value)
    formData.append('avis', avis.value)
    if (commentaire.value.trim()) formData.append('commentaire', commentaire.value.trim())
    await verificationApi.rapportGeometre(route.params.id, formData)
    router.push('/geometre/missions')
  } catch { alert('Erreur lors du dépôt du rapport') }
  submitting.value = false
}
</script>

<template>
  <div class="page-container" style="max-width: 700px;">
    <div v-if="loading" class="flex-center py-16">
      <div class="spinner"></div>
    </div>

    <div v-else-if="!verification" class="card text-center py-12">
      <i class="fas fa-file-lines" style="font-size: 48px; color: var(--border); margin-bottom: 16px;"></i>
      <p style="color: var(--text-secondary);">Mission introuvable.</p>
    </div>

    <template v-if="verification">
      <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
        <i class="fas fa-arrow-left"></i> Retour
      </button>
      <div class="flex items-center gap-3 mb-8">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--bg-page); color: var(--green-tree);">
          <i class="fas fa-file-lines"></i>
        </div>
        <div>
          <h1 class="section-title">Déposer mon rapport</h1>
          <p class="section-subtitle">Mission : {{ verification.titre || '#' + verification.id }}</p>
        </div>
      </div>

      <div class="card mb-6">
        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">Informations de la mission</h3>
        <dl class="divide-y text-sm" style="border-color: var(--border);">
          <div class="flex justify-between py-3">
            <dt style="color: var(--text-secondary);">Titre</dt>
            <dd class="font-medium" style="color: var(--text-primary);">{{ verification.titre || '-' }}</dd>
          </div>
          <div class="flex justify-between py-3">
            <dt style="color: var(--text-secondary);">Statut</dt>
            <dd><span class="badge" :class="'badge-' + (verification.statut || 'soumis')">{{ verification.statut }}</span></dd>
          </div>
          <div v-if="verification.parcelle" class="flex justify-between py-3">
            <dt style="color: var(--text-secondary);">Parcelle</dt>
            <dd class="font-medium" style="color: var(--text-primary);">{{ verification.parcelle.code || verification.parcelle.nom || '#' + verification.parcelle.id }}</dd>
          </div>
          <div v-if="verification.parcelle?.commune" class="flex justify-between py-3">
            <dt style="color: var(--text-secondary);">Commune</dt>
            <dd class="font-medium" style="color: var(--text-primary);">{{ verification.parcelle.commune }}</dd>
          </div>
        </dl>
      </div>

      <div class="card">
        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">Rapport géomètre</h3>

        <div class="form-group">
          <label class="form-label">Fichier du rapport <span style="color: var(--danger);">*</span></label>
          <div class="relative">
            <input type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png" @change="onFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
            <div class="flex items-center gap-3 p-4 rounded-lg" style="border: 2px dashed var(--border); background: var(--bg-page);">
              <i class="fas fa-upload" style="color: var(--green-tree);"></i>
              <span class="text-sm" :style="{ color: fichier ? 'var(--text-primary)' : 'var(--text-secondary)', fontWeight: fichier ? '600' : '400' }">
                {{ fichier ? fichier.name : 'Cliquez pour sélectionner un fichier (PDF, JPG, PNG)' }}
              </span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Avis <span style="color: var(--danger);">*</span></label>
          <select v-model="avis" class="form-select">
            <option value="favorable">Favorable</option>
            <option value="defavorable">Défavorable</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Commentaire</label>
          <textarea v-model="commentaire" class="form-textarea" rows="4" placeholder="Ajoutez vos observations..."></textarea>
        </div>

        <div class="flex gap-3 justify-end">
          <button @click="router.push('/geometre/missions')" class="btn-outline btn-sm">Annuler</button>
          <button @click="deposerRapport" :disabled="submitting || !fichier" class="btn-gold btn-sm flex items-center gap-1.5">
            <i class="fas fa-upload"></i> {{ submitting ? 'Dépôt en cours...' : 'Déposer mon rapport' }}
          </button>
        </div>
      </div>
    </template>
  </div>
</template>
