import api from './axios'

export default {
  list: (params) => api.get('/demandes-achat', { params }),
  show: (id) => api.get(`/demandes-achat/${id}`),
  create: (data) => api.post('/demandes-achat', data),
  repondre: (id, data) => api.patch(`/demandes-achat/${id}/repondre`, data),
  uploadDocument: (id, data) => api.post(`/demandes-achat/${id}/documents`, data),
  messages: (id) => api.get(`/demandes-achat/${id}/messages`),
  envoyerMessage: (id, data) => api.post(`/demandes-achat/${id}/messages`, data),
  accepterDemande: (id) => api.post(`/demandes-achat/${id}/accepter`),
  refuserDemande: (id) => api.post(`/demandes-achat/${id}/refuser`),
}
