import api from './axios'

export default {
  dashboard: () => api.get('/admin/dashboard'),
  getUsers: (params) => api.get('/admin/users', { params }),
  getPendingUsers: () => api.get('/admin/users/pending'),
  approveUser: (id) => api.patch(`/admin/users/${id}/approve`),
  toggleUserStatus: (id) => api.patch(`/admin/users/${id}/toggle-status`),
  updateUserRole: (id, data) => api.patch(`/admin/users/${id}/role`, data),
  createProfessionnel: (data) => api.post('/admin/professionnels', data),
  createCommune: (data) => api.post('/admin/localisation/communes', data),
  updateCommune: (id, data) => api.put(`/admin/localisation/communes/${id}`, data),
  deleteCommune: (id) => api.delete(`/admin/localisation/communes/${id}`),
  createArrondissement: (data) => api.post('/admin/localisation/arrondissements', data),
  updateArrondissement: (id, data) => api.put(`/admin/localisation/arrondissements/${id}`, data),
  deleteArrondissement: (id) => api.delete(`/admin/localisation/arrondissements/${id}`),
  createQuartier: (data) => api.post('/admin/localisation/quartiers', data),
  updateQuartier: (id, data) => api.put(`/admin/localisation/quartiers/${id}`, data),
  deleteQuartier: (id) => api.delete(`/admin/localisation/quartiers/${id}`),
  blockchainLogs: () => api.get('/blockchain'),
  blockchainModule: (module, referenceId) => api.get(`/blockchain/module/${module}${referenceId ? `/${referenceId}` : ''}`),
}
