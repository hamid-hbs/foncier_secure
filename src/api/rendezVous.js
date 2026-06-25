import api from './axios'

export default {
  list: () => api.get('/rendez-vous'),
  create: (data) => api.post('/rendez-vous', data),
  confirmer: (id, data) => api.post(`/rendez-vous/${id}/confirmer`, data),
  updateStatut: (id, data) => api.patch(`/rendez-vous/${id}`, data),
}
