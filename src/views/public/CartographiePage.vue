<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import cartographieApi from '@/api/cartographie'

const router = useRouter()
const data = ref(null)
const loading = ref(true)
const periode = ref('')
const mapEl = ref(null)

let map = null
let markersLayer = null

const parcelles = computed(() => data.value?.parcelles || [])
const terrainsSignales = computed(() => data.value?.terrains_signales || [])
const professionnels = computed(() => data.value?.professionnels || [])
const activiteRecente = computed(() => data.value?.activite_recente || [])

const markerStyles = {
  parcelle: { color: '#2d6a4f', icon: 'fa-map-pin' },
  verification: { color: '#e8a020', icon: 'fa-shield-halved' },
}

function isValidPoint(point) {
  const lat = Number(point?.lat)
  const lng = Number(point?.lng)
  return Number.isFinite(lat) && Number.isFinite(lng) && lat !== 0 && lng !== 0
}

function makeIcon(type) {
  const style = markerStyles[type] || markerStyles.parcelle
  return L.divIcon({
    className: '',
    html: `<div class="map-marker" style="--marker-color: ${style.color}"><i class="fas ${style.icon}"></i></div>`,
    iconSize: [34, 34],
    iconAnchor: [17, 32],
    popupAnchor: [0, -28],
  })
}

function popupForParcelle(p) {
  const location = [p.commune, p.arrondissement].filter(Boolean).join(', ') || 'Localisation inconnue'
  return `
    <div class="map-popup">
      <strong>${p.titre || 'Parcelle déclarée'}</strong>
      <span>${location}</span>
      <span>Statut : ${(p.statut || 'libre').replace('_', ' ')}</span>
      ${p.superficie ? `<span>Superficie : ${p.superficie} m²</span>` : ''}
      <button type="button" data-parcelle-id="${p.id}">Voir la parcelle</button>
    </div>
  `
}

function popupForVerification(point) {
  return `
    <div class="map-popup">
      <strong>${point.titre || 'Vérification à risque'}</strong>
      <span>Score : ${point.score ?? 'N/A'}</span>
    </div>
  `
}

function addMarker(point, type, popupHtml) {
  if (!isValidPoint(point)) return null
  return L.marker([Number(point.lat), Number(point.lng)], { icon: makeIcon(type) })
    .bindPopup(popupHtml)
    .addTo(markersLayer)
}

function renderMarkers() {
  if (!map || !markersLayer) return
  markersLayer.clearLayers()
  const bounds = []

  parcelles.value.forEach((p) => {
    const marker = addMarker(p, 'parcelle', popupForParcelle(p))
    if (marker) bounds.push(marker.getLatLng())
  })

  terrainsSignales.value.forEach((point) => {
    const marker = addMarker(point, 'verification', popupForVerification(point))
    if (marker) bounds.push(marker.getLatLng())
  })

  if (bounds.length) {
    map.fitBounds(L.latLngBounds(bounds), { padding: [28, 28], maxZoom: 14 })
  } else {
    map.setView([9.3077, 2.3158], 7)
  }
}

function initMap() {
  if (map || !mapEl.value) return
  map = L.map(mapEl.value, { zoomControl: true, scrollWheelZoom: true }).setView([9.3077, 2.3158], 7)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
  }).addTo(map)
  markersLayer = L.layerGroup().addTo(map)
  map.on('popupopen', (event) => {
    const button = event.popup.getElement()?.querySelector('[data-parcelle-id]')
    if (button) {
      button.addEventListener('click', () => {
        router.push(`/parcelles/${button.dataset.parcelleId}`)
      }, { once: true })
    }
  })
  renderMarkers()
}

async function fetchData() {
  loading.value = true
  try {
    const params = {}
    if (periode.value) params.periode = periode.value
    const res = await cartographieApi.getLayers(params)
    data.value = res.data
    await nextTick()
    initMap()
    renderMarkers()
    setTimeout(() => map?.invalidateSize(), 50)
  } catch (e) { console.error('Erreur chargement couches cartographie:', e)
    data.value = null
  }
  loading.value = false
}

watch(data, () => nextTick(() => { initMap(); renderMarkers() }))
onMounted(fetchData)
onUnmounted(() => { if (map) { map.remove(); map = null; markersLayer = null } })
</script>

<template>
  <div>
    <!-- Header -->
    <section class="py-14 relative overflow-hidden" style="background: var(--brand-dark);">
      <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute -top-16 left-1/4 w-80 h-80 rounded-full" style="background: radial-gradient(circle, #40916c, transparent);"></div>
      </div>
      <div class="max-w-7xl mx-auto px-5 sm:px-8 relative z-10">
        <div class="text-center mb-8">
          <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest text-gold bg-gold/15 mb-4">Cartographie</span>
          <h1 class="font-display font-extrabold text-4xl sm:text-5xl text-white mb-3">Carte foncière du Bénin</h1>
          <p class="text-white/60 text-lg">Parcelles et alertes géolocalisées en temps réel</p>
        </div>

        <!-- Filters -->
        <div class="max-w-sm mx-auto flex gap-3">
          <select v-model="periode" class="form-select flex-1" @change="fetchData">
            <option value="">Toutes les données</option>
            <option value="1mois">1 mois</option>
            <option value="6mois">6 mois</option>
            <option value="1an">1 an</option>
          </select>
          <button @click="fetchData" class="btn btn-outline text-white border-white/20 hover:bg-white/10 shrink-0">
            <i class="fas fa-rotate"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- Map section -->
    <section class="py-8 bg-stone-50">
      <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_280px] gap-6">
          <!-- Map -->
          <div class="card p-0 overflow-hidden shadow-xl">
            <div v-if="loading" class="flex items-center justify-center bg-stone-100" style="height: 540px;">
              <div class="text-center">
                <div class="spinner w-10 h-10 border-stone-200 border-t-brand mb-4"></div>
                <p class="text-sm text-stone-400 font-medium">Chargement de la carte…</p>
              </div>
            </div>
            <div v-show="!loading" ref="mapEl" style="height: 540px; width: 100%;"></div>
          </div>

          <!-- Legend & Stats -->
          <div class="space-y-4">
            <!-- Legend -->
            <div class="card">
              <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
                <i class="fas fa-circle-info text-brand text-sm"></i> Légende
              </h3>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[10px]" style="background: var(--brand);">
                      <i class="fas fa-map-pin"></i>
                    </div>
                    <span class="text-sm font-semibold text-stone-700">Parcelles</span>
                  </div>
                  <span class="font-display font-extrabold text-brand text-xl">{{ parcelles.length }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[10px]" style="background: var(--gold-dark);">
                      <i class="fas fa-shield-halved"></i>
                    </div>
                    <span class="text-sm font-semibold text-stone-700">Alertes vérification</span>
                  </div>
                  <span class="font-display font-extrabold text-gold-dark text-xl">{{ terrainsSignales.length }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-xl bg-stone-100 flex items-center justify-center text-stone-500 text-[10px]">
                      <i class="fas fa-user-tie"></i>
                    </div>
                    <span class="text-sm font-semibold text-stone-700">Professionnels</span>
                  </div>
                  <span class="font-display font-extrabold text-stone-700 text-xl">{{ professionnels.length }}</span>
                </div>
              </div>
            </div>

            <!-- Activité récente -->
            <div class="card flex-1">
              <h3 class="font-display font-bold text-stone-900 mb-4 flex items-center gap-2">
                <i class="fas fa-bolt text-gold text-sm"></i> Activité récente
              </h3>
              <div v-if="activiteRecente.length" class="space-y-3">
                <div v-for="item in activiteRecente.slice(0, 6)" :key="`${item.type}-${item.date}`"
                  class="flex items-start gap-3 py-2 border-b border-stone-50 last:border-0">
                  <div class="w-7 h-7 rounded-lg bg-brand-50 flex items-center justify-center text-brand shrink-0 text-xs mt-0.5">
                    <i :class="['fas', item.type === 'transaction' ? 'fa-file-signature' : item.type === 'verification' ? 'fa-shield-halved' : 'fa-map-pin']"></i>
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-stone-900 truncate">{{ item.titre || item.type }}</p>
                    <p class="text-xs text-stone-400 capitalize">{{ item.type }} · {{ item.statut }}</p>
                  </div>
                </div>
              </div>
              <p v-else class="text-sm text-stone-400 text-center py-4">Aucune activité récente</p>
            </div>

            <!-- No GPS notice -->
            <div v-if="!loading && parcelles.length === 0" class="alert alert-info">
              <i class="fas fa-info-circle shrink-0"></i>
              <span class="text-xs">Aucune parcelle avec coordonnées GPS enregistrées.</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
:deep(.map-marker) {
  width: 34px;
  height: 34px;
  border-radius: 999px;
  background: var(--marker-color);
  color: white;
  border: 3px solid white;
  box-shadow: 0 6px 18px rgba(0,0,0,0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
}

:deep(.map-popup) {
  min-width: 200px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-family: 'Inter', sans-serif;
}

:deep(.map-popup strong) {
  font-size: 14px;
  font-weight: 700;
  color: #1c1917;
}

:deep(.map-popup span) {
  font-size: 12px;
  color: #78716c;
}

:deep(.map-popup button) {
  margin-top: 8px;
  padding: 6px 12px;
  border-radius: 8px;
  background: #2d6a4f;
  color: white;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: background 0.15s;
}

:deep(.map-popup button:hover) {
  background: #40916c;
}
</style>
