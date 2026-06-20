import api from './axios'

export default {
  list: (params) => api.get('/support/tickets', { params }),
  show: (id) => api.get(`/support/tickets/${id}`),
  create: (data) => api.post('/support/tickets', data),
  repondre: (id, data) => api.post(`/support/tickets/${id}/repondre`, data),
  updateStatut: (id, data) => api.patch(`/support/tickets/${id}/statut`, data),
}
