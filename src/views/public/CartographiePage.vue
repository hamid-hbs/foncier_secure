<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import cartographieApi from '@/api/cartographie'
import { goBack } from '@/utils/navigation'

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
  parcelle: { color: '#2D6A4F', icon: 'fa-map-pin' },

  verification: { color: '#D4A373', icon: 'fa-shield-halved' },
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
    html: `
      <div class="map-marker" style="--marker-color: ${style.color}">
        <i class="fas ${style.icon}"></i>
      </div>
    `,
    iconSize: [34, 34],
    iconAnchor: [17, 32],
    popupAnchor: [0, -28],
  })
}

function popupForParcelle(p) {
  const location = [p.commune, p.arrondissement].filter(Boolean).join(', ') || 'Localisation non renseignee'
  return `
    <div class="map-popup">
      <strong>${p.titre || 'Parcelle declaree'}</strong>
      <span>${location}</span>
      <span>Statut: ${p.statut || 'libre'}</span>
      ${p.superficie ? `<span>Superficie: ${p.superficie} m2</span>` : ''}
      <button type="button" data-parcelle-id="${p.id}">Voir la parcelle</button>
    </div>
  `
}

function popupForVerification(point) {
  return `
    <div class="map-popup">
      <strong>${point.titre || 'Verification a risque'}</strong>
      <span>Score: ${point.score ?? 'N/A'}</span>
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

  map = L.map(mapEl.value, {
    zoomControl: true,
    scrollWheelZoom: true,
  }).setView([9.3077, 2.3158], 7)

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
  } catch {
    data.value = null
  }
  loading.value = false
}

watch(data, () => {
  nextTick(() => {
    initMap()
    renderMarkers()
  })
})

onMounted(fetchData)
onUnmounted(() => {
  if (map) {
    map.remove()
    map = null
    markersLayer = null
  }
})
</script>

<template>
  <div class="page-container">
    <button @click="goBack(router)" class="flex items-center gap-2 text-sm mb-4" style="color: var(--green-tree);">
      <i class="fas fa-arrow-left"></i> Retour
    </button>

    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--bg-page);">
        <i class="fas fa-map" style="color: var(--green-tree);"></i>
      </div>
      <div>
        <h1 class="section-title">Cartographie</h1>
        <p class="section-subtitle">Parcelles declarees et alertes foncieres geolocalisees</p>
      </div>
    </div>

    <div class="card p-4 mb-6">
      <div class="flex flex-wrap items-center gap-4">
        <label class="form-label mb-0">Periode</label>
        <select v-model="periode" class="form-select max-w-[220px]" @change="fetchData">
          <option value="">Toutes les donnees</option>
          <option value="1mois">1 mois</option>
          <option value="6mois">6 mois</option>
          <option value="1an">1 an</option>
        </select>
        <button class="btn-green btn-sm" @click="fetchData">
          <i class="fas fa-rotate"></i> Actualiser
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6">
      <section class="card p-0 overflow-hidden">
        <div v-if="loading" class="flex-center" style="height: 560px;">
          <div class="w-8 h-8 border-2 rounded-full animate-spin" style="border-color: var(--green-tree); border-top-color: transparent;"></div>
        </div>
        <div v-show="!loading" ref="mapEl" class="cartographie-map"></div>
      </section>

      <aside class="space-y-4">
        <div class="card-sm">
          <div class="flex items-center gap-3">
            <span class="legend-dot" style="background: var(--green-tree);"></span>
            <div>
              <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ parcelles.length }}</p>
              <p class="text-sm" style="color: var(--text-secondary);">Parcelles declarees</p>
            </div>
          </div>
        </div>
        <div class="card-sm">
          <div class="flex items-center gap-3">
            <span class="legend-dot" style="background: var(--gold);"></span>
            <div>
              <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ terrainsSignales.length }}</p>
              <p class="text-sm" style="color: var(--text-secondary);">Verifications a risque</p>
            </div>
          </div>
        </div>
        <div class="card-sm">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: #F0F7F4;">
              <i class="fas fa-user-tie" style="color: var(--green-tree);"></i>
            </div>
            <div>
              <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ professionnels.length }}</p>
              <p class="text-sm" style="color: var(--text-secondary);">Professionnels actifs</p>
            </div>
          </div>
        </div>
        <div class="card-sm">
          <h2 class="text-sm font-semibold mb-3" style="color: var(--text-primary);">Activite recente</h2>
          <div v-if="activiteRecente.length" class="space-y-3">
            <div v-for="item in activiteRecente.slice(0, 5)" :key="`${item.type}-${item.date}`" class="text-sm">
              <p class="font-medium truncate" style="color: var(--text-primary);">{{ item.titre || item.type }}</p>
              <p class="text-xs capitalize" style="color: var(--text-secondary);">{{ item.type }} - {{ item.statut }}</p>
            </div>
          </div>
          <p v-else class="text-sm" style="color: var(--text-secondary);">Aucune activite recente.</p>
        </div>
      </aside>
    </div>

    <div v-if="!loading && parcelles.length === 0" class="card text-center py-8 mt-6">
      <i class="fas fa-location-dot mb-3" style="color: #D1D5DB; font-size: 2.5rem;"></i>
      <p style="color: var(--text-secondary);">Aucune parcelle avec coordonnees GPS pour le moment.</p>
    </div>
  </div>
</template>

<style scoped>
.cartographie-map {
  height: 560px;
  width: 100%;
  background: #e5e7eb;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 999px;
  box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.05);
}

:deep(.map-marker) {
  width: 34px;
  height: 34px;
  border-radius: 999px;
  background: var(--marker-color);
  color: white;
  border: 3px solid white;
  box-shadow: 0 8px 18px rgba(0, 0, 0, 0.22);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

:deep(.map-popup) {
  min-width: 190px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  color: var(--text-primary);
}

:deep(.map-popup strong) {
  font-size: 14px;
}

:deep(.map-popup span) {
  font-size: 12px;
  color: var(--text-secondary);
}

:deep(.map-popup button) {
  margin-top: 6px;
  padding: 6px 10px;
  border-radius: 6px;
  background: var(--green-tree);
  color: white;
  font-size: 12px;
  font-weight: 600;
}
</style>
