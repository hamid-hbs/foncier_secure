import api from './axios'

export default {
  getCommunes: () => api.get('/localisation/communes'),
  getArrondissements: (communeId) => api.get(`/localisation/arrondissements/${communeId}`),
  getQuartiers: (arrondissementId) => api.get(`/localisation/quartiers/${arrondissementId}`),
}
