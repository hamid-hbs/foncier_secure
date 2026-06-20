import api from './axios'

export default {
  dashboard: () => api.get('/admin/dashboard'),
  getUsers: (params) => api.get('/admin/users', { params }),
  toggleUserStatus: (id) => api.patch(`/admin/users/${id}/toggle-status`),
  getRoleRequests: () => api.get('/admin/role-requests'),
  approveRoleRequest: (id, data) => api.patch(`/admin/role-requests/${id}`, data),
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
