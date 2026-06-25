import api from './axios'

export default {
  list: (params) => api.get('/parcelles', { params }),
  show: (id) => api.get(`/parcelles/${id}`),
  store: (data) => api.post('/parcelles', data),
  update: (id, data) => api.put(`/parcelles/${id}`, data),
  updateStatut: (id, data) => api.patch(`/parcelles/${id}/statut`, data),
  uploadDocument: (id, data) => api.post(`/parcelles/${id}/documents`, data),
  deleteDocument: (id, docId) => api.delete(`/parcelles/${id}/documents/${docId}`),
  historique: (id) => api.get(`/parcelles/${id}/historique`),
  listPublic: (params) => api.get('/parcelles', { params }),
  getPublic: (id) => api.get(`/parcelles/${id}`),
}
