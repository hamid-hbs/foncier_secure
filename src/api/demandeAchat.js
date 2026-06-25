import api from './axios'

export default {
  list: (params) => api.get('/demandes-achat', { params }),
  show: (id) => api.get(`/demandes-achat/${id}`),
  create: (data) => api.post('/demandes-achat', data),
  solliciterNotaire: (id) => api.post(`/demandes-achat/${id}/solliciter-notaire`),
  uploadDocument: (id, data) => api.post(`/demandes-achat/${id}/documents`, data),
  messages: (id) => api.get(`/demandes-achat/${id}/messages`),
  envoyerMessage: (id, data) => api.post(`/demandes-achat/${id}/messages`, data),
  documents: (id) => api.get(`/demandes-achat/${id}/documents`),
  accepter: (id) => api.patch(`/demandes-achat/${id}/accepter`),
  refuser: (id) => api.patch(`/demandes-achat/${id}/refuser`),
}
