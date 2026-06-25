import api from './axios'

export default {
  list: (params) => api.get('/dossiers-transaction', { params }),
  show: (id) => api.get(`/dossiers-transaction/${id}`),
  create: (data) => api.post('/dossiers-transaction', data),
  sendMessage: (id, data) => api.post(`/dossiers-transaction/${id}/messages`, data),
  messages: (id) => api.get(`/dossiers-transaction/${id}/messages`),
  addDocument: (id, data) => api.post(`/dossiers-transaction/${id}/documents`, data),
  documents: (id) => api.get(`/dossiers-transaction/${id}/documents`),
  validerPartie: (id) => api.post(`/dossiers-transaction/${id}/valider`),
  suspendre: (id) => api.post(`/dossiers-transaction/${id}/suspendre`),
  reouvrir: (id) => api.post(`/dossiers-transaction/${id}/reouvrir`),
  cloturer: (id) => api.post(`/dossiers-transaction/${id}/cloturer`),
}
