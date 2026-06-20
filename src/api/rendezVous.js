import api from './axios'

export default {
  index: (transactionId) => api.get(`/transactions/${transactionId}/rendez-vous`),
  store: (transactionId, data) => api.post(`/transactions/${transactionId}/rendez-vous`, data),
  confirmer: (id, data) => api.post(`/rendez-vous/${id}/confirmer`, data),
  updateStatut: (id, data) => api.patch(`/rendez-vous/${id}`, data),
}
