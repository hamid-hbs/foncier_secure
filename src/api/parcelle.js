import api from './axios'

export default {
  list: (params) => api.get('/parcelles', { params }),
  show: (id) => api.get(`/parcelles/${id}`),
  store: (data) => api.post('/parcelles', data),
  update: (id, data) => api.put(`/parcelles/${id}`, data),
  updateStatut: (id, data) => api.patch(`/parcelles/${id}/statut`, data),
  uploadDocument: (id, data) => api.post(`/parcelles/${id}/documents`, data),
  deleteDocument: (parcelleId, documentId) => api.delete(`/parcelles/${parcelleId}/documents/${documentId}`),
}
