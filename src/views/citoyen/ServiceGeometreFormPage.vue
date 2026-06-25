<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import serviceGeometreApi from '@/api/serviceGeometre'
import professionnelApi from '@/api/professionnel'
import parcelleApi from '@/api/parcelle'
import { goBack } from '@/utils/navigation'

const router = useRouter()
const form = ref({ parcelle_id: '', geometre_id: '', type_service: 'bornage', description: '', adresse_parcelle: '' })
const mesParcelles = ref([])
const geometres = ref([])
const loading = ref(false)
const error = ref('')
const errors = ref({})
const success = ref(false)

onMounted(async () => {
  try {
    const [p, g] = await Promise.all([
      parcelleApi.list({ per_page: 50 }),
      professionnelApi.list({ role: 'geometre', per_page: 50 }),
    ])
    mesParcelles.value = (p.data?.data || p.data || []).filter(Boolean)
    geometres.value = (g.data?.data || g.data || []).filter(Boolean)
  } catch (e) { console.error('Erreur chargement données:', e) }
})

async function submit() {
  loading.value = true
  error.value = ''
  errors.value = {}
  try {
    const payload = {
      geometre_id: form.value.geometre_id,
      parcelle_id: form.value.parcelle_id || undefined,
      titre: form.value.type_service,
      description: [form.value.description, form.value.adresse_parcelle ? `Adresse : ${form.value.adresse_parcelle}` : ''].filter(Boolean).join('\n'),
    }
    await serviceGeometreApi.create(payload)
    success.value = true
    setTimeout(() => router.push('/citoyen/services-geometre'), 1500)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors || {}
      error.value = Object.values(errors.value).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || 'Erreur lors de la soumission'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page-wrap max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-8">
      <button @click="goBack(router)" class="btn btn-ghost btn-icon text-stone-500">
        <i class="fas fa-arrow-left"></i>
      </button>
      <div>
        <h1 class="page-title">Solliciter un géomètre</h1>
        <p class="page-subtitle">Demande d'intervention pour votre parcelle</p>
      </div>
    </div>

    <div v-if="success" class="card">
      <div class="empty-state py-12">
        <div class="empty-icon bg-success/10 text-success"><i class="fas fa-check-double"></i></div>
        <p class="empty-title">Demande envoyée !</p>
        <p class="empty-text">Votre demande a été soumise. Le géomètre vous contactera.</p>
      </div>
    </div>

    <form v-else @submit.prevent="submit" class="space-y-5">
      <div v-if="error" class="alert alert-danger">
        <i class="fas fa-triangle-exclamation shrink-0"></i>
        <span>{{ error }}</span>
      </div>

      <div class="card p-6 space-y-5">
        <div>
          <label class="form-label">Géomètre <span class="text-red-500">*</span></label>
          <select v-model="form.geometre_id" class="form-select" :class="errors.geometre_id ? 'form-input-error' : ''" required>
            <option value="">Sélectionner un géomètre</option>
            <option v-for="g in geometres" :key="g.user_id || g.id" :value="g.user_id || g.id">
              {{ g.user?.prenom || '' }} {{ g.user?.nom || g.cabinet || '#' + (g.user_id || g.id) }}
            </option>
          </select>
          <p v-if="errors.geometre_id" class="form-error">{{ errors.geometre_id[0] }}</p>
        </div>

        <div>
          <label class="form-label">Parcelle concernée <span class="text-red-500">*</span></label>
          <select v-model="form.parcelle_id" class="form-select" :class="errors.parcelle_id ? 'form-input-error' : ''" required>
            <option value="">Sélectionner une parcelle</option>
            <option v-for="p in mesParcelles" :key="p.id" :value="p.id">
              {{ p.titre || p.code || '#' + p.id }} — {{ p.commune?.nom || '' }}
            </option>
          </select>
          <p v-if="errors.parcelle_id" class="form-error">{{ errors.parcelle_id[0] }}</p>
        </div>

        <div>
          <label class="form-label">Type de service <span class="text-red-500">*</span></label>
          <select v-model="form.type_service" class="form-select" :class="errors.titre ? 'form-input-error' : ''">
            <option value="bornage">Bornage</option>
            <option value="leve_topographique">Levé topographique</option>
            <option value="division_parcellaire">Division parcellaire</option>
            <option value="certificat">Certificat de conformité</option>
            <option value="autre">Autre</option>
          </select>
          <p v-if="errors.titre" class="form-error">{{ errors.titre[0] }}</p>
        </div>

        <div>
          <label class="form-label">Adresse / localisation précise</label>
          <input v-model="form.adresse_parcelle" type="text" class="form-input" placeholder="Quartier, rue, points de repère…" />
        </div>

        <div>
          <label class="form-label">Description de la demande</label>
          <textarea v-model="form.description" rows="4" class="form-input resize-none" placeholder="Décrivez les travaux à réaliser…"></textarea>
        </div>
      </div>

      <div class="flex gap-3">
        <button type="submit" class="btn btn-primary btn-lg" :disabled="loading">
          <div v-if="loading" class="spinner spinner-sm border-white/30 border-t-white"></div>
          <i v-else class="fas fa-paper-plane"></i>
          {{ loading ? 'Envoi…' : 'Envoyer la demande' }}
        </button>
        <button type="button" @click="goBack(router)" class="btn btn-ghost">Annuler</button>
      </div>
    </form>
  </div>
</template>
