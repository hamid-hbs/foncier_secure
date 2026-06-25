import api from './axios'

export default {
  listDossiers: (params) => api.get('/documents', { params }),
  showDossier: (id) => api.get(`/documents/${id}`),
  downloadDocument: (id) => api.get(`/documents/${id}/download`, { responseType: 'blob' }),
  deleteDocument: (id) => api.delete(`/documents/${id}`),
}
