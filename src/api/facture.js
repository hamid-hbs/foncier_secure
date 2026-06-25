import api from './axios'

export default {
  list: (params) => api.get('/factures', { params }),
  show: (id) => api.get(`/factures/${id}`),
  create: (data) => api.post('/factures', data),
  update: (id, data) => api.put(`/factures/${id}`, data),
  envoyer: (id) => api.post(`/factures/${id}/envoyer`),
  payer: (id) => api.post(`/factures/${id}/payer`),
  pdf: (id) => api.get(`/factures/${id}/pdf`, { responseType: 'blob' }),
}
