import api from './axios'

export default {
  list: (params) => api.get('/professionnels', { params }),
  show: (id) => api.get(`/professionnels/${id}`),
  giveReview: (id, data) => api.post(`/professionnels/${id}/avis`, data),
  updateProfile: (id, data) => api.put(`/professionnels/${id}`, data),
}
