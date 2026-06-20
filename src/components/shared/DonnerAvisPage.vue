<script setup>
import { ref, watch } from 'vue'
import professionnelApi from '@/api/professionnel'

const props = defineProps({
  visible: Boolean,
  professionalId: Number,
  professionalName: String,
})

const emit = defineEmits(['close', 'submitted'])

const rating = ref(0)
const hoverRating = ref(0)
const comment = ref('')
const submitting = ref(false)
const error = ref('')

watch(() => props.visible, (val) => {
  if (val) {
    rating.value = 0
    hoverRating.value = 0
    comment.value = ''
    error.value = ''
  }
})

function setRating(val) {
  rating.value = val
}

async function submit() {
  if (rating.value === 0) {
    error.value = 'Veuillez attribuer une note'
    return
  }
  error.value = ''
  submitting.value = true
  try {
    await professionnelApi.giveReview(props.professionalId, {
      note: rating.value,
      commentaire: comment.value,
    })
    emit('submitted')
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors de l'envoi de l'avis"
  } finally {
    submitting.value = false
  }
}

function close() {
  emit('close')
}
</script>

<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-overlay" @click.self="close">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">Donner mon avis</h3>
          <button @click="close" class="btn-icon btn-ghost">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <p class="text-sm mb-4" style="color: var(--text-secondary);">
            Vous évaluez <strong style="color: var(--text-primary);">{{ professionalName }}</strong>
          </p>

          <div v-if="error" class="p-3 rounded-lg text-sm mb-4" style="background: #FEE2E2; color: var(--danger); border: 1px solid #FECACA;">
            {{ error }}
          </div>

          <div class="form-group">
            <label class="form-label">Note</label>
            <div class="flex items-center gap-1">
              <span
                v-for="star in 5"
                :key="star"
                @click="setRating(star)"
                @mouseenter="hoverRating = star"
                @mouseleave="hoverRating = 0"
                class="cursor-pointer text-2xl transition-colors"
                :class="star <= (hoverRating || rating) ? 'text-[#D4A373]' : 'text-[#E5E7EB]'"
              >
                <i class="fas fa-star"></i>
              </span>
              <span class="text-sm ml-2" style="color: var(--text-secondary);">
                {{ rating ? rating + '/5' : 'Cliquez pour noter' }}
              </span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Commentaire (optionnel)</label>
            <textarea v-model="comment" class="form-textarea" rows="4" placeholder="Partagez votre expérience..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="close" class="btn-outline btn-sm">Annuler</button>
          <button @click="submit" class="btn-gold btn-sm" :disabled="submitting">
            <i class="fas fa-paper-plane"></i> {{ submitting ? 'Envoi...' : 'Envoyer mon avis' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
