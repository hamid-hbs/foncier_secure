import api from './axios'

export default {
  listDossiers: () => api.get('/coffre/dossiers'),
  createDossier: (data) => api.post('/coffre/dossiers', data),
  showDossier: (id) => api.get(`/coffre/dossiers/${id}`),
  uploadDocument: (dossierId, data) => api.post(`/coffre/dossiers/${dossierId}/documents`, data),
  downloadDocument: (id) => api.get(`/coffre/documents/${id}/telecharger`, { responseType: 'blob' }),
  shareDocument: (id, data) => api.post(`/coffre/documents/${id}/partager`, data),
  verifyIntegrite: (id) => api.get(`/coffre/documents/${id}/integrite`),
}
