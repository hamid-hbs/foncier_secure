import api from './axios'

export default {
  list: (params) => api.get('/missions', { params }),
  show: (id) => api.get(`/missions/${id}`),
  create: (data) => api.post('/missions', data),
  accepter: (id) => api.post(`/missions/${id}/accepter`),
  refuser: (id) => api.post(`/missions/${id}/refuser`),
  rapport: (id, data) => api.post(`/missions/${id}/rapport`, data),
}
